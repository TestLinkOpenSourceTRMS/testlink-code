<template>
  <div class="tc-editor">
    <div class="editor-header mb-3">
      <div class="d-flex justify-content-between align-items-center">
        <h5 class="mb-0">{{ tc.name }}</h5>
        <div class="d-flex align-items-center gap-2">
          <span v-if="autoSaved" class="text-success small">
            <i class="bi bi-check2 me-1"></i>자동 저장됨
          </span>
          <span v-if="saving" class="text-muted small">
            <span class="spinner-border spinner-border-sm me-1"></span>저장 중...
          </span>
        </div>
      </div>
      <div class="text-muted small mt-1">{{ tc.external_id }}</div>
    </div>

    <!-- 사전 조건 -->
    <div class="mb-4">
      <label class="editor-label">사전 조건</label>
      <div class="tiptap-wrapper" v-if="editors.preconditions">
        <div class="tiptap-toolbar">
          <button @click="editors.preconditions.chain().focus().toggleBold().run()" :class="{ active: editors.preconditions.isActive('bold') }">B</button>
          <button @click="editors.preconditions.chain().focus().toggleItalic().run()" :class="{ active: editors.preconditions.isActive('italic') }">I</button>
          <button @click="editors.preconditions.chain().focus().toggleBulletList().run()" :class="{ active: editors.preconditions.isActive('bulletList') }">≡</button>
        </div>
        <EditorContent :editor="editors.preconditions" class="tiptap-content" />
      </div>
    </div>

    <!-- 테스트 단계 -->
    <div class="mb-4">
      <div class="d-flex justify-content-between align-items-center mb-2">
        <label class="editor-label mb-0">테스트 단계</label>
        <button class="btn btn-sm btn-outline-primary" @click="addStep">
          <i class="bi bi-plus me-1"></i>단계 추가
        </button>
      </div>

      <div
        v-for="(step, idx) in steps"
        :key="step._key"
        class="step-editor-row"
        draggable="true"
        @dragstart="onDragStart(idx)"
        @dragover.prevent="onDragOver(idx)"
        @drop="onDrop(idx)"
      >
        <div class="step-handle">
          <i class="bi bi-grip-vertical text-muted"></i>
        </div>
        <div class="step-num">{{ idx + 1 }}</div>
        <div class="step-fields">
          <textarea
            class="form-control mb-2"
            v-model="step.actions"
            placeholder="실행 단계"
            rows="2"
            @input="scheduleAutoSave"
          ></textarea>
          <textarea
            class="form-control"
            v-model="step.expected_results"
            placeholder="예상 결과"
            rows="2"
            @input="scheduleAutoSave"
          ></textarea>
        </div>
        <button class="btn btn-sm btn-outline-danger step-delete" @click="removeStep(idx)" title="삭제">
          <i class="bi bi-trash"></i>
        </button>
      </div>

      <div v-if="steps.length === 0" class="text-muted small py-2">
        단계가 없습니다. "단계 추가" 버튼을 클릭하세요.
      </div>
    </div>

    <!-- 저장 버튼 -->
    <div class="d-flex gap-2">
      <button class="btn btn-primary" @click="save" :disabled="saving">
        <span v-if="saving" class="spinner-border spinner-border-sm me-1"></span>
        저장
      </button>
      <button class="btn btn-outline-secondary" @click="$emit('close')">취소</button>
    </div>
  </div>
</template>

<script setup>
import { ref, reactive, onMounted, onUnmounted } from 'vue'
import { useEditor, EditorContent } from '@tiptap/vue-3'
import StarterKit from '@tiptap/starter-kit'

const props = defineProps({
  tc: { type: Object, required: true }
})

const emit = defineEmits(['saved', 'close'])

const saving = ref(false)
const autoSaved = ref(false)
let autoSaveTimer = null
let dragSrcIdx = null

// 스텝 상태
const steps = reactive([])

// TipTap 에디터들
const editors = reactive({
  preconditions: null
})

