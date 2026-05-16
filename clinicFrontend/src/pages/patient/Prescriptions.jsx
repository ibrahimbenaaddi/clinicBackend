import { useQuery } from '@tanstack/react-query';
import { useAuth } from '../../context/AuthContext';
import { getPatientPrescriptions } from '../../api/prescriptions';
import { LoadingSpinner, EmptyState } from '../../components/shared';
import { PageHeader } from '../../components/layout/Layout';
import { Pill } from 'lucide-react';

export default function PatientPrescriptions() {
  const { user } = useAuth();
  const { data: res, isLoading } = useQuery({
    queryKey: ['patient-prescriptions', user?.id],
    queryFn: () => getPatientPrescriptions(user?.id),
    enabled: !!user?.id,
  });
  const rxs = res?.data?.data ?? [];

  return (
    <div className="animate-fade">
      <PageHeader title="Prescriptions" subtitle="Your prescribed medications" />
      {isLoading ? <LoadingSpinner fullPage /> : rxs.length === 0 ? (
        <EmptyState icon={<Pill size={48} />} title="No prescriptions found" />
      ) : (
        <div className="grid-2">
          {rxs.map(rx => (
            <div key={rx.id} className="card card-hover">
              <div style={{ display: 'flex', alignItems: 'center', gap: '0.75rem', marginBottom: '0.75rem' }}>
                <div style={{ width: 40, height: 40, borderRadius: 'var(--radius-md)', background: 'rgba(46,160,67,0.12)', display: 'flex', alignItems: 'center', justifyContent: 'center' }}>
                  <Pill size={18} color="var(--success)" />
                </div>
                <div style={{ fontWeight: 600 }}>{rx.medication_name}</div>
              </div>
              <p style={{ fontSize: '0.875rem', color: 'var(--text-secondary)' }}>{rx.instructions}</p>
              {rx.created_at && (
                <div style={{ marginTop: '0.75rem', fontSize: '0.75rem', color: 'var(--text-muted)' }}>
                  Issued: {new Date(rx.created_at).toLocaleDateString()}
                </div>
              )}
            </div>
          ))}
        </div>
      )}
    </div>
  );
}
