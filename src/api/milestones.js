import api from './index.js'

export const getMilestone = (id) => api.get(`/milestones/${id}`)
export const updateMilestone = (id, data) => api.put(`/milestones/${id}`, data)
export const deleteMilestone = (id) => api.delete(`/milestones/${id}`)
