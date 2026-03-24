import api from './index.js'

export const getSuiteTestCases = (suiteId) => api.get(`/testsuites/${suiteId}/testcases`)
export const getTestCase = (id) => api.get(`/testcases/${id}`)
