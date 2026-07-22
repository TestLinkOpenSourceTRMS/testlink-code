/* eslint-disable react-refresh/only-export-components */
import {
  createContext,
  useCallback,
  useContext,
  useMemo,
  useState,
  type ReactNode,
} from 'react'

export type Locale = 'en' | 'ko' | 'vi'

const STORAGE_KEY = 'tl.locale'

/**
 * English is the source dictionary — every other locale must provide
 * exactly these keys (tsc enforces it via the Messages type below).
 * Dynamic strings are template functions so word order can differ per locale.
 */
const en = {
  // shared
  loading: 'Loading…',
  add: 'Add',
  cancel: 'Cancel',
  edit: 'Edit',
  saved: 'Saved',
  build: 'Build',
  runs: 'Runs',
  total: 'Total',
  testCase: 'Test case',
  testSuite: 'Test suite',
  tester: 'Tester',
  coverage: 'Coverage',
  cases: 'Cases',

  // verdicts
  verdictPassed: 'Passed',
  verdictFailed: 'Failed',
  verdictBlocked: 'Blocked',
  verdictNotRun: 'Not run',

  // shell / nav
  navDashboard: 'Dashboard',
  navTestCases: 'Test cases',
  navRun: 'Run',
  navMatrix: 'Matrix',
  navPlans: 'Plans',
  navReports: 'Reports',
  signOut: 'Sign out',
  project: 'Project',
  testPlan: 'Test plan',
  language: 'Language',

  // login
  loginTagline: 'Plan, run and read your testing.',
  loginPlaceholder: 'Login',
  passwordPlaceholder: 'Password',
  signIn: 'Sign in',
  signingIn: 'Signing in…',
  signInFailed: 'Sign-in failed',

  // dashboard
  linkedCasesCount: (n: string) => `${n} linked test cases`,
  passRateLabel: 'pass rate',
  passRateSuffix: 'of latest runs',
  dailyExecutions: 'Daily executions',
  flakyCandidates: 'Flaky candidates',
  noFlaky: 'No pass/fail flapping detected. Steady suite.',
  statusFlips: 'Status flips',
  instability: 'Instability',

  // spec / suites
  suites: 'Suites',
  newSuite: 'New suite',
  newSuiteTitle: 'New suite (inside the selected suite, or at project root)',
  suiteNamePlaceholder: 'Suite name',
  newCaseNamePlaceholder: 'New test case name',
  pickSuite: 'Pick a suite to see its test cases.',
  emptySuite: 'This suite has no test cases yet — write the first one above.',
  selectCase: 'Select a test case to read it.',
  linkedToPlan: (plan: string) => `Linked to ${plan}`,
  alreadyInPlan: (plan: string) => `Already in ${plan}`,
  addToPlan: (plan: string) => `Add to ${plan}`,
  stepsCount: (n: number) => `Steps · ${n}`,
  noStepsWritten: 'No steps written for this case yet.',
  recentRuns: 'Recent runs',
  neverExecuted: 'Never executed.',
  expand: 'Expand',
  collapse: 'Collapse',

  // case editor
  saveChanges: 'Save changes',
  saving: 'Saving…',
  saveError: 'Could not save. Try again.',
  summary: 'Summary',
  summaryPlaceholder: 'What this case verifies',
  preconditions: 'Preconditions',
  preconditionsPlaceholder: 'State required before running',
  addStep: 'Add step',
  noStepsAddFirst: 'No steps — add the first one.',
  actionPlaceholder: 'Action',
  expectedResultPlaceholder: 'Expected result',
  removeStep: 'Remove step',

  // run
  assignedTo: 'Assigned to',
  assignTo: 'Assign to',
  everyone: 'Everyone',
  unassigned: 'Unassigned',
  casesPageInfo: (total: string, page: number, pages: number) =>
    `${total} cases · page ${page}/${pages}`,
  noLinkedCases: 'No test cases linked to this plan.',
  recordResult: 'Record result',
  pickFromQueue: 'Pick a case from the queue, decide, record.',
  viewStepsHistory: 'View steps & history →',
  notesPlaceholder: 'Notes for this run (optional)',
  saveResultError: 'Could not save the result. Try again.',

  // matrix
  resultMatrix: 'Result matrix',
  bySuiteTab: 'By suite',
  byCaseTab: 'By case',
  noSuitesLinked: 'No suites with linked test cases.',
  openCaseDetail: 'Open case-level detail',
  noCasesHere: 'No test cases here.',
  openInTestCases: 'Open in Test cases',
  clickSuiteHint: 'Click a suite row to drill into its cases.',

  // plans
  testPlans: 'Test plans',
  selectedTag: 'selected',
  newPlanPlaceholder: 'New plan name',
  createPlan: 'Create plan',
  builds: 'Builds',
  noBuilds: 'No builds yet — create the first one below.',
  activeLabel: 'active',
  inactiveLabel: 'inactive',
  openLabel: 'open',
  closedLabel: 'closed',
  newBuildPlaceholder: 'New build name',
  createBuild: 'Create build',

  // reports
  executionTrend: 'Execution trend',
  buildComparison: 'Build comparison',
  byTester: 'By tester',
  noExecutionsYet: 'No executions recorded yet.',
  flakyTestsAnalyzed: (n: string) => `Flaky tests · ${n} versions analyzed`,
  flips: 'Flips',

  // trend chart
  trendAriaLabel: 'Daily executions by verdict',

  // search
  searchPlaceholder: 'Search cases (name, ID, text)…',
  searchNoResults: 'Nothing found.',

  // attachments
  attachEvidence: 'Attach evidence (optional)',
  evidenceUploaded: 'Evidence uploaded',
  attachmentsLabel: 'Attachments',

  // projects & builds admin
  newProjectTitle: 'New project',
  projectNamePlaceholder: 'Project name',
  projectPrefixPlaceholder: 'Prefix (e.g. ABC)',
  createProject: 'Create project',
  buildClose: 'Close',
  buildReopen: 'Reopen',

  // export
  exportCsv: 'Export CSV',

  // requirements
  navRequirements: 'Requirements',
  reqSpecs: 'Specifications',
  newReqSpec: 'New spec',
  newReqSpecTitle:
    'New requirement spec (inside the selected spec, or at project root)',
  reqSpecDocIdPlaceholder: 'Doc ID (e.g. RS-1)',
  reqSpecTitlePlaceholder: 'Spec title',
  pickReqSpec: 'Pick a specification to see its requirements.',
  emptyReqSpec: 'No requirements in this spec yet — add the first one above.',
  selectRequirement: 'Select a requirement to read it.',
  newReqDocIdPlaceholder: 'Doc ID (e.g. REQ-1)',
  newReqTitlePlaceholder: 'New requirement title',
  scopeLabel: 'Scope',
  coveredCases: 'Covered test cases',
  noCoveredCases: 'No test cases cover this requirement yet.',
  coverageSearchPlaceholder: 'Search cases to cover…',
  unlink: 'Unlink',
  reqPlanCoverage: (plan: string) => `Coverage in ${plan}`,
  noReqCoverageInPlan:
    'No covered test cases are linked to this plan yet.',
  inPlanOfCovered: (inPlan: number, covered: number) =>
    `${inPlan}/${covered} in plan`,

  // admin
  navAdmin: 'Admin',
  adminNeedProject:
    'Select a project to manage its keywords, platforms and custom fields.',
  adminSaveError: 'Could not save. Check the fields and try again.',
  adminTabUsers: 'Users',
  adminTabKeywords: 'Keywords',
  adminTabPlatforms: 'Platforms',
  adminTabCustomFields: 'Custom fields',
  // users tab
  adminColLogin: 'Login',
  adminColName: 'Name',
  adminColEmail: 'Email',
  adminColRole: 'Role',
  adminColStatus: 'Status',
  adminActivate: 'Activate',
  adminDeactivate: 'Deactivate',
  newUserTitle: 'New user',
  userLoginPlaceholder: 'Login',
  userPasswordPlaceholder: 'Password',
  userFirstPlaceholder: 'First name',
  userLastPlaceholder: 'Last name',
  userEmailPlaceholder: 'Email',
  createUser: 'Create user',
  noUsers: 'No users.',
  // keywords tab
  keywordPlaceholder: 'Keyword',
  keywordNotesPlaceholder: 'Notes (optional)',
  addKeyword: 'Add keyword',
  noKeywords: 'No keywords in this project yet.',
  adminColKeyword: 'Keyword',
  adminColNotes: 'Notes',
  adminColUsage: 'Used by',
  adminDelete: 'Delete',
  confirmDeleteKeyword: (kw: string) =>
    `Delete keyword "${kw}"? Its links to test cases will be removed too.`,
  // platforms tab
  platformNamePlaceholder: 'Platform name',
  platformNotesPlaceholder: 'Notes (optional)',
  addPlatform: 'Add platform',
  noPlatforms: 'No platforms in this project yet.',
  adminColPlatform: 'Platform',
  adminColEnabled: 'Enabled on',
  enabledDesign: 'Design',
  enabledExecution: 'Execution',
  // custom fields tab
  adminColLabel: 'Label',
  adminColType: 'Type',
  adminColAppliesTo: 'Applies to',
  noCustomFields: 'No custom fields linked to this project.',
  cfLegacyHint:
    'Custom field creation is done in the legacy UI for now — this list is read-only.',
}

