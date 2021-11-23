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
      <el-button class="" type="primary" icon="el-icon-search" @click="handleFilter">
        {{ $t('table.search') }}
      </el-button>
      <el-button class="filter-item" style="margin-left: 10px;" type="success" icon="el-icon-plus" @click="editnow = true">
        New Purchase
      </el-button>
      <div class="custom-head-class">
        <strong v-if="total_available_bill">Total Bill:</strong><el-tag v-if="total_available_bill" type="success" effect="dark" class="ttlstock">{{ total_available_bill }}</el-tag>
        <strong v-if="total_available_weight">Total Weight:</strong><el-tag v-if="total_available_weight" type="primary" effect="dark" class="ttlstock">{{ total_available_weight }}</el-tag>
        <strong v-if="total_net_weight">Net Weight:</strong><el-tag v-if="total_net_weight" type="info" effect="dark" class="ttlstock">{{ total_net_weight }}</el-tag>
        <strong v-if="total_available_weight">Average Rate:</strong><el-tag v-if="total_available_weight" type="warning" effect="dark" class="ttlstock">{{ total_avg_rate }}</el-tag>
      </div>
    </div>
    <el-table
      :data="wheatlist"
      style="width: 100%"
    >
      <el-table-column label="ID" prop="id" />
      <el-table-column label="Customer Name" prop="customer_name" />
      <el-table-column label="phone" prop="phone" />
      <el-table-column label="Kaat" prop="Kaat" />
      <el-table-column label="Quality Kaat" prop="quality_kaat" />
      <el-table-column label="Total Weight" prop="total_weight" />
      <el-table-column label="Net Weight" prop="net_weight" />
      <el-table-column label="Rate" prop="rate" />
      <el-table-column label="Total Bill" prop="total_bill" />
      <el-table-column align="right">
        <template slot="header" slot-scope="scope">
          <el-input ref="search" v-model="query.keyword" size="mini" placeholder="Type to search" @input="debounceInput" />
        </template>
        <template slot-scope="scope">
          <el-button
            size="mini"
            @click="handleEdit(scope.row.id, scope.row.name)"
          >Edit</el-button>
          <el-button
            size="mini"
            type="danger"
            @click="handleDelete(scope.row.id, scope.row.name)"
          >Delete</el-button>
        </template>
      </el-table-column>
    </el-table>
    <pagination v-show="total>0" :total="total" :page.sync="query.page" :limit.sync="query.limit" @pagination="getList" />
    <el-drawer
      ref="drawer"
      title="Edit Record"
      :visible.sync="editnow"
      direction="rtl"
      custom-class="demo-drawer"
      size="90%"
    >
      <div class="demo-drawer__content">
        <template>
          <el-row :gutter="6">
            <el-col :span="5">
              <label>Rate</label>
            </el-col>
            <el-col :span="7">
              <el-input-number v-model="rate" :min="1" :max="5000" size="mini" />
            </el-col>
            <el-col :span="8">
              <el-button type="success" @click="handleChange()"> Change</el-button>
            </el-col>
          </el-row>
        </template>
        <el-divider />
        <el-form :model="wheat">
          <el-row :gutter="10">
            <el-col :span="8">
              <el-input v-model="wheat.customer_name" placeholder="Customer Name" prefix-icon="el-icon-user-solid" />
            </el-col>
            <el-col :span="8">
              <el-input v-model="wheat.phone" placeholder="Phone" prefix-icon="el-icon-phone" />
            </el-col>
            <el-col :span="8">
              <el-input v-model="wheat.address" placeholder="Address" prefix-icon="el-icon-coordinate" />
            </el-col>
          </el-row>
          <el-divider />
          <el-row :gutter="20">
            <el-col :span="10">
              <el-input ref="weight" v-model="addtocart.bags" :min="1" placeholder="Bags" prefix-icon="el-icon-shopping-bag-2" @keyup.native.enter="focusInput('bags')" />
            </el-col>
            <el-col :span="10">
              <el-input ref="bags" v-model="addtocart.weight" :min="1" placeholder="Weight" prefix-icon="el-icon-s-data" @keyup.native.enter="focusInput('weight');addRow();" />
            </el-col>
            <el-col :span="4">
              <el-button type="success" @click="addRow()"> Add to purchase</el-button>
            </el-col>
          </el-row>
          <el-table
            style="margin-top:20px; margin-bottom:20px"
            :data="wheat.cart"
            empty-text="No bag, weight added"
            size="mini"
            max-height="200"
            :summary-method="getSummaries"
            show-summary
          >
            <el-table-column type="index" width="80" />
            <el-table-column label="Bags" prop="bags" />
            <el-table-column label="Weight" prop="weight" />
            <el-table-column
              fixed="right"
              label="Operations"
              width="120"
            >
              <template slot-scope="scope">
                <el-button
                  type="text"
                  size="small"
                  @click.native.prevent="deleteRow(scope.row.bags, scope.row.weight)"
                >
                  Remove
                </el-button>
              </template>
            </el-table-column>
          </el-table>
          <el-row :gutter="20">
            <el-col :span="8">
              <el-input v-model="wheat.kaat">
                <template slot="prepend">Kaat</template>
              </el-input>
            </el-col>
            <el-col :span="8">
              <el-input v-model="wheat.quality_kaat" @keyup.native.enter="make_total()">
                <template slot="prepend">Quality Kaat</template>
              </el-input>
            </el-col>
            <el-col :span="8">
              <el-input v-model="wheat.total_bill">
                <template slot="prepend">Total</template>
              </el-input>
            </el-col>
          </el-row>
        </el-form>
        <div class="demo-drawer__footer" style="margin-top:10px">
          <el-button @click="editnow = false;clearForm();">Cancel</el-button>
          <el-button :disabled="wheat.cart.length <= 0" type="primary" :loading="loading" @click="onSubmit">{{ loading ? 'Submitting ...' : 'Submit' }}</el-button>
        </div>
      </div>
    </el-drawer>
    <el-dialog title="Print Invoice" :visible.sync="showprint">
      <printinvoice :invoiceid="invoiceid" :v-if="invoiceid" :paidamount="amountpay" />
    </el-dialog>
  </div>
