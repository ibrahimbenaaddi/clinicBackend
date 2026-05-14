import { useState } from 'react';
import { useQuery, useMutation, useQueryClient } from '@tanstack/react-query';
import { Plus, Edit2 } from 'lucide-react';
import toast from 'react-hot-toast';
import { useForm } from 'react-hook-form';
import { useAuth } from '../../context/AuthContext';
import { getDoctorInvoices, createInvoice, updateInvoice } from '../../api/invoices';
import { StatusBadge, LoadingSpinner, EmptyState, Modal } from '../../components/shared';
import { PageHeader } from '../../components/layout/Layout';
import { Receipt } from 'lucide-react';

const PAYMENT_METHODS = ['cash','card','insurance','bank_transfer'];
const INVOICE_STATUSES = ['pending','paid','cancelled','refunded','overdue'];

export default function DoctorInvoices() {
  const { user } = useAuth();
  const id = user?.id;
  const qc = useQueryClient();
  const [formOpen, setFormOpen] = useState(false);
  const [editing, setEditing]   = useState(null);
  const { register, handleSubmit, reset, setValue } = useForm();

  const { data: res, isLoading } = useQuery({
    queryKey: ['doctor-invoices', id],
    queryFn: () => getDoctorInvoices(id),
    enabled: !!id,
  });

  const openEdit = (inv) => {
    setEditing(inv);
    setValue('status', inv.status);
    setValue('amount', inv.amount);
    setValue('payment_method', inv.payment_method);
    setFormOpen(true);
  };

  const saveMut = useMutation({
    mutationFn: (data) => editing ? updateInvoice(editing.id, data) : createInvoice(data),
    onSuccess: () => {
      toast.success(editing ? 'Invoice updated' : 'Invoice created');
      qc.invalidateQueries(['doctor-invoices', id]);
      setFormOpen(false); setEditing(null); reset();
    },
    onError: (e) => toast.error(e.response?.data?.message || 'Save failed'),
  });

  const invoices = res?.data?.data ?? [];

  return (
    <div className="animate-fade">
      <PageHeader
        title="Invoices"
        subtitle="Manage patient billing"
        action={<button className="btn btn-primary" onClick={() => { setEditing(null); reset(); setFormOpen(true); }}><Plus size={16} />New Invoice</button>}
      />

      {isLoading ? <LoadingSpinner fullPage /> : invoices.length === 0 ? (
        <EmptyState icon={<Receipt size={48} />} title="No invoices yet" />
      ) : (
        <div className="table-wrapper">
          <table>
            <thead><tr><th>ID</th><th>Amount</th><th>Date</th><th>Payment</th><th>Status</th><th>Action</th></tr></thead>
            <tbody>
              {invoices.map(inv => (
                <tr key={inv.id}>
                  <td>#{inv.id}</td>
                  <td style={{ fontWeight: 600, color: 'var(--accent)' }}>${inv.amount}</td>
                  <td>{new Date(inv.invoice_date).toLocaleDateString()}</td>
                  <td style={{ textTransform: 'capitalize' }}>{inv.payment_method?.replace('_',' ')}</td>
                  <td><StatusBadge status={inv.status} /></td>
                  <td><button className="btn btn-sm btn-secondary" onClick={() => openEdit(inv)}><Edit2 size={12} />Edit</button></td>
                </tr>
              ))}
            </tbody>
          </table>
        </div>
      )}

      <Modal open={formOpen} title={editing ? 'Edit Invoice' : 'Create Invoice'} onClose={() => { setFormOpen(false); setEditing(null); reset(); }}>
        <form onSubmit={handleSubmit(d => saveMut.mutate(d))} style={{ display: 'flex', flexDirection: 'column', gap: '1rem' }}>
          {!editing && (
            <div className="form-group">
              <label className="form-label">Appointment ID</label>
              <input type="number" className="form-input" {...register('appointment_id', { required: true })} />
            </div>
          )}
          <div className="form-group">
            <label className="form-label">Amount ($)</label>
            <input type="number" step="0.01" className="form-input" {...register('amount', { required: true })} />
          </div>
          {!editing && (
            <div className="form-group">
              <label className="form-label">Invoice Date</label>
              <input type="datetime-local" className="form-input" {...register('invoice_date', { required: true })} />
            </div>
          )}
          <div className="form-group">
            <label className="form-label">Payment Method</label>
            <select className="form-input form-select" {...register('payment_method', { required: true })}>
              {PAYMENT_METHODS.map(m => <option key={m} value={m}>{m.replace('_',' ')}</option>)}
            </select>
          </div>
          {editing && (
            <div className="form-group">
              <label className="form-label">Status</label>
              <select className="form-input form-select" {...register('status')}>
                {INVOICE_STATUSES.map(s => <option key={s} value={s}>{s}</option>)}
              </select>
            </div>
          )}
          <div className="modal-footer" style={{ margin: 0 }}>
            <button type="button" className="btn btn-secondary" onClick={() => { setFormOpen(false); setEditing(null); reset(); }}>Cancel</button>
            <button type="submit" className="btn btn-primary" disabled={saveMut.isPending}>
              {saveMut.isPending ? <span className="spinner spinner-sm" /> : editing ? 'Update' : 'Create'}
            </button>
          </div>
        </form>
      </Modal>
    </div>
  );
}
