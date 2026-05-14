import { useState } from 'react';
import { useForm } from 'react-hook-form';
import { Link, useNavigate } from 'react-router-dom';
import toast from 'react-hot-toast';
import { useAuth } from '../../context/AuthContext';
import { registerDoctor } from '../../api/auth';

const SPECIALIZATIONS = ['cardiology','dermatology','neurology','pediatrics','orthopedics','ophthalmology'];

export default function RegisterDoctor() {
  const [loading, setLoading] = useState(false);
  const { login } = useAuth();
  const navigate  = useNavigate();
  const { register, handleSubmit, watch, formState: { errors } } = useForm();
  const password = watch("password");

  const onSubmit = async (data) => {
    setLoading(true);
    try {
      const res = await registerDoctor(data);
      const { token, user: userData } = res.data.data;
      login({ ...userData, role: 'doctor' }, token);
      toast.success('Doctor account created!');
      navigate('/doctor/dashboard');
    } catch (err) {
      toast.error(err.response?.data?.message || 'Registration failed');
    } finally {
      setLoading(false);
    }
  };

  return (
    <div className="auth-page">
      <div className="auth-card" style={{ maxWidth: 500 }}>
        <div className="auth-logo">
          <div className="auth-logo-icon">C</div>
          <div>
            <div style={{ fontWeight: 700, fontSize: '1.1rem' }}>ClinicApp</div>
            <div style={{ fontSize: '0.72rem', color: 'var(--text-secondary)' }}>Doctor Registration</div>
          </div>
        </div>
        <h2 className="auth-title">Create Doctor Account</h2>
        <p className="auth-subtitle">Register your medical practitioner account</p>

        <form className="auth-form" onSubmit={handleSubmit(onSubmit)}>
          <div className="grid-2">
            <div className="form-group">
              <label className="form-label">First Name</label>
              <input className={`form-input${errors.firstname ? ' error' : ''}`} placeholder="Jane"
                {...register('firstname', { required: 'Required' })} />
              {errors.firstname && <span className="form-error">{errors.firstname.message}</span>}
            </div>
            <div className="form-group">
              <label className="form-label">Last Name</label>
              <input className={`form-input${errors.lastname ? ' error' : ''}`} placeholder="Smith"
                {...register('lastname', { required: 'Required' })} />
              {errors.lastname && <span className="form-error">{errors.lastname.message}</span>}
            </div>
          </div>

          <div className="form-group">
            <label className="form-label">Email</label>
            <input className={`form-input${errors.email ? ' error' : ''}`} type="email"
              {...register('email', { required: 'Required' })} />
            {errors.email && <span className="form-error">{errors.email.message}</span>}
          </div>

          <div className="form-group">
            <label className="form-label">Password</label>
            <input className={`form-input${errors.password ? ' error' : ''}`} type="password"
              {...register('password', { required: 'Required', minLength: { value: 8, message: 'Min 8 chars' } })} />
            {errors.password && <span className="form-error">{errors.password.message}</span>}
          </div>

          <div className="form-group">
            <label className="form-label">Confirm Password</label>
            <input className={`form-input${errors.password_confirmation ? ' error' : ''}`} type="password" placeholder="Confirm password"
              {...register('password_confirmation', { 
                required: 'Required',
                validate: value => value === password || "The passwords do not match"
              })} />
            {errors.password_confirmation && <span className="form-error">{errors.password_confirmation.message}</span>}
          </div>

          <div className="form-group">
            <label className="form-label">Specialization</label>
            <select className={`form-input form-select${errors.specialization ? ' error' : ''}`}
              {...register('specialization', { required: 'Required' })}>
              <option value="">Select specialization</option>
              {SPECIALIZATIONS.map(s => (
                <option key={s} value={s}>{s.charAt(0).toUpperCase() + s.slice(1)}</option>
              ))}
            </select>
            {errors.specialization && <span className="form-error">{errors.specialization.message}</span>}
          </div>

          <div className="form-group">
            <label className="form-label">License Number</label>
            <input className={`form-input${errors.license_number ? ' error' : ''}`} placeholder="LIC-123456"
              {...register('license_number', { required: 'Required' })} />
            {errors.license_number && <span className="form-error">{errors.license_number.message}</span>}
          </div>

          <div className="form-group">
            <label className="form-label">Phone</label>
            <input className={`form-input${errors.phone ? ' error' : ''}`} placeholder="+1 234 567 8900"
              {...register('phone', { required: 'Required' })} />
            {errors.phone && <span className="form-error">{errors.phone.message}</span>}
          </div>

          <button className="btn btn-primary btn-block btn-lg" disabled={loading}>
            {loading ? <span className="spinner spinner-sm" /> : 'Create Account'}
          </button>
        </form>
        <p className="auth-link">Already have an account? <Link to="/login">Sign in</Link></p>
      </div>
    </div>
  );
}
