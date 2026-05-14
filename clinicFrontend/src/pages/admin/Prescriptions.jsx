import { useQuery, useMutation, useQueryClient } from '@tanstack/react-query';
import { Trash2 } from 'lucide-react';
import toast from 'react-hot-toast';
import { getAdminPrescriptions, deleteAdminPrescription } from '../../api/prescriptions';
import { LoadingSpinner, EmptyState, ConfirmDialog } from '../../components/shared';
import { PageHeader } from '../../components/layout/Layout';
import { useState } from 'react';
import { Pill } from 'lucide-react';

export default function AdminPrescriptions() {
  const qc = useQueryClient();
  const [deleting, setDeleting] = useState(null);

  const { data: res, isLoading } = useQuery({ queryKey: ['admin-prescriptions'], queryFn: getAdminPrescriptions });

  const deleteMut = useMutation({
    mutationFn: deleteAdminPrescription,
    onSuccess: () => { toast.success('Deleted'); qc.invalidateQueries(['admin-prescriptions']); setDeleting(null); },
  });

  const rxs = res?.data?.data ?? [];

  return (
    <div className="animate-fade">
      <PageHeader title="Prescriptions" subtitle="View all prescriptions in the system" />

      {isLoading ? <LoadingSpinner fullPage /> : rxs.length === 0 ? (
        <EmptyState icon={<Pill size={48} />} title="No prescriptions" />
      ) : (
        <div className="table-wrapper">
          <table>
            <thead><tr><th>ID</th><th>Record ID</th><th>Medication</th><th>Instructions</th><th>Action</th></tr></thead>
            <tbody>
              {rxs.map(rx => (
                <tr key={rx.id}>
                  <td>#{rx.id}</td>
                  <td>#{rx.record_id}</td>
                  <td style={{ fontWeight: 500, color: 'var(--success)' }}>{rx.medication_name}</td>
                  <td style={{ maxWidth: 250, overflow: 'hidden', textOverflow: 'ellipsis', whiteSpace: 'nowrap' }}>{rx.instructions}</td>
                  <td>
                    <button className="btn btn-sm btn-danger" onClick={() => setDeleting(rx.id)}><Trash2 size={12} /></button>
                  </td>
                </tr>
              ))}
            </tbody>
          </table>
        </div>
      )}

      <ConfirmDialog open={!!deleting} title="Delete Prescription" message="Permanently delete this prescription?"
        onConfirm={() => deleteMut.mutate(deleting)} onCancel={() => setDeleting(null)} loading={deleteMut.isPending} />
    </div>
  );
}
