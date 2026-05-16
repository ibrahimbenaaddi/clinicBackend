import { useState } from 'react';
import { useQuery, useMutation, useQueryClient } from '@tanstack/react-query';
import { Plus, Trash2, Edit2 } from 'lucide-react';
import toast from 'react-hot-toast';
import { useForm } from 'react-hook-form';
import { useAuth } from '../../context/AuthContext';
import { getDoctorRecords, createRecord, updateRecord, deleteRecord } from '../../api/records';
import { LoadingSpinner, EmptyState, ConfirmDialog, Modal } from '../../components/shared';
import { PageHeader } from '../../components/layout/Layout';
import { FileText } from 'lucide-react';

export default function DoctorRecords() {
  const { user } = useAuth();
  const id = user?.id;
  const qc = useQueryClient();
  const [formOpen, setFormOpen] = useState(false);
  const [editing, setEditing]   = useState(null);
  const [deleting, setDeleting] = useState(null);
  const { register, handleSubmit, reset, setValue, formState: { errors } } = useForm();

  const { data: res, isLoading } = useQuery({
    queryKey: ['doctor-records', id],
    queryFn: () => getDoctorRecords(id),
    enabled: !!id,
  });

  const openEdit = (r) => {
    setEditing(r);
    setValue('appointment_id', r.appointment_id);
    setValue('diagnosis_code', r.diagnosis_code);
    setValue('clinical_notes', r.clinical_notes);
    setValue('symptoms', r.symptoms);
    setFormOpen(true);
  };

  const saveMut = useMutation({
    mutationFn: (data) => editing ? updateRecord(editing.id, data) : createRecord(data),
    onSuccess: () => {
      toast.success(editing ? 'Record updated' : 'Record created');
      qc.invalidateQueries(['doctor-records', id]);
      setFormOpen(false); setEditing(null); reset();
    },
    onError: (e) => toast.error(e.response?.data?.message || 'Save failed'),
  });

  const deleteMut = useMutation({
    mutationFn: deleteRecord,
    onSuccess: () => { toast.success('Record deleted'); qc.invalidateQueries(['doctor-records', id]); setDeleting(null); },
    onError: () => toast.error('Delete failed'),
  });

  const records = res?.data?.data ?? [];

  return (
    <div className="animate-fade">
      <PageHeader
        title="Medical Records"
        subtitle="Manage patient medical records"
        action={<button className="btn btn-primary" onClick={() => { setEditing(null); reset(); setFormOpen(true); }}><Plus size={16} />New Record</button>}
      />

      {isLoading ? <LoadingSpinner fullPage /> : records.length === 0 ? (
        <EmptyState icon={<FileText size={48} />} title="No records yet" />
      ) : (
        <div className="table-wrapper">
          <table>
            <thead><tr><th>ID</th><th>Diagnosis Code</th><th>Symptoms</th><th>Notes</th><th>Actions</th></tr></thead>
            <tbody>
              {records.map(r => (
                <tr key={r.id}>
                  <td>#{r.id}</td>
                  <td style={{ fontFamily: 'monospace', color: 'var(--accent)' }}>{r.diagnosis_code}</td>
                  <td style={{ maxWidth: 160, overflow: 'hidden', textOverflow: 'ellipsis', whiteSpace: 'nowrap' }}>{r.symptoms}</td>
                  <td style={{ maxWidth: 200, overflow: 'hidden', textOverflow: 'ellipsis', whiteSpace: 'nowrap' }}>{r.clinical_notes}</td>
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

      <Modal open={formOpen} title={editing ? 'Edit Record' : 'Create Medical Record'} onClose={() => { setFormOpen(false); setEditing(null); reset(); }}>
        <form onSubmit={handleSubmit(d => saveMut.mutate(d))} style={{ display: 'flex', flexDirection: 'column', gap: '1rem' }}>
          {!editing && (
            <div className="form-group">
              <label className="form-label">Appointment ID</label>
              <input type="number" className={`form-input${errors.appointment_id ? ' error' : ''}`}
                {...register('appointment_id', { required: 'Required' })} />
            </div>
          )}
          <div className="form-group">
            <label className="form-label">Diagnosis Code</label>
            <input className={`form-input${errors.diagnosis_code ? ' error' : ''}`} placeholder="e.g. J00, I10"
              {...register('diagnosis_code', { required: 'Required' })} />
          </div>
          <div className="form-group">
            <label className="form-label">Symptoms</label>
            <textarea className={`form-input${errors.symptoms ? ' error' : ''}`} placeholder="Describe symptoms..."
              {...register('symptoms', { required: 'Required' })} />
          </div>
          <div className="form-group">
            <label className="form-label">Clinical Notes</label>
            <textarea className={`form-input${errors.clinical_notes ? ' error' : ''}`} placeholder="Clinical observations..."
              {...register('clinical_notes', { required: 'Required' })} />
          </div>
          <div className="modal-footer" style={{ margin: 0 }}>
            <button type="button" className="btn btn-secondary" onClick={() => { setFormOpen(false); setEditing(null); reset(); }}>Cancel</button>
            <button type="submit" className="btn btn-primary" disabled={saveMut.isPending}>
              {saveMut.isPending ? <span className="spinner spinner-sm" /> : editing ? 'Update' : 'Create'}
            </button>
          </div>
        </form>
      </Modal>

      <ConfirmDialog open={!!deleting} title="Delete Record" message="This will permanently delete this medical record."
        onConfirm={() => deleteMut.mutate(deleting)} onCancel={() => setDeleting(null)} loading={deleteMut.isPending} />
    </div>
  );
}
