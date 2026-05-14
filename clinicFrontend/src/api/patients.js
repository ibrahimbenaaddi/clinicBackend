import api from './axios';

// Patient
export const getPatientProfile  = (id) => api.get(`/patient/profile/${id}`);
export const updatePatientProfile = (id, data) => api.patch(`/patient/profile/${id}`, data);

// Doctor (viewing patients)
export const getDoctorPatients  = () => api.get('/doctor/patients');
export const getDoctorPatient   = (id) => api.get(`/doctor/patients/${id}`);

// Admin
export const getAdminPatients   = () => api.get('/admin/patients');
export const getAdminPatient    = (id) => api.get(`/admin/patients/${id}`);
export const createAdminPatient = (data) => api.post('/admin/patients', data);
export const updateAdminPatient = (id, data) => api.patch(`/admin/patients/${id}`, data);
export const deleteAdminPatient = (id) => api.delete(`/admin/patients/${id}`);
