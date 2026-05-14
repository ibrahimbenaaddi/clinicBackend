import { useState } from 'react';
import { useQuery } from '@tanstack/react-query';
import { Search } from 'lucide-react';
import { useAuth } from '../../context/AuthContext';
import { getDoctorPatients } from '../../api/patients';
import { LoadingSpinner, EmptyState } from '../../components/shared';
import { PageHeader } from '../../components/layout/Layout';
import { Link } from 'react-router-dom';
import { Users } from 'lucide-react';

export default function DoctorPatients() {
  const { user } = useAuth();
  const [search, setSearch] = useState('');

  const { data: res, isLoading } = useQuery({
    queryKey: ['doctor-patients'],
    queryFn: getDoctorPatients,
    enabled: !!user?.id,
  });

  const patients = (res?.data?.data ?? []).filter(p =>
    !search || `${p.firstname} ${p.lastname} ${p.email}`.toLowerCase().includes(search.toLowerCase())
  );

  return (
    <div className="animate-fade">
      <PageHeader title="My Patients" subtitle="Patients who have had appointments with you" />
      <div className="filter-bar">
        <div className="search-bar" style={{ maxWidth: 320 }}>
          <Search size={15} />
          <input placeholder="Search patient..." value={search} onChange={e => setSearch(e.target.value)} />
        </div>
      </div>
      {isLoading ? <LoadingSpinner fullPage /> : patients.length === 0 ? (
        <EmptyState icon={<Users size={48} />} title="No patients found" />
      ) : (
        <div className="table-wrapper">
          <table>
            <thead><tr><th>Name</th><th>Email</th><th>Phone</th><th>D.O.B</th><th>Action</th></tr></thead>
            <tbody>
              {patients.map(p => (
                <tr key={p.id}>
                  <td style={{ fontWeight: 500 }}>{p.firstname} {p.lastname}</td>
                  <td style={{ color: 'var(--text-secondary)' }}>{p.email}</td>
                  <td>{p.patient?.phone ?? '—'}</td>
                  <td>{p.patient?.date_birth ? new Date(p.patient.date_birth).toLocaleDateString() : '—'}</td>
                  <td><Link to={`/doctor/patients/${p.id}`} className="btn btn-sm btn-secondary">View History</Link></td>
                </tr>
              ))}
            </tbody>
          </table>
        </div>
      )}
    </div>
  );
}