export type Messages = typeof en
export type MsgKey = keyof Messages
/** keys whose value is a plain string (no template parameters) */
export type StringMsgKey = {
  [K in MsgKey]: Messages[K] extends string ? K : never
}[MsgKey]

const ko: Messages = {
  loading: '불러오는 중…',
  add: '추가',
  cancel: '취소',
  edit: '편집',
  saved: '저장됨',
  build: '빌드',
  runs: '실행 횟수',
  total: '합계',
  testCase: '테스트 케이스',
  testSuite: '테스트 스위트',
  tester: '테스터',
  coverage: '커버리지',
  cases: '케이스 수',

  verdictPassed: '성공',
  verdictFailed: '실패',
  verdictBlocked: '차단됨',
  verdictNotRun: '미실행',

  navDashboard: '대시보드',
  navTestCases: '테스트 케이스',
  navRun: '실행',
  navMatrix: '매트릭스',
  navPlans: '테스트 플랜',
  navReports: '리포트',
  signOut: '로그아웃',
  project: '프로젝트',
  testPlan: '테스트 플랜',
  language: '언어',

  loginTagline: '테스트를 계획하고, 실행하고, 확인하세요.',
  loginPlaceholder: '아이디',
  passwordPlaceholder: '비밀번호',
  signIn: '로그인',
  signingIn: '로그인 중…',
  signInFailed: '로그인에 실패했습니다',

  linkedCasesCount: (n) => `연결된 테스트 케이스 ${n}개`,
  passRateLabel: '최근 실행 성공률',
  passRateSuffix: '',
  dailyExecutions: '일별 실행 현황',
  flakyCandidates: '불안정 테스트 후보',
  noFlaky: '성공/실패 반복이 감지되지 않았습니다. 안정적인 스위트입니다.',
  statusFlips: '상태 변동',
  instability: '불안정도',

  suites: '스위트',
  newSuite: '새 스위트',
  newSuiteTitle: '새 스위트 (선택한 스위트 안 또는 프로젝트 루트에 생성)',
  suiteNamePlaceholder: '스위트 이름',
  newCaseNamePlaceholder: '새 테스트 케이스 이름',
  pickSuite: '스위트를 선택하면 테스트 케이스가 표시됩니다.',
  emptySuite:
    '이 스위트에는 아직 테스트 케이스가 없습니다. 위에서 첫 케이스를 작성해 보세요.',
  selectCase: '테스트 케이스를 선택해 내용을 확인하세요.',
  linkedToPlan: (plan) => `${plan}에 추가됨`,
  alreadyInPlan: (plan) => `이미 ${plan}에 있음`,
  addToPlan: (plan) => `${plan}에 추가`,
  stepsCount: (n) => `스텝 · ${n}`,
  noStepsWritten: '이 케이스에는 아직 작성된 스텝이 없습니다.',
  recentRuns: '최근 실행',
  neverExecuted: '실행 이력이 없습니다.',
  expand: '펼치기',
  collapse: '접기',

  saveChanges: '변경사항 저장',
  saving: '저장 중…',
  saveError: '저장하지 못했습니다. 다시 시도해 주세요.',
  summary: '요약',
  summaryPlaceholder: '이 케이스가 검증하는 내용',
  preconditions: '사전 조건',
  preconditionsPlaceholder: '실행 전에 갖춰야 할 상태',
  addStep: '스텝 추가',
  noStepsAddFirst: '스텝이 없습니다. 첫 스텝을 추가해 보세요.',
  actionPlaceholder: '수행 동작',
  expectedResultPlaceholder: '기대 결과',
  removeStep: '스텝 삭제',

  assignedTo: '담당자',
  assignTo: '담당자 지정',
  everyone: '전체',
  unassigned: '미지정',
  casesPageInfo: (total, page, pages) =>
    `${total}개 케이스 · ${page}/${pages} 페이지`,
  noLinkedCases: '이 플랜에 연결된 테스트 케이스가 없습니다.',
  recordResult: '결과 기록',
  pickFromQueue: '큐에서 케이스를 선택하고 판정을 기록하세요.',
  viewStepsHistory: '스텝 및 이력 보기 →',
  notesPlaceholder: '이번 실행에 대한 메모 (선택)',
  saveResultError: '결과를 저장하지 못했습니다. 다시 시도해 주세요.',

  resultMatrix: '결과 매트릭스',
  bySuiteTab: '스위트별',
  byCaseTab: '케이스별',
  noSuitesLinked: '연결된 테스트 케이스가 있는 스위트가 없습니다.',
  openCaseDetail: '케이스 단위 상세 보기',
  noCasesHere: '표시할 테스트 케이스가 없습니다.',
  openInTestCases: '테스트 케이스 화면에서 열기',
  clickSuiteHint: '스위트 행을 클릭하면 케이스 단위로 볼 수 있습니다.',

  testPlans: '테스트 플랜',
  selectedTag: '선택됨',
  newPlanPlaceholder: '새 플랜 이름',
  createPlan: '플랜 생성',
  builds: '빌드',
  noBuilds: '아직 빌드가 없습니다. 아래에서 첫 빌드를 생성하세요.',
  activeLabel: '활성',
  inactiveLabel: '비활성',
  openLabel: '열림',
  closedLabel: '닫힘',
  newBuildPlaceholder: '새 빌드 이름',
  createBuild: '빌드 생성',

  executionTrend: '실행 추이',
  buildComparison: '빌드 비교',
  byTester: '테스터별',
  noExecutionsYet: '아직 기록된 실행이 없습니다.',
  flakyTestsAnalyzed: (n) => `불안정 테스트 · ${n}개 버전 분석`,
  flips: '변동 횟수',

  trendAriaLabel: '판정별 일별 실행 수',

  searchPlaceholder: '케이스 검색 (이름, ID, 내용)…',
  searchNoResults: '검색 결과가 없습니다.',

  attachEvidence: '증적 첨부 (선택)',
  evidenceUploaded: '증적이 업로드되었습니다',
  attachmentsLabel: '첨부파일',

  newProjectTitle: '새 프로젝트',
  projectNamePlaceholder: '프로젝트 이름',
  projectPrefixPlaceholder: '접두어 (예: ABC)',
  createProject: '프로젝트 생성',
  buildClose: '닫기',
  buildReopen: '다시 열기',

  exportCsv: 'CSV 내보내기',

  navRequirements: '요구사항',
  reqSpecs: '요구사항 명세',
  newReqSpec: '새 명세',
  newReqSpecTitle: '새 요구사항 명세 (선택한 명세 안 또는 프로젝트 루트에 생성)',
  reqSpecDocIdPlaceholder: '문서 ID (예: RS-1)',
  reqSpecTitlePlaceholder: '명세 제목',
  pickReqSpec: '명세를 선택하면 요구사항이 표시됩니다.',
  emptyReqSpec:
    '이 명세에는 아직 요구사항이 없습니다. 위에서 첫 요구사항을 추가해 보세요.',
  selectRequirement: '요구사항을 선택해 내용을 확인하세요.',
  newReqDocIdPlaceholder: '문서 ID (예: REQ-1)',
  newReqTitlePlaceholder: '새 요구사항 제목',
  scopeLabel: '범위',
  coveredCases: '연결된 케이스',
  noCoveredCases: '아직 이 요구사항을 커버하는 테스트 케이스가 없습니다.',
  coverageSearchPlaceholder: '커버할 케이스 검색…',
  unlink: '연결 해제',
  reqPlanCoverage: (plan) => `${plan} 커버리지`,
  noReqCoverageInPlan: '이 플랜에 연결된 커버 케이스가 아직 없습니다.',
  inPlanOfCovered: (inPlan, covered) => `${covered}개 중 ${inPlan}개 플랜에 포함`,

  navAdmin: '관리',
  adminNeedProject: '프로젝트를 선택하면 키워드, 플랫폼, 커스텀 필드를 관리할 수 있습니다.',
  adminSaveError: '저장하지 못했습니다. 입력값을 확인하고 다시 시도해 주세요.',
  adminTabUsers: '사용자',
  adminTabKeywords: '키워드',
  adminTabPlatforms: '플랫폼',
  adminTabCustomFields: '커스텀 필드',

  adminColLogin: '아이디',
  adminColName: '이름',
  adminColEmail: '이메일',
  adminColRole: '역할',
  adminColStatus: '상태',
  adminActivate: '활성화',
  adminDeactivate: '비활성화',
  newUserTitle: '새 사용자',
  userLoginPlaceholder: '아이디',
  userPasswordPlaceholder: '비밀번호',
  userFirstPlaceholder: '이름',
  userLastPlaceholder: '성',
  userEmailPlaceholder: '이메일',
  createUser: '사용자 생성',
  noUsers: '사용자가 없습니다.',

  keywordPlaceholder: '키워드',
  keywordNotesPlaceholder: '설명 (선택)',
  addKeyword: '키워드 추가',
  noKeywords: '이 프로젝트에는 아직 키워드가 없습니다.',
  adminColKeyword: '키워드',
  adminColNotes: '설명',
  adminColUsage: '사용 케이스',
  adminDelete: '삭제',
  confirmDeleteKeyword: (kw) =>
    `키워드 "${kw}"를 삭제할까요? 테스트 케이스와의 연결도 함께 삭제됩니다.`,

  platformNamePlaceholder: '플랫폼 이름',
  platformNotesPlaceholder: '설명 (선택)',
  addPlatform: '플랫폼 추가',
  noPlatforms: '이 프로젝트에는 아직 플랫폼이 없습니다.',
  adminColPlatform: '플랫폼',
  adminColEnabled: '활성 범위',
  enabledDesign: '설계',
  enabledExecution: '실행',

  adminColLabel: '레이블',
  adminColType: '유형',
  adminColAppliesTo: '적용 대상',
  noCustomFields: '이 프로젝트에 연결된 커스텀 필드가 없습니다.',
  cfLegacyHint:
    '커스텀 필드 생성은 현재 레거시 UI에서 진행합니다. 이 목록은 읽기 전용입니다.',
}

