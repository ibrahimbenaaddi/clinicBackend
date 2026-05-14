import { useQuery } from '@tanstack/react-query';
import { useAuth } from '../../context/AuthContext';
import { getPatientInvoices } from '../../api/invoices';
import { StatusBadge, LoadingSpinner, EmptyState } from '../../components/shared';
import { PageHeader } from '../../components/layout/Layout';
import { Receipt } from 'lucide-react';

export default function PatientInvoices() {
  const { user } = useAuth();
  const { data: res, isLoading } = useQuery({
    queryKey: ['patient-invoices', user?.id],
    queryFn: () => getPatientInvoices(user?.id),
    enabled: !!user?.id,
  });
  const invoices = res?.data?.data ?? [];

  return (
    <div className="animate-fade">
      <PageHeader title="Invoices" subtitle="Your billing history" />
      {isLoading ? <LoadingSpinner fullPage /> : invoices.length === 0 ? (
        <EmptyState icon={<Receipt size={48} />} title="No invoices yet" />
      ) : (
        <div className="table-wrapper">
          <table>
            <thead><tr><th>ID</th><th>Amount</th><th>Date</th><th>Payment Method</th><th>Status</th></tr></thead>
            <tbody>
              {invoices.map(inv => (
                <tr key={inv.id}>
                  <td>#{inv.id}</td>
                  <td style={{ fontWeight: 600, color: 'var(--accent)' }}>${inv.amount}</td>
                  <td>{new Date(inv.invoice_date).toLocaleDateString()}</td>
                  <td style={{ textTransform: 'capitalize' }}>{inv.payment_method?.replace('_', ' ')}</td>
                  <td><StatusBadge status={inv.status} /></td>
                </tr>
              ))}
            </tbody>
          </table>
        </div>
      )}
    </div>
  );
}
