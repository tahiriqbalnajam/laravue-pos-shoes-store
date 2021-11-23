<template>
  <div class="app-container">
    <div id="printMe">
      <table class="invoice_header" style="border-bottom: 5px double #000;padding-bottom: 10px;margin-bottom: 10px;width: 100%;">
        <tr>
          <td>
            <div class="company-name">{{ settings.company_name }}</div>
            <div style="font-size: 15px;font-weight: bold;">{{ settings.address }}</div>
            <div style="	font-size: 14px;font-weight: bold;">Cell# {{ settings.phone }}</div>
          </td>
          <td class="invoice-heading" style="	text-align: right;font-weight: bold;text-decoration: underline;font-size: 23px;">
            Wheat Invoice
          </td>
        </tr>
      </table>
      <table style="width:100%">
        <tr>
          <td style="width:50%">
            <table class="tblwdborder">
              <tr>
                <th>Party Name:</th>
                <td>{{ wheat.customer_name }}</td>
              </tr>
              <tr>
                <th>Address:</th>
                <td>{{ wheat.address }}</td>
              </tr>
              <tr>
                <th>Phone:</th>
                <td>{{ wheat.phone }}</td>
              </tr>
            </table>
          </td>
        </tr>
      </table>
      <table style="width:100%">
        <tr>
          <td style="width:50%">
            <table class="tblwdborder">
              <tr>
                <th>Inv.#</th>
                <td>{{ wheat.id }}</td>
                <th>Date:</th>
                <td>{{ wheat.created_at | dateformat }}</td>
              </tr>
            </table>
          </td>
        </tr>
      </table>
      <table class="tblwdborder idlprint-table">
        <tr class="textcenter">
          <th>Bags</th><th>Total Weight</th><th>Net Weight</th><th>Quality Kaat</th><th>Kaat</th><th>Rate</th>
        </tr>
        <tr v-for="product in wheat.cart_products" :key="product.id">
          <td width="35%">{{ product.bags }}</td>
          <td align="right" valign="center">{{ product.weight }}</td>
          <td align="right" valign="center">{{ wheat.net_weight }}</td>
          <td align="right" valign="center">{{ wheat.quality_kaat }}</td>
          <td align="right" valign="center">{{ wheat.Kaat }}</td>
          <td align="right" valign="center">{{ wheat.rate }}</td>
        </tr>
      </table>
      <table style="width:100%">
        <tr>
          <td style="width:50%">
            <div class="sub-heading" style="font-weight: bold;">Total Items:</div>
            <div class="invoice-note" style="margin-top: 10px;color: rgb(159, 155, 155);font-size: 13px;">{{ settings.invoice_footer }}</div>
          </td>
          <td style="width:30%">
            <table class="tblwdborder footerstat">
              <tr class="bill-final">
                <th>Grand Total</th>
                <td class="custom-grand-total">{{ wheat.total_bill }}</td>
              </tr>
            </table>
          </td>
        </tr>
      </table>
    </div>
    <el-button v-shortkey="['ctrl', 'c']" type="primary" plain @click="print">Print</el-button>
  </div>
</template>
<script>
import Resource from '@/api/resource';
import moment from 'moment';
const setPro = new Resource('settings');
const salePro = new Resource('wheatlastinvoiceid');
import { getwheatLastInoice } from '@/api/sale';
export default {
  name: 'Printinvoice',
  saleid: 45,
  components: { },
  directives: { },
  filters: {
    dateformat: (date) => {
      return (!date) ? '' : moment(date).format('DD/MM/YYYY');
    },
  },
  props: {
    invoiceid: {
      type: Number,
      required: true,
    },
    paidamount: {
      type: Number,
      required: true,
    },
  },
  data() {
    return {
      settings: [],
      wheat: [],
      bags: [],
      remainingBalance: 0,
      prev: 0,
      grandT: 0,
      sale: [],
    };
  },
  computed: {
  },
  watch: {
    invoiceid: function(val, oldval) {
      this.getSale(val);
    },
  },
  created() {
    this.getList();
    this.getSale(this.invoiceid);
  },
  methods: {
    getTotalwdDisc(price, qty, dscntype, disc1, disc2) {
      let total = price * qty;

      if (disc1){
        total = (dscntype === 'rs') ? total - disc1 : total - (total * (disc1 / 100));
      }
      if (disc2) {
        total = (dscntype === 'rs') ? total - disc2 : total - (total * (disc2 / 100));
      }
      return total.toFixed(2);
    },
    async getList() {
      const { data } = await setPro.list();
      this.settings = data.settings;
    },
    async getSale(id) {
      this.prev = this.paidamount;
      const { data } = await getwheatLastInoice(id);
      this.wheat = data;
    },
    saleTotal(dscntype, total, discount) {
      total = parseFloat(total);
      discount = parseFloat(discount);
      if (dscntype === 'rs') {
        total = (total + discount);
      } else {
        total = (total + (total * discount / 100) + 0);
      }
      return total;
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
    changeDate(date) {
    },
  },
};
</script>
<style>
#printMe {
  margin:0 20px 0 20px;
}
.tblwdborder {
  border-collapse: collapse;
  width: 100%;
}
.tblwdborder th {
  text-align: center;
  border: 1px solid #000;
  padding: 3px;
}
.tblwdborder tr td, .tblwdborder tr  th {
  border: 1px solid #000;
  padding: 3px;
  text-align: center;
}
.company-name {
  font-size: 22px;
  font-weight: bold;
}
.address {

}
.phone {

}
.app-container{
  font-size: 12px;
}

.sale_products {
  margin:10px 0  10px 0;
}
.sub-heading {
	font-weight: bold;
}
.customer-detail {

}
.customer-detail th {

}

.bill-final td, .bill-final th {
  background: #c7c7c7;
}
.invoice-note {
  margin-top: 10px;
  color: rgb(159, 155, 155);
  font-size: 13px;
}
.invoice-heading {

}
.product_print {
  border-collapse: collapse;
}
.product_print tr td {
  border: 1px solid #000;
}
.textcenter th {
  text-align: center;
}
.idlprint-table{
  border-collapse: collapse;
  font-size: 12px;
  margin: 20px 0;
}

.idlprint-table th,
.idlprint-table td{
  padding: 10px;
}

.idlprint-table th{
  background-color: #c7c7c7 ;
}

.idlprint-table td{
}
@media print {
  .el-table th.is-leaf, .el-table td {
    padding: 2px 0 2px 2px;
  }
}
.footerstat tr th,.footerstat tr td {
  font-size: 13px;
}
.custom-grand-total {
    font-size: 27px !important;
    font-weight: bolder;
}
</style>
