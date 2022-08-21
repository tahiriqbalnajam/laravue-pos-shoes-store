<template>
  <div class="app-container">
    <el-row>
      <el-col :span="24" class="sale-top-bar">
        <el-card shadow="always">
          <el-row :gutter="20">
            <el-col :xs="8" :sm="7" :md="7" :lg="6" :xl="6">
              <el-radio-group v-model="cart.saletype" size="small">
                <el-radio-button label="sale" border @change="cart.saleid = ''">Sale</el-radio-button>
                <el-radio-button label="return" border class="salereturn" @change="focusInput('saleid')">Return</el-radio-button>
              </el-radio-group>
            </el-col>
            <el-col :span="6">
              <el-input ref="saleid" v-model="cart.saleid" placeholder="Enter Sale Id" :disabled="cart.saletype == 'sale' && searchsale == false" @keyup.native.enter="getSale()" />
            </el-col>
            <el-col :span="3">
              <el-checkbox v-model="searchsale" label="Search Sale" border @change.native="focusInput('saleid')" @click="searchsale = !searchsale;" />
            </el-col>
            <el-divider direction="vertical" />
            <el-col :span="3">
              <el-checkbox v-model="wholesaler" label="Wholesaler" border />
            </el-col>
            <el-col :span="3" style="margin-top:8px;">
              <el-tooltip content="Discount in" placement="top">
                <el-switch v-model="cart.discount_type" active-text="Rs" inactive-text="%" active-color="#13ce66" inactive-color="#ff4949" active-value="rs" inactive-value="prcnt" />
              </el-tooltip>
            </el-col>
          </el-row>
        </el-card>
      </el-col>
    </el-row>
    <el-row :gutter="3" class="mt-10">
      <el-col :span="24">
        <el-card shadow="always">
          <el-row :gutter="5">
            <el-col :span="8">
              <el-select
                ref="selectedproduct"
                v-model="addtocart.selectedproduct"
                :disabled="cart.saletype == 'return'"
                clearable
                filterable
                remote
                reserve-keyword
                default-first-option
                placeholder="Start typing or scaning for product"
                :remote-method="getProducts"
                :loading="loading"
                label="Select Product"
                @change="addCartProduct"
                @keyup.native.enter="(addtocart.selectedproduct != '') ? focusInput('selectproduct') : ''"
              >
                <el-option
                  v-for="product in products"
                  :key="product.id"
                  :label="product.name"
                  :value="product.id"
                >
                  <span style="float: left">
                    {{ product.name }} 
                    <span v-if="product.size"> Size: <b style="color:#13ce66">{{ product.size }}</b></span> 
                    <span v-if="product.color"> - Color: <b style="color:#ff4949">{{ product.color }}</b></span> 
                    <span> - Brand: <b style="color:#1890ff">{{ product.manufacturer.name }}</b></span>
                  </span>
                  <span style="float: left; margin-left:50px; color: #8492a6; font-size: 13px">Stock: {{ parseInt(product.purchase)-parseInt(product.sale) }}</span>
                </el-option>
              </el-select>
            </el-col>
            <el-col :span="3">
              <el-select
                v-show="settings.show_expiry == 'show'"
                ref="selectedbatch"
                v-model="addtocart.batch_number"
                clearable
                filterable
                remote
                reserve-keyword
                default-first-option
                placeholder="Batch"
                :loading="loading"
                label="Select Batch"
                @keyup.native.enter="(addtocart.batch_number != '') ? focusInput('price') : ''"
              >
                <el-option
                  v-for="batch in batches"
                  :key="batch.id"
                  :label="batch.batch_no"
                  :value="batch.id"
                >
                  <span style="float: left">Batch#: {{ batch.batch_no }}</span>
                  <span style="float: right; color: red; font-size: 13px; margin-left:10px;">Expiry: {{ batch.exp_date }}</span>
                </el-option>
              </el-select>
            </el-col>
            <el-col :span="3">
              <el-input ref="price" v-model="addtocart.price" placeholder="Price" :disabled="cart.saletype == 'return'" @keyup.native.enter="(addtocart.price != '') ? focusInput('quantity'):''" />
            </el-col>
            <el-col :span="2">
              <el-input ref="quantity" v-model="addtocart.quantity" placeholder="Qty" :disabled="cart.saletype == 'return'" @keyup.native.enter="(addtocart.quantity != '') ? focusInput('bonus'):''" />
            </el-col>
            <el-col :span="2">
              <el-input v-show="settings.show_bonus == 'show'" ref="bonus" v-model="addtocart.bonus" placeholder="Bonus" @keyup.native.enter="focusInput('discount1')" />
            </el-col>
            <el-col :span="2">
              <el-input ref="discount1" v-model="addtocart.discount1" placeholder="Disc.1" @keyup.native.enter="focusInput('discount2')" />
            </el-col>
            <el-col :span="2">
              <el-input v-show="settings.show_disc2 == 'show'" ref="discount2" v-model="addtocart.discount2" placeholder="Disc.2" @keyup.native.enter="((addtocart.selectedproduct != '') && (addtocart.price != '') && (addtocart.quantity != '')) ? addToCartFun() : ''" />
            </el-col>
            <el-col :span="2" class="row-total">
              {{ rowTotal() }}
            </el-col>
          </el-row>
        </el-card>
      </el-col>
    </el-row>
    <el-row :gutter="12" class="mt-10">
      <el-col :span="5">
        <el-card shadow="always" style="margin-bottom:10px">
          <div slot="header" class="clearfix">
            <span>Select Customer</span>
          </div>
          <div class="el-autocomplete">
            <div class="el-input el-input-group el-input-group--append">
              <el-select
                v-model="cart.customer"
                clearable
                filterable
                remote
                reserve-keyword
                default-first-option
                placeholder="Find Customer"
                :remote-method="getCustomers"
                :loading="loading"
                @select.native="getPrevBalance()"
                @change="getPrevBalance()"
              >
                <el-option
                  v-for="customer in customers"
                  :key="customer.id"
                  :label="customer.name"
                  :value="customer.id"
                />
              </el-select>
              <el-button type="primary" style="margin-left:3px" @click="customerForm = !customerForm;"><svg-icon icon-class="add" /></el-button>
            </div>
          </div>
        </el-card>
        <el-card shadow="always">
          <div slot="header" class="clearfix">
            <span>Select Saleman</span>
          </div>
          <div class="el-input">
            <el-select
              v-model="cart.staff"
              :disabled="cart.saletype == 'return'"
              clearable
              filterable
              reserve-keyword
              default-first-option
              placeholder="Select Saleman"
              :loading="loading"
            >
              <el-option
                v-for="staff in staffers"
                :key="staff.id"
                :label="staff.name"
                :value="staff.id"
              />
            </el-select>
          </div>
          <el-row :gutter="6">
            <el-col v-for="fp in featured_product" :key="fp.id" :span="12">
              <el-card shadow="always" class="featurd-product-list" @click.native="addFeaturedCart(fp.id)">
                <div class="producname">{{ fp.name }}</div>
                <div class="productprice">Price: {{ fp.sale_price }}</div>
                <div class="producnamecode">Code: {{ fp.code }}</div>
              </el-card>
            </el-col>
          </el-row>
        </el-card>
      </el-col>
      <el-col :span="19">
        <el-card shadow="always">
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
              <div class="cartheader">Return: </div>
              <el-tag
                type="info"
                class="tagheader"
                effect="dark"
              >
                {{ Cashreturn }}
              </el-tag>
            </div>
          </div>
          <el-table :data="cart.products" stripe border>
            <el-table-column label="" :min-width="20">
              <template slot-scope="scope">
                <el-button type="danger" icon="el-icon-delete" size="mini" circle @click="removeCartProduct(scope.row.selectedproduct)" />
              </template>
            </el-table-column>
            <el-table-column label="Product" :min-width="150">
              <template slot-scope="scope">
                <div class="cart-product">
                  {{ scope.row.name }} 
                  <span class="small" :show="scope.row.size">({{ scope.row.size }})</span>
                  <span class="small" :show="scope.row.color"> - ({{ scope.row.color }})</span>
                </div>
              </template>
            </el-table-column>
            <el-table-column label="Price" :min-width="40">
              <template slot-scope="scope">
                <el-input v-model="scope.row.price" :disabled="cart.saletype == 'return'" controls-position="right" :min="1" size="mini" @change="updateProducts()" />
              </template>
            </el-table-column>
            <el-table-column label="Quantity">
              <template slot-scope="scope">
                <el-input v-model="scope.row.quantity" :disabled="cart.saletype == 'return'" controls-position="right" :min="1" size="mini" @change="updateProducts()" />
              </template>
            </el-table-column>
            <el-table-column v-if="settings.bonus == 'show'" label="Bonus">
              <template slot-scope="scope">
                <el-input v-model="scope.row.bonus" controls-position="right" :min="1" size="mini" @change="updateProducts()" />
              </template>
            </el-table-column>
            <el-table-column label="Discount" :min-width="40">
              <template slot-scope="scope">
                <el-input
                  v-model="scope.row.discount1"
                  :disabled="cart.saletype == 'return'"
                  size="mini"
                  placeholder="%"
                  clearable
                />
              </template>
            </el-table-column>
            <el-table-column v-if="settings.show_disc2 == 'show'" label="Discount2" :min-width="40">
              <template slot-scope="scope">
                <el-input
                  v-model="scope.row.discount2"
                  size="mini"
                  placeholder="%"
                  clearable
                />
              </template>
            </el-table-column>
            <el-table-column label="Total">
              <template slot-scope="scope">
                <div class="cart-product">{{ productTotal(scope.row.quantity, scope.row.price, scope.row.discount1,scope.row.discount2) }} </div>
              </template>
            </el-table-column>
          </el-table>
          <el-row type="flex" justify="end" style="margin-top:20px">
            <el-col :xs="24" :sm="24" :md="16" :lg="8" :xl="8">
              <el-input v-model="cart.bill_discount" placeholder="Discount on bill" @keyup.native="refreshCart()">
                <template slot="prepend">Discount</template>
              </el-input>
              <el-input v-model="cart.cash" placeholder="Cash paid" class="mt-10" @keyup.native="refreshCart()">
                <template slot="prepend">Cash</template>
              </el-input>
            </el-col>
          </el-row>
          <div class="sale-final-buttons">
            <el-row :gutter="20" type="flex" justify="space-between">
              <el-col :span="8" class="prev_balance">
                <div v-if="cart.customer"><span class="prevb_title">Previous Balance:</span> {{ cart.prev_balance }}</div>
              </el-col>
              <el-col :span="16">
                <el-button v-if="loadedsale" type="primary" icon="el-icon-download" @click="holdSaleAgain()">Hold '{{ loadedsale }}' Again</el-button>
                <el-popover
                  placement="top"
                  width="300"
                  trigger="click"
                >
                  <el-form ref="form" label-width="120px">
                    <el-form-item label="Naqad Raqm">
                      <el-input v-model="cart.paid_amount" @keyup.native="setCredit()" @keyup.native.enter="completeSale()" />
                    </el-form-item>
                    <el-form-item label="Udhar Raqm">
                      {{ cart.credit }}
                    </el-form-item>
                  </el-form>
                  <el-button slot="reference" type="primary" icon="el-icon-bank-card" :disabled="totalAmount <= 0">Payment</el-button>
                </el-popover>
                <el-popover
                  placement="top"
                  width="300"
                  trigger="click"
                >
                  <el-form ref="form" label-width="120px">
                    <el-form-item label="Sale Name">
                      <el-input v-model="holdsale.name" :disabled="totalAmount == 0" @keyup.native.enter="holdSale()" />
                    </el-form-item>
                    <el-table :data="holdsales">
                      <el-table-column label="Sale" prop="name" />
                      <el-table-column label="Action">
                        <template slot-scope="scope">
                          <el-button type="danger" icon="el-icon-delete" size="mini" circle @click="removeHoldSale(scope.row.name)" />
                          <el-button type="danger" icon="el-icon-upload2" size="mini" circle @click="loadHoldSale(scope.row.name)" />
                        </template>
                      </el-table-column>
                    </el-table>
                  </el-form>
                  <el-button slot="reference" type="warning" icon="el-icon-plus">Hold Sale</el-button>
                </el-popover>
                <el-button-group>
                  <el-button type="danger" icon="el-icon-close" @click="cancelSale()">Cancel</el-button>
                  <el-button v-shortkey="['ctrl', 'z']" type="success" icon="el-icon-goods" :disabled="totalAmount <= 0" @click="completeSale()">Cash</el-button>
                </el-button-group>
              </el-col>
            </el-row>
          </div>
        </el-card>
      </el-col>
    </el-row>
    <add-account v-if="customerForm" :default-type="1" @addcustomer="addCustomerpopup" @newcustomer="newCustomer" />
    <el-dialog title="Print Invoice" :visible.sync="showprint">
      <printinvoice :invoiceid="invoiceid" :v-if="invoiceid" :paidamount="amountpay" />
    </el-dialog>
  </div>
