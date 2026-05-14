import { useState } from 'react';
import { useQuery } from '@tanstack/react-query';
import { Link } from 'react-router-dom';
import { Search } from 'lucide-react';
import { getPatientDoctors } from '../../api/doctors';
import { LoadingSpinner, EmptyState } from '../../components/shared';
import { PageHeader } from '../../components/layout/Layout';

const SPECIALIZATIONS = ['all','cardiology','dermatology','neurology','pediatrics','orthopedics','ophthalmology'];
const SPEC_COLORS = { cardiology: '#f85149', dermatology: '#d29922', neurology: '#388bfd', pediatrics: '#2ea043', orthopedics: '#00b4d8', ophthalmology: '#bc8cff' };

export default function PatientDoctors() {
  const [search, setSearch] = useState('');
  const [spec, setSpec]     = useState('all');

  const { data: res, isLoading } = useQuery({ queryKey: ['patient-doctors'], queryFn: getPatientDoctors });
  const doctors = res?.data?.data ?? [];

  const filtered = doctors
    .filter(d => spec === 'all' || d.specialization === spec)
    .filter(d => !search || `${d.firstname} ${d.lastname}`.toLowerCase().includes(search.toLowerCase()));

  return (
    <div className="animate-fade">
      <PageHeader title="Doctors" subtitle="Browse available doctors and book appointments" />

      <div className="filter-bar">
        <div className="search-bar" style={{ flex: 1, maxWidth: 300 }}>
          <Search size={15} />
          <input placeholder="Search doctor..." value={search} onChange={e => setSearch(e.target.value)} />
        </div>
        {SPECIALIZATIONS.map(s => (
          <button key={s} className={`btn btn-sm ${spec === s ? 'btn-primary' : 'btn-secondary'}`} onClick={() => setSpec(s)}>
            {s === 'all' ? 'All' : s.charAt(0).toUpperCase() + s.slice(1)}
          </button>
        ))}
      </div>

      {isLoading ? <LoadingSpinner fullPage /> : filtered.length === 0 ? (
        <EmptyState title="No doctors found" />
      ) : (
        <div className="grid-3">
          {filtered.map(d => (
            <Link to="/patient/appointments" state={{ openBook: true, doctorId: d.id }} key={d.id} style={{ textDecoration: 'none' }}>
              <div className="card card-hover" style={{ height: '100%' }}>
                <div style={{ width: 56, height: 56, borderRadius: '50%', background: `${SPEC_COLORS[d.specialization] ?? 'var(--accent)'}22`, display: 'flex', alignItems: 'center', justifyContent: 'center', marginBottom: '1rem', fontSize: '1.25rem', fontWeight: 700, color: SPEC_COLORS[d.specialization] ?? 'var(--accent)' }}>
                  {d.firstname?.[0]}{d.lastname?.[0]}
                </div>
                <div style={{ fontWeight: 700, fontSize: '1rem' }}>Dr. {d.firstname} {d.lastname}</div>
                <div style={{ fontSize: '0.8rem', color: 'var(--text-secondary)', marginTop: '0.25rem', marginBottom: '0.75rem', textTransform: 'capitalize' }}>{d.specialization}</div>
                <span className="btn btn-primary btn-sm" style={{ width: 'fit-content' }}>Book Appointment</span>
              </div>
            </Link>
          ))}
        </div>
      )}
    </div>
  );
}