const vi: Messages = {
  loading: 'Đang tải…',
  add: 'Thêm',
  cancel: 'Hủy',
  edit: 'Sửa',
  saved: 'Đã lưu',
  build: 'Bản dựng',
  runs: 'Lần chạy',
  total: 'Tổng',
  testCase: 'Trường hợp kiểm thử',
  testSuite: 'Bộ kiểm thử',
  tester: 'Người kiểm thử',
  coverage: 'Độ bao phủ',
  cases: 'Số ca',

  verdictPassed: 'Đạt',
  verdictFailed: 'Trượt',
  verdictBlocked: 'Bị chặn',
  verdictNotRun: 'Chưa chạy',

  navDashboard: 'Bảng điều khiển',
  navTestCases: 'Trường hợp kiểm thử',
  navRun: 'Chạy',
  navMatrix: 'Ma trận',
  navPlans: 'Kế hoạch',
  navReports: 'Báo cáo',
  signOut: 'Đăng xuất',
  project: 'Dự án',
  testPlan: 'Kế hoạch kiểm thử',
  language: 'Ngôn ngữ',

  loginTagline: 'Lập kế hoạch, chạy và theo dõi việc kiểm thử.',
  loginPlaceholder: 'Tên đăng nhập',
  passwordPlaceholder: 'Mật khẩu',
  signIn: 'Đăng nhập',
  signingIn: 'Đang đăng nhập…',
  signInFailed: 'Đăng nhập thất bại',

  linkedCasesCount: (n) => `${n} trường hợp kiểm thử được liên kết`,
  passRateLabel: 'tỷ lệ đạt',
  passRateSuffix: 'trong các lần chạy gần nhất',
  dailyExecutions: 'Thực thi hằng ngày',
  flakyCandidates: 'Ứng viên kiểm thử không ổn định',
  noFlaky: 'Không phát hiện dao động đạt/trượt. Bộ kiểm thử ổn định.',
  statusFlips: 'Lần đổi trạng thái',
  instability: 'Độ bất ổn',

  suites: 'Bộ kiểm thử',
  newSuite: 'Bộ mới',
  newSuiteTitle: 'Bộ mới (trong bộ đang chọn, hoặc ở gốc dự án)',
  suiteNamePlaceholder: 'Tên bộ kiểm thử',
  newCaseNamePlaceholder: 'Tên trường hợp kiểm thử mới',
  pickSuite: 'Chọn một bộ để xem các trường hợp kiểm thử.',
  emptySuite:
    'Bộ này chưa có trường hợp kiểm thử — hãy viết cái đầu tiên ở trên.',
  selectCase: 'Chọn một trường hợp kiểm thử để xem.',
  linkedToPlan: (plan) => `Đã liên kết với ${plan}`,
  alreadyInPlan: (plan) => `Đã có trong ${plan}`,
  addToPlan: (plan) => `Thêm vào ${plan}`,
  stepsCount: (n) => `Các bước · ${n}`,
  noStepsWritten: 'Trường hợp này chưa có bước nào.',
  recentRuns: 'Lần chạy gần đây',
  neverExecuted: 'Chưa từng được thực thi.',
  expand: 'Mở rộng',
  collapse: 'Thu gọn',

  saveChanges: 'Lưu thay đổi',
  saving: 'Đang lưu…',
  saveError: 'Không thể lưu. Vui lòng thử lại.',
  summary: 'Tóm tắt',
  summaryPlaceholder: 'Trường hợp này kiểm chứng điều gì',
  preconditions: 'Điều kiện tiên quyết',
  preconditionsPlaceholder: 'Trạng thái cần có trước khi chạy',
  addStep: 'Thêm bước',
  noStepsAddFirst: 'Chưa có bước nào — hãy thêm bước đầu tiên.',
  actionPlaceholder: 'Hành động',
  expectedResultPlaceholder: 'Kết quả mong đợi',
  removeStep: 'Xóa bước',

  assignedTo: 'Giao cho',
  assignTo: 'Giao cho',
  everyone: 'Tất cả',
  unassigned: 'Chưa giao',
  casesPageInfo: (total, page, pages) =>
    `${total} trường hợp · trang ${page}/${pages}`,
  noLinkedCases: 'Chưa có trường hợp kiểm thử nào liên kết với kế hoạch này.',
  recordResult: 'Ghi kết quả',
  pickFromQueue: 'Chọn một trường hợp từ hàng đợi, đánh giá và ghi lại.',
  viewStepsHistory: 'Xem các bước & lịch sử →',
  notesPlaceholder: 'Ghi chú cho lần chạy này (tùy chọn)',
  saveResultError: 'Không thể lưu kết quả. Vui lòng thử lại.',

  resultMatrix: 'Ma trận kết quả',
  bySuiteTab: 'Theo bộ',
  byCaseTab: 'Theo ca',
  noSuitesLinked: 'Không có bộ nào có trường hợp kiểm thử được liên kết.',
  openCaseDetail: 'Mở chi tiết theo từng ca',
  noCasesHere: 'Không có trường hợp kiểm thử nào ở đây.',
  openInTestCases: 'Mở trong Trường hợp kiểm thử',
  clickSuiteHint: 'Nhấp vào một hàng bộ để xem chi tiết các ca.',

  testPlans: 'Kế hoạch kiểm thử',
  selectedTag: 'đang chọn',
  newPlanPlaceholder: 'Tên kế hoạch mới',
  createPlan: 'Tạo kế hoạch',
  builds: 'Bản dựng',
  noBuilds: 'Chưa có bản dựng — hãy tạo cái đầu tiên bên dưới.',
  activeLabel: 'hoạt động',
  inactiveLabel: 'ngừng',
  openLabel: 'mở',
  closedLabel: 'đóng',
  newBuildPlaceholder: 'Tên bản dựng mới',
  createBuild: 'Tạo bản dựng',

  executionTrend: 'Xu hướng thực thi',
  buildComparison: 'So sánh bản dựng',
  byTester: 'Theo người kiểm thử',
  noExecutionsYet: 'Chưa có lần thực thi nào được ghi nhận.',
  flakyTestsAnalyzed: (n) => `Kiểm thử không ổn định · đã phân tích ${n} phiên bản`,
  flips: 'Lần đổi',

  trendAriaLabel: 'Thực thi hằng ngày theo kết quả',

  searchPlaceholder: 'Tìm ca kiểm thử (tên, ID, nội dung)…',
  searchNoResults: 'Không tìm thấy kết quả.',

  attachEvidence: 'Đính kèm bằng chứng (tùy chọn)',
  evidenceUploaded: 'Đã tải lên bằng chứng',
  attachmentsLabel: 'Tệp đính kèm',

  newProjectTitle: 'Dự án mới',
  projectNamePlaceholder: 'Tên dự án',
  projectPrefixPlaceholder: 'Tiền tố (ví dụ: ABC)',
  createProject: 'Tạo dự án',
  buildClose: 'Đóng',
  buildReopen: 'Mở lại',

  exportCsv: 'Xuất CSV',

  navRequirements: 'Yêu cầu',
  reqSpecs: 'Đặc tả yêu cầu',
  newReqSpec: 'Đặc tả mới',
  newReqSpecTitle:
    'Đặc tả yêu cầu mới (trong đặc tả đang chọn, hoặc ở gốc dự án)',
  reqSpecDocIdPlaceholder: 'Mã tài liệu (vd: RS-1)',
  reqSpecTitlePlaceholder: 'Tiêu đề đặc tả',
  pickReqSpec: 'Chọn một đặc tả để xem các yêu cầu.',
  emptyReqSpec:
    'Đặc tả này chưa có yêu cầu nào — hãy thêm cái đầu tiên ở trên.',
  selectRequirement: 'Chọn một yêu cầu để xem nội dung.',
  newReqDocIdPlaceholder: 'Mã tài liệu (vd: REQ-1)',
  newReqTitlePlaceholder: 'Tiêu đề yêu cầu mới',
  scopeLabel: 'Phạm vi',
  coveredCases: 'Các ca kiểm thử bao phủ',
  noCoveredCases: 'Chưa có ca kiểm thử nào bao phủ yêu cầu này.',
  coverageSearchPlaceholder: 'Tìm ca kiểm thử để bao phủ…',
  unlink: 'Hủy liên kết',
  reqPlanCoverage: (plan) => `Độ bao phủ trong ${plan}`,
  noReqCoverageInPlan:
    'Chưa có ca được bao phủ nào liên kết với kế hoạch này.',
  inPlanOfCovered: (inPlan, covered) =>
    `${inPlan}/${covered} trong kế hoạch`,

  navAdmin: 'Quản trị',
  adminNeedProject:
    'Chọn một dự án để quản lý từ khóa, nền tảng và trường tùy chỉnh của nó.',
  adminSaveError: 'Không thể lưu. Hãy kiểm tra các trường và thử lại.',
  adminTabUsers: 'Người dùng',
  adminTabKeywords: 'Từ khóa',
  adminTabPlatforms: 'Nền tảng',
  adminTabCustomFields: 'Trường tùy chỉnh',

  adminColLogin: 'Tên đăng nhập',
  adminColName: 'Họ tên',
  adminColEmail: 'Email',
  adminColRole: 'Vai trò',
  adminColStatus: 'Trạng thái',
  adminActivate: 'Kích hoạt',
  adminDeactivate: 'Vô hiệu hóa',
  newUserTitle: 'Người dùng mới',
  userLoginPlaceholder: 'Tên đăng nhập',
  userPasswordPlaceholder: 'Mật khẩu',
  userFirstPlaceholder: 'Tên',
  userLastPlaceholder: 'Họ',
  userEmailPlaceholder: 'Email',
  createUser: 'Tạo người dùng',
  noUsers: 'Không có người dùng nào.',

  keywordPlaceholder: 'Từ khóa',
  keywordNotesPlaceholder: 'Ghi chú (tùy chọn)',
  addKeyword: 'Thêm từ khóa',
  noKeywords: 'Dự án này chưa có từ khóa nào.',
  adminColKeyword: 'Từ khóa',
  adminColNotes: 'Ghi chú',
  adminColUsage: 'Được dùng bởi',
  adminDelete: 'Xóa',
  confirmDeleteKeyword: (kw) =>
    `Xóa từ khóa "${kw}"? Các liên kết với trường hợp kiểm thử cũng sẽ bị xóa.`,

  platformNamePlaceholder: 'Tên nền tảng',
  platformNotesPlaceholder: 'Ghi chú (tùy chọn)',
  addPlatform: 'Thêm nền tảng',
  noPlatforms: 'Dự án này chưa có nền tảng nào.',
  adminColPlatform: 'Nền tảng',
  adminColEnabled: 'Bật cho',
  enabledDesign: 'Thiết kế',
  enabledExecution: 'Thực thi',

  adminColLabel: 'Nhãn',
  adminColType: 'Loại',
  adminColAppliesTo: 'Áp dụng cho',
  noCustomFields: 'Không có trường tùy chỉnh nào liên kết với dự án này.',
  cfLegacyHint:
    'Việc tạo trường tùy chỉnh hiện được thực hiện trong giao diện cũ — danh sách này chỉ để xem.',
}

