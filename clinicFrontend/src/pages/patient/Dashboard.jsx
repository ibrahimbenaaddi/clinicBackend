import { useQuery } from '@tanstack/react-query';
import { Calendar, Receipt, Pill } from 'lucide-react';
import { Link } from 'react-router-dom';
import { useAuth } from '../../context/AuthContext';
import { getPatientAppointments } from '../../api/appointments';
import { getPatientInvoices } from '../../api/invoices';
import { getPatientPrescriptions } from '../../api/prescriptions';
import { StatusBadge, LoadingSpinner } from '../../components/shared';
import { PageHeader } from '../../components/layout/Layout';

export default function PatientDashboard() {
  const { user } = useAuth();
  const id = user?.id;

  const { data: apptRes, isLoading: apptLoading } = useQuery({
    queryKey: ['patient-appointments', id],
    queryFn: () => getPatientAppointments(id),
    enabled: !!id,
  });
  const { data: invoiceRes } = useQuery({
    queryKey: ['patient-invoices', id],
    queryFn: () => getPatientInvoices(id),
    enabled: !!id,
  });
  const { data: rxRes } = useQuery({
    queryKey: ['patient-prescriptions', id],
    queryFn: () => getPatientPrescriptions(id),
    enabled: !!id,
  });

  const appointments   = apptRes?.data?.data ?? [];
  const invoices       = invoiceRes?.data?.data ?? [];
  const prescriptions  = rxRes?.data?.data ?? [];

  const upcoming  = appointments.filter(a => ['pending','confirmed'].includes(a.status));
  const pending$  = invoices.filter(i => i.status === 'pending');

  const stats = [
    { label: 'Upcoming Appointments', value: upcoming.length,      icon: Calendar, color: '#00b4d8', bg: 'rgba(0,180,216,0.12)', link: '/patient/appointments' },
    { label: 'Total Appointments',    value: appointments.length,  icon: Calendar, color: '#388bfd', bg: 'rgba(56,139,253,0.12)', link: '/patient/appointments' },
    { label: 'Prescriptions',         value: prescriptions.length, icon: Pill,     color: '#2ea043', bg: 'rgba(46,160,67,0.12)',  link: '/patient/prescriptions' },
    { label: 'Pending Invoices',      value: pending$.length,      icon: Receipt,  color: '#d29922', bg: 'rgba(210,153,34,0.12)', link: '/patient/invoices' },
  ];

  return (
    <div className="animate-fade">
      <PageHeader
        title={`Welcome back, ${user?.firstname}!`}
        subtitle="Here's an overview of your health records"
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

      <div className="grid-2">
        <div className="card">
          <div className="flex items-center justify-between mb-2">
            <h2 style={{ fontWeight: 600, fontSize: '1rem' }}>Recent Appointments</h2>
            <Link to="/patient/appointments" className="btn btn-ghost btn-sm">View all</Link>
          </div>
          {apptLoading ? <LoadingSpinner /> : appointments.slice(0, 5).length === 0 ? (
            <p className="text-muted" style={{ fontSize: '0.875rem' }}>No appointments yet</p>
          ) : (
            <div style={{ display: 'flex', flexDirection: 'column', gap: '0.75rem' }}>
              {appointments.slice(0, 5).map(a => (
                <Link to={`/patient/appointments/${a.id}`} key={a.id}
                  style={{ display: 'flex', justifyContent: 'space-between', alignItems: 'center', padding: '0.75rem', background: 'var(--bg-tertiary)', borderRadius: 'var(--radius-md)', textDecoration: 'none' }}>
                  <div>
                    <div style={{ fontSize: '0.875rem', fontWeight: 500 }}>
                      Dr. {a.doctor?.firstname} {a.doctor?.lastname}
                    </div>
                    <div style={{ fontSize: '0.78rem', color: 'var(--text-secondary)' }}>
                      {new Date(a.start_time).toLocaleDateString()}
                    </div>
                  </div>
                  <StatusBadge status={a.status} />
                </Link>
              ))}
            </div>
          )}
        </div>

        <div className="card">
          <div className="flex items-center justify-between mb-2">
            <h2 style={{ fontWeight: 600, fontSize: '1rem' }}>Recent Invoices</h2>
            <Link to="/patient/invoices" className="btn btn-ghost btn-sm">View all</Link>
          </div>
          {invoices.slice(0, 5).length === 0 ? (
            <p className="text-muted" style={{ fontSize: '0.875rem' }}>No invoices yet</p>
          ) : (
            <div style={{ display: 'flex', flexDirection: 'column', gap: '0.75rem' }}>
              {invoices.slice(0, 5).map(inv => (
                <div key={inv.id} style={{ display: 'flex', justifyContent: 'space-between', alignItems: 'center', padding: '0.75rem', background: 'var(--bg-tertiary)', borderRadius: 'var(--radius-md)' }}>
                  <div>
                    <div style={{ fontSize: '0.875rem', fontWeight: 600 }}>${inv.amount}</div>
                    <div style={{ fontSize: '0.78rem', color: 'var(--text-secondary)' }}>{inv.payment_method}</div>
                  </div>
                  <StatusBadge status={inv.status} />
                </div>
              ))}
            </div>
          )}
        </div>
      </div>
    </div>
  );
}
