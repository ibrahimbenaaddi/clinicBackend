import { useState } from 'react';
import { useQuery, useMutation, useQueryClient } from '@tanstack/react-query';
import { Plus, Edit2, Trash2, Search } from 'lucide-react';
import toast from 'react-hot-toast';
import { useForm } from 'react-hook-form';
import { getAdminDoctors, createAdminDoctor, updateAdminDoctor, deleteAdminDoctor } from '../../api/doctors';
import { LoadingSpinner, EmptyState, ConfirmDialog, Modal } from '../../components/shared';
import { PageHeader } from '../../components/layout/Layout';
import { Stethoscope } from 'lucide-react';

const SPECS = ['cardiology','dermatology','neurology','pediatrics','orthopedics','ophthalmology'];

export default function AdminDoctors() {
  const qc = useQueryClient();
  const [search, setSearch]   = useState('');
  const [formOpen, setFormOpen] = useState(false);
  const [editing, setEditing]   = useState(null);
  const [deleting, setDeleting] = useState(null);
  const { register, handleSubmit, reset, setValue } = useForm();

  const { data: res, isLoading } = useQuery({ queryKey: ['admin-doctors'], queryFn: getAdminDoctors });

  const openEdit = (d) => {
    setEditing(d);
    ['firstname','lastname','email','specialization','license_number','phone'].forEach(k => setValue(k, d[k] ?? d.doctor?.[k]));
    setFormOpen(true);
  };

  const saveMut = useMutation({
    mutationFn: (data) => editing ? updateAdminDoctor(editing.id, data) : createAdminDoctor(data),
    onSuccess: () => { toast.success(editing ? 'Updated' : 'Doctor created'); qc.invalidateQueries(['admin-doctors']); setFormOpen(false); setEditing(null); reset(); },
    onError: (e) => toast.error(e.response?.data?.message || 'Save failed'),
  });

  const deleteMut = useMutation({
    mutationFn: deleteAdminDoctor,
    onSuccess: () => { toast.success('Doctor deleted'); qc.invalidateQueries(['admin-doctors']); setDeleting(null); },
    onError: () => toast.error('Delete failed'),
  });

  const doctors = (res?.data?.data ?? []).filter(d =>
    !search || `${d.firstname} ${d.lastname} ${d.email}`.toLowerCase().includes(search.toLowerCase())
  );

  return (
    <div className="animate-fade">
      <PageHeader
        title="Doctors"
        subtitle="Manage all doctors in the system"
        action={<button className="btn btn-primary" onClick={() => { setEditing(null); reset(); setFormOpen(true); }}><Plus size={16} />Add Doctor</button>}
      />

      <div className="filter-bar">
        <div className="search-bar" style={{ maxWidth: 320 }}>
          <Search size={15} />
          <input placeholder="Search doctors..." value={search} onChange={e => setSearch(e.target.value)} />
        </div>
      </div>

      {isLoading ? <LoadingSpinner fullPage /> : doctors.length === 0 ? (
        <EmptyState icon={<Stethoscope size={48} />} title="No doctors found" />
      ) : (
        <div className="table-wrapper">
          <table>
            <thead><tr><th>Name</th><th>Email</th><th>Specialization</th><th>License</th><th>Phone</th><th>Actions</th></tr></thead>
            <tbody>
              {doctors.map(d => (
                <tr key={d.id}>
                  <td style={{ fontWeight: 500 }}>Dr. {d.firstname} {d.lastname}</td>
                  <td style={{ color: 'var(--text-secondary)' }}>{d.email}</td>
                  <td style={{ textTransform: 'capitalize' }}>{d.doctor?.specialization ?? d.specialization ?? '—'}</td>
                  <td style={{ fontFamily: 'monospace', fontSize: '0.8rem' }}>{d.doctor?.license_number ?? '—'}</td>
                  <td>{d.doctor?.phone ?? '—'}</td>
                  <td>
                    <div style={{ display: 'flex', gap: '0.4rem' }}>
                      <button className="btn btn-sm btn-secondary" onClick={() => openEdit(d)}><Edit2 size={12} /></button>
                      <button className="btn btn-sm btn-danger" onClick={() => setDeleting(d.id)}><Trash2 size={12} /></button>
                    </div>
                  </td>
                </tr>
              ))}
            </tbody>
          </table>
        </div>
      )}

      <Modal open={formOpen} title={editing ? 'Edit Doctor' : 'Add Doctor'} onClose={() => { setFormOpen(false); setEditing(null); reset(); }}>
        <form onSubmit={handleSubmit(d => saveMut.mutate(d))} style={{ display: 'flex', flexDirection: 'column', gap: '1rem' }}>
          <div className="grid-2">
            <div className="form-group">
              <label className="form-label">First Name</label>
              <input className="form-input" {...register('firstname', { required: true })} />
            </div>
            <div className="form-group">
              <label className="form-label">Last Name</label>
              <input className="form-input" {...register('lastname', { required: true })} />
            </div>
          </div>
          <div className="form-group">
            <label className="form-label">Email</label>
            <input type="email" className="form-input" {...register('email', { required: true })} />
          </div>
          {!editing && (
            <div className="form-group">
              <label className="form-label">Password</label>
              <input type="password" className="form-input" {...register('password', { required: true })} />
            </div>
          )}
          <div className="form-group">
            <label className="form-label">Specialization</label>
            <select className="form-input form-select" {...register('specialization', { required: true })}>
              <option value="">Select...</option>
              {SPECS.map(s => <option key={s} value={s}>{s.charAt(0).toUpperCase() + s.slice(1)}</option>)}
            </select>
          </div>
          <div className="grid-2">
            <div className="form-group">
              <label className="form-label">License Number</label>
              <input className="form-input" {...register('license_number', { required: true })} />
            </div>
            <div className="form-group">
              <label className="form-label">Phone</label>
              <input className="form-input" {...register('phone', { required: true })} />
            </div>
          </div>
          <div className="modal-footer" style={{ margin: 0 }}>
            <button type="button" className="btn btn-secondary" onClick={() => { setFormOpen(false); setEditing(null); reset(); }}>Cancel</button>
            <button type="submit" className="btn btn-primary" disabled={saveMut.isPending}>
              {saveMut.isPending ? <span className="spinner spinner-sm" /> : editing ? 'Update' : 'Add Doctor'}
            </button>
          </div>
        </form>
      </Modal>

      <ConfirmDialog open={!!deleting} title="Delete Doctor" message="This will permanently delete this doctor and all associated data."
        onConfirm={() => deleteMut.mutate(deleting)} onCancel={() => setDeleting(null)} loading={deleteMut.isPending} />
    </div>
  );
}