const dictionaries: Record<Locale, Messages> = { en, ko, vi }

/** shown in the language switcher — each locale names itself, on purpose */
export const LOCALE_OPTIONS: { value: Locale; label: string }[] = [
  { value: 'en', label: 'EN' },
  { value: 'ko', label: '한국어' },
  { value: 'vi', label: 'Tiếng Việt' },
]

function isLocale(v: unknown): v is Locale {
  return v === 'en' || v === 'ko' || v === 'vi'
}

function detectLocale(): Locale {
  try {
    const stored = localStorage.getItem(STORAGE_KEY)
    if (isLocale(stored)) return stored
  } catch {
    /* storage unavailable — fall through to navigator */
  }
  const lang =
    typeof navigator !== 'undefined' ? navigator.language.toLowerCase() : ''
  if (lang.startsWith('ko')) return 'ko'
  if (lang.startsWith('vi')) return 'vi'
  return 'en'
}

interface I18nContextValue {
  locale: Locale
  setLocale: (locale: Locale) => void
}

const I18nContext = createContext<I18nContextValue | null>(null)

export function I18nProvider({ children }: { children: ReactNode }) {
  const [locale, setLocaleState] = useState<Locale>(detectLocale)
  const setLocale = useCallback((next: Locale) => {
    setLocaleState(next)
    try {
      localStorage.setItem(STORAGE_KEY, next)
    } catch {
      /* storage unavailable — the choice just won't persist */
    }
  }, [])
  const value = useMemo(() => ({ locale, setLocale }), [locale, setLocale])
  return <I18nContext.Provider value={value}>{children}</I18nContext.Provider>
}

/**
 * `t('key')` returns the message for the active locale.
 * Plain keys give strings; parameterized keys give typed template
 * functions, e.g. `t('addToPlan')(plan.name)`.
 */
export function useT() {
  const ctx = useContext(I18nContext)
  if (!ctx) throw new Error('useT must be used inside <I18nProvider>')
  const { locale, setLocale } = ctx
  const t = useCallback(
    <K extends MsgKey>(key: K): Messages[K] => dictionaries[locale][key],
    [locale],
  )
  return { t, locale, setLocale }
}
