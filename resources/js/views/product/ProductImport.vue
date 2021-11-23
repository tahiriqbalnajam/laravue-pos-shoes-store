<template>
  <div class="app-container">
    <upload-excel-component :on-success="handleSuccess" :before-upload="beforeUpload" />
  </div>
</template>
<script>
import UploadExcelComponent from '@/components/UploadExcel/index.vue';
import { importProducts } from '@/api/product';
import Resource from '@/api/resource';
const addPro = new Resource('product');
export default {
  name: 'ProductImport',
  components: { UploadExcelComponent },
  directives: { },
  data() {
    return {
      tableData: [],
      tableHeader: [],
    };
  },
  methods: {
    beforeUpload(file) {
      const isLt1M = file.size / 1024 / 1024 < 1;

      if (isLt1M) {
        return true;
      }

      this.$message({
        message: 'Please do not upload files larger than 1m in size.',
        type: 'warning',
      });
      return false;
    },
    async handleSuccess({ results, header }) {
      const query = {
        header: header,
        results: results,
      };
      await importProducts(query);
      // this.tableData = results;
      // this.tableHeader = header;
    },
  },
};
</script>
<style  scoped>
</style>