onMounted(() => {
  // 사전 조건 에디터
  editors.preconditions = useEditor({
    content: props.tc.preconditions || '',
    extensions: [StarterKit],
    onUpdate: () => scheduleAutoSave()
  })

  // 스텝 초기화
  if (props.tc.steps && props.tc.steps.length > 0) {
    props.tc.steps.forEach((s, i) => {
      steps.push({
        _key: Date.now() + i,
        step_number: s.step_number,
        actions: s.actions || '',
        expected_results: s.expected_results || ''
      })
    })
  }
})

onUnmounted(() => {
  if (autoSaveTimer) clearTimeout(autoSaveTimer)
  editors.preconditions?.destroy()
})

function addStep() {
  steps.push({
    _key: Date.now(),
    step_number: steps.length + 1,
    actions: '',
    expected_results: ''
  })
  scheduleAutoSave()
}

function removeStep(idx) {
  steps.splice(idx, 1)
  steps.forEach((s, i) => s.step_number = i + 1)
  scheduleAutoSave()
}

// 드래그 앤 드롭 재순서
function onDragStart(idx) { dragSrcIdx = idx }
function onDragOver(idx) {}
function onDrop(targetIdx) {
  if (dragSrcIdx === null || dragSrcIdx === targetIdx) return
  const moved = steps.splice(dragSrcIdx, 1)[0]
  steps.splice(targetIdx, 0, moved)
  steps.forEach((s, i) => s.step_number = i + 1)
  dragSrcIdx = null
  scheduleAutoSave()
}

function scheduleAutoSave() {
  autoSaved.value = false
  if (autoSaveTimer) clearTimeout(autoSaveTimer)
  autoSaveTimer = setTimeout(() => autoSave(), 2000)
}

async function autoSave() {
  if (saving.value) return
  await save(true)
}

async function save(isAuto = false) {
  saving.value = true
  try {
    const payload = {
      preconditions: editors.preconditions?.getHTML() || '',
      steps: steps.map((s, i) => ({
        step_number: i + 1,
        actions: s.actions,
        expected_results: s.expected_results
      }))
    }
    // API 호출 (현재는 console.log로 확인)
    console.log('저장:', payload)
    // TODO: API 연동 - PUT /testcases/{id}

    if (isAuto) {
      autoSaved.value = true
      setTimeout(() => autoSaved.value = false, 3000)
    } else {
      emit('saved', payload)
    }
  } catch (e) {
    console.error('저장 실패:', e)
  } finally {
    saving.value = false
  }
}
</script>

<style scoped>
.tc-editor { padding: 4px; }
.editor-label { font-weight: 600; font-size: 0.8rem; text-transform: uppercase; color: #6c757d; letter-spacing: 0.5px; margin-bottom: 8px; display: block; }
.tiptap-wrapper { border: 1px solid #dee2e6; border-radius: 8px; overflow: hidden; }
.tiptap-toolbar {
  background: #f8f9fa;
  border-bottom: 1px solid #dee2e6;
  padding: 6px 10px;
  display: flex; gap: 4px;
}
.tiptap-toolbar button {
  background: none; border: none;
  padding: 2px 8px; border-radius: 4px;
  cursor: pointer; font-weight: 600;
}
.tiptap-toolbar button:hover { background: #e9ecef; }
.tiptap-toolbar button.active { background: #0d6efd; color: white; }
.tiptap-content { padding: 12px; min-height: 80px; outline: none; font-size: 0.9rem; }

.step-editor-row {
  display: flex;
  align-items: flex-start;
  gap: 10px;
  background: #f8f9fa;
  border: 1px solid #e9ecef;
  border-radius: 8px;
  padding: 12px;
  margin-bottom: 8px;
  cursor: grab;
}
.step-editor-row:active { cursor: grabbing; }
.step-handle { padding-top: 8px; color: #adb5bd; }
.step-num {
  width: 28px; height: 28px; min-width: 28px;
  background: #0d6efd; color: white;
  border-radius: 50%;
  display: flex; align-items: center; justify-content: center;
  font-size: 0.8rem; font-weight: 600;
  margin-top: 4px;
}
.step-fields { flex: 1; }
.step-delete { margin-top: 4px; }
</style>
