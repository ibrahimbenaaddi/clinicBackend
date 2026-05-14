import api from './axios';

// Doctor managing own slots
export const getDoctorSlots  = (doctorId) => api.get(`/doctor/slots/doctor/${doctorId}`);
export const getDoctorSlot   = (id) => api.get(`/doctor/slots/${id}`);
export const createSlot      = (data) => api.post('/doctor/slots', data);
export const updateSlot      = (id, data) => api.patch(`/doctor/slots/${id}`, data);
export const deleteSlot      = (id) => api.delete(`/doctor/slots/${id}`);

// Admin
export const getAdminSlots   = () => api.get('/admin/slots');
export const getAdminSlot    = (id) => api.get(`/admin/slots/${id}`);
export const createAdminSlot = (data) => api.post('/admin/slots', data);
export const updateAdminSlot = (id, data) => api.patch(`/admin/slots/${id}`, data);
export const deleteAdminSlot = (id) => api.delete(`/admin/slots/${id}`);
