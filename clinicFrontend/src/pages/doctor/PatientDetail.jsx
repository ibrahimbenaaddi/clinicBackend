import { useParams, Link } from 'react-router-dom';
import { useQuery } from '@tanstack/react-query';
import { ArrowLeft, Clock, FileText, Pill } from 'lucide-react';
import { getDoctorPatient } from '../../api/patients';
import { LoadingSpinner, EmptyState, StatusBadge } from '../../components/shared';
import { PageHeader } from '../../components/layout/Layout';

export default function DoctorPatientDetail() {
  const { id } = useParams();

  const { data: res, isLoading } = useQuery({
    queryKey: ['doctor-patient-detail', id],
    queryFn: () => getDoctorPatient(id),
    enabled: !!id,
  });

  const patient = res?.data?.data;

  if (isLoading) return <div className="animate-fade"><LoadingSpinner fullPage /></div>;
  if (!patient) return <div className="animate-fade"><EmptyState title="Patient not found" /></div>;

  const appointments = patient.appointments || [];

  return (
    <div className="animate-fade">
      <Link to="/doctor/patients" className="btn btn-ghost btn-sm" style={{ marginBottom: '1rem', width: 'fit-content' }}>
        <ArrowLeft size={16} /> Back to Patients
      </Link>
      
      <PageHeader
        title={`${patient.firstname} ${patient.lastname}`}
        subtitle={`Patient History & Details`}
      />

      <div className="grid-2" style={{ marginBottom: '2rem' }}>
        <div className="card">
          <h3 style={{ fontSize: '1.1rem', marginBottom: '1rem' }}>Personal Information</h3>
          <div style={{ display: 'grid', gridTemplateColumns: '1fr 1fr', gap: '1rem' }}>
            <div><span style={{ color: 'var(--text-secondary)' }}>Email:</span> {patient.email}</div>
            <div><span style={{ color: 'var(--text-secondary)' }}>Phone:</span> {patient.phone || '—'}</div>
            <div><span style={{ color: 'var(--text-secondary)' }}>D.O.B:</span> {patient.date_birth ? new Date(patient.date_birth).toLocaleDateString() : '—'}</div>
            <div><span style={{ color: 'var(--text-secondary)' }}>Insurance:</span> {patient.insurance_info || '—'}</div>
            <div style={{ gridColumn: '1 / -1' }}><span style={{ color: 'var(--text-secondary)' }}>Address:</span> {patient.address || '—'}</div>
          </div>
        </div>
      </div>

      <h3 style={{ fontSize: '1.1rem', marginBottom: '1rem' }}>Appointment History</h3>
      {appointments.length === 0 ? (
        <EmptyState icon={<Clock size={48} />} title="No history found" />
      ) : (
        <div className="table-wrapper">
          <table>
            <thead>
              <tr>
                <th>Date</th>
                <th>Time</th>
                <th>Reason</th>
                <th>Status</th>
                <th>Records</th>
                <th>Prescriptions</th>
              </tr>
            </thead>
            <tbody>
              {appointments.map(a => (
                <tr key={a.id}>
                  <td>{new Date(a.start_time).toLocaleDateString()}</td>
                  <td>{new Date(a.start_time).toLocaleTimeString([], { hour: '2-digit', minute: '2-digit' })}</td>
                  <td style={{ maxWidth: 200, overflow: 'hidden', textOverflow: 'ellipsis', whiteSpace: 'nowrap' }}>{a.reason_for_visit}</td>
                  <td><StatusBadge status={a.status} /></td>
                  <td>
                    {a.record ? (
                      <div style={{ display: 'flex', alignItems: 'center', gap: '0.25rem', color: 'var(--accent)' }}>
                        <FileText size={14} /> {a.record.diagnosis_code}
                      </div>
                    ) : '—'}
                  </td>
                  <td>
                    {a.record?.prescriptions?.length > 0 ? (
                      <div style={{ display: 'flex', alignItems: 'center', gap: '0.25rem', color: 'var(--success)' }}>
                        <Pill size={14} /> {a.record.prescriptions.length} items
                      </div>
                    ) : '—'}
                  </td>
                </tr>
              ))}
            </tbody>
          </table>
        </div>
      )}
    </div>
  );
}
