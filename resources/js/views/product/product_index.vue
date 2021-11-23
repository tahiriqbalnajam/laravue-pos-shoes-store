<template>
  <div class="app-container">
    <div class="filter-container">
      <el-input v-model="query.keyword" class="filter-item" placeholder="Type to search" style="width:150px" @keyup.native="search_data" />
      <el-select
        v-model="query.manufact_id"
        class="filter-item"
        clearable
        filterable
        remote
        reserve-keyword
        default-first-option
        placeholder="Find By Manufacturer"
        :remote-method="getManufacture"
        :loading="loading"
      >
        <el-option
          v-for="customer in customers"
          :key="customer.id"
          :label="customer.name"
          :value="customer.id"
        />
      </el-select>
      <el-button class="filter-item" type="primary" icon="el-icon-search" @click="getList">
        Search
      </el-button>
      <el-dropdown class="filter-item" @command="handleCommand">
        <el-button type="success">
          Add Product <i class="el-icon-arrow-down el-icon--right" />
        </el-button>
        <el-dropdown-menu slot="dropdown">
          <el-dropdown-item command="simple">Simple Product</el-dropdown-item>
          <el-dropdown-item command="variable">Variable Product</el-dropdown-item>
        </el-dropdown-menu>
      </el-dropdown>
      <el-button class="filter-item" style="margin-left: 10px;" type="danger" icon="el-icon-magic-stick" @click="handleImport">
        Import Products
      </el-button>
    </div>
    <el-table :data="list" style="width: 100%" :row-class-name="tableRowClassName">
      <el-table-column label="Code" prop="code" />
      <el-table-column label="Name" prop="name" sortable />
      <el-table-column label="Category" prop="category.title" />
      <el-table-column label="Color" prop="color" />
      <el-table-column label="Size" prop="size" />
      <el-table-column label="P Price" prop="purchase_price" v-if="checkRole(['admin'])"/>
      <el-table-column label="S Price" prop="sale_price" />
      <el-table-column label="W Price" prop="wholesale_price" />
      <el-table-column label="Stock">
        <template slot-scope="scope">
          {{ scope.row.purchase - scope.row.sale }}
        </template>
      </el-table-column>
      <el-table-column label="Status">
        <template slot-scope="scope">
          <el-badge is-dot class="badge" :type="scope.row.status == 'enable' ? 'primary' : ''">{{ scope.row.status }}</el-badge>
        </template>
      </el-table-column>
      <el-table-column align="right" fixed="right" width="180">
        <template slot="header" slot-scope="scope">
          <el-input ref="search" v-model="query.keyword" size="mini" placeholder="Type to search" @keyup.native="search_data" />
        </template>
        <template slot-scope="scope">
          <el-tooltip content="Stock" placement="top">
            <el-button
              size="mini"
              @click="handleStock(scope.row.id, scope.row.name)"
            ><svg-icon icon-class="stock" /></el-button>
          </el-tooltip>
          <el-tooltip content="Edit Product" placement="top" v-if="checkRole(['admin'])">
            <el-button
              size="mini"
              type="danger"
              @click="handleEdit(scope.row.id, scope.row.name)"
            ><svg-icon icon-class="edit" />
            </el-button>
          </el-tooltip>
        </template>
      </el-table-column>
    </el-table>
    <pagination v-show="total>0" :total="total" :page.sync="query.page" :limit.sync="query.limit" @pagination="getList" />
    <el-drawer title="Manage Stock" :visible.sync="showstock" size="70%" class="idldrawer">
      <el-row type="flex" class="row-bg" justify="space-between">
        <el-col :span="12" v-if="checkRole(['admin'])">
          <el-form ref="stockForm" :inline="true" :rules="stockrules" :model="stock" size="mini" :loading="stock_loading">
            <el-form-item label="Quantity" prop="stockquantity">
              <el-input-number v-model="stock.quantity" />
            </el-form-item>
            <el-form-item label="Remarks" prop="remarks">
              <el-input v-model="stock.remarks" clearable />
            </el-form-item>
            <el-form-item>
              <el-button type="primary" @click="addStock('stockForm')">Add Stock</el-button>
            </el-form-item>
          </el-form>
        </el-col>
        <el-col :span="6" style="margin-right:20px"><strong>Current Stock</strong><el-tag type="success" effect="dark" class="ttlstock">{{ totalstock }}</el-tag></el-col>
      </el-row>

      <el-divider />
      <el-table :data="stocklist" style="width: 100%" height="600">
        <el-table-column label="Data Tracking">
          <template slot-scope="scope">
            {{ changeDate(scope.row.created_at) }}
          </template>
        </el-table-column>
        <el-table-column label="Employee" prop="user_id" />
        <el-table-column label="In/Out Qty" prop="quantity" />
        <el-table-column label="Inv. Type" prop="inventory_type" />
        <el-table-column label="Remarks" prop="remarks" />
      </el-table>
      <pagination v-show="query.total>0" :total="queryy.total" :page.sync="queryy.page" :limit.sync="queryy.limit" @pagination="handleStock(stock.product_id)" />
    </el-drawer>
  </div>
