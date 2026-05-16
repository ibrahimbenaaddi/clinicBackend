import api from './axios';

// Patient
export const getPatientRecords = (patientId) => api.get(`/patient/${patientId}/records`);
export const getPatientRecord  = (id) => api.get(`/patient/records/${id}`);

// Doctor
export const getDoctorRecords  = (doctorId) => api.get(`/doctor/${doctorId}/records`);
export const getDoctorRecord   = (id) => api.get(`/doctor/records/${id}`);
export const createRecord      = (data) => api.post('/doctor/records', data);
export const updateRecord      = (id, data) => api.patch(`/doctor/records/${id}`, data);
export const deleteRecord      = (id) => api.delete(`/doctor/records/${id}`);

// Admin
export const getAdminRecords   = () => api.get('/admin/records');
export const getAdminRecord    = (id) => api.get(`/admin/records/${id}`);
export const createAdminRecord = (data) => api.post('/admin/records', data);
export const updateAdminRecord = (id, data) => api.patch(`/admin/records/${id}`, data);
export const deleteAdminRecord = (id) => api.delete(`/admin/records/${id}`);
