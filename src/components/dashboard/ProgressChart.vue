<template>
  <div class="progress-charts">
    <div class="row g-3">
      <!-- 도넛 차트: 상태 분포 -->
      <div class="col-md-5">
        <div class="chart-card">
          <h6 class="chart-title">실행 현황</h6>
          <div class="donut-wrapper">
            <canvas ref="donutCanvas"></canvas>
            <div class="donut-center" v-if="stats">
              <div class="donut-pct">{{ stats.pass_rate }}%</div>
              <div class="donut-label">통과율</div>
            </div>
          </div>
          <div class="legend d-flex flex-wrap gap-3 justify-content-center mt-2" v-if="stats">
            <div class="legend-item" v-for="item in legendItems" :key="item.key">
              <span class="legend-dot" :style="{ background: item.color }"></span>
              <span class="legend-text">{{ item.label }}: {{ stats[item.key] }}</span>
            </div>
          </div>
        </div>
      </div>

      <!-- 바 차트: 날짜별 추이 -->
      <div class="col-md-7">
        <div class="chart-card">
          <h6 class="chart-title">실행 추이 (최근 30일)</h6>
          <canvas ref="barCanvas" style="height: 220px;"></canvas>
        </div>
      </div>
    </div>
  </div>
</template>

<script setup>
import { ref, onMounted, onUnmounted, watch } from 'vue'
import { Chart, registerables } from 'chart.js'
import { getPlanProgress } from '../../api/executions.js'

Chart.register(...registerables)

const props = defineProps({
  planId: { type: [String, Number], required: true },
  autoRefresh: { type: Boolean, default: true }
})

const donutCanvas = ref(null)
const barCanvas = ref(null)
const stats = ref(null)
let donutChart = null
let barChart = null
let refreshTimer = null

const legendItems = [
  { key: 'passed', label: '통과', color: '#198754' },
  { key: 'failed', label: '실패', color: '#dc3545' },
  { key: 'blocked', label: '차단', color: '#fd7e14' },
  { key: 'not_run', label: '미실행', color: '#dee2e6' }
]

async function loadData() {
  try {
    const res = await getPlanProgress(props.planId)
    stats.value = res.data?.item || null
    if (stats.value) {
      updateDonutChart()
      updateBarChart()
    }
  } catch (e) {
    console.error('차트 데이터 로드 실패:', e)
  }
}

function updateDonutChart() {
  if (!donutCanvas.value || !stats.value) return

  const data = {
    labels: ['통과', '실패', '차단', '미실행'],
    datasets: [{
      data: [
        stats.value.passed,
        stats.value.failed,
        stats.value.blocked,
        stats.value.not_run
      ],
      backgroundColor: ['#198754', '#dc3545', '#fd7e14', '#dee2e6'],
      borderWidth: 0,
      hoverOffset: 4
    }]
  }

  if (donutChart) {
    donutChart.data = data
    donutChart.update()
  } else {
    donutChart = new Chart(donutCanvas.value, {
      type: 'doughnut',
      data,
      options: {
        responsive: true,
        maintainAspectRatio: true,
        cutout: '70%',
        plugins: {
          legend: { display: false },
          tooltip: {
            callbacks: {
              label: (ctx) => ` ${ctx.label}: ${ctx.raw}건`
            }
          }
        }
      }
    })
  }
}

function updateBarChart() {
  if (!barCanvas.value || !stats.value?.trend) return

  // 날짜별 집계
  const trendMap = {}
  ;(stats.value.trend || []).forEach(row => {
    const date = row.exec_date
    if (!trendMap[date]) trendMap[date] = { p: 0, f: 0, b: 0 }
    trendMap[date][row.status] = parseInt(row.cnt)
  })

  const dates = Object.keys(trendMap).sort()
  const passData = dates.map(d => trendMap[d].p || 0)
  const failData = dates.map(d => trendMap[d].f || 0)
  const blockedData = dates.map(d => trendMap[d].b || 0)

  const data = {
    labels: dates.map(d => d.substring(5)), // MM-DD 형식
    datasets: [
      { label: '통과', data: passData, backgroundColor: '#198754' },
      { label: '실패', data: failData, backgroundColor: '#dc3545' },
      { label: '차단', data: blockedData, backgroundColor: '#fd7e14' }
    ]
  }

  if (barChart) {
    barChart.data = data
    barChart.update()
  } else {
    barChart = new Chart(barCanvas.value, {
      type: 'bar',
      data,
      options: {
        responsive: true,
        maintainAspectRatio: false,
        scales: {
          x: { stacked: true, grid: { display: false } },
          y: { stacked: true, beginAtZero: true, ticks: { precision: 0 } }
        },
        plugins: {
          legend: { position: 'bottom', labels: { boxWidth: 12, font: { size: 11 } } }
        }
      }
    })
  }
}

onMounted(() => {
  loadData()
  if (props.autoRefresh) {
    refreshTimer = setInterval(loadData, 30000)
  }
})

onUnmounted(() => {
  if (refreshTimer) clearInterval(refreshTimer)
  if (donutChart) donutChart.destroy()
  if (barChart) barChart.destroy()
})

watch(() => props.planId, loadData)
</script>

<style scoped>
.chart-card {
  background: white;
  border: 1px solid #e9ecef;
  border-radius: 10px;
  padding: 20px;
}
.chart-title { font-weight: 600; color: #495057; margin-bottom: 16px; }
.donut-wrapper { position: relative; max-width: 200px; margin: 0 auto; }
.donut-center {
  position: absolute;
  top: 50%; left: 50%;
  transform: translate(-50%, -50%);
  text-align: center;
  pointer-events: none;
}
.donut-pct { font-size: 1.6rem; font-weight: 700; color: #1e2a3a; }
.donut-label { font-size: 0.75rem; color: #6c757d; }
.legend-item { display: flex; align-items: center; gap: 5px; font-size: 0.8rem; }
.legend-dot { width: 10px; height: 10px; border-radius: 50%; flex-shrink: 0; }
</style>
