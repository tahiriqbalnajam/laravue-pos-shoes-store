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
      </el-row>
    </div>
    <el-button v-shortkey="['ctrl', 'c']" type="primary" plain @click="print">Print</el-button>
    <div id="printMe">
      <div class="filter-container">
        <el-row type="flex" class="row-bg" justify="space-between">
          <el-col :span="24">
            <h1>Grand Sale Report</h1>
          <!--<el-col :xs="8" :sm="6" :md="4" :lg="8" :xl="8">-->
            <strong v-if="totalsalepaid">Total Amount Received :</strong><el-tag v-if="totalsalepaid" type="warning" effect="dark" class="ttlstock">{{ totalsalepaid }}</el-tag>
            <strong v-if="totalsale">Total Credit Sale:</strong><el-tag v-if="totalsale" type="danger" effect="dark" class="ttlstock">{{ totalsale-totalsalepaid }}</el-tag>
            <strong v-if="totalsale">Total Sale:</strong><el-tag v-if="totalsale" type="warning" effect="dark" class="ttlstock">{{ totalsale }}</el-tag>
            
          </el-col>
        </el-row>
      </div>
      <div v-if="purchases">
        <h1>Sale Report</h1>
        <table class="tblwdborder idlprint-table">
          <tr class="textcenter">
            <th>No#</th><th>Total Items</th><th>Total Amount</th><th>Paid Amount</th><th>Date</th>
          </tr>
          <tr v-for="(sal, index) in sale" v-bind="sale.id">
            <td>{{ index+1 }}</td>
            <td align="left" valign="center">{{ sal.total_items }}</td>
            <td align="right" valign="center">{{ sal.total }}</td>
            <td align="right" valign="center">{{ sal.totalpiad }}</td>
            <td align="right" valign="center">{{ sal.created_at | dateformat }}</td>
          </tr>
          <tr>
            <td colspan="2" align=""><strong>Grand Total</strong></td>
            <td align="right">{{ saletotal }}</td>
            <td align="right">{{ saletotalpaid }}</td>
            <td align="right"><strong>Balance: {{ saletotal - saletotalpaid }}</strong></td>
          </tr>
        </table>
      </div>
      <div v-if="purchases">
        <h1>Purchase Report</h1>
        <table class="tblwdborder idlprint-table">
          <tr class="textcenter">
            <th>No#</th><th>Name</th><th>Total Items</th><th>Total Amount</th><th>Paid Amount</th><th>Date</th>
          </tr>
          <tr v-for="(pur, index) in purchases" v-bind="purchases.id">
            <td>{{ index+1 }}</td>
            <td width="50%" style="font-weight: bold;">{{ pur.name }} </td>
            <td align="left" valign="center">{{ pur.total_items }}</td>
            <td align="right" valign="center">{{ pur.total_amount }}</td>
            <td align="right" valign="center">{{ pur.paid_amount }}</td>
            <td align="right" valign="center">{{ pur.created_at | dateformat }}</td>
          </tr>
          <tr>
            <td colspan="3" align=""><strong>Grand Total</strong></td>
            <td align="right">{{ total_purchase }}</td>
            <td align="right">{{ total_ppiad }}</td>
            <td align="right"><strong>Balance: {{ total_purchase - total_ppiad }}</strong></td>
          </tr>
        </table>
      </div>
      <h1>Transection Detail</h1>
      <table class="tblwdborder idlprint-table">
        <tr class="textcenter">
          <th>No#</th><th>Name</th><th>Phone</th><th>Jama</th><th>Naam</th>
        </tr>
        <tr v-for="(product, index) in account_detail" v-bind="account_detail.id">
          <td>{{ index+1 }}</td>
          <td width="50%" style="font-weight: bold;">{{ product.name }} </td>
          <td align="left" valign="center">{{ product.phone }}</td>
          <td align="right" valign="center">{{ product.jama }}</td>
          <td align="right" valign="center">{{ product.naam }}</td>
        </tr>
        <tr>
            <td colspan="3" align=""><strong>Grand Total</strong></td>
            <td align="right">{{ totaljama }}</td>
            <td align="right">{{ totalnaam }}</td>
          </tr>
      </table>
    </div>
    <!-- Table start here -->
  </div>