</template>
<script>
import Pagination from '@/components/Pagination';
import Resource from '@/api/resource';
const wheatPro = new Resource('wheat');
import Printinvoice from './wheat_print';
export default {
  name: '',
  components: { Printinvoice, Pagination },
  directives: { },
  data() {
    return {
      wheated: null,
      custData: 0,
      search: '',
      total: 0,
      total_available_bill: 0,
      total_available_weight: 0,
      total_avg_rate: 0,
      total_net_weight: 0,
      loading: false,
      downloading: false,
      editnow: false,
      showprint: false,
      invoiceid: null,
      amountpay: 3,
      formLabelWidth: '250',
      rate: 0,
      wheatlist: [],
      weight_total: 0,
      wheat: {
        id: '',
        customer_name: '',
        phone: '',
        kaat: '',
        quality_kaat: '',
        cart: [],
        total_bill: 0,
        decided_rate: 0,
        final_total: 0,
        address: '',
        net_weight: 0,
      },
      addtocart: {
        bags: '',
        weight: '',
      },
      query: {
        page: 1,
        limit: 15,
        keyword: '',
        daterange: [this.todayDate(), this.todayDate()],
        role: '',
      },
      defaultDate: new Date().toJSON().slice(0, 10).replace(/-/g, '/'),
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
    };
  },
  computed: {
  },
  created() {
    this.rate = (localStorage.getItem('rate').length != '') ? localStorage.getItem('rate') : 1;
    this.wheat.decided_rate = this.rate;
    this.getList();
  },
  methods: {
    handleFilter() {
      this.getList();
    },
    todayDate() {
      var today = new Date();
      var dd = String(today.getDate()).padStart(2, '0');
      var mm = String(today.getMonth() + 1).padStart(2, '0');
      var yyyy = today.getFullYear();
      today = yyyy + '-' + mm + '-' + dd;
      return today;
    },
    debounceInput: _.debounce(function(e) {
      this.getList();
    }, 500),
    async getList() {
      const { original } = await wheatPro.list(this.query);
      this.wheatlist = original.wheat.data;
      this.total = original.wheat.total;
      this.total_available_bill = original.total_wheat_bill[0].total_sales;
      this.total_available_weight = original.total_wheat_weight[0].total_weight;
      this.total_avg_rate = original.total_wheat_rate[0].total_rate;
      this.total_net_weight = original.net_weight[0].net_weight;
    },
    async search_data() {
      await this.getList();
    },
    async handleEdit(id, name) {
      console.log(id);
      const { data } = await wheatPro.get(id);
      this.wheat = data.wheat;
      this.editnow = true;
    },
    async handleDelete(id, name) {
      console.log(id);
      this.$confirm('Do you really want to delete?', 'Warning', {
        confirmButtonText: 'OK',
        cancelButtonText: 'Cancel',
        type: 'warning',
      }).then(async() => {
        await wheatPro.destroy(id);
        this.getList();
        this.$message({
          type: 'success',
          message: name + ' Delete successfully',
        });
      });
    },
    addRow() {
      if (this.addtocart.bags == '' || this.addtocart.weight === '') {
        this.$message({
          type: 'error',
          message: 'Enter bags and wights first.',
        });
        return;
      }
      this.wheat.cart = [...this.wheat.cart, { ...this.addtocart }];
      this.addtocart.bags = '';
      this.addtocart.weight = '';
    },
    deleteRow(bags, weight) {
      this.wheat.cart = this.wheat.cart.filter(row => (row.bags !== bags && row.wieght !== weight));
      this.make_total();
      this.console.log('Hello');
    },
    getSummaries(param) {
      const { columns, data } = param;
      const sums = [];
      columns.forEach((column, index) => {
        if (index === 0) {
          sums[index] = 'Totals';
          return;
        }
        const values = data.map(item => Number(item[column.property]));
        if (!values.every(value => isNaN(value))) {
          sums[index] = values.reduce((prev, curr) => {
            const value = Number(curr);
            if (!isNaN(value)) {
              this.wheat.final_total = prev + curr;
              return prev + curr;
            } else {
              this.wheat.final_total = prev;
              return prev;
            }
          }, 0);
        } else {
          sums[index] = '--';
        }
      });
      return sums;
    },
    async onSubmit() {
      if (this.wheat.id !== '') {
        this.wheat.push(this.rate);
        await wheatPro.update(this.wheat.id, this.wheat);
        this.wheat.kaat = '';
        this.wheat.quality_kaat = '';
        this.wheat.customer_name = '';
        this.wheat.phone = '';
        this.wheat.address = '';
        this.wheat.total_bill = 0;
        this.wheat.cart = [];
        this.getList();
      } else {
        this.custData = await wheatPro.store(this.wheat);
        this.invoiceid = this.custData.id;
        this.showprint = true;
        this.wheat.kaat = '';
        this.wheat.quality_kaat = '';
        this.wheat.customer_name = '';
        this.wheat.phone = '';
        this.wheat.address = '';
        this.wheat.total_bill = 0;
        this.wheat.cart = [];
        this.getList();
      }
    },
    handleChange() {
      const rate = this.rate;
      localStorage.setItem('rate', rate);
      this.$message({
        type: 'success',
        message: 'Rate is set to ' + rate,
      });
      this.rate = localStorage.getItem('rate');
    },
    focusInput(refrr) {
      this.$refs[refrr].focus();
    },
    make_total() {
      const total_kaat = parseFloat(this.wheat.kaat) + parseFloat(this.wheat.quality_kaat);
      this.wheat.net_weight = this.wheat.final_total - total_kaat;
      const kaat = this.wheat.final_total - total_kaat;
      console.log(kaat);
      this.wheat.total_bill = (kaat / 40) * this.rate;
      console.log(this.wheat.total_bill);
    },
    clearForm() {
      this.wheat.kaat = '';
      this.wheat.quality_kaat = '';
      this.wheat.customer_name = '';
      this.wheat.phone = '';
      this.wheat.address = '';
      this.wheat.total_bill = 0;
    },
  },
};
</script>
<style  scoped>
  .el-drawer__body {
    flex: 1;
    padding: 20px;
  }
  .demo-drawer__content {
    display: flex;
    flex-direction: column;
    height: 100%;
    padding: 20px;
  }
  .ttlstock{
    font-size: 20px;
    font-weight: bold;
  }
  .custom-head-class{
    float: right;
  }
</style>
