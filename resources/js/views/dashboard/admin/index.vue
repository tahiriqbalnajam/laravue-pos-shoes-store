<template>
  <div class="dashboard-editor-container">
    <panel-group @handleSetLineChartData="handleSetLineChartData" />

    <el-row style="background:#fff;padding:16px 16px 0;margin-bottom:32px;">
      <line-chart v-if="lineChartData" :chart-data="lineChartData" :x-axis-data="xaxisdata" />
    </el-row>
  </div>
</template>

<script>
import GithubCorner from '@/components/GithubCorner';
import PanelGroup from './components/PanelGroup';
import LineChart from './components/LineChart';
import { dailySale } from '@/api/helper';

const lineChartData = {
  newVisitis: {
    expectedData: [100, 120, 161, 134, 105, 160, 165],
    actualData: [120, 82, 91, 154, 162, 140, 145],
  },
  messages: {
    expectedData: [200, 192, 120, 144, 160, 130, 140],
    actualData: [180, 160, 151, 106, 145, 150, 130],
  },
  purchases: {
    expectedData: [80, 100, 121, 104, 105, 90, 100],
    actualData: [120, 90, 100, 138, 142, 130, 130],
  },
  shoppings: {
    expectedData: [130, 140, 141, 142, 145, 150, 160],
    actualData: [120, 82, 91, 154, 162, 140, 130],
  },
};

export default {
  name: 'DashboardAdmin',
  components: {
    GithubCorner,
    PanelGroup,
    LineChart,
  },
  data() {
    return {
      lineChartData: '',
      xaxisdata: '',
      stock: '',
    };
  },
  created() {
    this.dailySaleFun();
  },
  methods: {
    handleSetLineChartData(type) {
      this.lineChartData = lineChartData[type];
    },
    async dailySaleFun() {
      const { data } = await dailySale();
      const dates = data.sales.map(sale => {
        return sale.date;
      });
      const dailysale = data.sales.map(sale => {
        return sale.total;
      });

      this.xaxisdata = dates;
      this.lineChartData = {
        sales: dailysale,
      };
    },
  },
};
</script>

<style rel="stylesheet/scss" lang="scss" scoped>
.dashboard-editor-container {
  padding: 32px;
  background-color: rgb(240, 242, 245);
  .chart-wrapper {
    background: #fff;
    padding: 16px 16px 0;
    margin-bottom: 32px;
  }
}
</style>
