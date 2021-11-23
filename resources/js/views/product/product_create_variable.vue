<template>
  <div class="app-container">
    <el-form ref="productform" :rules="rules" :model="product" size="mini" :loading="loading">
      <div class="filter-container">
        <el-form-item label="Variation Set">
          <el-select v-model="variation_set" placeholder="Select">
            <el-option
              v-for="set in variant_set"
              :key="set.id"
              :label="set.set_name"
              :value="set.set_code"
            />
          </el-select>
        </el-form-item>
      </div>
      <el-row :gutter="10">
        <el-col :span="8">
          <el-card class="box-card">
            <div slot="header" class="clearfix">
              <span>Product Info</span>
            </div>
            <el-form-item label="Code Start">
              <el-input v-model="product.code" clearable />
            </el-form-item>
            <el-form-item label="Name" prop="name">
              <el-input v-model="product.name" clearable />
            </el-form-item>
            <el-form-item label="Category" prop="category">
              <el-select
                v-model="product.category_id"
                filterable
                reserve-keyword
                placeholder="Search Category"
                :remote-method="getlist"
                :loading="loading">
                <el-option
                  v-for="cat in categories"
                  :key="cat.id"
                  :label="cat.title"
                  :value="cat.id"
                />
              </el-select>
              <el-button @click="showCatPopup()"><svg-icon icon-class="add" /></el-button>
            </el-form-item>
            <el-form-item label="Manufacturer" prop="manufacturer">
              <el-select
                v-model="product.manufacture_id"
                filterable
                reserve-keyword
                placeholder="Search manufacturer"
                :loading="manfucloading">
                <el-option
                  v-for="manfucaturer in manfuacturers"
                  :key="manfucaturer.id"
                  :label="manfucaturer.name"
                  :value="manfucaturer.id" 
                />
              </el-select>
              <el-button @click="manufacPopup()"><svg-icon icon-class="add" /></el-button>
            </el-form-item>
            <el-form-item>
              <el-button type="primary" :loading="loading" @click="onSubmit('productform')">Create</el-button>
              <el-button>Cancel</el-button>
            </el-form-item>
          </el-card>
        </el-col>
        <el-col :span="16">
          <el-card class="box-card">
            <div slot="header" class="clearfix">
              <span>Variation + Price</span>
            </div>
            <el-row v-for="(supvari, index) in product.variants"  :key="index" :gutter="10">
              <el-col :span="3">
                <el-form-item label="Code">
                  <el-input v-model="supvari.code" type="text" step="any" min="0"  />
                </el-form-item>
              </el-col>
              <el-col :span="3">
                <el-form-item label="Color">
                  <el-select
                    v-model="supvari.selected_color"
                    filterable
                    default-first-option
                    clearable
                    placeholder="Choose color">
                    <el-option
                      v-for="color in supvari.colors"
                      :key="color"
                      :label="color"
                      :value="color"
                    />
                  </el-select>
                </el-form-item>
              </el-col>
              <el-col :span="3">
                <el-form-item label="Size">
                  <el-select
                    v-model="supvari.selected_size"
                    filterable
                    clearable
                    default-first-option
                    placeholder="Choose size">
                    <el-option
                      v-for="size in supvari.sizes"
                      :key="size"
                      :label="size"
                      :value="size"
                    />
                  </el-select>
                </el-form-item>
              </el-col>
              <el-col :span="3">
                <el-form-item label="Qty">
                  <el-input v-model="supvari.quantity" type="number" step="any" min="0"  />
                </el-form-item>
              </el-col>
              <el-col :span="3">
                <el-form-item label="P.P">
                  <el-input v-model="supvari.purchase_price" type="number" step="any" min="0"  />
                </el-form-item>
              </el-col>
              <el-col :span="3">
                <el-form-item label="S.P">
                  <el-input v-model="supvari.sale_price" type="number" step="any" min="0"  />
                </el-form-item>
              </el-col>
              <el-col :span="3">
                <el-form-item label="W.P">
                  <el-input v-model="supvari.wholesale_price" type="number" step="any" min="0"  />
                </el-form-item>
              </el-col>
              <el-col :span="2" class="delicon">
                <el-form-item>
                  <el-button v-if="index > 0" type="danger" icon="el-icon-delete" circle size="mini" @click="removeVariant(index)" />
                </el-form-item>
              </el-col>
            </el-row>
            <el-row>
              <el-col :span="10">
                <el-button type="success" @click="addVariant()">Add Variation</el-button>
              </el-col>
            </el-row>
          </el-card>
        </el-col>
      </el-row>
    </el-form>
    <el-dialog title="Add Category" :visible.sync="showcategorypopup">
      <el-form :model="category">
        <el-form-item label="Category Title" label-width="100">
          <el-input v-model="category.title" autocomplete="off"/>
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
          <el-input v-model="manufacturer.name" autocomplete="off"/>
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
          <el-input v-model="unitsofmeasure.name" autocomplete="off"/>
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
const variants = new Resource('variants');
export default {
  name: '',
  components: { },
  directives: {},
  loading: false,
  data() {
    return {
      list: null,
      variant_set: [],
      variations: [],
      variation_set: 'size_color',
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
        type: 'variable',
        purchase_price: '',
        sale_price: '',
        wholesale_price: '',
        quantity: 1,
        reorder: 1,
        category_id: '',
        manufacture_id: '',
        unit: '',
        variants: [{
          quantity: 1,
          purchase_price: 0,
          sale_price: 0,
          wholesale_price: 0,
          colors: [
            'Bige',
            'Black',
            'Blue',
            'Brown',
            'Camel',
            'Cofey',
            'Fawn',
            'Golden',
            'Gray',
            'Green',
            'Mustard',
            'Maroon',
            'Mint',
            'Navy',
            'Natural',
            'Peach',
            'Pink',
            'Red',
            'Skin',
            'Sky bule',
            'Tan',
            'Yellow',
            'White Maroon',
          ],
          sizes: [
            '1','2','3','4','5','6','7','8','9','10','11','12','13','14','15','16','17','18','19','20','21','22','23','24','25','26','27','28','29','30',
            '31','32','33','34','35','36','37','38','39','40','41','42','43','44','45','46','47','48','49'
          ],
        }],
      },
      singvariant: {
        quantity: 1,
        purchase_price: 0,
        sale_price: 0,
        wholesale_price: 0,
        colors: [
          'Bige',
          'Black',
          'Blue',
          'Brown',
          'Camel',
          'Cofey',
          'Fawn',
          'Golden',
          'Gray',
          'Green',
          'Mustard',
          'Maroon',
          'Mint',
          'Navy',
          'Natural',
          'Peach',
          'Pink',
          'Red',
          'Skin',
          'Sky bule',
          'Tan',
          'Yellow',
          'White Maroon',
        ],
        sizes: [
          '1','2','3','4','5','6','7','8','9','10','11','12','13','14','15','16','17','18','19','20','21','22','23','24','25','26','27','28','29','30',
          '31','32','33','34','35','36','37','38','39','40','41','42','43','44','45','46','47','48','49'
        ],
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
          { type: 'array', required: true, message: 'Enter purchase price', trigger: 'change' },
        ],
        sale_price: [
          { type: 'array', required: true, message: 'Enter sale price', trigger: 'change' },
        ],
        quantity: [
          { type: 'array', required: true, message: 'Enter quantity', trigger: 'change' },
        ],
      }
    };
  },
  computed: {},
  created() {
    if ( this.$route.params && this.$route.params.id) {
      this.product.id = this.$route.params.id;
      this.showinventory = false;
      this.getProduct(this.product.id);
    }
    this.getlist();
    this.getVariants();
    this.getManufacturer();
    this.getunits();
  },
  methods: {
    addVariant() {
      this.product.variants.push({ ...this.singvariant });
    },
    removeVariant(index) {
      this.product.variants = this.product.variants.filter((vari,i) => i !== index);
    },
    getProduct(id) {
      const { data } = addPro.get(id).then( result => {
        this.product = data;
      })
    },
    async getVariants() {
      const { data } = await variants.list();
      this.variant_set = Object.values(data)[0];
      this.variation_set = Object.values(data)[0][0].set_code;
      const getvariations = data.variations.filter(vari => vari.set_code === this.variation_set);
      this.variations = getvariations[0].variants;
      this.product.variants[0].options = getvariations[0].variants;
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
      const {data} = category.store(this.category).then(result => {
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
        const {data} = manufac.store(this.manufacturer).then(result => {
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
            this.product = {
              id: '',
              name: '',
              uid: '',
              type: 'variable',
              purchase_price: '',
              sale_price: '',
              wholesale_price: '',
              quantity: 1,
              reorder: 1,
              category_id: '',
              manufacture_id: '',
              unit: '',
              variants: [{
                quantity: 1,
                purchase_price: 0,
                sale_price: 0,
                wholesale_price: 0,
                colors: [
                  'Bige',
                  'Black',
                  'Blue',
                  'Brown',
                  'Camel',
                  'Cofey',
                  'Fawn',
                  'Golden',
                  'Gray',
                  'Green',
                  'Mustard',
                  'Maroon',
                  'Mint',
                  'Navy',
                  'Natural',
                  'Peach',
                  'Pink',
                  'Red',
                  'Skin',
                  'Sky bule',
                  'Tan',
                  'Yellow',
                  'White Maroon',
                ],
                sizes: [
                  '1','2','3','4','5','6','7','8','9','10','11','12','13','14','15','16','17','18','19','20','21','22','23','24','25','26','27','28','29','30',
                  '31','32','33','34','35','36','37','38','39','40','41','42','43','44','45','46','47','48','49'
                ],
              }],
            };
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
  .delicon {
    padding-top: 27px;
  }
</style>
