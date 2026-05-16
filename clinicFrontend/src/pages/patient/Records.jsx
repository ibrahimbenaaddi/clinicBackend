import { useQuery } from '@tanstack/react-query';
import { useAuth } from '../../context/AuthContext';
import { getPatientRecords } from '../../api/records';
import { LoadingSpinner, EmptyState } from '../../components/shared';
import { PageHeader } from '../../components/layout/Layout';
import { Link } from 'react-router-dom';
import { FileText } from 'lucide-react';

export default function PatientRecords() {
  const { user } = useAuth();
  const { data: res, isLoading } = useQuery({
    queryKey: ['patient-records', user?.id],
    queryFn: () => getPatientRecords(user?.id),
    enabled: !!user?.id,
  });
  const records = res?.data?.data ?? [];

  return (
    <div className="animate-fade">
      <PageHeader title="Medical Records" subtitle="View your medical history" />
      {isLoading ? <LoadingSpinner fullPage /> : records.length === 0 ? (
        <EmptyState icon={<FileText size={48} />} title="No medical records yet" />
      ) : (
        <div className="table-wrapper">
          <table>
            <thead><tr><th>#</th><th>Diagnosis Code</th><th>Symptoms</th><th>Date</th><th>Action</th></tr></thead>
            <tbody>
              {records.map(r => (
                <tr key={r.id}>
                  <td>{r.id}</td>
                  <td style={{ fontFamily: 'monospace', color: 'var(--accent)' }}>{r.diagnosis_code}</td>
                  <td style={{ maxWidth: 200, overflow: 'hidden', textOverflow: 'ellipsis', whiteSpace: 'nowrap' }}>{r.symptoms}</td>
                  <td>{r.created_at ? new Date(r.created_at).toLocaleDateString() : '—'}</td>
                  <td><Link to={`/patient/records/${r.id}`} className="btn btn-sm btn-secondary">View</Link></td>
                </tr>
              ))}
            </tbody>
          </table>
        </div>
      )}
    </div>
  );
}
