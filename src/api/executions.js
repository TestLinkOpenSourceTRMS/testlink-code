import api from './index.js'

export const createExecution = (data) => api.post('/executions', data)
export const getPlanExecutions = (planId) => api.get(`/testplans/${planId}/executions`)
export const getPlanProgress = (planId) => api.get(`/testplans/${planId}/progress`)
export const getPlanBuilds = (planId) => api.get(`/testplans/${planId}/builds`)
