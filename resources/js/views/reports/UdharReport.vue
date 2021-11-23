<template>
  <div class="app-container">
    <el-button v-shortkey="['ctrl', 'c']" type="primary" plain @click="print">Print</el-button>
    <div id="printMe">
      <table class="invoice_header" style="border-bottom: 5px double #000;padding-bottom: 10px;margin-bottom: 10px;width: 100%;">
        <tr>
          <td>
            <div class="company-name">{{ settings.company_name }}</div>
            <div style="font-size: 15px;font-weight: bold;">{{ settings.address }}</div>
            <div style="	font-size: 14px;font-weight: bold;">Cell# {{ settings.phone }}</div>
          </td>
          <td class="invoice-heading" style="	text-align: right;font-weight: bold;text-decoration: underline;font-size: 23px;">
            Recovery Report
          </td>
        </tr>
      </table>
      <table class="tblwdborder idlprint-table">
        <tr class="textcenter">
          <th>No#</th><th>Name</th><th>Phone</th><th>Udhar</th>
        </tr>
        <tr v-for="(product, index) in products" v-bind="product.id">
          <td>{{ index+1 }}</td>
          <td width="50%" style="font-weight: bold;">{{ product.name }} </td>
          <td align="left" valign="center">{{ product.phone }}</td>
          <td align="right" valign="center">{{ product.total }}</td>
        </tr>
        <tr>
          <td colspan="3" align="right">Grand Total</td>
          <td align="right">{{ grandTotal }}</td>
        </tr>
      </table>
    </div>
  </div>
</template>
<script>
import Resource from '@/api/resource';
import moment from 'moment';
const setPro = new Resource('settings');
const udharPro = new Resource('udhar_total');
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
  },
  data() {
    return {
      settings: [],
      products: [],
      product: [],
      grandTotal: 0,
      qty: 0,
    };
  },
  computed: {
  },
  watch: {
  },
  created() {
    this.getList();
    this.getProduct();
  },
  methods: {
    async getList() {
      const { data } = await setPro.list();
      this.settings = data.settings;
    },
    async getProduct() {
      const { data } = await udharPro.list();
      this.products = data.udhar;
      this.products = this.products.filter(pro => {
        if (pro.total < 0){
          return pro;
        }
      });
      this.grandTotal = this.products.map(element => parseInt(element.total)).reduce((a, b) => a + b, 0);
      console.log(this.grandTotal);
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
  text-align: right;
  border: 1px solid #000;
  padding: 3px;
}
.tblwdborder tr td, .tblwdborder tr  th {
  border: 1px solid #000;
  padding: 3px;
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

</style>
