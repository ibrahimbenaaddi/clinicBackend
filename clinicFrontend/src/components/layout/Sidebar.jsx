import { NavLink, useNavigate } from 'react-router-dom';
import {
  LayoutDashboard, Calendar, Users, FileText,
  Pill, Receipt, Clock, LogOut, User, Stethoscope,
} from 'lucide-react';
import { useAuth } from '../../context/AuthContext';
import { logoutPatient, logoutDoctor, logoutAdmin } from '../../api/auth';
import toast from 'react-hot-toast';

const NAV = {
  patient: [
    { to: '/patient/dashboard',      label: 'Dashboard',      icon: LayoutDashboard },
    { to: '/patient/appointments',   label: 'Appointments',   icon: Calendar },
    { to: '/patient/doctors',        label: 'Doctors',        icon: Stethoscope },
    { to: '/patient/records',        label: 'Medical Records',icon: FileText },
    { to: '/patient/prescriptions',  label: 'Prescriptions',  icon: Pill },
    { to: '/patient/invoices',       label: 'Invoices',       icon: Receipt },
    { to: '/patient/profile',        label: 'Profile',        icon: User },
  ],
  doctor: [
    { to: '/doctor/dashboard',       label: 'Dashboard',      icon: LayoutDashboard },
    { to: '/doctor/appointments',    label: 'Appointments',   icon: Calendar },
    { to: '/doctor/patients',        label: 'My Patients',    icon: Users },
    { to: '/doctor/records',         label: 'Medical Records',icon: FileText },
    { to: '/doctor/prescriptions',   label: 'Prescriptions',  icon: Pill },
    { to: '/doctor/invoices',        label: 'Invoices',       icon: Receipt },
    { to: '/doctor/slots',           label: 'My Slots',       icon: Clock },
    { to: '/doctor/profile',         label: 'Profile',        icon: User },
  ],
  admin: [
    { to: '/admin/dashboard',        label: 'Dashboard',      icon: LayoutDashboard },
    { to: '/admin/doctors',          label: 'Doctors',        icon: Stethoscope },
    { to: '/admin/patients',         label: 'Patients',       icon: Users },
    { to: '/admin/appointments',     label: 'Appointments',   icon: Calendar },
    { to: '/admin/records',          label: 'Medical Records',icon: FileText },
    { to: '/admin/prescriptions',    label: 'Prescriptions',  icon: Pill },
    { to: '/admin/invoices',         label: 'Invoices',       icon: Receipt },
    { to: '/admin/slots',            label: 'Slots',          icon: Clock },
  ],
};

const ROLE_LABEL = { patient: 'Patient Portal', doctor: 'Doctor Portal', admin: 'Admin Panel' };

export default function Sidebar() {
  const { role, logout, user } = useAuth();
  const navigate = useNavigate();
  const links = NAV[role] ?? [];

  const handleLogout = async () => {
    try {
      if (role === 'patient') await logoutPatient();
      else if (role === 'doctor') await logoutDoctor();
      else await logoutAdmin();
    } catch { /* ignore */ }
    logout();
    toast.success('Logged out');
    navigate('/login');
  };

  return (
    <aside className="sidebar">
      <div className="sidebar-logo">
        <div className="sidebar-logo-icon">C</div>
        <div>
          <div className="sidebar-logo-text">ClinicApp</div>
          <div className="sidebar-logo-sub">{ROLE_LABEL[role]}</div>
        </div>
      </div>

      <nav className="sidebar-nav">
        <div className="sidebar-section-label">Menu</div>
        {links.map(({ to, label, icon: Icon }) => (
          <NavLink
            key={to}
            to={to}
            className={({ isActive }) => `nav-link${isActive ? ' active' : ''}`}
          >
            <Icon size={16} />
            {label}
          </NavLink>
        ))}
      </nav>

      <div className="sidebar-footer">
        <div style={{ fontSize: '0.78rem', color: 'var(--text-secondary)', marginBottom: '0.5rem', padding: '0 0.25rem' }}>
          {user?.firstname} {user?.lastname}
        </div>
        <button className="nav-link btn-ghost" style={{ width: '100%' }} onClick={handleLogout}>
          <LogOut size={16} />
          Logout
        </button>
      </div>
    </aside>
  );
}
