import api from './axios';

// Patient
export const getPatientInvoices = (patientId) => api.get(`/patient/${patientId}/invoices`);
export const getPatientInvoice  = (id) => api.get(`/patient/invoices/${id}`);

// Doctor
export const getDoctorInvoices  = (doctorId) => api.get(`/doctor/${doctorId}/invoices`);
export const getDoctorInvoice   = (id) => api.get(`/doctor/invoices/${id}`);
export const createInvoice      = (data) => api.post('/doctor/invoices', data);
export const updateInvoice      = (id, data) => api.patch(`/doctor/invoices/${id}`, data);

// Admin
export const getAdminInvoices   = () => api.get('/admin/invoices');
export const getAdminInvoice    = (id) => api.get(`/admin/invoices/${id}`);
export const createAdminInvoice = (data) => api.post('/admin/invoices', data);
export const updateAdminInvoice = (id, data) => api.patch(`/admin/invoices/${id}`, data);
export const deleteAdminInvoice = (id) => api.delete(`/admin/invoices/${id}`);
