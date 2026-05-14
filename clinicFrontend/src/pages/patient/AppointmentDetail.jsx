import { useQuery, useMutation, useQueryClient } from '@tanstack/react-query';
import { useParams, useNavigate } from 'react-router-dom';
import { ArrowLeft, XCircle } from 'lucide-react';
import toast from 'react-hot-toast';
import { useAuth } from '../../context/AuthContext';
import { getPatientAppointment, cancelAppointment } from '../../api/appointments';
import { StatusBadge, LoadingSpinner, ConfirmDialog } from '../../components/shared';
import { PageHeader } from '../../components/layout/Layout';
import { useState } from 'react';

function InfoRow({ label, value }) {
  return (
    <div style={{ display: 'flex', justifyContent: 'space-between', padding: '0.75rem 0', borderBottom: '1px solid var(--border)' }}>
      <span style={{ fontSize: '0.8rem', color: 'var(--text-secondary)', fontWeight: 600, textTransform: 'uppercase', letterSpacing: '0.05em' }}>{label}</span>
      <span style={{ fontSize: '0.9rem', fontWeight: 500 }}>{value}</span>
    </div>
  );
}

export default function PatientAppointmentDetail() {
  const { id } = useParams();
  const { user } = useAuth();
  const navigate = useNavigate();
  const qc = useQueryClient();
  const [confirmOpen, setConfirmOpen] = useState(false);

  const { data: res, isLoading } = useQuery({
    queryKey: ['patient-appointment', id],
    queryFn: () => getPatientAppointment(id),
  });

  const cancelMut = useMutation({
    mutationFn: () => cancelAppointment(user?.id, id),
    onSuccess: () => {
      toast.success('Appointment cancelled');
      qc.invalidateQueries(['patient-appointments']);
      setConfirmOpen(false);
    },
    onError: (e) => toast.error(e.response?.data?.message || 'Failed to cancel'),
  });

  if (isLoading) return <LoadingSpinner fullPage />;
  const a = res?.data?.data;
  if (!a) return <div>Appointment not found</div>;

  const canCancel = ['pending', 'confirmed'].includes(a.status);

  return (
    <div className="animate-fade">
      <PageHeader
        title="Appointment Details"
        action={
          <div style={{ display: 'flex', gap: '0.5rem' }}>
            <button className="btn btn-secondary" onClick={() => navigate(-1)}><ArrowLeft size={16} />Back</button>
            {canCancel && <button className="btn btn-danger" onClick={() => setConfirmOpen(true)}><XCircle size={16} />Cancel</button>}
          </div>
        }
      />

      <div className="grid-2">
        <div className="card">
          <h3 style={{ fontWeight: 600, marginBottom: '1rem' }}>Appointment Info</h3>
          <InfoRow label="Status" value={<StatusBadge status={a.status} />} />
          <InfoRow label="Date" value={new Date(a.start_time).toLocaleDateString()} />
          <InfoRow label="Start" value={new Date(a.start_time).toLocaleTimeString([], { hour: '2-digit', minute: '2-digit' })} />
          <InfoRow label="End" value={new Date(a.end_time).toLocaleTimeString([], { hour: '2-digit', minute: '2-digit' })} />
          <InfoRow label="Reason" value={a.reason_for_visit} />
        </div>

        <div className="card">
          <h3 style={{ fontWeight: 600, marginBottom: '1rem' }}>Doctor</h3>
          {a.doctor ? (
            <>
              <InfoRow label="Name" value={`Dr. ${a.doctor.firstname} ${a.doctor.lastname}`} />
              <InfoRow label="Specialization" value={a.doctor.specialization ?? '—'} />
              <InfoRow label="Phone" value={a.doctor.phone ?? '—'} />
            </>
          ) : <p className="text-muted">No doctor info</p>}
        </div>

        {a.record && (
          <div className="card">
            <h3 style={{ fontWeight: 600, marginBottom: '1rem' }}>Medical Record</h3>
            <InfoRow label="Diagnosis" value={a.record.diagnosis_code} />
            <InfoRow label="Symptoms" value={a.record.symptoms} />
            <div style={{ marginTop: '0.75rem' }}>
              <div className="form-label" style={{ marginBottom: '0.25rem' }}>Clinical Notes</div>
              <p style={{ fontSize: '0.875rem', color: 'var(--text-secondary)' }}>{a.record.clinical_notes}</p>
            </div>
          </div>
        )}

        {a.invoices?.length > 0 && (
          <div className="card">
            <h3 style={{ fontWeight: 600, marginBottom: '1rem' }}>Invoices</h3>
            {a.invoices.map(inv => (
              <div key={inv.id} style={{ display: 'flex', justifyContent: 'space-between', padding: '0.5rem 0', borderBottom: '1px solid var(--border)' }}>
                <span style={{ fontWeight: 600 }}>${inv.amount}</span>
                <StatusBadge status={inv.status} />
              </div>
            ))}
          </div>
        )}
      </div>

      <ConfirmDialog
        open={confirmOpen}
        title="Cancel Appointment"
        message="Are you sure you want to cancel this appointment? This action cannot be undone."
        onConfirm={() => cancelMut.mutate()}
        onCancel={() => setConfirmOpen(false)}
        loading={cancelMut.isPending}
      />
    </div>
  );
}
