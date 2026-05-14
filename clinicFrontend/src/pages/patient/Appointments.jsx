import { useState } from 'react';
import { useQuery, useMutation, useQueryClient } from '@tanstack/react-query';
import { Link, useLocation } from 'react-router-dom';
import { Plus, Search, Calendar } from 'lucide-react';
import toast from 'react-hot-toast';
import { useAuth } from '../../context/AuthContext';
import { getPatientAppointments, bookAppointment, getAvailableSlots } from '../../api/appointments';
import { getPatientDoctors } from '../../api/doctors';
import { StatusBadge, LoadingSpinner, EmptyState, Modal } from '../../components/shared';
import { PageHeader } from '../../components/layout/Layout';
import { useForm } from 'react-hook-form';

export default function PatientAppointments() {
  const { user } = useAuth();
  const id = user?.id;
  const qc = useQueryClient();
  const location = useLocation();
  const [filter, setFilter] = useState('all');
  const [search, setSearch] = useState('');
  const [bookOpen, setBookOpen] = useState(location.state?.openBook || false);
  const [selectedDoctor, setSelectedDoctor] = useState(location.state?.doctorId || '');

  const { data: res, isLoading } = useQuery({
    queryKey: ['patient-appointments', id],
    queryFn: () => getPatientAppointments(id),
    enabled: !!id,
  });
  const { data: docRes } = useQuery({
    queryKey: ['patient-doctors'],
    queryFn: getPatientDoctors,
    enabled: bookOpen,
  });
  const { data: slotsRes } = useQuery({
    queryKey: ['available-slots', selectedDoctor],
    queryFn: () => getAvailableSlots(selectedDoctor),
    enabled: !!selectedDoctor,
  });

  const { register, handleSubmit, reset } = useForm();

  const bookMut = useMutation({
    mutationFn: bookAppointment,
    onSuccess: () => {
      toast.success('Appointment booked!');
      qc.invalidateQueries(['patient-appointments', id]);
      setBookOpen(false);
      reset();
      setSelectedDoctor('');
    },
    onError: (e) => toast.error(e.response?.data?.message || 'Booking failed'),
  });

  const appointments = res?.data?.data ?? [];
  const doctors = docRes?.data?.data ?? [];
  const slots   = slotsRes?.data?.data ?? [];

  const filtered = appointments
    .filter(a => filter === 'all' || a.status === filter)
    .filter(a => !search || `${a.doctor?.firstname} ${a.doctor?.lastname}`.toLowerCase().includes(search.toLowerCase()));

  const STATUS_FILTERS = ['all','pending','confirmed','completed','cancelled','no_show'];

  return (
    <div className="animate-fade">
      <PageHeader
        title="My Appointments"
        subtitle="View and manage your appointments"
        action={<button className="btn btn-primary" onClick={() => setBookOpen(true)}><Plus size={16} />Book Appointment</button>}
      />

      <div className="filter-bar">
        <div className="search-bar" style={{ flex: 1, maxWidth: 320 }}>
          <Search size={15} />
          <input placeholder="Search by doctor..." value={search} onChange={e => setSearch(e.target.value)} />
        </div>
        {STATUS_FILTERS.map(s => (
          <button key={s} className={`btn btn-sm ${filter === s ? 'btn-primary' : 'btn-secondary'}`} onClick={() => setFilter(s)}>
            {s === 'all' ? 'All' : s.replace('_',' ')}
          </button>
        ))}
      </div>

      {isLoading ? <LoadingSpinner fullPage /> : filtered.length === 0 ? (
        <EmptyState icon={<Calendar size={48} />} title="No appointments found" description="Book your first appointment" />
      ) : (
        <div className="table-wrapper">
          <table>
            <thead>
              <tr>
                <th>Doctor</th><th>Date</th><th>Time</th><th>Reason</th><th>Status</th><th>Action</th>
              </tr>
            </thead>
            <tbody>
              {filtered.map(a => (
                <tr key={a.id}>
                  <td>Dr. {a.doctor?.firstname} {a.doctor?.lastname}</td>
                  <td>{new Date(a.start_time).toLocaleDateString()}</td>
                  <td>{new Date(a.start_time).toLocaleTimeString([], { hour: '2-digit', minute: '2-digit' })}</td>
                  <td style={{ maxWidth: 200, overflow: 'hidden', textOverflow: 'ellipsis', whiteSpace: 'nowrap' }}>{a.reason_for_visit}</td>
                  <td><StatusBadge status={a.status} /></td>
                  <td><Link to={`/patient/appointments/${a.id}`} className="btn btn-sm btn-secondary">View</Link></td>
                </tr>
              ))}
            </tbody>
          </table>
        </div>
      )}

      <Modal open={bookOpen} title="Book Appointment" onClose={() => { setBookOpen(false); reset(); setSelectedDoctor(''); }}>
        <form onSubmit={handleSubmit(d => bookMut.mutate(d))} style={{ display: 'flex', flexDirection: 'column', gap: '1rem' }}>
          <div className="form-group">
            <label className="form-label">Select Doctor</label>
            <select className="form-input form-select" {...register('doctor_id', { required: true })}
              onChange={e => { setSelectedDoctor(e.target.value); }}>
              <option value="">Choose a doctor</option>
              {doctors.map(d => <option key={d.id} value={d.id}>Dr. {d.firstname} {d.lastname} — {d.specialization}</option>)}
            </select>
          </div>
          <div className="form-group">
            <label className="form-label">Available Slot</label>
            <select className="form-input form-select" {...register('slot_id', { required: true })} disabled={!selectedDoctor}>
              <option value="">Select a slot</option>
              {slots.filter(s => s.is_available).map(s => (
                <option key={s.id} value={s.id}>{s.start_time} – {s.end_time} ({s.available_spots} spots left)</option>
              ))}
            </select>
          </div>
          <div className="form-group">
            <label className="form-label">Reason for Visit</label>
            <textarea className="form-input" placeholder="Describe your symptoms..." {...register('reason_for_visit', { required: true })} />
          </div>
          <input type="hidden" value={id} {...register('patient_id')} />
          <div className="modal-footer" style={{ margin: 0 }}>
            <button type="button" className="btn btn-secondary" onClick={() => setBookOpen(false)}>Cancel</button>
            <button type="submit" className="btn btn-primary" disabled={bookMut.isPending}>
              {bookMut.isPending ? <span className="spinner spinner-sm" /> : 'Book'}
            </button>
          </div>
        </form>
      </Modal>
    </div>
  );
}
