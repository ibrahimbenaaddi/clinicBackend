import { useState } from 'react';
import { useQuery, useMutation, useQueryClient } from '@tanstack/react-query';
import { Trash2, Edit2 } from 'lucide-react';
import toast from 'react-hot-toast';
import { useForm } from 'react-hook-form';
import { getAdminRecords, updateAdminRecord, deleteAdminRecord } from '../../api/records';
import { LoadingSpinner, EmptyState, ConfirmDialog, Modal } from '../../components/shared';
import { PageHeader } from '../../components/layout/Layout';
import { FileText } from 'lucide-react';

export default function AdminRecords() {
  const qc = useQueryClient();
  const [editing, setEditing]   = useState(null);
  const [deleting, setDeleting] = useState(null);
  const { register, handleSubmit, reset, setValue } = useForm();

  const { data: res, isLoading } = useQuery({ queryKey: ['admin-records'], queryFn: getAdminRecords });

  const openEdit = (r) => {
    setEditing(r);
    setValue('diagnosis_code', r.diagnosis_code);
    setValue('symptoms', r.symptoms);
    setValue('clinical_notes', r.clinical_notes);
  };

  const updateMut = useMutation({
    mutationFn: (data) => updateAdminRecord(editing.id, data),
    onSuccess: () => { toast.success('Updated'); qc.invalidateQueries(['admin-records']); setEditing(null); reset(); },
    onError: () => toast.error('Update failed'),
  });

  const deleteMut = useMutation({
    mutationFn: deleteAdminRecord,
    onSuccess: () => { toast.success('Deleted'); qc.invalidateQueries(['admin-records']); setDeleting(null); },
  });

  const records = res?.data?.data ?? [];

  return (
    <div className="animate-fade">
      <PageHeader title="Medical Records" subtitle="View and manage all medical records" />

      {isLoading ? <LoadingSpinner fullPage /> : records.length === 0 ? (
        <EmptyState icon={<FileText size={48} />} title="No records" />
      ) : (
        <div className="table-wrapper">
          <table>
            <thead><tr><th>ID</th><th>Appointment</th><th>Diagnosis</th><th>Symptoms</th><th>Actions</th></tr></thead>
            <tbody>
              {records.map(r => (
                <tr key={r.id}>
                  <td>#{r.id}</td>
                  <td>#{r.appointment_id}</td>
                  <td style={{ fontFamily: 'monospace', color: 'var(--accent)' }}>{r.diagnosis_code}</td>
                  <td style={{ maxWidth: 200, overflow: 'hidden', textOverflow: 'ellipsis', whiteSpace: 'nowrap' }}>{r.symptoms}</td>
                  <td>
                    <div style={{ display: 'flex', gap: '0.4rem' }}>
                      <button className="btn btn-sm btn-secondary" onClick={() => openEdit(r)}><Edit2 size={12} /></button>
                      <button className="btn btn-sm btn-danger" onClick={() => setDeleting(r.id)}><Trash2 size={12} /></button>
                    </div>
                  </td>
                </tr>
              ))}
            </tbody>
          </table>
        </div>
      )}

      <Modal open={!!editing} title="Edit Record" onClose={() => { setEditing(null); reset(); }}>
        <form onSubmit={handleSubmit(d => updateMut.mutate(d))} style={{ display: 'flex', flexDirection: 'column', gap: '1rem' }}>
          <div className="form-group">
            <label className="form-label">Diagnosis Code</label>
            <input className="form-input" {...register('diagnosis_code', { required: true })} />
          </div>
          <div className="form-group">
            <label className="form-label">Symptoms</label>
            <textarea className="form-input" {...register('symptoms', { required: true })} />
          </div>
          <div className="form-group">
            <label className="form-label">Clinical Notes</label>
            <textarea className="form-input" {...register('clinical_notes', { required: true })} />
          </div>
          <div className="modal-footer" style={{ margin: 0 }}>
            <button type="button" className="btn btn-secondary" onClick={() => { setEditing(null); reset(); }}>Cancel</button>
            <button type="submit" className="btn btn-primary" disabled={updateMut.isPending}>
              {updateMut.isPending ? <span className="spinner spinner-sm" /> : 'Update'}
            </button>
          </div>
        </form>
      </Modal>

      <ConfirmDialog open={!!deleting} title="Delete Record" message="Permanently delete this medical record?"
        onConfirm={() => deleteMut.mutate(deleting)} onCancel={() => setDeleting(null)} loading={deleteMut.isPending} />
    </div>
  );
}
