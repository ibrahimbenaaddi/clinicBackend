import api from './axios';

// Patient browsing doctors
export const getPatientDoctors = () => api.get('/patient/doctors');
export const getPatientDoctor  = (id) => api.get(`/patient/doctors/${id}`);

// Doctor profile
export const getDoctorProfile  = (id) => api.get(`/doctor/profile/${id}`);
export const updateDoctorProfile = (id, data) => api.patch(`/doctor/profile/${id}`, data);

// Admin
export const getAdminDoctors   = () => api.get('/admin/doctors');
export const getAdminDoctor    = (id) => api.get(`/admin/doctors/${id}`);
export const createAdminDoctor = (data) => api.post('/admin/doctors', data);
export const updateAdminDoctor = (id, data) => api.patch(`/admin/doctors/${id}`, data);
export const deleteAdminDoctor = (id) => api.delete(`/admin/doctors/${id}`);
