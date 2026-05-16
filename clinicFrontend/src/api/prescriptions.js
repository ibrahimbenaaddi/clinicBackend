import api from './axios';

// Patient
export const getPatientPrescriptions = (patientId) => api.get(`/patient/${patientId}/prescriptions`);
export const getPatientPrescription  = (id) => api.get(`/patient/prescriptions/${id}`);

// Doctor
export const getDoctorPrescriptions  = (doctorId) => api.get(`/doctor/${doctorId}/prescriptions`);
export const getDoctorPrescription   = (id) => api.get(`/doctor/prescriptions/${id}`);
export const createPrescription      = (data) => api.post('/doctor/prescriptions', data);
export const updatePrescription      = (id, data) => api.patch(`/doctor/prescriptions/${id}`, data);
export const deletePrescription      = (id) => api.delete(`/doctor/prescriptions/${id}`);

// Admin
export const getAdminPrescriptions   = () => api.get('/admin/prescriptions');
export const getAdminPrescription    = (id) => api.get(`/admin/prescriptions/${id}`);
export const createAdminPrescription = (data) => api.post('/admin/prescriptions', data);
export const updateAdminPrescription = (id, data) => api.patch(`/admin/prescriptions/${id}`, data);
export const deleteAdminPrescription = (id) => api.delete(`/admin/prescriptions/${id}`);
