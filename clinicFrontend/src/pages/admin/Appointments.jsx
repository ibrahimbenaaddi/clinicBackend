import { useState } from 'react';
import { useQuery, useMutation, useQueryClient } from '@tanstack/react-query';
import { Trash2, Edit2, Search } from 'lucide-react';
import toast from 'react-hot-toast';
import { useForm } from 'react-hook-form';
import { getAdminAppointments, updateAdminAppointment, deleteAdminAppointment } from '../../api/appointments';
import { StatusBadge, LoadingSpinner, EmptyState, ConfirmDialog, Modal } from '../../components/shared';
import { PageHeader } from '../../components/layout/Layout';
import { Calendar } from 'lucide-react';

const STATUSES = ['pending','confirmed','completed','cancelled','no_show'];

export default function AdminAppointments() {
  const qc = useQueryClient();
  const [search, setSearch]     = useState('');
  const [filter, setFilter]     = useState('all');
  const [editing, setEditing]   = useState(null);
  const [deleting, setDeleting] = useState(null);
  const { register, handleSubmit, reset, setValue } = useForm();

  const { data: res, isLoading } = useQuery({ queryKey: ['admin-appointments'], queryFn: getAdminAppointments });

  const openEdit = (a) => {
    setEditing(a);
    setValue('status', a.status);
    setValue('reason_for_visit', a.reason_for_visit);
  };

  const updateMut = useMutation({
    mutationFn: (data) => updateAdminAppointment(editing.id, data),
    onSuccess: () => { toast.success('Updated'); qc.invalidateQueries(['admin-appointments']); setEditing(null); reset(); },
    onError: (e) => toast.error(e.response?.data?.message || 'Update failed'),
  });

  const deleteMut = useMutation({
    mutationFn: deleteAdminAppointment,
    onSuccess: () => { toast.success('Deleted'); qc.invalidateQueries(['admin-appointments']); setDeleting(null); },
  });

  const appointments = (res?.data?.data ?? [])
    .filter(a => filter === 'all' || a.status === filter)
    .filter(a => !search || `${a.patient?.firstname} ${a.doctor?.firstname}`.toLowerCase().includes(search.toLowerCase()));

  return (
    <div className="animate-fade">
      <PageHeader title="Appointments" subtitle="View and manage all appointments" />

      <div className="filter-bar">
        <div className="search-bar" style={{ flex: 1, maxWidth: 300 }}>
          <Search size={15} />
          <input placeholder="Search..." value={search} onChange={e => setSearch(e.target.value)} />
        </div>
        {['all',...STATUSES].map(s => (
          <button key={s} className={`btn btn-sm ${filter === s ? 'btn-primary' : 'btn-secondary'}`} onClick={() => setFilter(s)}>
            {s === 'all' ? 'All' : s.replace('_', ' ')}
          </button>
        ))}
      </div>

      {isLoading ? <LoadingSpinner fullPage /> : appointments.length === 0 ? (
        <EmptyState icon={<Calendar size={48} />} title="No appointments" />
      ) : (
        <div className="table-wrapper">
          <table>
            <thead><tr><th>Patient</th><th>Doctor</th><th>Date</th><th>Status</th><th>Actions</th></tr></thead>
            <tbody>
              {appointments.map(a => (
                <tr key={a.id}>
                  <td>{a.patient?.firstname} {a.patient?.lastname}</td>
                  <td>Dr. {a.doctor?.firstname} {a.doctor?.lastname}</td>
                  <td>{new Date(a.start_time).toLocaleDateString()}</td>
                  <td><StatusBadge status={a.status} /></td>
                  <td>
                    <div style={{ display: 'flex', gap: '0.4rem' }}>
                      <button className="btn btn-sm btn-secondary" onClick={() => openEdit(a)}><Edit2 size={12} /></button>
                      <button className="btn btn-sm btn-danger" onClick={() => setDeleting(a.id)}><Trash2 size={12} /></button>
                    </div>
                  </td>
                </tr>
              ))}
            </tbody>
          </table>
        </div>
      )}

      <Modal open={!!editing} title="Edit Appointment" onClose={() => { setEditing(null); reset(); }}>
        <form onSubmit={handleSubmit(d => updateMut.mutate(d))} style={{ display: 'flex', flexDirection: 'column', gap: '1rem' }}>
          <div className="form-group">
            <label className="form-label">Status</label>
            <select className="form-input form-select" {...register('status')}>
              {STATUSES.map(s => <option key={s} value={s}>{s.replace('_',' ')}</option>)}
            </select>
          </div>
          <div className="form-group">
            <label className="form-label">Reason for Visit</label>
            <textarea className="form-input" {...register('reason_for_visit')} />
          </div>
          <div className="modal-footer" style={{ margin: 0 }}>
            <button type="button" className="btn btn-secondary" onClick={() => { setEditing(null); reset(); }}>Cancel</button>
            <button type="submit" className="btn btn-primary" disabled={updateMut.isPending}>
              {updateMut.isPending ? <span className="spinner spinner-sm" /> : 'Update'}
            </button>
          </div>
        </form>
      </Modal>

      <ConfirmDialog open={!!deleting} title="Delete Appointment" message="This will permanently delete this appointment."
        onConfirm={() => deleteMut.mutate(deleting)} onCancel={() => setDeleting(null)} loading={deleteMut.isPending} />
    </div>
  );
}
