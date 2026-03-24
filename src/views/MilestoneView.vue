<template>
  <div>
    <div class="d-flex justify-content-between align-items-center mb-4">
      <div>
        <h2 class="mb-0">밀리스톤</h2>
        <small class="text-muted" v-if="projectName">{{ projectName }}</small>
      </div>
      <button class="btn btn-primary" @click="showForm = true" :disabled="!currentProjectId">
        <i class="bi bi-plus-lg me-1"></i>밀리스톤 추가
      </button>
    </div>

    <!-- 프로젝트 미선택 안내 -->
    <div v-if="!currentProjectId" class="alert alert-info">
      <i class="bi bi-info-circle me-2"></i>
      상단 헤더에서 프로젝트를 선택하면 밀리스톤을 관리할 수 있습니다.
    </div>

    <!-- 통계 요약 -->
    <div v-else class="row g-3 mb-4">
      <div class="col-md-3">
        <div class="stat-mini">
          <div class="stat-mini-value">{{ milestones.length }}</div>
          <div class="stat-mini-label">전체</div>
        </div>
      </div>
      <div class="col-md-3">
        <div class="stat-mini">
          <div class="stat-mini-value text-success">{{ completedCount }}</div>
          <div class="stat-mini-label">완료</div>
        </div>
      </div>
      <div class="col-md-3">
        <div class="stat-mini">
          <div class="stat-mini-value text-danger">{{ overdueCount }}</div>
          <div class="stat-mini-label">기한 초과</div>
        </div>
      </div>
      <div class="col-md-3">
        <div class="stat-mini">
          <div class="stat-mini-value text-primary">{{ activeCount }}</div>
          <div class="stat-mini-label">진행 중</div>
        </div>
      </div>
    </div>

    <!-- 로딩 -->
    <div v-if="loading" class="text-center py-5">
      <div class="spinner-border text-primary"></div>
    </div>

    <!-- 밀리스톤 목록 -->
    <MilestoneList
      v-else
      :milestones="milestones"
      @edit="openEdit"
      @delete="confirmDelete"
    />

    <!-- 밀리스톤 폼 모달 -->
    <MilestoneForm
      v-if="showForm"
      :milestone="editingMilestone"
      :loading="saving"
      @save="saveMilestone"
      @close="closeForm"
    />

    <!-- 삭제 확인 모달 -->
    <div v-if="deletingMilestone" class="modal fade show d-block" tabindex="-1" @click.self="deletingMilestone = null">
      <div class="modal-dialog modal-sm">
        <div class="modal-content">
          <div class="modal-header">
            <h6 class="modal-title">삭제 확인</h6>
          </div>
          <div class="modal-body">
            <p>"{{ deletingMilestone.name }}"을(를) 삭제하시겠습니까?</p>
          </div>
          <div class="modal-footer">
            <button class="btn btn-sm btn-secondary" @click="deletingMilestone = null">취소</button>
            <button class="btn btn-sm btn-danger" @click="deleteMilestone">삭제</button>
          </div>
        </div>
      </div>
    </div>
    <div v-if="deletingMilestone" class="modal-backdrop fade show"></div>
  </div>
</template>

<script setup>
import { ref, computed, onMounted, watch } from 'vue'
import MilestoneList from '../components/milestones/MilestoneList.vue'
import MilestoneForm from '../components/milestones/MilestoneForm.vue'
import { getProjectMilestones, createProjectMilestone } from '../api/projects.js'
import { updateMilestone, deleteMilestone as deleteMilestoneApi } from '../api/milestones.js'

const milestones = ref([])
const loading = ref(false)
const saving = ref(false)
const showForm = ref(false)
const editingMilestone = ref(null)
const deletingMilestone = ref(null)

// 프로젝트 ID는 URL에서 가져오거나 localStorage에서 복원
const currentProjectId = ref(null)
const projectName = ref('')

// 통계
const completedCount = computed(() => milestones.value.filter(m => m.is_completed == 1).length)
const overdueCount = computed(() => milestones.value.filter(m => {
  return m.due_date && m.is_completed != 1 && new Date(m.due_date) < new Date()
}).length)
const activeCount = computed(() => milestones.value.filter(m => m.is_completed != 1).length)

onMounted(() => {
  // localStorage에서 마지막 선택 프로젝트 복원
  const savedProjectId = localStorage.getItem('tl_current_project_id')
  if (savedProjectId) {
    currentProjectId.value = savedProjectId
    projectName.value = localStorage.getItem('tl_current_project_name') || ''
    loadMilestones()
  }
})

async function loadMilestones() {
  if (!currentProjectId.value) return
  loading.value = true
  try {
    const res = await getProjectMilestones(currentProjectId.value)
    milestones.value = res.data?.items || []
  } catch (e) {
    console.error(e)
  } finally {
    loading.value = false
  }
}

function openEdit(ms) {
  editingMilestone.value = ms
  showForm.value = true
}

function closeForm() {
  showForm.value = false
  editingMilestone.value = null
}

async function saveMilestone(data) {
  saving.value = true
  try {
    if (editingMilestone.value?.id) {
      await updateMilestone(editingMilestone.value.id, data)
    } else {
      await createProjectMilestone(currentProjectId.value, data)
    }
    await loadMilestones()
    closeForm()
  } catch (e) {
    console.error(e)
  } finally {
    saving.value = false
  }
}

function confirmDelete(ms) {
  deletingMilestone.value = ms
}

async function deleteMilestone() {
  if (!deletingMilestone.value) return
  try {
    await deleteMilestoneApi(deletingMilestone.value.id)
    await loadMilestones()
  } catch (e) {
    console.error(e)
  } finally {
    deletingMilestone.value = null
  }
}
</script>

<style scoped>
.stat-mini {
  background: white;
  border: 1px solid #e9ecef;
  border-radius: 8px;
  padding: 16px;
  text-align: center;
}
.stat-mini-value { font-size: 1.8rem; font-weight: 700; }
.stat-mini-label { color: #6c757d; font-size: 0.8rem; margin-top: 2px; }
</style>
