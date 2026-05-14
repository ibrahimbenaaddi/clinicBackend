import { BrowserRouter, Routes, Route, Navigate } from 'react-router-dom';
import { QueryClient, QueryClientProvider } from '@tanstack/react-query';
import { Toaster } from 'react-hot-toast';
import { AuthProvider, useAuth } from './context/AuthContext';
import { ProtectedLayout } from './components/layout/Layout';

// Auth pages
import Login from './pages/auth/Login';
import RegisterPatient from './pages/auth/RegisterPatient';
import RegisterDoctor from './pages/auth/RegisterDoctor';

// Patient pages
import PatientDashboard    from './pages/patient/Dashboard';
import PatientAppointments from './pages/patient/Appointments';
import PatientAppointmentDetail from './pages/patient/AppointmentDetail';
import PatientDoctors      from './pages/patient/Doctors';
import PatientRecords      from './pages/patient/Records';
import PatientPrescriptions from './pages/patient/Prescriptions';
import PatientInvoices     from './pages/patient/Invoices';
import PatientProfile      from './pages/patient/Profile';

// Doctor pages
import DoctorDashboard    from './pages/doctor/Dashboard';
import DoctorAppointments from './pages/doctor/Appointments';
import DoctorPatients     from './pages/doctor/Patients';
import DoctorPatientDetail from './pages/doctor/PatientDetail';
import DoctorRecords      from './pages/doctor/Records';
import DoctorPrescriptions from './pages/doctor/Prescriptions';
import DoctorInvoices     from './pages/doctor/Invoices';
import DoctorSlots        from './pages/doctor/Slots';
import DoctorProfile      from './pages/doctor/Profile';

// Admin pages
import AdminDashboard     from './pages/admin/Dashboard';
import AdminDoctors       from './pages/admin/Doctors';
import AdminPatients      from './pages/admin/Patients';
import AdminAppointments  from './pages/admin/Appointments';
import AdminRecords       from './pages/admin/Records';
import AdminPrescriptions from './pages/admin/Prescriptions';
import AdminInvoices      from './pages/admin/Invoices';
import AdminSlots         from './pages/admin/Slots';

const queryClient = new QueryClient({
  defaultOptions: { queries: { retry: 1, staleTime: 30_000 } },
});

function RootRedirect() {
  const { isAuthenticated, role } = useAuth();
  if (!isAuthenticated) return <Navigate to="/login" replace />;
  return <Navigate to={`/${role}/dashboard`} replace />;
}

export default function App() {
  return (
    <QueryClientProvider client={queryClient}>
      <BrowserRouter>
        <AuthProvider>
          <Toaster
            position="top-right"
            toastOptions={{
              style: {
                background: '#1c2333',
                color: '#e6edf3',
                border: '1px solid #30363d',
                borderRadius: '10px',
                fontSize: '0.875rem',
              },
              success: { iconTheme: { primary: '#2ea043', secondary: '#000' } },
              error:   { iconTheme: { primary: '#f85149', secondary: '#fff' } },
            }}
          />
          <Routes>
            {/* Root */}
            <Route path="/" element={<RootRedirect />} />

            {/* Auth */}
            <Route path="/login"             element={<Login />} />
            <Route path="/register/patient"  element={<RegisterPatient />} />
            <Route path="/register/doctor"   element={<RegisterDoctor />} />

            {/* Patient Portal */}
            <Route element={<ProtectedLayout allowedRole="patient" />}>
              <Route path="/patient/dashboard"            element={<PatientDashboard />} />
              <Route path="/patient/appointments"         element={<PatientAppointments />} />
              <Route path="/patient/appointments/:id"     element={<PatientAppointmentDetail />} />
              <Route path="/patient/doctors"              element={<PatientDoctors />} />
              <Route path="/patient/records"              element={<PatientRecords />} />
              <Route path="/patient/prescriptions"        element={<PatientPrescriptions />} />
              <Route path="/patient/invoices"             element={<PatientInvoices />} />
              <Route path="/patient/profile"              element={<PatientProfile />} />
            </Route>

            {/* Doctor Portal */}
            <Route element={<ProtectedLayout allowedRole="doctor" />}>
              <Route path="/doctor/dashboard"             element={<DoctorDashboard />} />
              <Route path="/doctor/appointments"          element={<DoctorAppointments />} />
              <Route path="/doctor/patients"              element={<DoctorPatients />} />
              <Route path="/doctor/patients/:id"          element={<DoctorPatientDetail />} />
              <Route path="/doctor/records"               element={<DoctorRecords />} />
              <Route path="/doctor/prescriptions"         element={<DoctorPrescriptions />} />
              <Route path="/doctor/invoices"              element={<DoctorInvoices />} />
              <Route path="/doctor/slots"                 element={<DoctorSlots />} />
              <Route path="/doctor/profile"               element={<DoctorProfile />} />
            </Route>

            {/* Admin Portal */}
            <Route element={<ProtectedLayout allowedRole="admin" />}>
              <Route path="/admin/dashboard"              element={<AdminDashboard />} />
              <Route path="/admin/doctors"                element={<AdminDoctors />} />
              <Route path="/admin/patients"               element={<AdminPatients />} />
              <Route path="/admin/appointments"           element={<AdminAppointments />} />
              <Route path="/admin/records"                element={<AdminRecords />} />
              <Route path="/admin/prescriptions"          element={<AdminPrescriptions />} />
              <Route path="/admin/invoices"               element={<AdminInvoices />} />
              <Route path="/admin/slots"                  element={<AdminSlots />} />
            </Route>

            {/* 404 fallback */}
            <Route path="*" element={<Navigate to="/login" replace />} />
          </Routes>
        </AuthProvider>
      </BrowserRouter>
    </QueryClientProvider>
  );
}
