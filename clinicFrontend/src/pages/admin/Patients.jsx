import { useState } from 'react';
import { useQuery, useMutation, useQueryClient } from '@tanstack/react-query';
import { Plus, Edit2, Trash2, Search } from 'lucide-react';
import toast from 'react-hot-toast';
import { useForm } from 'react-hook-form';
import { getAdminPatients, createAdminPatient, updateAdminPatient, deleteAdminPatient } from '../../api/patients';
import { LoadingSpinner, EmptyState, ConfirmDialog, Modal } from '../../components/shared';
import { PageHeader } from '../../components/layout/Layout';
import { Users } from 'lucide-react';

export default function AdminPatients() {
  const qc = useQueryClient();
  const [search, setSearch]     = useState('');
  const [formOpen, setFormOpen] = useState(false);
  const [editing, setEditing]   = useState(null);
  const [deleting, setDeleting] = useState(null);
  const { register, handleSubmit, reset, setValue } = useForm();

  const { data: res, isLoading } = useQuery({ queryKey: ['admin-patients'], queryFn: getAdminPatients });

  const openEdit = (p) => {
    setEditing(p);
    ['firstname','lastname','email','phone','address','insurance_info'].forEach(k => setValue(k, p[k] ?? p.patient?.[k]));
    setValue('date_birth', p.patient?.date_birth?.split('T')[0] ?? '');
    setFormOpen(true);
  };

  const saveMut = useMutation({
    mutationFn: (data) => editing ? updateAdminPatient(editing.id, data) : createAdminPatient(data),
    onSuccess: () => { toast.success(editing ? 'Updated' : 'Patient created'); qc.invalidateQueries(['admin-patients']); setFormOpen(false); setEditing(null); reset(); },
    onError: (e) => toast.error(e.response?.data?.message || 'Save failed'),
  });

  const deleteMut = useMutation({
    mutationFn: deleteAdminPatient,
    onSuccess: () => { toast.success('Patient deleted'); qc.invalidateQueries(['admin-patients']); setDeleting(null); },
  });

  const patients = (res?.data?.data ?? []).filter(p =>
    !search || `${p.firstname} ${p.lastname} ${p.email}`.toLowerCase().includes(search.toLowerCase())
  );

  return (
    <div className="animate-fade">
      <PageHeader
        title="Patients"
        subtitle="Manage all patients in the system"
        action={<button className="btn btn-primary" onClick={() => { setEditing(null); reset(); setFormOpen(true); }}><Plus size={16} />Add Patient</button>}
      />

      <div className="filter-bar">
        <div className="search-bar" style={{ maxWidth: 320 }}>
          <Search size={15} />
          <input placeholder="Search patients..." value={search} onChange={e => setSearch(e.target.value)} />
        </div>
      </div>

      {isLoading ? <LoadingSpinner fullPage /> : patients.length === 0 ? (
        <EmptyState icon={<Users size={48} />} title="No patients found" />
      ) : (
        <div className="table-wrapper">
          <table>
            <thead><tr><th>Name</th><th>Email</th><th>Phone</th><th>D.O.B</th><th>Insurance</th><th>Actions</th></tr></thead>
            <tbody>
              {patients.map(p => (
                <tr key={p.id}>
                  <td style={{ fontWeight: 500 }}>{p.firstname} {p.lastname}</td>
                  <td style={{ color: 'var(--text-secondary)' }}>{p.email}</td>
                  <td>{p.patient?.phone ?? '—'}</td>
                  <td>{p.patient?.date_birth ? new Date(p.patient.date_birth).toLocaleDateString() : '—'}</td>
                  <td style={{ maxWidth: 140, overflow: 'hidden', textOverflow: 'ellipsis', whiteSpace: 'nowrap' }}>{p.patient?.insurance_info ?? '—'}</td>
                  <td>
                    <div style={{ display: 'flex', gap: '0.4rem' }}>
                      <button className="btn btn-sm btn-secondary" onClick={() => openEdit(p)}><Edit2 size={12} /></button>
                      <button className="btn btn-sm btn-danger" onClick={() => setDeleting(p.id)}><Trash2 size={12} /></button>
                    </div>
                  </td>
                </tr>
              ))}
            </tbody>
          </table>
        </div>
      )}

      <Modal open={formOpen} title={editing ? 'Edit Patient' : 'Add Patient'} onClose={() => { setFormOpen(false); setEditing(null); reset(); }}>
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
          <div className="grid-2">
            <div className="form-group">
              <label className="form-label">Date of Birth</label>
              <input type="date" className="form-input" {...register('date_birth', { required: true })} />
            </div>
            <div className="form-group">
              <label className="form-label">Phone</label>
              <input className="form-input" {...register('phone', { required: true })} />
            </div>
          </div>
          <div className="form-group">
            <label className="form-label">Address</label>
            <input className="form-input" {...register('address', { required: true })} />
          </div>
          <div className="form-group">
            <label className="form-label">Insurance Info</label>
            <input className="form-input" {...register('insurance_info', { required: true })} />
          </div>
          <div className="modal-footer" style={{ margin: 0 }}>
            <button type="button" className="btn btn-secondary" onClick={() => { setFormOpen(false); setEditing(null); reset(); }}>Cancel</button>
            <button type="submit" className="btn btn-primary" disabled={saveMut.isPending}>
              {saveMut.isPending ? <span className="spinner spinner-sm" /> : editing ? 'Update' : 'Add Patient'}
            </button>
          </div>
        </form>
      </Modal>

      <ConfirmDialog open={!!deleting} title="Delete Patient" message="This will permanently delete this patient and all their data."
        onConfirm={() => deleteMut.mutate(deleting)} onCancel={() => setDeleting(null)} loading={deleteMut.isPending} />
    </div>
  );
}
