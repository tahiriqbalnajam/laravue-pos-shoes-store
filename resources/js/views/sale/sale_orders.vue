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
          <el-button class="" type="primary" icon="el-icon-search" @click="handleFilter">
            {{ $t('table.search') }}
          </el-button>
        </el-col>
        <el-col :span="6" style="margin-right:20px">
          <strong>Total Sale:</strong><el-tag type="success" effect="dark" class="ttlstock">{{ totalsale }}</el-tag>
          <strong>Total Profit:</strong><el-tag type="success" effect="dark" class="ttlstock">{{ totalprofit }}</el-tag>
        </el-col>
      </el-row>
    </div>
    <!-- Table start here -->
    <el-table
      :data="list"
      stripe
      border
      style="width: 100%"
    >
      <el-table-column type="expand">
        <template slot-scope="props">
          <el-table :data="props.row.products" border stripe>
            <el-table-column label="Product" prop="product.name" />
            <el-table-column label="Qty" prop="quantity" />
            <el-table-column label="Bonus" prop="bonus" />
            <el-table-column label="Price" prop="price" />
            <el-table-column label="Disc1" prop="discount1" />
            <el-table-column label="Disc2" prop="discount2" />
            <el-table-column label="Total">
              <template slot-scope="props">
                {{ perProductSum(props.row.quantity, props.row.price, props.row.discount1, props.row.discount2) }}
              </template>
            </el-table-column>
          </el-table>
        </template>
      </el-table-column>
      <el-table-column
        label="Sale#"
        prop="id"
      />
      <el-table-column label="Date">
        <template slot-scope="props">
          <p>{{ props.row.created_at | dateformat }}</p>
        </template>
      </el-table-column>
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
    <pagination v-show="total>0" :total="total" :page.sync="query.page" :limit.sync="query.limit" @pagination="getList" />
  </div>
</template>
<script>
import Pagination from '@/components/Pagination';
import Resource from '@/api/resource';
import moment from 'moment';
import purchase_indexVue from '../purchase/purchase_index.vue';
const saleReso = new Resource('sale');
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
      totalsale: 0,
      totalprofit: 0,
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
      this.totalsale = data.sales.data.map(sale => sale.total).reduce((total, sale) => parseFloat(total) + parseFloat(sale), 0);
      let ppurchse = 0;
      let psale = 0;
      data.sales.data.forEach(sale => {
        ppurchse += sale.products.reduce((total, product) => total + (product.quantity * product.purchase_price), 0);
        psale += sale.products.reduce((total, product) => total + (product.quantity * product.price), 0);
      });
      this.totalprofit = psale - ppurchse;
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
