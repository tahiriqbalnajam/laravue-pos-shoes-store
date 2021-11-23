<template>
  <div class="app-container">
    <div class="filter-container">
      <el-row type="flex" class="row-bg" justify="space-between">
        <el-col :span="12">
          <el-date-picker
            v-model="query.daterange"
            type="daterange"
            align="right"
            unlink-panels
            range-separator="To"
            start-placeholder="Start date"
            end-placeholder="End date"
            :picker-options="pickerOptions"
            format="dd/MM/yyyy"
            value-format="yyyy-MM-dd"
            style="width:415px"
            :default-value="defaultDate"
          />
          <el-select
            v-model="query.customer"
            clearable
            filterable
            remote
            reserve-keyword
            default-first-option
            placeholder="Find Customer"
            :remote-method="getCustomers"
            :loading="loading"
          >
            <el-option
              v-for="customer in customers"
              :key="customer.id"
              :label="customer.name"
              :value="customer.id"
            />
          </el-select>
          <el-button class="" type="primary" icon="el-icon-search" @click="handleFilter">
            {{ $t('table.search') }}
          </el-button>
        </el-col>
        <el-col :span="12" style="text-align:right">
        <!--<el-col :xs="8" :sm="6" :md="4" :lg="8" :xl="8">-->
          <strong v-if="totalsale">Sale:</strong><el-tag v-if="totalsale" type="warning" effect="dark" class="ttlstock">{{ totalsale }}</el-tag>
          <strong v-if="salereturn">Return:</strong><el-tag v-if="salereturn" type="danger" effect="dark" class="ttlstock">{{ salereturn }}</el-tag>
          <strong v-if="grandtotal">Total:</strong><el-tag v-if="grandtotal" type="primary" effect="dark" class="ttlstock">{{ grandtotal }}</el-tag>
          <strong v-if="totalprofit">Total Profit:</strong><el-tag v-if="totalprofit" type="success" effect="dark" class="ttlstock">{{ totalprofit }}</el-tag>
        </el-col>
      </el-row>
    </div>
    <!-- Table start here -->
    <el-table
      :data="list"
      stripe
      border
      style="width: 100%"
      size="mini"
    >
      <el-table-column type="expand">
        <template slot-scope="props">
          <el-table :data="props.row.products" border stripe>
            <el-table-column label="Product" prop="product.name" />
            <el-table-column label="Qty" prop="quantity" />
            <el-table-column label="Bonus" prop="bonus" />
            <el-table-column label="Price" prop="price" />
            <el-table-column label="Disc" prop="discount1" />
            <el-table-column label="Bill Discount" prop="bill_discount" />
            <el-table-column label="Total">
              <template slot-scope="props">
                {{ perProductSum(props.row.quantity, props.row.price, props.row.discount1, props.row.bill_discount, props.row.discount_type) }}
              </template>
            </el-table-column>
          </el-table>
        </template>
      </el-table-column>
      <el-table-column
        label="Sale#"
      >
        <template slot-scope="scope">
          <el-button prop="id" type="text" size="small" @click="print(scope.row.id)">{{ scope.row.id }}</el-button>
        </template>
      </el-table-column>
      <el-table-column label="Date">
        <template slot-scope="props">
          <p>{{ props.row.created_at | dateformat }}</p>
        </template>
      </el-table-column>
      <el-table-column
        label="Type"
        prop="type"
      />
      <el-table-column
        label="Customer"
        prop="customer.name"
      />
      <el-table-column
        label="Total"
        prop="total"
      />
      <el-table-column
        label="Quantity"
        prop="quantity"
      />
      <el-table-column
        label="Items"
        prop="total_items"
      />
      <el-table-column
        label="Discount"
        prop="discount"
      />
    </el-table>
    <el-dialog title="Print Invoice" :visible.sync="showprint">
      <printinvoice :invoiceid="invoiceid" :v-if="invoiceid" />
    </el-dialog>
    <pagination v-show="total>0" :total="total" :page.sync="query.page" :limit.sync="query.limit" @pagination="getList" />
  </div>