</template>
<script>
import Resource from '@/api/resource';
const customer = new Resource('customer');
const staff = new Resource('staff');
const prod = new Resource('product');
const batch = new Resource('getbatchs');
const saleResorc = new Resource('sale');
const settings = new Resource('settings');
const areaRes = new Resource('areas');
import { getLastInoice } from '@/api/sale';
import { getPreviousBalance } from '@/api/sale';
import { getBadges } from '@/api/sale';
import { getSaleman } from '@/api/customer';
import Printinvoice from './print';
import AddAccount from '../accounts/AddAccount';
export default {
  name: '',
  components: { Printinvoice, AddAccount },
  directives: { },
  data() {
    return {
      wholesaler: false,
      loadedsale: null,
      salequantity: '',
      purchasequantity: '',
      areas: '',
      settings: '',
      customerForm: false,
      searchsale: false,
      showprint: false,
      invoiceid: null,
      amountpay: null,
      list: null,
      search: '',
      total: 0,
      loading: false,
      showcustpopup: false,
      downloading: false,
      products: '',
      featured_product: '',
      batches: '',
      removeHoldedSale: '',
      query: {
        page: 1,
        limit: 15,
        keyword: '',
        select: '',
        search: 'yes',
      },
      customers: null,
      staffers: [],
      addcustomer: {
        name: '',
        phone: '',
        address: '',
        area_id: '',
      },
      addtocart: {
        selectedproduct: '',
        name: '',
        code: '',
        size: '',
        color: '',
        batch_number: '',
        price: '',
        purchase_price: '',
        quantity: '1',
        bonus: '',
        discount1: '',
        discount2: '',
        total: '',
      },
      cart: {
        saletype: 'sale',
        bill_discount: 0,
        discount_type: 'rs',
        customer: '',
        staff: '',
        paid_amount: 0,
        credit: 0,
        due: '',
        products: [],
        ttlQuantity: 0,
        ttlItems: 0,
        ttlAmount: 0,
        prev_balance: 0,
        cash: 0,
      },
      holdsale: {
        name: '',
        cart: '',
      },
      holdsales: [],
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
      const qty = this.cart.products.map(element => parseInt(element.quantity)).reduce((a, b) => a + b, 0);
      const bonus = this.cart.products.map(element => parseInt(element.bonus)).reduce((a, b) => a + b, 0);
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
    Cashreturn: function() {
      return this.cart.cash - this.totalAmount;
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
    this.getStaff();
  },
  mounted() {
    this.$refs.selectedproduct.focus();
    if (localStorage.getItem('sales')) {
      this.holdsales = JSON.parse(localStorage.getItem('sales'));
    }
  },
  methods: {
    addCustomerpopup(data) {
      this.customerForm = data;
    },
    newCustomer(customer) {
      this.customers = customer.data.accounts;
      this.cart.customer = customer.data.accounts[0].id;
    },
    async getAreas() {
      const areas = await areaRes.list();
      this.areas = areas.data.areas;
    },
    async getSettings() {
      const { data } = await settings.list();
      this.settings = data.settings;
    },
    updateProducts() {
      this.refreshCart();
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
    async getCustomers(query) {
      this.loading = true;
      this.query.keyword = query;
      const { data } = await customer.list(this.query);
      this.customers = data.accounts.data;
      this.cart.prev_balance = data.prev_balnace;
      console.log(this.cart.prev_balance);
      this.loading = false;
    },
    async getStaff() {
      const { data } = await getSaleman();
      this.staffers = data.saleman;
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
      let selectedproduct = this.products.filter(product => product.id === index);
      selectedproduct = selectedproduct[0];
      this.fillProductInfo(selectedproduct);
    },
    addFeaturedCart(index) {
      let selectedproduct = this.featured_product.filter(product => product.id === index);
      selectedproduct = selectedproduct[0];
      this.cartProducts(selectedproduct);
    },
    fillProductInfo(selectedproduct) {
      this.addtocart.price = (this.wholesaler) ? selectedproduct.wholesale_price : selectedproduct.sale_price;
      this.addtocart.purchase_price = selectedproduct.purchase_price;
      this.addtocart.name = selectedproduct.name;
      this.addtocart.code = selectedproduct.code;
      this.addtocart.size = selectedproduct.size;
      this.addtocart.color = selectedproduct.color;
      this.salequantity = selectedproduct.sale;
      this.purchasequantity = selectedproduct.purchase;
      this.getBadges(selectedproduct.id);
    },
    async getBadges(productid){
      const { data } = await getBadges(productid);
      this.batches = data.batches;
    },
    async getPrevBalance() {
      const { data } = await getPreviousBalance(this.cart.customer);
      this.cart.prev_balance = data.prev_balnace[0].acc_total;
    },
    async getSale() {
      const { data } = await saleResorc.get(this.cart.saleid);
      data.sale.products = data.sale.products.map(product => {
        return { ...product, name: product.product.name, size: product.product.size, color: product.product.color, selectedproduct: product.product.id };
      });
      this.cart.products = data.sale.products;
      this.cart.staff = data.sale.saleman.id;
      this.refreshCart();
    },
    focusInput(inputcase) {
      let refrr;
      switch (inputcase) {
        case 'selectproduct':
          refrr = (this.settings.show_expiry === 'show') ? 'selectedbatch' : 'price';
          break;
        case 'price':
          refrr = 'price';
          break;
        case 'quantity':
          refrr = 'quantity';
          break;
        case 'bonus':
          refrr = (this.settings.show_bonus === 'show') ? 'bonus' : 'discount1';
          break;
        case 'discount1':
          refrr = 'discount1';
          break;
        case 'discount2':
          if (this.settings.show_disc2 === 'show') {
            refrr = 'discount2';
          } else {
            ((this.addtocart.selectedproduct !== '') && (this.addtocart.price !== '') && (this.addtocart.quantity !== '')) ? this.addToCartFun() : '';
            refrr = 'selectedproduct';
          }
          break;
        default:
          refrr = 'selectedproduct';
      }
      console.log(refrr);
      this.$refs[refrr].focus();
    },
    addToCartFun() {
      const availableStock = this.purchasequantity - this.salequantity;
      if (availableStock < this.addtocart.quantity){
        this.$confirm('Low Stock! Still do you want to sale it?', 'Warning', {
          confirmButtonText: 'OK',
          cancelButtonText: 'Cancel',
          type: 'warning',
        }).then(() => {
          this.preformAddtoCart();
        });
      } else {
        this.preformAddtoCart();
      }
    },
    preformAddtoCart() {
      const productExist = this.cart.products.findIndex(product => product.id === this.addtocart.selectedproduct);
      if (productExist >= 0) {
        const newquantity = this.cart.products[productExist].quantity + 1;
        this.$set(this.cart.products[productExist], 'quantity', newquantity);
        this.refreshCart();
      } else {
        const newproduct = this.addtocart;
        if (this.addtocart.purchase_price > this.addtocart.price){
          this.$message.error('You are going to sell less than purchase price.');
        }
        this.addtocart.bonus = (this.addtocart.bonus) ? this.addtocart.bonus : 0;
        this.addtocart.discount1 = (this.addtocart.discount1) ? this.addtocart.discount1 : 0;
        this.addtocart.discount2 = (this.addtocart.discount2) ? this.addtocart.discount2 : 0;
        this.cart.products.push({ ...this.addtocart });
        this.selectedproduct = '';
        this.resetAddToCart();
        this.focusInput('selectedproduct');
      }
    },
    refreshCart() {
      this.cart.products = this.cart.products.filter(product => product.id == product.id);
    },
    holdSaleAgain() {
      this.holdsale.name = this.loadedsale;
      this.holdSale();
      this.loadedsale = null;
    },
    holdSale() {
      if (this.holdsale.name != '') {
        this.holdsale.cart = { ...this.cart };
        this.holdsales.push({ ...this.holdsale });
        localStorage.setItem('sales', JSON.stringify(this.holdsales));
        this.cart.products = [];
        this.holdsale.name = '';
        this.$message({
          type: 'success',
          message: 'Sale hold successfully.',
        });
      }
    },
    removeHoldSale(salename) {
      this.$confirm('Do you really want to delete hold sale "' + salename + '". Continue?', 'Warning', {
        confirmButtonText: 'OK',
        cancelButtonText: 'Cancel',
        type: 'warning',
      }).then(() => {
        this.holdsales = this.holdsales.filter(sale => {
          if (sale.name != salename) {
            return sale;
          }
        });
        localStorage.setItem('sales', JSON.stringify(this.holdsales));
        this.$message({
          type: 'success',
          message: 'Hold sale delted successfully',
        });
      });
    },
    loadHoldSale(salename) {
      this.removeHoldedSale = salename;
      this.holdsales = this.holdsales.filter(sale => {
        if (sale.name != salename) {
          console.log('in ture');
          return true;
        } else {
          console.log('in false');
          this.cart = sale.cart;
          this.loadedsale = sale.name;
          return false;
        }
      });
      localStorage.setItem('sales', JSON.stringify(this.holdsales));
    },
    setCredit() {
      this.cart.credit = this.cart.ttlAmount - this.cart.paid_amount;
    },
    async getLatestInvoiceId() {
      const { data } = await getLastInoice();
    },
    resetAddToCart() {
      this.addtocart.selectedproduct = '';
      this.addtocart.name = '';
      this.addtocart.code = '';
      this.addtocart.batch_number = '';
      this.addtocart.price = '';
      this.addtocart.quantity = '1';
      this.addtocart.bonus = '';
      this.addtocart.discount1 = '';
      this.addtocart.discount2 = '';
      this.addtocart.total = '';
    },
    productTotal(quantity, price, discount, discount2) {
      quantity = parseFloat(quantity);
      price = parseFloat(price);
      discount = parseFloat(discount);
      let total = quantity * price;
      if (discount) {
        total = (this.cart.discount_type === 'rs') ? (total - discount) : total - (total * discount / 100);
      }

      if (discount2 > 0) {
        total = (this.cart.discount_type === 'rs') ? (total - discount2) : (total - (total * discount2 / 100));
      }
      return total.toFixed(2);
    },
    removeCartProduct(id) {
      this.cart.products = this.cart.products.filter(product => product.selectedproduct !== id);
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
    async completeSale() {
      if (this.cart.credit > 0 && this.cart.customer == ''){
        this.$message({
          message: 'Please add customer first',
          type: 'error',
        });
      } else {
        const { data } = await saleResorc.store(this.cart).then(result => {
          this.$message({
            message: 'Sale added successfully.',
            type: 'success',
          });
          this.loadedsale = '';
          this.invoiceid = result.data.sale.id;
          this.amountpay = this.cart.prev_balance;
          this.cart.products = [];
          this.showprint = true;
        });
      }
    },
    addCustomer(formName) {
      this.$refs[formName].validate((valid) => {
        if (valid) {
          const { data } = customer.store(this.addcustomer).then(result => {
            console.log(result);
            this.customers = result.data.accounts;
            this.cart.customer = result.data.accounts[0].id;
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
.mt-10 {
  margin-top: 10px;
}
.mt-20 {
  margin-top: 20px;
}
.cart-product {
  font-weight: bold;
}
.small {
  font-weight: normal;
  font-size:13px;
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
.salereturn.is-checked {
  border-color: red;
  &.el-radio__label{
    color:red;
  }
}
.prev_balance {
  font-size: 18px;
  font-weight: bold;
  text-align:left;
}
.row-total {
	margin-top: 10px;
	margin-left: 6px;
  font-weight: bold;
}
.sale-top-bar >>> .el-card__body {
    padding:8px 20px !important;
}

.el-select .el-input.is-focus .el-input__inner {
  border-color: #409EFF!important;
}

.el-input.el-input-group.el-input-group--append .el-select .el-input__suffix {
  display: none;
}
.salereturn.is-active >>> span {
	background: #ff4949 !important;
	border-color: #ff4949 !important;
	box-shadow: -1px 0 0 0 #ff4949 !important;
}
</style>

