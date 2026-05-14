import { useState } from 'react';
import { useQuery, useMutation, useQueryClient } from '@tanstack/react-query';
import { Trash2, Edit2 } from 'lucide-react';
import toast from 'react-hot-toast';
import { useForm } from 'react-hook-form';
import { getAdminInvoices, updateAdminInvoice, deleteAdminInvoice } from '../../api/invoices';
import { StatusBadge, LoadingSpinner, EmptyState, ConfirmDialog, Modal } from '../../components/shared';
import { PageHeader } from '../../components/layout/Layout';
import { Receipt } from 'lucide-react';

const STATUSES = ['pending','paid','cancelled','refunded','overdue'];
const METHODS  = ['cash','card','insurance','bank_transfer'];

export default function AdminInvoices() {
  const qc = useQueryClient();
  const [editing, setEditing]   = useState(null);
  const [deleting, setDeleting] = useState(null);
  const { register, handleSubmit, reset, setValue } = useForm();

  const { data: res, isLoading } = useQuery({ queryKey: ['admin-invoices'], queryFn: getAdminInvoices });

  const openEdit = (inv) => {
    setEditing(inv);
    setValue('status', inv.status);
    setValue('amount', inv.amount);
    setValue('payment_method', inv.payment_method);
  };

  const updateMut = useMutation({
    mutationFn: (data) => updateAdminInvoice(editing.id, data),
    onSuccess: () => { toast.success('Updated'); qc.invalidateQueries(['admin-invoices']); setEditing(null); reset(); },
    onError: () => toast.error('Update failed'),
  });

  const deleteMut = useMutation({
    mutationFn: deleteAdminInvoice,
    onSuccess: () => { toast.success('Deleted'); qc.invalidateQueries(['admin-invoices']); setDeleting(null); },
  });

  const invoices = res?.data?.data ?? [];

  return (
    <div className="animate-fade">
      <PageHeader title="Invoices" subtitle="View and manage all invoices" />

      {isLoading ? <LoadingSpinner fullPage /> : invoices.length === 0 ? (
        <EmptyState icon={<Receipt size={48} />} title="No invoices" />
      ) : (
        <div className="table-wrapper">
          <table>
            <thead><tr><th>ID</th><th>Amount</th><th>Date</th><th>Payment</th><th>Status</th><th>Actions</th></tr></thead>
            <tbody>
              {invoices.map(inv => (
                <tr key={inv.id}>
                  <td>#{inv.id}</td>
                  <td style={{ fontWeight: 600, color: 'var(--accent)' }}>${inv.amount}</td>
                  <td>{new Date(inv.invoice_date).toLocaleDateString()}</td>
                  <td style={{ textTransform: 'capitalize' }}>{inv.payment_method?.replace('_',' ')}</td>
                  <td><StatusBadge status={inv.status} /></td>
                  <td>
                    <div style={{ display: 'flex', gap: '0.4rem' }}>
                      <button className="btn btn-sm btn-secondary" onClick={() => openEdit(inv)}><Edit2 size={12} /></button>
                      <button className="btn btn-sm btn-danger" onClick={() => setDeleting(inv.id)}><Trash2 size={12} /></button>
                    </div>
                  </td>
                </tr>
              ))}
            </tbody>
          </table>
        </div>
      )}

      <Modal open={!!editing} title="Edit Invoice" onClose={() => { setEditing(null); reset(); }}>
        <form onSubmit={handleSubmit(d => updateMut.mutate(d))} style={{ display: 'flex', flexDirection: 'column', gap: '1rem' }}>
          <div className="form-group">
            <label className="form-label">Amount ($)</label>
            <input type="number" step="0.01" className="form-input" {...register('amount', { required: true })} />
          </div>
          <div className="form-group">
            <label className="form-label">Payment Method</label>
            <select className="form-input form-select" {...register('payment_method')}>
              {METHODS.map(m => <option key={m} value={m}>{m.replace('_',' ')}</option>)}
            </select>
          </div>
          <div className="form-group">
            <label className="form-label">Status</label>
            <select className="form-input form-select" {...register('status')}>
              {STATUSES.map(s => <option key={s} value={s}>{s}</option>)}
            </select>
          </div>
          <div className="modal-footer" style={{ margin: 0 }}>
            <button type="button" className="btn btn-secondary" onClick={() => { setEditing(null); reset(); }}>Cancel</button>
            <button type="submit" className="btn btn-primary" disabled={updateMut.isPending}>
              {updateMut.isPending ? <span className="spinner spinner-sm" /> : 'Update'}
            </button>
          </div>
        </form>
      </Modal>

      <ConfirmDialog open={!!deleting} title="Delete Invoice" message="Permanently delete this invoice?"
        onConfirm={() => deleteMut.mutate(deleting)} onCancel={() => setDeleting(null)} loading={deleteMut.isPending} />
    </div>
  );
}