</template>
<script>
import Pagination from '@/components/Pagination';
import checkRole from '@/utils/role';
import moment from 'moment';
import Resource from '@/api/resource';
import { getStock } from '@/api/product';
import { addStock } from '@/api/product';
import { getStockbyid } from '@/api/product';
const customer = new Resource('manufacturer');
const addPro = new Resource('product');
export default {
  name: '',
  components: { Pagination },
  directives: { },
  data() {
    return {
      list: null,
      total: 0,
      loading: true,
      customers: [],
      search: '',
      showstock: false,
      downloading: false,
      query: {
        page: 1,
        limit: 10,
        id: '',
        keyword: '',
        role: '',
        total: 0,
        manufact_id: '',
      },
      queryy: {
        page: 1,
        limit: 10,
        id: '',
        keyword: '',
        role: '',
        total: 0,
      },
      stock_loading: false,
      stocklist: null,
      stock: {
        outlet_id: '1',
        product_id: '',
        quantity: '',
        remarks: '',
      },
      totalstock: '',
      stockrules: {
      },
      drawer: {
        direction: 'left',
      },
    };
  },
  computed: {
  },
  created() {
    this.getList();
  },
  methods: {
    checkRole,
    handleCommand(command) {
      if (command === 'simple'){
        this.$router.push({ path: 'create' });
      }
      if (command === 'variable') {
        this.$router.push({ path: 'createvariable' });
      }
    },
    tableRowClassName({ row, rowIndex }) {
      const stock = row.purchase - row.sale;
      if (stock < row.reorder_level) {
        return 'warning-row';
      }
      return '';
    },
    async getList() {
      this.list = [];
      const { data } = await addPro.list(this.query);
      this.list = data.products.data;
      this.total = data.products.total;
      console.log(this.total);
    },
    handleCreate() {
      this.$router.push({ path: 'create' });
    },
    handleEdit(id, name) {
      this.$router.push({ path: 'edit/' + id });
    },
    handleImport() {
      this.$router.push({ path: 'import' });
    },
    search_data: _.debounce(function(e) {
      this.getList();
    }, 500),
    async handleStock(id, name = '') {
      this.showstock = true;
      this.stock.product_id = id;
      this.query.id = id;
      const { data } = await getStock(this.query);
      this.stocklist = data.stock.data;
      this.queryy.total = data.stock.total;
      this.totalstock = data.total_stock;
    },
    async addStock(formName) {
      if (this.stock.quantity) {
        await addStock(this.stock);
        this.stock.quantity = 0;
        this.stock.remarks = '';
        const { data } = await getStockbyid(this.stock.product_id);
        this.stocklist = [];
        this.stocklist = data.stock.data;
        console.log(this.stocklist);
      } else {
        this.$message({
          type: 'error',
          message: 'First enter quantity.',
        });
      }
    },
    async getManufacture(query) {
      this.loading = true;
      this.query.keyword = query;
      const { data } = await customer.list(this.query);
      this.customers = data.manfuacturers;
      this.loading = false;
    },
    handleFilter(){
      console.log(this.manufact_id);
    },
    changeDate(date) {
      return moment(date).format('DD MMM, YYYY');
    },
  },
};
</script>
<style>
  .el-drawer__body {
    padding:0 20px;
  }
  .badge {
    top:4px;
  }
  .ttlstock {
    font-weight: bold;
    font-size: 18px;
    margin-left: 10px;
  }
  .el-table .warning-row {
    background: #f5f4f4;
  }
  .success-row {
    background: #f0f9eb;
  }
</style>
