import { useQuery, useMutation, useQueryClient } from '@tanstack/react-query';
import { useForm } from 'react-hook-form';
import { useEffect, useState } from 'react';
import toast from 'react-hot-toast';
import { useAuth } from '../../context/AuthContext';
import { getPatientProfile, updatePatientProfile } from '../../api/patients';
import { LoadingSpinner } from '../../components/shared';
import { PageHeader } from '../../components/layout/Layout';
import { Edit2, Save } from 'lucide-react';

export default function PatientProfile() {
  const { user } = useAuth();
  const id = user?.id;
  const qc = useQueryClient();
  const [editing, setEditing] = useState(false);

  const { data: res, isLoading } = useQuery({
    queryKey: ['patient-profile', id],
    queryFn: () => getPatientProfile(id),
    enabled: !!id,
  });

  const profile = res?.data?.data;
  const { register, handleSubmit, reset } = useForm();

  useEffect(() => {
    if (profile) reset({ phone: profile.phone, address: profile.address, insurance_info: profile.insurance_info });
  }, [profile, reset]);

  const updateMut = useMutation({
    mutationFn: (data) => updatePatientProfile(id, data),
    onSuccess: () => { toast.success('Profile updated!'); qc.invalidateQueries(['patient-profile', id]); setEditing(false); },
    onError: () => toast.error('Update failed'),
  });

  if (isLoading) return <LoadingSpinner fullPage />;

  return (
    <div className="animate-fade">
      <PageHeader
        title="My Profile"
        action={
          !editing
            ? <button className="btn btn-secondary" onClick={() => setEditing(true)}><Edit2 size={16} />Edit Profile</button>
            : <button className="btn btn-ghost btn-sm" onClick={() => { setEditing(false); reset(); }}>Cancel</button>
        }
      />

      <div className="grid-2" style={{ maxWidth: 900 }}>
        <div className="card">
          <h3 style={{ fontWeight: 600, marginBottom: '1rem' }}>Personal Info</h3>
          <div style={{ display: 'flex', flexDirection: 'column', gap: '0.75rem' }}>
            {[
              { label: 'First Name', value: user?.firstname },
              { label: 'Last Name',  value: user?.lastname  },
              { label: 'Email',      value: user?.email     },
              { label: 'Date of Birth', value: profile?.date_birth ? new Date(profile.date_birth).toLocaleDateString() : '—' },
            ].map(row => (
              <div key={row.label} style={{ display: 'flex', justifyContent: 'space-between', padding: '0.5rem 0', borderBottom: '1px solid var(--border)' }}>
                <span style={{ fontSize: '0.8rem', color: 'var(--text-secondary)', fontWeight: 600, textTransform: 'uppercase', letterSpacing: '0.05em' }}>{row.label}</span>
                <span style={{ fontSize: '0.9rem' }}>{row.value}</span>
              </div>
            ))}
          </div>
        </div>

        <div className="card">
          <h3 style={{ fontWeight: 600, marginBottom: '1rem' }}>Contact & Insurance</h3>
          {editing ? (
            <form onSubmit={handleSubmit(d => updateMut.mutate(d))} style={{ display: 'flex', flexDirection: 'column', gap: '1rem' }}>
              <div className="form-group">
                <label className="form-label">Phone</label>
                <input className="form-input" {...register('phone')} />
              </div>
              <div className="form-group">
                <label className="form-label">Address</label>
                <input className="form-input" {...register('address')} />
              </div>
              <div className="form-group">
                <label className="form-label">Insurance Info</label>
                <input className="form-input" {...register('insurance_info')} />
              </div>
              <button className="btn btn-primary" disabled={updateMut.isPending}>
                {updateMut.isPending ? <span className="spinner spinner-sm" /> : <><Save size={16} />Save Changes</>}
              </button>
            </form>
          ) : (
            <div style={{ display: 'flex', flexDirection: 'column', gap: '0.75rem' }}>
              {[
                { label: 'Phone', value: profile?.phone },
                { label: 'Address', value: profile?.address },
                { label: 'Insurance', value: profile?.insurance_info },
              ].map(row => (
                <div key={row.label} style={{ display: 'flex', justifyContent: 'space-between', padding: '0.5rem 0', borderBottom: '1px solid var(--border)' }}>
                  <span style={{ fontSize: '0.8rem', color: 'var(--text-secondary)', fontWeight: 600, textTransform: 'uppercase', letterSpacing: '0.05em' }}>{row.label}</span>
                  <span style={{ fontSize: '0.9rem' }}>{row.value ?? '—'}</span>
                </div>
              ))}
            </div>
          )}
        </div>
      </div>
    </div>
  );
}
