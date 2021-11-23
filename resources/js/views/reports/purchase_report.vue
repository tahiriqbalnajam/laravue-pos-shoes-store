<template>
  <div class="app-container">
    <div class="filter-container">
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
        v-model="query.supplier"
        clearable
        filterable
        remote
        reserve-keyword
        default-first-option
        placeholder="Find Supplier"
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
      <div class="cartheader">Total Purchase: </div>
      <el-tag
        type="success"
        class="tagheader"
        effect="dark"
      >
        {{ totalPurchase }}
      </el-tag>
    </div>
    <el-table
      :data="list"
      stripe
      style="width: 100%"
    >
      <el-table-column type="expand" border>
        <template slot-scope="props">
          <el-table :data="props.row.products" border stripe>
            <el-table-column label="Product" prop="product.name" />
            <el-table-column label="Qty" prop="quantity" />
            <el-table-column label="Price" prop="price" />
            <el-table-column label="Disc1" prop="discount1" />
            <el-table-column label="Disc2" prop="discount2" />
            <el-table-column label="Total">
              <template slot-scope="props">
                {{ perProductSum(props.row.quantity, props.row.price, props.row.discount1 , props.row.discount2) }}
              </template>
            </el-table-column>
          </el-table>
        </template>
      </el-table-column>
      <el-table-column
        label="ID"
        prop="id"
      />
      <el-table-column label="purch- Date">
        <template slot-scope="props">
          <p>{{ props.row.created_at | dateformat }}</p>
        </template>
      </el-table-column>
      <el-table-column
        label="Type"
        prop="purchase_type"
      />
      <el-table-column
        label="Supplier"
        prop="supplier.name"
      />
      <el-table-column
        label="Total"
        prop="subtotal"
      />
    </el-table>
    <pagination v-show="total>0" :total="total" :page.sync="query.page" :limit.sync="query.limit" @pagination="getList" />
  </div>
</template>
<script>
import Pagination from '@/components/Pagination';
import Resource from '@/api/resource';
import moment from 'moment';
const customer = new Resource('customer');
const purchReso = new Resource('purchase');
export default {
  name: '',
  components: { Pagination },
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
      search: '',
      totalPurchase: '',
      totalQuantity: 0,
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
        supplier: '',
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
      const { data } = await purchReso.list(this.query);
      this.list = data.purchases.data;
      this.total = data.purchases.total;
      console.log(data.total_purchase);
      this.totalPurchase = data.totalpurchase;
      data.purchases.data.forEach(purchase => {
        this.totalQuantity += purchase.products.reduce(function(total, product) {
          return parseFloat(total) + parseFloat(product.quantity);
        }, 0);
      });
      console.log(this.totalQuantity);
    },
    perProductSum(qty, price, disc1, disc2) {
      if (disc1 || disc2){
        const total = parseFloat(qty) * parseFloat(price);
        const discount = total * ((disc1 + disc2) / 100);
        return total - discount;
      } else {
        return qty * price;
      }
    },
    handleFilter() {
      this.getList();
    },
    async getCustomers(query) {
      this.loading = true;
      this.query.keyword = query;
      const { data } = await customer.list(this.query);
      this.customers = data.accounts.data;
      this.loading = false;
    },
  },
};
</script>
<style  scoped>
.cartheader {
  font-weight: bold;
  font-size: 17px;
  margin-right: 5px;
  display: inline-block;
  margin-left: 20px;
}
.tagheader {
  font-weight: bold;
  font-size: 17px;
}
</style>

