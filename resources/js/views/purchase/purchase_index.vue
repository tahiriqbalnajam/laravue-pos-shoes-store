<template>
  <div class="app-container">
    <!-- //////////////////////////////////// -->
    <!-- Top bar start -->
    <!-- /////////////////////////////////// -->
    <el-row class="divtop">
      <el-col :span="24">
        <el-card shadow="always">
          <el-row type="flex" justify="space-between">
            <el-col :span="10">
              <el-row type="flex" justify="end">
                <el-col :xs="8" :sm="12" :md="12" :lg="12" :xl="8">
                  <el-radio-group v-model="cart.purchasetype" size="small">
                    <el-radio-button label="purchase" border>Purchase</el-radio-button>
                    <el-radio-button fill="#f56c6c" label="return" border class="salereturn" @change="focusInput('purchaseid')">Return</el-radio-button>
                  </el-radio-group>
                </el-col>
                <el-col>
                  <el-input ref="purchaseid" v-model="purchaseid" placeholder="Enter Purchase Id" :disabled="cart.purchasetype == 'purchase'" @keyup.native.enter="getPurchase()" />
                </el-col>
              </el-row>
            </el-col>
            <el-divider direction="vertical" class="pur-top-divider" />
            <el-col :span="6">
              <el-date-picker
                v-model="cart.created_at"
                type="date"
                format="dd, MM yyyy"
                value-format="yyyy-MM-dd"
                placeholder="Pick a day of purchase"
              />
            </el-col>
            <el-divider direction="vertical" class="pur-top-divider" />
            <el-col :span="6">
              <el-select
                v-model="cart.supplier"
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
              <el-button type="success" @click="customerForm = !customerForm"><svg-icon icon-class="add" /></el-button>
            </el-col>
            <el-divider direction="vertical" class="pur-top-divider" />
            <el-col :span="3" style="margin-top:8px;">
              <el-tooltip content="Discount in" placement="top">
                <el-switch v-model="cart.discount_type" active-text="Rs" inactive-text="%" active-color="#13ce66" inactive-color="#ff4949" active-value="rs" inactive-value="prcnt" />
              </el-tooltip>
            </el-col>
          </el-row>
        </el-card>
      </el-col>
    </el-row>
    <!-- //////////////////////////////////// -->
    <!-- second bar start -->
    <!-- /////////////////////////////////// -->
    <el-row :gutter="12">
      <el-col :span="24">
        <el-card shadow="always">
          <el-row :gutter="10" label-position="top">
            <el-col :span="8">
              <el-select
                ref="prod_select"
                v-model="selectedproduct"
                clearable
                filterable
                remote
                reserve-keyword
                default-first-option
                placeholder="Start typing or scaning for product"
                :remote-method="getProducts"
                :loading="loading"
                class="selectproduct"
                @change="addCart"
                @keyup.native.enter="(settings.show_expiry == 'show') ? focusInput('batch_no') : focusInput('price')"
              >
                <el-option
                  v-for="product in products"
                  :key="product.id"
                  :label="product.name"
                  :value="product.id"
                >
                  <span style="float: left">{{ product.name }}</span>
                  <span style="float: left; margin-left:50px; color: #8492a6; font-size: 13px">Code: {{ product.code }}</span>
                  <span style="float: left; margin-left:50px; color: #8492a6; font-size: 13px">Category: {{ product.category.title }}</span>
                  <span style="float: left; margin-left:50px; color: #8492a6; font-size: 13px">UOM: {{ product.uom.name }}</span>
                  <span style="float: right; color: red; font-size: 13px">Price: {{ product.purchase_price }}</span>
                </el-option>
              </el-select>
            </el-col>
            <el-col :span="2">
              <el-input v-show="settings.show_expiry == 'show'" ref="batch_no" v-model="productDetail.batch_number" placeholder="Batch#" @keyup.native.enter="focusInput('date')" />
            </el-col>
            <el-col :span="3">
              <el-date-picker
                v-show="settings.show_expiry == 'show'"
                ref="date"
                v-model="productDetail.exp_date"
                type="month"
                placeholder="Exp. Date"
                format="MM/yyyy"
                value-format="yyyy-MM-dd"
                @change="focusInput('price')"
              />
            </el-col>
            <el-col :span="2">
              <el-input ref="price" v-model="productDetail.price" placeholder="Price" @keyup.native.enter="(productDetail.price != '') ? focusInput('quantity') : ''" @keyup.native.37="focusInput('date')" />
            </el-col>
            <el-col :span="2">
              <el-input ref="quantity" v-model="productDetail.quantity" placeholder="Quantity" @keyup.native.enter="(productDetail.quantity != '') ? (settings.show_bonus == 'show') ? focusInput('bonus') : focusInput('Dis1') :''" />
            </el-col>
            <el-col :span="2">
              <el-input v-show="settings.show_bonus == 'show'" ref="bonus" v-model="productDetail.bonus" placeholder="Bonus" @keyup.native.enter="focusInput('Dis1')" />
            </el-col>
            <el-col :span="2">
              <el-input ref="Dis1" v-model="productDetail.discount1" :placeholder="cart.discount_type == 'rs' ? 'Dis.1 Rs' : 'Dis.1 %'" @keyup.native.enter="(settings.show_disc2 == 'show') ? focusInput('Dis2') : addCartProductnew()" />
            </el-col>
            <el-col :span="2">
              <el-input v-show="settings.show_disc2 == 'show'" ref="Dis2" v-model="productDetail.discount2" :placeholder="cart.discount_type == 'rs' ? 'Dis.1 Rs' : 'Dis.1 %'" @keyup.native.enter="addCartProductnew()" />
            </el-col>
          </el-row>
        </el-card>
      </el-col>
    </el-row>
    <!-- //////////////////////////////////// -->
    <!-- Cart start -->
    <!-- /////////////////////////////////// -->
    <el-row :gutter="12" class="mt-10">
      <el-col :span="24">
        <el-card shadow="always">
          <!-- //////////////////////////////////// -->
          <!-- Cart Top -->
          <!-- /////////////////////////////////// -->
          <div slot="header" class="clearfix">
            <div class="tag-group">
              <div class="cartheader first">Qty: </div>
              <el-tag
                effect="dark"
                class="tagheader"
              >
                {{ totalQuantity }}
              </el-tag>
              <div class="cartheader">Items: </div>
              <el-tag
                type="success"
                class="tagheader"
                effect="dark"
              >
                {{ totalItems }}
              </el-tag>
              <div class="cartheader">Total: </div>
              <el-tag
                type="danger"
                class="tagheader"
                effect="dark"
              >
                {{ totalAmount }}
              </el-tag>
            </div>
          </div>
          <!-- //////////////////////////////////// -->
          <!-- Cart table -->
          <!-- /////////////////////////////////// -->
          <el-table :data="cart.products" stripe border>
            <el-table-column label="" :min-width="20">
              <template slot-scope="scope">
                <el-button type="danger" icon="el-icon-delete" size="mini" circle @click="removeCartProduct(scope.row.id)" />
              </template>
            </el-table-column>
            <el-table-column label="Product" :min-width="130">
              <template slot-scope="scope">
                <div class="cart-product">{{ scope.row.name }} <span class="small" :show="scope.row.code">({{ scope.row.code }})</span></div>
              </template>
            </el-table-column>
            <el-table-column v-if="settings.show_expiry == 'show'" label="Batch No">
              <template slot-scope="scope">
                <el-input-number v-model="scope.row.batch_no" controls-position="right" :min="1" size="mini" @change="updateProducts()" />
              </template>
            </el-table-column>
            <el-table-column v-if="settings.show_expiry == 'show'" label="Expiry Date" :min-width="150">
              <template slot-scope="scope">
                <el-date-picker
                  v-model="scope.row.exp_date"
                  type="date"
                  format="yyyy-MM-dd"
                />
              </template>
            </el-table-column>
            <el-table-column label="Price" prop="price" :min-width="50" />
            <el-table-column label="Quantity">
              <template slot-scope="scope">
                <el-input-number v-model="scope.row.quantity" controls-position="right" :min="1" size="mini" @change="updateProducts()" />
              </template>
            </el-table-column>
            <el-table-column v-if="settings.bonus == 'show'" label="Bonus">
              <template slot-scope="scope">
                <el-input-number v-model="scope.row.bonus" controls-position="right" :min="0" size="mini" @change="updateProducts()" />
              </template>
            </el-table-column>
            <el-table-column label="Discount 1">
              <template slot-scope="scope">
                <el-input-number v-model="scope.row.discount1" controls-position="right" :min="0" size="mini" @change="updateProducts()" />
              </template>
            </el-table-column>
            <el-table-column v-if="settings.show_disc2 == 'show'" label="Discount 2">
              <template slot-scope="scope">
                <el-input-number v-model="scope.row.discount2" controls-position="right" :min="0" size="mini" @change="updateProducts()" />
              </template>
            </el-table-column>
            <el-table-column label="Total">
              <template slot-scope="scope">
                <div class="cart-product">{{ productTotal(scope.row.quantity, scope.row.price, scope.row.discount1, scope.row.discount2) }} </div>
              </template>
            </el-table-column>
          </el-table>
          <el-row type="flex" justify="end" style="margin-top:20px">
            <el-col :xs="24" :sm="24" :md="16" :lg="8" :xl="8">
              <el-input v-model="cart.bill_discount" placeholder="Discount on bill" :disabled="totalItems == 0" @keyup.native="refreshCart()">
                <template slot="prepend">Discount</template>
              </el-input>
            </el-col>
          </el-row>
          <!-- //////////////////////////////////// -->
          <!-- Cart payment button -->
          <!-- /////////////////////////////////// -->
          <div class="sale-final-buttons">
            <el-button-group>
              <el-popover
                placement="top"
                width="300"
                trigger="click"
              >
                <el-form ref="form" label-width="120px">
                  <el-form-item label="Sale Name">
                    <el-input v-model="holdpurchase.name" :disabled="totalAmount == 0" @change="holdSale()" />
                  </el-form-item>
                  <el-table :data="holdPurchases">
                    <el-table-column label="Sale" prop="name" />
                    <el-table-column label="Action">
                      <template slot-scope="scope">
                        <el-button type="danger" icon="el-icon-delete" size="mini" circle @click="removeHoldSale(scope.row.name)" />
                        <el-button type="danger" icon="el-icon-upload2" size="mini" circle @click="loadHoldSale(scope.row.name)" />
                      </template>
                    </el-table-column>
                  </el-table>
                </el-form>

              </el-popover>
              <el-popover
                v-model="visible"
                placement="top"
                width="300"
                trigger="click"
              >
                <el-form ref="form" label-width="120px">
                  <el-form-item label="Naqad Raqm">
                    <el-input ref="naqad" v-model="cart.paid_amount" :disabled="totalItems == 0" @keyup.native="setCredit()" @keyup.native.enter="completePurchase()" />
                  </el-form-item>
                  <el-form-item label="Udhar Raqm">
                    {{ cart.credit }}
                  </el-form-item>
                </el-form>
                <el-button slot="reference" type="primary" icon="el-icon-bank-card">Payment</el-button>
              </el-popover>
              <el-button-group>
                <el-button type="danger" icon="el-icon-close" @click="cancelSale()">Cancel</el-button>
                <el-button type="success" icon="el-icon-goods" @click="completePurchase()">Cash</el-button>
              </el-button-group>
            </el-button-group>
          </div>
        </el-card>
      </el-col>
    </el-row>
    <!-- //////////////////////////////////// -->
    <!-- Add customer popup -->
    <!-- /////////////////////////////////// -->
    <add-account v-if="customerForm" :default-type="2" @addcustomer="addCustomerpopup" @newcustomer="newCustomer" />
    <!-- //////////////////////////////////// -->
    <!-- Invoice print -->
    <!-- /////////////////////////////////// -->
    <el-dialog title="Print Invoice" :visible.sync="showprint">
      <printinvoice :invoiceid="invoiceid" :v-if="invoiceid" />
    </el-dialog>
  </div>
