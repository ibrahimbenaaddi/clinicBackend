import { useQuery } from '@tanstack/react-query';
import { Users, Stethoscope, Calendar, Receipt } from 'lucide-react';
import { Link } from 'react-router-dom';
import { getAdminDoctors } from '../../api/doctors';
import { getAdminPatients } from '../../api/patients';
import { getAdminAppointments } from '../../api/appointments';
import { getAdminInvoices } from '../../api/invoices';
import { StatusBadge, LoadingSpinner } from '../../components/shared';
import { PageHeader } from '../../components/layout/Layout';

export default function AdminDashboard() {
  const { data: docRes }  = useQuery({ queryKey: ['admin-doctors'],       queryFn: getAdminDoctors });
  const { data: patRes }  = useQuery({ queryKey: ['admin-patients'],      queryFn: getAdminPatients });
  const { data: apptRes, isLoading } = useQuery({ queryKey: ['admin-appointments'], queryFn: getAdminAppointments });
  const { data: invRes }  = useQuery({ queryKey: ['admin-invoices'],      queryFn: getAdminInvoices });

  const doctors      = docRes?.data?.data  ?? [];
  const patients     = patRes?.data?.data  ?? [];
  const appointments = apptRes?.data?.data ?? [];
  const invoices     = invRes?.data?.data  ?? [];

  const totalRevenue = invoices.filter(i => i.status === 'paid').reduce((s, i) => s + i.amount, 0);

  const stats = [
    { label: 'Total Doctors',      value: doctors.length,      icon: Stethoscope, color: '#00b4d8', bg: 'rgba(0,180,216,0.12)', link: '/admin/doctors' },
    { label: 'Total Patients',     value: patients.length,     icon: Users,       color: '#388bfd', bg: 'rgba(56,139,253,0.12)', link: '/admin/patients' },
    { label: 'Total Appointments', value: appointments.length, icon: Calendar,    color: '#d29922', bg: 'rgba(210,153,34,0.12)', link: '/admin/appointments' },
    { label: 'Total Revenue',      value: `$${totalRevenue.toFixed(0)}`, icon: Receipt, color: '#2ea043', bg: 'rgba(46,160,67,0.12)', link: '/admin/invoices' },
  ];

  return (
    <div className="animate-fade">
      <PageHeader title="Admin Dashboard" subtitle="System-wide overview and management" />

      <div className="grid-4 mb-3">
        {stats.map(s => (
          <Link to={s.link} key={s.label} style={{ textDecoration: 'none' }}>
            <div className="stat-card card-hover">
              <div className="stat-card-icon" style={{ background: s.bg }}>
                <s.icon size={20} color={s.color} />
              </div>
              <div className="stat-card-value" style={{ color: s.color }}>{s.value}</div>
              <div className="stat-card-label">{s.label}</div>
            </div>
          </Link>
        ))}
      </div>

      <div className="card">
        <div className="flex items-center justify-between mb-2">
          <h2 style={{ fontWeight: 600 }}>Recent Appointments</h2>
          <Link to="/admin/appointments" className="btn btn-ghost btn-sm">View all</Link>
        </div>
        {isLoading ? <LoadingSpinner /> : appointments.slice(0, 8).length === 0 ? (
          <p className="text-muted" style={{ fontSize: '0.875rem' }}>No appointments</p>
        ) : (
          <div className="table-wrapper" style={{ border: 'none' }}>
            <table>
              <thead><tr><th>Patient</th><th>Doctor</th><th>Date</th><th>Status</th></tr></thead>
              <tbody>
                {appointments.slice(0, 8).map(a => (
                  <tr key={a.id}>
                    <td>{a.patient?.firstname} {a.patient?.lastname}</td>
                    <td>Dr. {a.doctor?.firstname} {a.doctor?.lastname}</td>
                    <td>{new Date(a.start_time).toLocaleDateString()}</td>
                    <td><StatusBadge status={a.status} /></td>
                  </tr>
                ))}
              </tbody>
            </table>
          </div>
        )}
      </div>
    </div>
  );
}
