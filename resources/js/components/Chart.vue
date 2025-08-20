<template>
  <div>
    <apexchart
      :type="type"
      :height="height"
      :options="chartOptions"
      :series="series"
    />
  </div>
</template>

<script>
import VueApexCharts from "vue3-apexcharts";

export default {
  name: "Chart",
  components: {
    apexchart: VueApexCharts,
  },
  props: {
    title: {
      type: String,
      default: "Gráfico",
    },
    type: {
      type: String,
      default: "bar",
    },
    height: {
      type: [Number, String],
      default: 350,
    },
    categories: {
      type: Array,
      default: () => [],
    },
    series: {
      type: Array,
      default: () => [],
    },
  },
  computed: {
    chartOptions() {
      return {
        chart: {
          id: "vuechart-example",
        },
        xaxis: {
          categories: this.categories,
        },
        title: {
          text: this.title,
          align: "center",
        },
      };
    },
  },
};
</script>
