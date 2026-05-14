import api from './axios';

export const loginPatient  = (data) => api.post('/patients/login', data);
export const registerPatient = (data) => api.post('/patients/register', data);
export const logoutPatient = () => api.post('/patient/logout');

export const loginDoctor   = (data) => api.post('/doctors/login', data);
export const registerDoctor = (data) => api.post('/doctors/register', data);
export const logoutDoctor  = () => api.post('/doctor/logout');

export const loginAdmin    = (data) => api.post('/admins/login', data);
export const logoutAdmin   = () => api.post('/admin/logout');