</template>
<script>
import Pagination from '@/components/Pagination';
import Resource from '@/api/resource';
import moment from 'moment';
import purchase_indexVue from '../purchase/purchase_index.vue';
const customer = new Resource('customer');
const saleReso = new Resource('sale');
const report = new Resource('grandreport');
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
      totalsalepaid: 0,
      account_detail: [],
      purchases: [],
      total_purchase: 0,
      total_ppiad: 0,
      sale: [],
      saletotal: 0,
      saletotalpaid: 0,
      totaljama: 0,
      totalnaam: 0,
      
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
      const { data } = await report.list(this.query);
      this.totalsale = data.total_sale;
      this.totalsalepaid = data.total_paid;
      this.account_detail = data.accounts_detail;
      this.totaljama = this.account_detail.reduce((total, product) => total += parseInt(product.jama), 0);
      this.totalnaam = this.account_detail.reduce((total, product) => total += parseInt(product.naam), 0);
      this.sale = data.sales;
      this.saletotal = this.sale.reduce((total, product) => total += parseInt(product.total), 0);
      this.saletotalpaid = this.sale.reduce((total, product) => total += parseInt(product.totalpiad), 0);

      this.purchases = data.purchase;
      //this.total_purchase = 
      this.total_purchase = this.purchases.reduce((total, product) => total += parseInt(product.total_amount), 0);
      this.total_ppiad = this.purchases.reduce((total, product) => total += parseInt(product.paid_amount), 0);
      // data.purchase.forEach(amount => {
      //   this.total_purchase += amount.reduce((total, product) => total + product.total_amount, 0);
      // });
      this.account_detail = this.account_detail.filter(pro => {
        if (pro.jama != 0 || pro.naam != 0){
          return pro;
        }
      });
      console.log(this.total_purchase);
       //alert('asdf');
      // this.list = data.sales.data;
      // this.total = data.sales.total;
      // this.salereturn = data.total_sale_return[0].total_price;
      // this.totalsale = data.total_sale[0].total_price;
      // this.grandtotal = (data.total_sale[0].total_price - data.total_sale_return[0].total_price).toFixed(2);
      // const ppurchse = data.total_sale[0].total_purchase;
      // let psale = 0;
      // data.sales.data.forEach(sale => {
      //   psale += sale.products.reduce((total, product) => total + (product.quantity * product.price), 0);
      // });
      // this.profit_sale = data.total_sale_profit;
      // this.return_sale = data.total_return_profit;
      // this.totalprofit = this.profit_sale.map(element => parseInt(element.total_sale_profit)).reduce((a, b) => a + b, 0);
      // this.return_sale = this.return_sale.map(element => parseInt(element.total_return_profit)).reduce((a, b) => a + b, 0);
      // this.totalprofit = this.totalprofit - this.return_sale;
      // this.totalprofit.toFixed(2);
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
    print() {
      var contents = document.getElementById('printMe').innerHTML;
      var frame1 = document.createElement('iframe');
      const headcontent = document.getElementsByTagName('head')[0].innerHTML;
      frame1.name = 'frame1';
      frame1.style.position = 'absolute';
      frame1.style.top = '-1000000px';
      document.getElementById('printMe').appendChild(frame1);
      var frameDoc = frame1.contentWindow ? frame1.contentWindow : frame1.contentDocument.document ? frame1.contentDocument.document : frame1.contentDocument;
      frameDoc.document.open();
      frameDoc.document.write('<html><head><title>DIV Contents</title>');
      frameDoc.document.write(headcontent);
      frameDoc.document.write('</head><body>');
      frameDoc.document.write(contents);
      frameDoc.document.write('</body></html>');
      frameDoc.document.close();
      setTimeout(function() {
        window.frames['frame1'].focus();
        window.frames['frame1'].print();
        document.getElementById('printMe').removeChild(frame1);
      }, 500);
      return false;
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
