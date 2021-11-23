<template>
  <div class="app-container">
    <el-header>Add Product</el-header>
    <el-form ref="productform" :rules="rules" :model="product" label-width="110px" size="mini" :loading="loading">
      <el-row :gutter="10">
        <el-col :span="8">
          <el-card class="box-card">
            <div slot="header" class="clearfix">
              <span>Product Info</span>
            </div>
            <el-form-item label="Code#">
              <el-input v-model="product.code" clearable />
            </el-form-item>
            <el-form-item label="Name" prop="name">
              <el-input v-model="product.name" clearable />
            </el-form-item>
            <el-form-item label="Category" prop="name">
              <el-select
                v-model="product.category_id"
                filterable
                reserve-keyword
                placeholder="Search Category"
                :remote-method="getlist"
                :loading="loading"
              >
                <el-option
                  v-for="cat in categories"
                  :key="cat.id"
                  :label="cat.title"
                  :value="cat.id"
                />
              </el-select>
              <el-button @click="showCatPopup()"><svg-icon icon-class="add" /></el-button>
            </el-form-item>
            <el-form-item label="Manufacturer" prop="name">
              <el-select
                v-model="product.manufacturer"
                filterable
                reserve-keyword
                placeholder="Search manufacturer"
                :loading="manfucloading"
              >
                <el-option
                  v-for="manfucaturer in manfuacturers"
                  :key="manfucaturer.id"
                  :label="manfucaturer.name"
                  :value="manfucaturer.id"
                />
              </el-select>
              <el-button @click="manufacPopup()"><svg-icon icon-class="add" /></el-button>
            </el-form-item>
            <el-form-item label="UOM" prop="name">
              <el-select
                v-model="product.unit"
                filterable
                reserve-keyword
                placeholder="Search Unit"
                :loading="unitsloading"
              >
                <el-option
                  v-for="unit in units"
                  :key="unit.id"
                  :label="unit.name"
                  :value="unit.id"
                />
              </el-select>
              <el-button @click="unitsPopup()"><svg-icon icon-class="add" /></el-button>
            </el-form-item>
            <el-form-item>
              <el-button type="primary" :loading="loading" @click="onSubmit('productform')">Create</el-button>
              <el-button>Cancel</el-button>
            </el-form-item>
          </el-card>
        </el-col>
        <el-col :span="8">
          <el-card class="box-card">
            <div slot="header" class="clearfix">
              <span>Price</span>
            </div>
            <el-form-item label="Purchase Price" prop="purchase_price">
              <el-input v-model="product.purchase_price" type="number" step="any" min="0" />
            </el-form-item>
            <el-form-item label="Sale Price" prop="sale_price">
              <el-input v-model="product.sale_price" type="number" step="any" min="0" />
            </el-form-item>
            <el-form-item label="Wholesale Price">
              <el-input v-model="product.wholesale_price" type="money" />
            </el-form-item>
          </el-card>
        </el-col>
        <el-col :span="8">
          <el-card class="box-card">
            <div slot="header" class="clearfix">
              <span>Inventory</span>
            </div>
            <el-form-item label="Quantity" prop="quantity">
              <el-input v-model="product.quantity" type="number" step="any" />
            </el-form-item>
            <el-form-item label="Reorder Level">
              <el-input v-model="product.reorder" clearable />
            </el-form-item>
          </el-card>
        </el-col>
      </el-row>
    </el-form>
    <el-dialog title="Add Category" :visible.sync="showcategorypopup">
      <el-form :model="category">
        <el-form-item label="Category Title" label-width="100">
          <el-input v-model="category.title" autocomplete="off" />
        </el-form-item>
      </el-form>
      <span slot="footer" class="dialog-footer">
        <el-button @click="showcategorypopup = false">Cancel</el-button>
        <el-button type="primary" @click="addCategory()">Confirm</el-button>
      </span>
    </el-dialog>
    <el-dialog title="Add Manufacturer" :visible.sync="showmanufacpopup">
      <el-form :model="category">
        <el-form-item label="Manfucturer Name" label-width="100">
          <el-input v-model="manufacturer.name" autocomplete="off" />
        </el-form-item>
      </el-form>
      <span slot="footer" class="dialog-footer">
        <el-button @click="showmanufacpopup = false">Cancel</el-button>
        <el-button type="primary" @click="addManufac()">Confirm</el-button>
      </span>
    </el-dialog>
    <el-dialog title="Add Units" :visible.sync="showunitspopup">
      <el-form :model="category">
        <el-form-item label="Units Name" label-width="100">
          <el-input v-model="unitsofmeasure.name" autocomplete="off" />
        </el-form-item>
      </el-form>
      <span slot="footer" class="dialog-footer">
        <el-button @click="showunitspopup = false">Cancel</el-button>
        <el-button type="primary" @click="addUnit()">Confirm</el-button>
      </span>
    </el-dialog>
  </div>
