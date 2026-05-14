import { useState } from 'react';
import { useQuery, useMutation, useQueryClient } from '@tanstack/react-query';
import { Plus, Trash2, Edit2 } from 'lucide-react';
import toast from 'react-hot-toast';
import { useForm } from 'react-hook-form';
import { useAuth } from '../../context/AuthContext';
import { getDoctorPrescriptions, createPrescription, updatePrescription, deletePrescription } from '../../api/prescriptions';
import { LoadingSpinner, EmptyState, ConfirmDialog, Modal } from '../../components/shared';
import { PageHeader } from '../../components/layout/Layout';
import { Pill } from 'lucide-react';

export default function DoctorPrescriptions() {
  const { user } = useAuth();
  const id = user?.id;
  const qc = useQueryClient();
  const [formOpen, setFormOpen] = useState(false);
  const [editing, setEditing]   = useState(null);
  const [deleting, setDeleting] = useState(null);
  const { register, handleSubmit, reset, setValue } = useForm();

  const { data: res, isLoading } = useQuery({
    queryKey: ['doctor-prescriptions', id],
    queryFn: () => getDoctorPrescriptions(id),
    enabled: !!id,
  });

  const openEdit = (rx) => {
    setEditing(rx);
    setValue('record_id', rx.record_id);
    setValue('medication_name', rx.medication_name);
    setValue('instructions', rx.instructions);
    setFormOpen(true);
  };

  const saveMut = useMutation({
    mutationFn: (data) => editing ? updatePrescription(editing.id, data) : createPrescription(data),
    onSuccess: () => {
      toast.success(editing ? 'Updated' : 'Created');
      qc.invalidateQueries(['doctor-prescriptions', id]);
      setFormOpen(false); setEditing(null); reset();
    },
    onError: (e) => toast.error(e.response?.data?.message || 'Save failed'),
  });

  const deleteMut = useMutation({
    mutationFn: deletePrescription,
    onSuccess: () => { toast.success('Deleted'); qc.invalidateQueries(['doctor-prescriptions', id]); setDeleting(null); },
  });

  const rxs = res?.data?.data ?? [];

  return (
    <div className="animate-fade">
      <PageHeader
        title="Prescriptions"
        subtitle="Manage patient prescriptions"
        action={<button className="btn btn-primary" onClick={() => { setEditing(null); reset(); setFormOpen(true); }}><Plus size={16} />New Prescription</button>}
      />

      {isLoading ? <LoadingSpinner fullPage /> : rxs.length === 0 ? (
        <EmptyState icon={<Pill size={48} />} title="No prescriptions yet" />
      ) : (
        <div className="grid-2">
          {rxs.map(rx => (
            <div key={rx.id} className="card card-hover">
              <div style={{ display: 'flex', justifyContent: 'space-between', alignItems: 'flex-start' }}>
                <div style={{ display: 'flex', alignItems: 'center', gap: '0.75rem' }}>
                  <div style={{ width: 36, height: 36, borderRadius: 'var(--radius-sm)', background: 'rgba(46,160,67,0.12)', display: 'flex', alignItems: 'center', justifyContent: 'center' }}>
                    <Pill size={16} color="var(--success)" />
                  </div>
                  <div style={{ fontWeight: 600 }}>{rx.medication_name}</div>
                </div>
                <div style={{ display: 'flex', gap: '0.3rem' }}>
                  <button className="btn btn-ghost btn-icon btn-sm" onClick={() => openEdit(rx)}><Edit2 size={13} /></button>
                  <button className="btn btn-ghost btn-icon btn-sm" onClick={() => setDeleting(rx.id)}><Trash2 size={13} color="var(--danger)" /></button>
                </div>
              </div>
              <p style={{ fontSize: '0.875rem', color: 'var(--text-secondary)', marginTop: '0.75rem' }}>{rx.instructions}</p>
            </div>
          ))}
        </div>
      )}

      <Modal open={formOpen} title={editing ? 'Edit Prescription' : 'New Prescription'} onClose={() => { setFormOpen(false); setEditing(null); reset(); }}>
        <form onSubmit={handleSubmit(d => saveMut.mutate(d))} style={{ display: 'flex', flexDirection: 'column', gap: '1rem' }}>
          {!editing && (
            <div className="form-group">
              <label className="form-label">Record ID</label>
              <input type="number" className="form-input" {...register('record_id', { required: true })} />
            </div>
          )}
          <div className="form-group">
            <label className="form-label">Medication Name</label>
            <input className="form-input" placeholder="e.g. Amoxicillin 500mg" {...register('medication_name', { required: true })} />
          </div>
          <div className="form-group">
            <label className="form-label">Instructions</label>
            <textarea className="form-input" placeholder="e.g. Take twice daily after meals" {...register('instructions', { required: true })} />
          </div>
          <div className="modal-footer" style={{ margin: 0 }}>
            <button type="button" className="btn btn-secondary" onClick={() => { setFormOpen(false); setEditing(null); reset(); }}>Cancel</button>
            <button type="submit" className="btn btn-primary" disabled={saveMut.isPending}>
              {saveMut.isPending ? <span className="spinner spinner-sm" /> : editing ? 'Update' : 'Create'}
            </button>
          </div>
        </form>
      </Modal>

      <ConfirmDialog open={!!deleting} title="Delete Prescription" message="This will permanently delete this prescription."
        onConfirm={() => deleteMut.mutate(deleting)} onCancel={() => setDeleting(null)} loading={deleteMut.isPending} />
    </div>
  );
}
