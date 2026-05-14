import { useQuery, useMutation, useQueryClient } from '@tanstack/react-query';
import { useForm } from 'react-hook-form';
import { useEffect, useState } from 'react';
import toast from 'react-hot-toast';
import { useAuth } from '../../context/AuthContext';
import { getDoctorProfile, updateDoctorProfile } from '../../api/doctors';
import { LoadingSpinner } from '../../components/shared';
import { PageHeader } from '../../components/layout/Layout';
import { Edit2, Save } from 'lucide-react';

const SPECIALIZATIONS = ['cardiology','dermatology','neurology','pediatrics','orthopedics','ophthalmology'];

export default function DoctorProfile() {
  const { user } = useAuth();
  const id = user?.id;
  const qc = useQueryClient();
  const [editing, setEditing] = useState(false);

  const { data: res, isLoading } = useQuery({
    queryKey: ['doctor-profile', id],
    queryFn: () => getDoctorProfile(id),
    enabled: !!id,
  });

  const profile = res?.data?.data;
  const { register, handleSubmit, reset } = useForm();

  useEffect(() => {
    if (profile) reset({ phone: profile.doctor?.phone, specialization: profile.doctor?.specialization, license_number: profile.doctor?.license_number });
  }, [profile, reset]);

  const updateMut = useMutation({
    mutationFn: (data) => updateDoctorProfile(id, data),
    onSuccess: () => { toast.success('Profile updated!'); qc.invalidateQueries(['doctor-profile', id]); setEditing(false); },
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
          {[
            { label: 'First Name', value: user?.firstname },
            { label: 'Last Name',  value: user?.lastname  },
            { label: 'Email',      value: user?.email     },
          ].map(row => (
            <div key={row.label} style={{ display: 'flex', justifyContent: 'space-between', padding: '0.5rem 0', borderBottom: '1px solid var(--border)' }}>
              <span style={{ fontSize: '0.8rem', color: 'var(--text-secondary)', fontWeight: 600, textTransform: 'uppercase', letterSpacing: '0.05em' }}>{row.label}</span>
              <span style={{ fontSize: '0.9rem' }}>{row.value}</span>
            </div>
          ))}
        </div>

        <div className="card">
          <h3 style={{ fontWeight: 600, marginBottom: '1rem' }}>Professional Info</h3>
          {editing ? (
            <form onSubmit={handleSubmit(d => updateMut.mutate(d))} style={{ display: 'flex', flexDirection: 'column', gap: '1rem' }}>
              <div className="form-group">
                <label className="form-label">Specialization</label>
                <select className="form-input form-select" {...register('specialization')}>
                  {SPECIALIZATIONS.map(s => <option key={s} value={s}>{s.charAt(0).toUpperCase() + s.slice(1)}</option>)}
                </select>
              </div>
              <div className="form-group">
                <label className="form-label">License Number</label>
                <input className="form-input" {...register('license_number')} />
              </div>
              <div className="form-group">
                <label className="form-label">Phone</label>
                <input className="form-input" {...register('phone')} />
              </div>
              <button className="btn btn-primary" disabled={updateMut.isPending}>
                {updateMut.isPending ? <span className="spinner spinner-sm" /> : <><Save size={16} />Save</>}
              </button>
            </form>
          ) : (
            [
              { label: 'Specialization', value: profile?.doctor?.specialization },
              { label: 'License',        value: profile?.doctor?.license_number },
              { label: 'Phone',          value: profile?.doctor?.phone },
            ].map(row => (
              <div key={row.label} style={{ display: 'flex', justifyContent: 'space-between', padding: '0.5rem 0', borderBottom: '1px solid var(--border)' }}>
                <span style={{ fontSize: '0.8rem', color: 'var(--text-secondary)', fontWeight: 600, textTransform: 'uppercase', letterSpacing: '0.05em' }}>{row.label}</span>
                <span style={{ fontSize: '0.9rem', textTransform: 'capitalize' }}>{row.value ?? '—'}</span>
              </div>
            ))
          )}
        </div>
      </div>
    </div>
  );
}