</template>
<script>
import Resource from '@/api/resource';
const addPro = new Resource('product');
const category = new Resource('category');
const manufac = new Resource('manufacturer');
const unit = new Resource('unitsmeasure');
export default {
  name: '',
  components: { },
  directives: {},
  loading: false,
  data() {
    return {
      list: null,
      total: 0,
      loading: false,
      manfucloading: false,
      unitsloading: false,
      manfuacturers: [],
      units: [],
      downloading: false,
      showcategorypopup: false,
      showmanufacpopup: false,
      showunitspopup: false,
      query: {
        page: 1,
        limit: 15,
        keyword: '',
        role: '',
      },
      product: {
        id: '',
        name: '',
        uid: '',
        purchase_price: '',
        sale_price: '',
        wholesale_price: '',
        quantity: 1,
        reorder: 1,
        category_id: '',
        manufacturer: '',
        unit: '',
      },
      category: {
        title: '',
      },
      manufacturer: {
        name: '',
      },
      unitsofmeasure: {
        name: '',
      },
      categories: null,
      rules: {
        name: [
          { required: true, message: 'Please enter product name', trigger: 'blur' },
          { min: 3, max: 50, message: 'Length should be 3 to 50', trigger: 'blur' },
        ],
        purchase_price: [
          { required: true, message: 'Please enter product name', trigger: 'blur' },
        ],
        sale_price: [
          { required: true, message: 'Please enter product name', trigger: 'blur' },
        ],
        quantity: [
          { required: true, message: 'Please enter product name', trigger: 'blur' },
        ],
      },
    };
  },
  computed: {},
  created() {
    if (this.$route.params && this.$route.params.id) {
      this.product.id = this.$route.params.id;
      this.showinventory = false;
      this.getProduct(this.product.id);
    }
    this.getlist();
    this.getManufacturer();
    this.getunits();
  },
  methods: {
    getProduct(id) {
      const { data } = addPro.get(id).then(result => {
        this.product = result;
      });
    },
    async getlist(query) {
      this.loading = true;
      const { data } = await category.list(this.query);
      this.categories = data.categories;
      this.loading = false;
    },
    async getManufacturer(query) {
      this.manfucloading = true;
      this.query.keyword = query;
      const { data } = await manufac.list(this.query);
      this.manfuacturers = data.manfuacturers;
      this.manfucloading = false;
    },
    async getunits(query) {
      this.unitsloading = true;
      this.query.keyword = query;
      const { data } = await unit.list(this.query);
      this.units = data.units;
      this.unitsloading = false;
    },
    showCatPopup() {
      this.showcategorypopup = true;
    },
    manufacPopup() {
      this.showmanufacpopup = true;
    },
    unitsPopup() {
      this.showunitspopup = true;
    },
    async addCategory() {
      const { data } = category.store(this.category).then(result => {
        this.product.category_id = result.id;
        this.$message({
          message: 'Category added Successfully.',
          type: 'success',
        });
        this.showcategorypopup = false;
        this.getlist();
      });
    },
    async addManufac() {
      if (this.manufacturer.name.length) {
        const { data } = manufac.store(this.manufacturer).then(result => {
          this.manfuacturers = [result];
          this.product.manufacturer = result.id;
          this.$message({
            message: 'Manufacturer added Successfully.',
            type: 'success',
          });
          this.showpopup = false;
          this.getManufacturer();
        });
      }
    },
    async addUnit() {
      if (this.unitsofmeasure.name.length) {
        const { data } = unit.store(this.unitsofmeasure).then(result => {
          this.product.unit = result.id;
          this.$message({
            message: 'unit added Successfully.',
            type: 'success',
          });
          this.showunitspopup = false;
          this.getunits();
          this.unitsofmeasure.name = '';
        });
      }
    },
    onSubmit(formName) {
      this.$refs[formName].validate((valid) => {
        if (valid) {
          this.loading = true;
          const { data } = addPro.store(this.product).then(result => {
            this.product = {};
            this.loading = false;
            this.$message({
              message: 'Added Successfully.',
              type: 'success',
            });
          }).catch(() => {
            this.loading = false;
          });
        } else {
          console.log('error submit!!');
          return false;
        }
      });
    },
  },
};
</script>
<style scoped>
  .el-form-item__label {
    font-size: 13px !important;
  }
</style>
