<template>
  <div class="chart-card">
    <h6 class="chart-title">{{ title }}</h6>
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
      default: 200,
    },
    categories: {
      type: Array,
      default: () => [],
    },
    series: {
      type: Array,
      default: () => [],
    },
    colors: {
      type: Array,
      default: () => ['#8FBC8F', '#87CEEB', '#2F4F4F'],
    },
    horizontal: {
      type: Boolean,
      default: false,
    },
    stacked: {
      type: Boolean,
      default: false,
    },
    donutSize: {
      type: String,
      default: '50%',
    },
    showLabels: {
      type: Array,
      default: () => [],
    }
  },
  computed: {
    chartOptions() {
      const baseOptions = {
        chart: {
          toolbar: {
            show: false
          }
        },
        colors: this.colors,
        dataLabels: {
          enabled: false
        },
        legend: {
          show: false
        }
      };

      // Configuración específica para donut
      if (this.type === 'donut') {
        return {
          ...baseOptions,
          labels: this.showLabels.length ? this.showLabels : this.categories,
          plotOptions: {
            pie: {
              donut: {
                size: this.donutSize
              }
            }
          },
          stroke: {
            width: 0
          }
        };
      }

      // Configuración para gráficos de barras
      if (this.type === 'bar') {
        return {
          ...baseOptions,
          chart: {
            ...baseOptions.chart,
            stacked: this.stacked
          },
          plotOptions: {
            bar: {
              horizontal: this.horizontal,
              columnWidth: this.horizontal ? '70%' : '60%',
              barHeight: this.horizontal ? '70%' : undefined,
              borderRadius: this.horizontal ? 2 : 4
            }
          },
          xaxis: {
            categories: this.categories,
            labels: {
              show: this.horizontal ? false : !this.hideAxisLabels,
              style: {
                fontSize: '12px',
                colors: '#666'
              }
            },
            axisBorder: {
              show: !this.horizontal
            },
            axisTicks: {
              show: false
            }
          },
          yaxis: {
            show: this.horizontal,
            labels: {
              show: false
            },
            axisBorder: {
              show: this.horizontal
            }
          },
          grid: {
            show: false
          }
        };
      }

      // Configuración por defecto
      return {
        ...baseOptions,
        xaxis: {
          categories: this.categories,
        }
      };
    },
  },
};
</script>

<style scoped>
.chart-card {
  background: rgba(255, 255, 255, 0.4);
  backdrop-filter: blur(10px);
  border-radius: 12px;
  padding: 15px 20px 25px 20px;
  box-shadow: 0 2px 10px rgba(0, 0, 0, 0.05);
  border: 1px solid rgba(255, 255, 255, 0.2);
  margin-top: 20px;
}

.chart-title {
  color: #2F4F4F;
  font-weight: 600;
  margin-bottom: 15px;
  font-size: 14px;
}
</style>