</template>
<script>
import Pagination from '@/components/Pagination';
import Resource from '@/api/resource';
import moment from 'moment';
import purchase_indexVue from '../purchase/purchase_index.vue';
const customer = new Resource('customer');
const saleReso = new Resource('sale');
import Printinvoice from '../sale/print';
export default {
  name: '',
  components: { Pagination, Printinvoice },
  directives: { },
  filters: {
    dateformat: (date) => {
      return (!date) ? '' : moment(date).format('DD MMM, YYYY');
    },
  },
  data() {
    return {
      defaultDate: new Date().toJSON().slice(0, 10).replace(/-/g, '/'),
      list: null,
      totalsale: 0,
      salereturn: 0,
      totalprofit: 0,
      profit_sale: [],
      return_sale: [],
      invoiceid: null,
      customers: [],
      grandtotal: 0,
      showprint: false,
      search: '',
      total: 0,
      loading: true,
      downloading: false,
      pickerOptions: {
        shortcuts: [{
          text: 'Last week',
          onClick(picker) {
            const end = new Date();
            const start = new Date();
            start.setTime(start.getTime() - 3600 * 1000 * 24 * 7);
            picker.$emit('pick', [start, end]);
          },
        }, {
          text: 'Last month',
          onClick(picker) {
            const end = new Date();
            const start = new Date();
            start.setTime(start.getTime() - 3600 * 1000 * 24 * 30);
            picker.$emit('pick', [start, end]);
          },
        }, {
          text: 'Last 3 months',
          onClick(picker) {
            const end = new Date();
            const start = new Date();
            start.setTime(start.getTime() - 3600 * 1000 * 24 * 90);
            picker.$emit('pick', [start, end]);
          },
        }],
      },
      query: {
        page: 1,
        limit: 15,
        keyword: '',
        daterange: [this.todayDate(), this.todayDate()],
        role: '',
        customer: '',
      },
    };
  },
  computed: {
  },
  created() {
    this.getList();
  },
  methods: {
    todayDate() {
      var today = new Date();
      var dd = String(today.getDate()).padStart(2, '0');
      var mm = String(today.getMonth() + 1).padStart(2, '0');
      var yyyy = today.getFullYear();
      today = yyyy + '-' + mm + '-' + dd;
      return today;
    },
    async getList() {
      const { data } = await saleReso.list(this.query);
      this.list = data.sales.data;
      this.total = data.sales.total;
      this.salereturn = data.total_sale_return[0].total_price;
      this.totalsale = data.total_sale[0].total_price;
      this.grandtotal = (data.total_sale[0].total_price - data.total_sale_return[0].total_price).toFixed(2);
      const ppurchse = data.total_sale[0].total_purchase;
      let psale = 0;
      data.sales.data.forEach(sale => {
        psale += sale.products.reduce((total, product) => total + (product.quantity * product.price), 0);
      });
      this.profit_sale = data.total_sale_profit;
      this.return_sale = data.total_return_profit;
      this.totalprofit = this.profit_sale.map(element => parseInt(element.total_sale_profit)).reduce((a, b) => a + b, 0);
      this.return_sale = this.return_sale.map(element => parseInt(element.total_return_profit)).reduce((a, b) => a + b, 0);
      this.totalprofit = this.totalprofit - this.return_sale;
      this.totalprofit.toFixed(2);
    },
    async getCustomers(query) {
      this.loading = true;
      this.query.keyword = query;
      const { data } = await customer.list(this.query);
      this.customers = data.accounts.data;
      this.loading = false;
    },
    perProductSum(qty, price, disc1, disc2, discount_type) {
      if (disc1 || disc2){
       let discount = 0;
        const total = parseFloat(qty) * parseFloat(price);
        if(discount_type === 'rs'){
          discount = parseFloat(disc1) + parseFloat(disc2);
        }
        else{
          discount = total * ((parseFloat(disc1) + parseFloat(disc2)) / 100);
        }
        //const discount = total * ((parseFloat(disc1) + parseFloat(disc2)) / 100);
        console.log(discount);
        return total - discount;
      } else {
        return qty * price;
      }
    },
    handleFilter() {
      this.getList();
    },
    print(id){
      this.invoiceid = id;
      this.showprint = true;
    },
  },
};
</script>
<style  scoped>
  .ttlstock {
    font-weight: bold;
    font-size: 18px;
    margin-left: 10px;
  }
</style>
