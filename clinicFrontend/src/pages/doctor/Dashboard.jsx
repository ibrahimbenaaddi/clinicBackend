import { useQuery } from '@tanstack/react-query';
import { Calendar, Users, Receipt } from 'lucide-react';
import { Link } from 'react-router-dom';
import { useAuth } from '../../context/AuthContext';
import { getDoctorAppointments } from '../../api/appointments';
import { getDoctorInvoices } from '../../api/invoices';
import { StatusBadge, LoadingSpinner } from '../../components/shared';
import { PageHeader } from '../../components/layout/Layout';

export default function DoctorDashboard() {
  const { user } = useAuth();
  const id = user?.id;

  const { data: apptRes, isLoading } = useQuery({
    queryKey: ['doctor-appointments', id],
    queryFn: () => getDoctorAppointments(id),
    enabled: !!id,
  });
  const { data: invRes } = useQuery({
    queryKey: ['doctor-invoices', id],
    queryFn: () => getDoctorInvoices(id),
    enabled: !!id,
  });

  const appointments = apptRes?.data?.data ?? [];
  const invoices     = invRes?.data?.data ?? [];

  const today = new Date().toDateString();
  const todayAppts = appointments.filter(a => new Date(a.start_time).toDateString() === today);
  const pending = appointments.filter(a => a.status === 'pending');
  const totalRevenue = invoices.filter(i => i.status === 'paid').reduce((sum, i) => sum + i.amount, 0);

  const stats = [
    { label: "Today's Appointments", value: todayAppts.length,      icon: Calendar, color: '#00b4d8', bg: 'rgba(0,180,216,0.12)', link: '/doctor/appointments' },
    { label: 'Pending Confirmations', value: pending.length,        icon: Calendar, color: '#d29922', bg: 'rgba(210,153,34,0.12)', link: '/doctor/appointments' },
    { label: 'Total Appointments',    value: appointments.length,   icon: Users,    color: '#388bfd', bg: 'rgba(56,139,253,0.12)', link: '/doctor/appointments' },
    { label: 'Total Revenue',         value: `$${totalRevenue.toFixed(0)}`, icon: Receipt, color: '#2ea043', bg: 'rgba(46,160,67,0.12)', link: '/doctor/invoices' },
  ];

  return (
    <div className="animate-fade">
      <PageHeader
        title={`Dr. ${user?.firstname} ${user?.lastname}`}
        subtitle="Doctor Dashboard — manage your patients and schedule"
      />

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
          <h2 style={{ fontWeight: 600 }}>Today&apos;s Schedule</h2>
          <Link to="/doctor/appointments" className="btn btn-ghost btn-sm">View all</Link>
        </div>
        {isLoading ? <LoadingSpinner /> : todayAppts.length === 0 ? (
          <p className="text-muted" style={{ fontSize: '0.875rem' }}>No appointments today</p>
        ) : (
          <div style={{ display: 'flex', flexDirection: 'column', gap: '0.75rem' }}>
            {todayAppts.map(a => (
              <Link to={`/doctor/appointments/${a.id}`} key={a.id}
                style={{ display: 'flex', justifyContent: 'space-between', alignItems: 'center', padding: '0.85rem 1rem', background: 'var(--bg-tertiary)', borderRadius: 'var(--radius-md)', textDecoration: 'none' }}>
                <div>
                  <div style={{ fontWeight: 500 }}>{a.patient?.firstname} {a.patient?.lastname}</div>
                  <div style={{ fontSize: '0.78rem', color: 'var(--text-secondary)' }}>
                    {new Date(a.start_time).toLocaleTimeString([], { hour: '2-digit', minute: '2-digit' })} — {new Date(a.end_time).toLocaleTimeString([], { hour: '2-digit', minute: '2-digit' })}
                  </div>
                  <div style={{ fontSize: '0.78rem', color: 'var(--text-muted)', marginTop: '0.2rem' }}>{a.reason_for_visit}</div>
                </div>
                <StatusBadge status={a.status} />
              </Link>
            ))}
          </div>
        )}
      </div>
    </div>
  );
}