</template>
<script>
const settings = new Resource('settings');
import Resource from '@/api/resource';
const customer = new Resource('customer');
import { getTotalStock } from '@/api/product';
const prod = new Resource('product');
const purchResorc = new Resource('purchase');
const retResorc = new Resource('returntype');
import AddAccount from '../accounts/AddAccount';
import { getpurLastInoice } from '@/api/sale';
import Printinvoice from './PurchasePrint';

export default {
  name: '',
  components: { Printinvoice, AddAccount },
  directives: { },
  data() {
    return {
      list: null,
      customers: [],
      selectedproductt: '',
      batches: [],
      settings: '',
      purchaseid: '',
      totalFinalDiscount: '',
      invoiceid: null,
      showprint: false,
      merged: '',
      value1: '',
      search: '',
      total: 0,
      loading: false,
      customerForm: false,
      downloading: false,
      products: '',
      featured_product: '',
      visible: false,
      query: {
        page: 1,
        limit: 15,
        keyword: '',
        select: '',
      },
      addcustomer: {
        name: '',
        phone: '',
        address: '',
        area_id: '',
      },
      selectedproduct: '',
      cart: {
        purchasetype: 'purchase',
        batch_number: '',
        bonus: '',
        bill_discount: 0,
        discount_type: 'rs',
        supplier: '',
        due: '',
        products: [],
        ttlQuantity: 0,
        ttlItems: 0,
        ttlAmount: 0,
        paid_amount: 0,
        credit: 0,
        prev_balance: 0,
        created_at: '',
      },
      holdpurchase: {
        name: '',
        cart: '',
      },
      holdPurchases: [],
      productDetail: {
        batch_no: '',
        bonus: '',
        price: '',
        quantity: '',
        discount1: '',
        discount2: '',
        exp_date: '',
        totalStock: '',
        previous_price: '',
      },
      customeraddrules: {
        name: [
          { required: true, message: 'Please enter customer name', trigger: 'blur' },
          { min: 3, max: 50, message: 'Length should be 3 to 50', trigger: 'blur' },
        ],
      },
    };
  },
  computed: {
    totalQuantity: function() {
      const qty = this.cart.products.map(element => parseFloat(element.quantity)).reduce((a, b) => a + b, 0);
      // let bonus =  this.cart.products.map(element => parseInt(element.bonus)).reduce((a, b) => a + b, 0);
      const bonus = 0;
      return qty + parseFloat(bonus);
    },
    totalItems: function() {
      return this.cart.products.length;
    },
    totalAmount: function() {
      let ttlamnt = this.cart.products.map(pro => {
        return this.getTotalwdDisc(pro.price, pro.quantity, pro.discount1, pro.discount2);
      }).reduce((a, b) => a + parseFloat(b), 0);
      ttlamnt = (this.cart.discount_type === 'rs') ? ttlamnt - this.cart.bill_discount : ttlamnt - (ttlamnt * this.cart.bill_discount / 100);
      return ttlamnt.toFixed(2);
    },
  },
  watch: {
    totalQuantity: function() {
      this.cart.ttlQuantity = this.totalQuantity;
    },
    totalItems: function() {
      this.cart.ttlItems = this.totalItems;
    },
    totalAmount: function() {
      this.cart.ttlAmount = this.totalAmount;
      this.cart.paid_amount = this.cart.ttlAmount;
    },
  },
  created() {
    this.getFeaturedProducts();
    this.getLatestInvoiceId();
    this.getSettings();
  },
  mounted() {
    this.focusInput('prod_select');
  },
  methods: {
    setCredit() {
      this.cart.credit = this.cart.ttlAmount - this.cart.paid_amount;
    },
    rowTotal() {
      return this.getTotalwdDisc(this.addtocart.price, this.addtocart.quantity, this.addtocart.discount1, this.addtocart.discount2);
    },
    getTotalwdDisc(price, qty, disc1, disc2) {
      let total = price * qty;
      if (disc1){
        total = (this.cart.discount_type === 'rs') ? total - disc1 : total - (total * (disc1 / 100));
        console.log(total);
      }
      if (disc2) {
        total = (this.cart.discount_type === 'rs') ? total - disc2 : total - total - (total * (disc2 / 100));
      }
      return total.toFixed(2);
    },
    async getSettings() {
      const { data } = await settings.list();
      this.settings = data.settings;
    },
    addCustomerpopup(data) {
      this.customerForm = data;
    },
    newCustomer(customer) {
      this.customers = customer.data.accounts;
      this.cart.supplier = customer.data.accounts[0].id;
    },
    updateProducts() {
      this.refreshCart();
    },
    async getCustomers(query) {
      this.loading = true;
      this.query.keyword = query;
      const { data } = await customer.list(this.query);
      this.customers = data.accounts.data;
      this.loading = false;
    },
    async getProducts(query) {
      this.loading = true;
      this.query.keyword = query;
      this.query.select = 'productonly';
      const { data } = await prod.list(this.query);
      this.products = data.products.data;
      this.loading = false;
    },
    async getFeaturedProducts(query) {
      const { data } = await prod.customlist('featured_product', '');
      this.featured_product = data.products;
    },
    addCartProduct(index, $event) {
      let selectedproduct = this.products.filter(product => product.id == index);
      selectedproduct = selectedproduct[0];
      this.cartProducts(selectedproduct);
    },
    async addCart(index, $event) {
      let selectedproduct = this.products.filter(product => product.id == index);
      selectedproduct = selectedproduct[0];
      const { data } = await getTotalStock(selectedproduct.id);
      this.productDetail.totalStock = data.stock;
      localStorage.setItem('totalstock', JSON.stringify(this.productDetail.totalStock));
      this.productDetail.previous_price = selectedproduct.purchase_price;
    },
    async getPurchase() {
      const { data } = await retResorc.get(this.purchaseid);
      this.cart.products = data.purchases;
      this.cart.batches = data.purchases;
      this.cart.products = this.cart.products.map(product => {
        return { ...product, batch_no: product.batches.batch_no, exp_date: product.batches.exp_date, name: product.product.name, code: product.product.code, selectedproduct: product.product.id, totalStock: this.productDetail.totalStock, previous_price: this.productDetail.previous_price };
      });
      this.refreshCart();
    },
    addCartProductnew() {
      if (this.selectedproduct === ''){
        this.$message({
          message: 'Please Select Product First.',
          type: 'error',
        });
      } else {
        let selectedproductt = this.products.filter(product => product.id == this.selectedproduct);
        selectedproductt = selectedproductt[0];
        const selectedproduct = { ...selectedproductt, ...this.productDetail };
        this.cartProducts(selectedproduct);
        this.selectedproduct = '';
        this.productDetail.batch_number = '';
        this.productDetail.bonus = '';
        this.productDetail.exp_date = '';
        this.productDetail.quantity = '';
        this.productDetail.discount1 = '';
        this.productDetail.discount2 = '';
        this.productDetail.price = '';
      }
    },
    focusInput(refrr) {
      this.$refs[refrr].focus();
    },
    holdSale() {
      if (this.holdpurchase.name != '') {
        this.holdpurchase.cart = this.cart.products;
        this.holdPurchases.push({ ...this.holdpurchase });
        localStorage.setItem('sales', JSON.stringify(this.holdPurchases));
        this.cart.products = [];
        this.holdpurchase.name = '';
        this.$message({
          type: 'success',
          message: 'Purchase hold successfully.',
        });
      }
    },
    loadHoldSale(salename) {
      console.log(salename);
      console.log(this.holdPurchase);
      this.holdPurchase = this.holdPurchases.filter(purchase => {
        if (purchase.name === salename) {
          purchase.cart.filter(pur => {
            this.cart.push({ ...this.holdPurchase });
          });
        }
      });
      return this.cart;
      // localStorage.setItem('sales', JSON.stringify(this.holdPurchase));
    },
    addFeaturedCart(index) {
      let selectedproduct = this.featured_product.filter(product => product.id == index);
      selectedproduct = selectedproduct[0];
      this.cartProducts(selectedproduct);
    },
    cartProducts(selectedproduct) {
      const productExist = this.cart.products.findIndex(product => product.id == selectedproduct.id);
      if (productExist >= 0) {
        this.cart.products.push(selectedproduct);
        this.refreshCart();
      } else {
        if (selectedproduct.discount1 > 0 || selectedproduct.discount2 > 0){
          selectedproduct.discount = (selectedproduct.discount1 / 100) + (selectedproduct.discount2 / 100);
        }
        this.cart.products.push(selectedproduct);
      }
    },
    refreshCart() {
      this.cart.products = this.cart.products.filter(product => product.id == product.id);
    },
    getTotalam(totalAmount){
      this.cart.raqamType.udhar = totalAmount;
    },
    productTotal(quantity, price, discount, discount2) {
      quantity = parseFloat(quantity);
      price = parseFloat(price);
      discount = parseFloat(discount);
      let total = quantity * price;
      if (discount) {
        total = (this.cart.discount_type === 'rs') ? total - discount : total - (total * discount / 100);
      }

      if (discount2 > 0) {
        total = (this.cart.discount_type === 'rs') ? total - discount2 : total - (total * discount2 / 100);
      }
      return total.toFixed(2);
    },
    removeCartProduct(id) {
      this.cart.products = this.cart.products.filter(product => product.id !== id);
    },
    cancelSale() {
      if (this.cart.products.length) {
        this.$alert('Are you really want to cancel this sale?', 'Title', {
          confirmButtonText: 'OK',
          callback: action => {
            this.cart.products = [];
            this.$message({
              type: 'success',
              message: 'Sale deleted successfully.',
            });
          },
        });
      }
    },
    async completePurchase() {
      if (this.cart.supplier == '') {
        this.$message({
          message: 'Please enter supllier.',
          type: 'error',
        });
      } else {
        const { data } = await purchResorc.store(this.cart).then(result => {
          this.$message({
            message: 'Purchase added successfully.',
            type: 'success',
          });
          // this.showprint = true;
          this.cart.products = [];
          this.cart.supplier = '';
        });
      }
    },
    async getLatestInvoiceId() {
      const { data } = await getpurLastInoice();
    },
    addCustomer(formName) {
      this.$refs[formName].validate((valid) => {
        if (valid) {
          const { data } = customer.store(this.addcustomer).then(result => {
            this.customers = result;
            this.$message({
              message: 'Added Successfully.',
              type: 'success',
            });
            this.showcustpopup = false;
          });
        }
      });
    },
  },
};
</script>
<style  scoped>
.divtop {
  margin-bottom: 10px;
}
.divtop >>> .el-card__body {
    padding:8px 20px !important;
}
.salereturn.is-active >>> span {
	background: #ff4949 !important;
	border-color: #ff4949 !important;
	box-shadow: -1px 0 0 0 #ff4949 !important;
}
.el-switch__label.is-active {
  font-weight: bold !important;
}
.selectproduct {
  width:80%;
}
.mt-10 {
  margin-top: 10px;
}
.cart-product {
  font-weight: bold;
}
.small {
  font-weight: normal;
  font-size:10px;
}
.cartheader {
  font-weight: bold;
  font-size: 17px;
  margin-right: 5px;
  display: inline-block;
  margin-left: 20px;
}
.first {
  margin-left: 0;
  display: inline-block;
}
.tagheader {
  font-weight: bold;
  font-size: 17px;
}
.sale-final-buttons {
  margin-top: 20px;
  text-align: right;
}
.featurd-product-list {
	margin-bottom: 10px;
  cursor: pointer;
}
.producname {
	font-weight: bold;
}
.productprice {
	color: #ed3338;
	font-size: 12px;
}
.producnamecode {
  font-size:12px;
}
.pur-top-divider {
  margin:12px 8px;
  background-color: #000;
}
</style>
