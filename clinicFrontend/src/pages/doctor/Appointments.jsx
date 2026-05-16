import { useState } from 'react';
import { useQuery, useMutation, useQueryClient } from '@tanstack/react-query';
import { Link } from 'react-router-dom';
import { Search } from 'lucide-react';
import toast from 'react-hot-toast';
import { useAuth } from '../../context/AuthContext';
import { getDoctorAppointments, updateAppointmentStatus } from '../../api/appointments';
import { StatusBadge, LoadingSpinner, EmptyState, Modal } from '../../components/shared';
import { PageHeader } from '../../components/layout/Layout';

const STATUSES = ['all','pending','confirmed','completed','cancelled','no_show'];
const ALLOWED_TRANSITIONS = { pending: ['confirmed','cancelled'], confirmed: ['completed','no_show','cancelled'], completed: [], cancelled: [], no_show: [] };

export default function DoctorAppointments() {
  const { user } = useAuth();
  const id = user?.id;
  const qc = useQueryClient();
  const [filter, setFilter] = useState('all');
  const [search, setSearch] = useState('');
  const [updating, setUpdating] = useState(null); // { apptId, currentStatus }

  const { data: res, isLoading } = useQuery({
    queryKey: ['doctor-appointments', id],
    queryFn: () => getDoctorAppointments(id),
    enabled: !!id,
  });

  const statusMut = useMutation({
    mutationFn: ({ apptId, status }) => updateAppointmentStatus(id, apptId, { status }),
    onSuccess: () => { toast.success('Status updated'); qc.invalidateQueries(['doctor-appointments', id]); setUpdating(null); },
    onError: (e) => toast.error(e.response?.data?.message || 'Update failed'),
  });

  const appointments = res?.data?.data ?? [];
  const filtered = appointments
    .filter(a => filter === 'all' || a.status === filter)
    .filter(a => !search || `${a.patient?.firstname} ${a.patient?.lastname}`.toLowerCase().includes(search.toLowerCase()));

  return (
    <div className="animate-fade">
      <PageHeader title="Appointments" subtitle="Manage your patient appointments" />

      <div className="filter-bar">
        <div className="search-bar" style={{ flex: 1, maxWidth: 300 }}>
          <Search size={15} />
          <input placeholder="Search patient..." value={search} onChange={e => setSearch(e.target.value)} />
        </div>
        {STATUSES.map(s => (
          <button key={s} className={`btn btn-sm ${filter === s ? 'btn-primary' : 'btn-secondary'}`} onClick={() => setFilter(s)}>
            {s === 'all' ? 'All' : s.replace('_', ' ')}
          </button>
        ))}
      </div>

      {isLoading ? <LoadingSpinner fullPage /> : filtered.length === 0 ? (
        <EmptyState title="No appointments found" />
      ) : (
        <div className="table-wrapper">
          <table>
            <thead><tr><th>Patient</th><th>Date</th><th>Time</th><th>Reason</th><th>Status</th><th>Actions</th></tr></thead>
            <tbody>
              {filtered.map(a => (
                <tr key={a.id}>
                  <td>{a.patient?.firstname} {a.patient?.lastname}</td>
                  <td>{new Date(a.start_time).toLocaleDateString()}</td>
                  <td>{new Date(a.start_time).toLocaleTimeString([], { hour: '2-digit', minute: '2-digit' })}</td>
                  <td style={{ maxWidth: 180, overflow: 'hidden', textOverflow: 'ellipsis', whiteSpace: 'nowrap' }}>{a.reason_for_visit}</td>
                  <td><StatusBadge status={a.status} /></td>
                  <td>
                    <div style={{ display: 'flex', gap: '0.4rem' }}>
                      <Link to={`/doctor/appointments/${a.id}`} className="btn btn-sm btn-secondary">View</Link>
                      {ALLOWED_TRANSITIONS[a.status]?.length > 0 && (
                        <button className="btn btn-sm btn-primary" onClick={() => setUpdating({ apptId: a.id, currentStatus: a.status })}>Update</button>
                      )}
                    </div>
                  </td>
                </tr>
              ))}
            </tbody>
          </table>
        </div>
      )}

      <Modal open={!!updating} title="Update Appointment Status" onClose={() => setUpdating(null)}>
        {updating && (
          <div style={{ display: 'flex', flexDirection: 'column', gap: '0.75rem' }}>
            <p style={{ color: 'var(--text-secondary)', fontSize: '0.875rem' }}>Select a new status:</p>
            <div style={{ display: 'flex', gap: '0.5rem', flexWrap: 'wrap' }}>
              {ALLOWED_TRANSITIONS[updating.currentStatus].map(s => (
                <button key={s} className="btn btn-secondary"
                  disabled={statusMut.isPending}
                  onClick={() => statusMut.mutate({ apptId: updating.apptId, status: s })}>
                  {statusMut.isPending ? <span className="spinner spinner-sm" /> : s.replace('_', ' ')}
                </button>
              ))}
            </div>
          </div>
        )}
      </Modal>
    </div>
  );
}
