<template>
  <div>
    <el-button @click="showPopup()"><svg-icon icon-class="add" /></el-button>
    <el-dialog title="Add Category" :visible.sync="showmanufacpopup">
      <el-form :model="category">
        <el-form-item label="Manfucturer Name" label-width="100">
          <el-input v-model="manufacturer.title" autocomplete="off" />
        </el-form-item>
      </el-form>
      <span slot="footer" class="dialog-footer">
        <el-button @click="showmanufacpopup = false">Cancel</el-button>
        <el-button type="primary" @click="addManufac()">Confirm</el-button>
      </span>
    </el-dialog>
  </div>
</template>
<script>
import Resource from '@/api/resource';
const manufac = new Resource('manufacturer');
export default {
  name: 'AddManufacturer',
  components: {},
  directives: { },
  data() {
    return {
      showmanufacpopup: false,
      loading: true,
    };
  },
  computed: {
  },
  created() {
    this.getList();
  },
  methods: {
    async addManufac() {
      const { data } = await addPro.list(this.query);
      this.list = data.products.data;
      this.total = data.products.total;
    },
  },
};
</script>
<style  scoped>
</style>
