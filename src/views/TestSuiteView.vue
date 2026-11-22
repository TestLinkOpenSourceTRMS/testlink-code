<template>
  <div class="suite-view d-flex gap-3">
    <!-- 왼쪽: 스위트 트리 -->
    <div class="suite-panel">
      <h6 class="panel-title">테스트 스위트</h6>
      <div v-if="loading" class="text-center py-3"><div class="spinner-border spinner-border-sm"></div></div>
      <TestSuiteTree
        v-else
        :suites="suites"
        :selected-id="selectedSuiteId"
        @select="selectSuite"
      />
    </div>

    <!-- 가운데: TC 목록 -->
    <div class="tc-list-panel" v-if="selectedSuiteId">
      <div class="d-flex justify-content-between align-items-center mb-3">
        <h6 class="panel-title mb-0">{{ selectedSuite?.name }}</h6>
      </div>
      <div v-if="tcLoading" class="text-center py-3"><div class="spinner-border spinner-border-sm"></div></div>
      <TestCaseList
        v-else
        :test-cases="testCases"
        :selected-id="editingTC?.id"
        @select="loadTC"
        @edit="loadTC"
      />
    </div>

    <!-- 오른쪽: TC 편집기 -->
    <div class="tc-editor-panel" v-if="editingTC">
      <TestCaseEditor
        :tc="editingTC"
        @saved="onSaved"
        @close="editingTC = null"
      />
    </div>

    <!-- 스위트 미선택 안내 -->
    <div v-if="!selectedSuiteId" class="flex-grow-1 d-flex align-items-center justify-content-center text-muted">
      <div class="text-center">
        <i class="bi bi-folder2 fs-1 d-block mb-3"></i>
        왼쪽에서 테스트 스위트를 선택하세요
      </div>
    </div>
  </div>
</template>

<script setup>
import { ref, computed, onMounted } from 'vue'
import { useRoute } from 'vue-router'
import TestSuiteTree from '../components/testcases/TestSuiteTree.vue'
import TestCaseList from '../components/testcases/TestCaseList.vue'
import TestCaseEditor from '../components/testcases/TestCaseEditor.vue'
import { getProjectTestSuites } from '../api/projects.js'
import { getSuiteTestCases, getTestCase } from '../api/testcases.js'

const route = useRoute()
const suites = ref([])
const testCases = ref([])
const selectedSuiteId = ref(null)
const editingTC = ref(null)
const loading = ref(true)
const tcLoading = ref(false)

const selectedSuite = computed(() => suites.value.find(s => s.id == selectedSuiteId.value))

onMounted(async () => {
  try {
    const res = await getProjectTestSuites(route.params.id)
    suites.value = res.data?.items || []
  } catch (e) {
    console.error(e)
  } finally {
    loading.value = false
  }
})

async function selectSuite(suite) {
  selectedSuiteId.value = suite.id
  editingTC.value = null
  tcLoading.value = true
  try {
    const res = await getSuiteTestCases(suite.id)
    testCases.value = res.data?.items || []
  } catch (e) {
    console.error(e)
  } finally {
    tcLoading.value = false
  }
}

async function loadTC(tc) {
  try {
    const res = await getTestCase(tc.id || tc.tc_id)
    editingTC.value = res.data?.item || tc
  } catch (e) {
    editingTC.value = tc
  }
}

function onSaved() {
  // 저장 후 목록 새로 고침
  if (selectedSuiteId.value) selectSuite(selectedSuite.value)
}
</script>

<style scoped>
.suite-view { height: calc(100vh - 110px); overflow: hidden; }
.suite-panel {
  width: 220px; min-width: 220px;
  background: white; border: 1px solid #e9ecef; border-radius: 10px; padding: 12px;
  overflow-y: auto;
}
.tc-list-panel {
  width: 320px; min-width: 320px;
  background: white; border: 1px solid #e9ecef; border-radius: 10px; padding: 12px;
  overflow-y: auto;
}
.tc-editor-panel {
  flex: 1; background: white; border: 1px solid #e9ecef; border-radius: 10px; padding: 16px;
  overflow-y: auto;
}
.panel-title { font-weight: 600; font-size: 0.85rem; text-transform: uppercase; color: #6c757d; letter-spacing: 0.5px; }
</style>
