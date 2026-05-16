import { useState } from 'react';
import { useQuery, useMutation, useQueryClient } from '@tanstack/react-query';
import { Trash2, Plus } from 'lucide-react';
import toast from 'react-hot-toast';
import { useForm } from 'react-hook-form';
import { getAdminSlots, createAdminSlot, deleteAdminSlot } from '../../api/slots';
import { StatusBadge, LoadingSpinner, EmptyState, ConfirmDialog, Modal } from '../../components/shared';
import { PageHeader } from '../../components/layout/Layout';
import { Clock } from 'lucide-react';

export default function AdminSlots() {
  const qc = useQueryClient();
  const [createOpen, setCreateOpen] = useState(false);
  const [deleting, setDeleting]     = useState(null);
  const { register, handleSubmit, reset } = useForm();

  const { data: res, isLoading } = useQuery({ queryKey: ['admin-slots'], queryFn: getAdminSlots });

  const createMut = useMutation({
    mutationFn: createAdminSlot,
    onSuccess: () => { toast.success('Slot created'); qc.invalidateQueries(['admin-slots']); setCreateOpen(false); reset(); },
    onError: (e) => toast.error(e.response?.data?.message || 'Failed'),
  });

  const deleteMut = useMutation({
    mutationFn: deleteAdminSlot,
    onSuccess: () => { toast.success('Slot deleted'); qc.invalidateQueries(['admin-slots']); setDeleting(null); },
  });

  const slots = res?.data?.data ?? [];

  return (
    <div className="animate-fade">
      <PageHeader
        title="Appointment Slots"
        subtitle="Manage all availability slots"
        action={<button className="btn btn-primary" onClick={() => { reset(); setCreateOpen(true); }}><Plus size={16} />Add Slot</button>}
      />

      {isLoading ? <LoadingSpinner fullPage /> : slots.length === 0 ? (
        <EmptyState icon={<Clock size={48} />} title="No slots" />
      ) : (
        <div className="table-wrapper">
          <table>
            <thead><tr><th>ID</th><th>Doctor ID</th><th>Start</th><th>End</th><th>Booked</th><th>Status</th><th>Action</th></tr></thead>
            <tbody>
              {slots.map(s => (
                <tr key={s.slot_id || s.id}>
                  <td>#{s.slot_id || s.id}</td>
                  <td>{s.doctor_id}</td>
                  <td>{new Date(s.start_time).toLocaleString()}</td>
                  <td>{new Date(s.end_time).toLocaleString()}</td>
                  <td>{s.booked_count}/{s.max_patients}</td>
                  <td><StatusBadge status={s.status} /></td>
                  <td>
                    <button className="btn btn-sm btn-danger" onClick={() => setDeleting(s.slot_id || s.id)}><Trash2 size={12} /></button>
                  </td>
                </tr>
              ))}
            </tbody>
          </table>
        </div>
      )}

      <Modal open={createOpen} title="Create Slot" onClose={() => { setCreateOpen(false); reset(); }}>
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
            <label className="form-label">Doctor ID</label>
            <input type="number" className="form-input" {...register('doctor_id', { required: true })} />
          </div>
          <div className="form-group">
            <label className="form-label">Start Date & Time</label>
            <input type="datetime-local" className="form-input" {...register('start_time', { required: true })} />
          </div>
          <div className="form-group">
            <label className="form-label">End Date & Time</label>
            <input type="datetime-local" className="form-input" {...register('end_time', { required: true })} />
          </div>
          <div className="form-group">
            <label className="form-label">Max Patients</label>
            <input type="number" min="1" defaultValue="1" className="form-input" {...register('max_patients', { required: true })} />
          </div>
          <div className="modal-footer" style={{ margin: 0 }}>
            <button type="button" className="btn btn-secondary" onClick={() => { setCreateOpen(false); reset(); }}>Cancel</button>
            <button type="submit" className="btn btn-primary" disabled={createMut.isPending}>
              {createMut.isPending ? <span className="spinner spinner-sm" /> : 'Create'}
            </button>
          </div>
        </form>
      </Modal>

      <ConfirmDialog open={!!deleting} title="Delete Slot" message="Permanently delete this slot?"
        onConfirm={() => deleteMut.mutate(deleting)} onCancel={() => setDeleting(null)} loading={deleteMut.isPending} />
    </div>
  );
}
