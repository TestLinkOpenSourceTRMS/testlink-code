import api from './index.js'

export const getProjects = () => api.get('/testprojects')
export const getProject = (id) => api.get(`/testprojects/${id}`)
export const getProjectTestSuites = (id) => api.get(`/testprojects/${id}/testsuites`)
export const getProjectTestPlans = (id) => api.get(`/testprojects/${id}/testplans`)
export const getProjectDashboard = (id) => api.get(`/testprojects/${id}/dashboard`)
export const getProjectMilestones = (id) => api.get(`/testprojects/${id}/milestones`)
export const createProjectMilestone = (id, data) => api.post(`/testprojects/${id}/milestones`, data)
