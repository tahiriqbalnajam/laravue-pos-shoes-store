<template>
  <div class="app-container">
    <el-row :gutter="20">
      <el-col :span="12">
        <el-card shadow="always">
          <div slot="header" class="clearfix">
            <h2>Return Item</h2>
          </div>
          <el-row :gutter="20">
            <el-col :span="15">
              <el-select
                ref="selectedproduct"
                v-model="exchange.from"
                clearable
                filterable
                remote
                reserve-keyword
                default-first-option
                placeholder="Start typing or scaning for product"
                :remote-method="getProducts"
                :loading="loading"
                label="Select Product"
                @change="showProductData"
                @keyup.native.enter="(exchange.from != '') ? focusInput('selectproduct') : ''"
              >
                <el-option
                  v-for="product in products"
                  :key="product.id"
                  :label="(product.size) ? product.name + ' (' + product.size + ')' : product.name"
                  :value="product.id"
                >
                  <span style="float: left">
                    {{ product.name }} 
                    <span v-if="product.size"> Size: <b style="color:#13ce66">{{ product.size }}</b></span> 
                    <span v-if="product.color"> - Color: <b style="color:#ff4949">{{ product.color }}</b></span> 
                    <span> - Brand: <b style="color:#1890ff">{{ product.manufacturer.name }}</b></span>
                  </span>
                  <span style="float: left; margin-left:50px; color: #8492a6; font-size: 13px">Stock: {{ parseInt(product.purchase)-parseInt(product.sale) }}</span>
                </el-option>
              </el-select>
            </el-col>
            <el-col v-show="productfrom.price.length" :span="9" class="exchange-price">Price: {{ productfrom.price }}</el-col>
          </el-row>
        </el-card>
      </el-col>
      <el-col :span="12">
        <el-card shadow="always">
          <div slot="header" class="clearfix">
            <h2>Exchange With</h2>
          </div>
          <el-row :gutter="20">
            <el-col :span="15">
              <el-select
                ref="selectedproduct"
                v-model="exchange.to"
                clearable
                filterable
                remote
                reserve-keyword
                default-first-option
                placeholder="Start typing or scaning for product"
                :remote-method="getProducts"
                :loading="loading"
                label="Select Product"
                @change="showProductToData"
                @keyup.native.enter="(exchange.to != '') ? focusInput('selectproduct') : ''"
              >
                <el-option
                  v-for="product in products"
                  :key="product.id"
                  :label="(product.size) ? product.name + ' (' + product.size + ')' : product.name"
                  :value="product.id"
                >
                  <span style="float: left">
                    {{ product.name }} 
                    <span v-if="product.size"> Size: <b style="color:#13ce66">{{ product.size }}</b></span> 
                    <span v-if="product.color"> - Color: <b style="color:#ff4949">{{ product.color }}</b></span> 
                    <span> - Brand: <b style="color:#1890ff">{{ product.manufacturer.name }}</b></span>
                  </span>
                  <span style="float: left; margin-left:50px; color: #8492a6; font-size: 13px">Stock: {{ parseInt(product.purchase)-parseInt(product.sale) }}</span>
                </el-option>
              </el-select>
            </el-col>
            <el-col v-show="productto.price.length" :span="9" class="exchange-price">Price: {{ productto.price }}</el-col>
          </el-row>    
        </el-card>
      </el-col>
      <el-col :span="24" style="margin-top:20px">
        <el-card shadow="always">
          <el-button
            :disabled="productfrom.price == '' || productfrom.price != productto.price" 
            type="primary"
            :loading="loading"
            @click="exchangeNow()"
          >Exchange Now</el-button>
        </el-card>
      </el-col>
    </el-row>
  </div>
</template>
<script>
import Resource from '@/api/resource';
import {exchangeProducts} from '@/api/sale';
const prod = new Resource('product');
export default {
  name: '',
  components: { },
  directives: { },
  data() {
    return {
      loading: false,
      products: [],
      productfrom: {
        price: '',
      },
      productto: {
        price: '',
      },
      exchange: {
        from: '',
        to: '',
      },
      restexchange: {
        from: '',
        to: '',
      },
      query: {
        page: 1,
        limit: 15,
        keyword: '',
        role: '',
      },
    };
  },
  computed: {
  },
  created() {
  },
  methods: {
    debounceInput: _.debounce(function (e) {
      this.getList();
    }, 500),
    async getProducts(query) {
      this.loading = true;
      this.query.keyword = query;
      this.query.select = 'productonly';
      const { data } = await prod.list(this.query);
      this.products = data.products.data;
      this.loading = false;
    },
    showProductData(index, $event){
      let selectedproduct = this.products.filter(product => product.id === index);
      selectedproduct = selectedproduct[0];
      this.productfrom.price = selectedproduct.sale_price;
    },
    showProductToData(index, $event){
      let selectedproduct = this.products.filter(product => product.id === index);
      selectedproduct = selectedproduct[0];
      this.productto.price = selectedproduct.sale_price;
    },
    async exchangeNow() {
      if (this.productfrom.price == '' || this.productto.price == '' || this.productfrom.price !=  this.productto.price){
        this.$message({
          message: 'Select product first and prices must be same',
          type: 'error',
        });
      } else {
        this.loading = true;
        const { data } = await exchangeProducts(this.exchange).then(result => {
          this.$message({
            message: 'Products exchanged successfully.',
            type: 'success',
          });
          this.products = [];
          this.exchange = { ...this.restexchange };
          this.loading = false;
        });
      }
    },
  },
};
</script>
<style  scoped>
  .exchange-price {
      padding-top: 11px;
  }
</style>
