import api from './axios';

// Patient
export const getPatientAppointments = (patientId) => api.get(`/patient/${patientId}/appointments`);
export const getPatientAppointment  = (id) => api.get(`/patient/appointments/${id}`);
export const bookAppointment        = (data) => api.post('/patient/appointments', data);
export const cancelAppointment      = (patientId, apptId) => api.patch(`/patient/${patientId}/appointments/${apptId}/cancel`);
export const getAvailableSlots      = (doctorId) => api.get(`/patient/availableSlots/${doctorId}`);

// Doctor
export const getDoctorAppointments  = (doctorId) => api.get(`/doctor/${doctorId}/appointments`);
export const getDoctorAppointment   = (id) => api.get(`/doctor/appointments/${id}`);
export const updateAppointmentStatus = (doctorId, apptId, data) => api.patch(`/doctor/${doctorId}/appointments/${apptId}/status`, data);
export const getPatientAppointmentsByDoctor = (patientId) => api.get(`/doctor/patients/${patientId}/appointments`);

// Admin
export const getAdminAppointments   = () => api.get('/admin/appointments');
export const getAdminAppointment    = (id) => api.get(`/admin/appointments/${id}`);
export const createAdminAppointment = (data) => api.post('/admin/appointments', data);
export const updateAdminAppointment = (id, data) => api.patch(`/admin/appointments/${id}`, data);
export const deleteAdminAppointment = (id) => api.delete(`/admin/appointments/${id}`);
