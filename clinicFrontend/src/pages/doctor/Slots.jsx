import { useState } from 'react';
import { useQuery, useMutation, useQueryClient } from '@tanstack/react-query';
import { Plus, Trash2, Clock } from 'lucide-react';
import toast from 'react-hot-toast';
import { useForm } from 'react-hook-form';
import { useAuth } from '../../context/AuthContext';
import { getDoctorSlots, createSlot, deleteSlot } from '../../api/slots';
import { StatusBadge, LoadingSpinner, EmptyState, ConfirmDialog, Modal } from '../../components/shared';
import { PageHeader } from '../../components/layout/Layout';

export default function DoctorSlots() {
  const { user } = useAuth();
  const id = user?.id;
  const qc = useQueryClient();
  const [createOpen, setCreateOpen] = useState(false);
  const [deleting, setDeleting] = useState(null);

  const { data: res, isLoading } = useQuery({
    queryKey: ['doctor-slots', id],
    queryFn: () => getDoctorSlots(id),
    enabled: !!id,
  });

  const { register, handleSubmit, reset, formState: { errors } } = useForm();

  const createMut = useMutation({
    mutationFn: (data) => createSlot({ ...data, doctor_id: id }),
    onSuccess: () => { toast.success('Slot created!'); qc.invalidateQueries(['doctor-slots', id]); setCreateOpen(false); reset(); },
    onError: (e) => toast.error(e.response?.data?.message || 'Failed to create slot'),
  });

  const deleteMut = useMutation({
    mutationFn: (slotId) => deleteSlot(slotId),
    onSuccess: () => { toast.success('Slot deleted'); qc.invalidateQueries(['doctor-slots', id]); setDeleting(null); },
    onError: () => toast.error('Failed to delete slot'),
  });

  const slots = res?.data?.data ?? [];

  return (
    <div className="animate-fade">
      <PageHeader
        title="My Availability Slots"
        subtitle="Manage your schedule and availability"
        action={<button className="btn btn-primary" onClick={() => setCreateOpen(true)}><Plus size={16} />Add Slot</button>}
      />

      {isLoading ? <LoadingSpinner fullPage /> : slots.length === 0 ? (
        <EmptyState icon={<Clock size={48} />} title="No slots yet" description="Add your availability slots for patients to book" />
      ) : (
        <div className="grid-3">
          {slots.map(s => (
            <div key={s.slot_id || s.id} className="card card-hover">
              <div style={{ display: 'flex', justifyContent: 'space-between', alignItems: 'flex-start', marginBottom: '0.75rem' }}>
                <StatusBadge status={s.status} />
                <button className="btn btn-ghost btn-icon btn-sm" onClick={() => setDeleting(s.slot_id || s.id)}>
                  <Trash2 size={14} color="var(--danger)" />
                </button>
              </div>
              <div style={{ fontWeight: 600, marginBottom: '0.4rem' }}>
                {new Date(s.start_time).toLocaleDateString()}
              </div>
              <div style={{ fontSize: '0.875rem', color: 'var(--text-secondary)' }}>
                {new Date(s.start_time).toLocaleTimeString([], { hour: '2-digit', minute: '2-digit' })} —{' '}
                {new Date(s.end_time).toLocaleTimeString([], { hour: '2-digit', minute: '2-digit' })}
              </div>
              <div style={{ marginTop: '0.75rem', fontSize: '0.8rem', color: 'var(--text-muted)' }}>
                {s.booked_count} / {s.max_patients} booked
              </div>
              <div style={{ marginTop: '0.5rem', height: 4, borderRadius: 2, background: 'var(--border)', overflow: 'hidden' }}>
                <div style={{ height: '100%', width: `${(s.booked_count / s.max_patients) * 100}%`, background: 'var(--accent)', borderRadius: 2 }} />
              </div>
            </div>
          ))}
        </div>
      )}

      <Modal open={createOpen} title="Create Availability Slot" onClose={() => { setCreateOpen(false); reset(); }}>
        <form onSubmit={handleSubmit(d => {
          const formatDateTime = (dt) => {
            let str = dt.replace('T', ' ');
            if (str.length === 16) str += ':00';
            return str;
          };
          createMut.mutate({
            ...d,
            start_time: formatDateTime(d.start_time),
            end_time: formatDateTime(d.end_time)
          });
        })} style={{ display: 'flex', flexDirection: 'column', gap: '1rem' }}>
          <div className="form-group">
            <label className="form-label">Start Date & Time</label>
            <input type="datetime-local" className={`form-input${errors.start_time ? ' error' : ''}`}
              {...register('start_time', { required: 'Required' })} />
          </div>
          <div className="form-group">
            <label className="form-label">End Date & Time</label>
            <input type="datetime-local" className={`form-input${errors.end_time ? ' error' : ''}`}
              {...register('end_time', { required: 'Required' })} />
          </div>
          <div className="form-group">
            <label className="form-label">Max Patients</label>
            <input type="number" min="1" className={`form-input${errors.max_patients ? ' error' : ''}`}
              defaultValue={1} {...register('max_patients', { required: true, min: 1 })} />
          </div>
          <div className="modal-footer" style={{ margin: 0 }}>
            <button type="button" className="btn btn-secondary" onClick={() => { setCreateOpen(false); reset(); }}>Cancel</button>
            <button type="submit" className="btn btn-primary" disabled={createMut.isPending}>
              {createMut.isPending ? <span className="spinner spinner-sm" /> : 'Create Slot'}
            </button>
          </div>
        </form>
      </Modal>

      <ConfirmDialog open={!!deleting} title="Delete Slot" message="This will permanently delete this time slot."
        onConfirm={() => deleteMut.mutate(deleting)} onCancel={() => setDeleting(null)} loading={deleteMut.isPending} />
    </div>
  );
}
