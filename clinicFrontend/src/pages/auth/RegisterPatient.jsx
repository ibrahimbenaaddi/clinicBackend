import { useState } from 'react';
import { useForm } from 'react-hook-form';
import { Link, useNavigate } from 'react-router-dom';
import toast from 'react-hot-toast';
import { useAuth } from '../../context/AuthContext';
import { registerPatient } from '../../api/auth';

export default function RegisterPatient() {
  const [loading, setLoading] = useState(false);
  const { login } = useAuth();
  const navigate  = useNavigate();
  const { register, handleSubmit, watch, formState: { errors } } = useForm();
  const password = watch("password");

  const onSubmit = async (data) => {
    setLoading(true);
    try {
      const res = await registerPatient(data);
      const { token, user: userData } = res.data.data;
      login({ ...userData, role: 'patient' }, token);
      toast.success('Account created! Welcome!');
      navigate('/patient/dashboard');
    } catch (err) {
      const msg = err.response?.data?.message || 'Registration failed';
      toast.error(msg);
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
            <div style={{ fontSize: '0.72rem', color: 'var(--text-secondary)' }}>Patient Registration</div>
          </div>
        </div>
        <h2 className="auth-title">Create Patient Account</h2>
        <p className="auth-subtitle">Fill in your details to get started</p>

        <form className="auth-form" onSubmit={handleSubmit(onSubmit)}>
          <div className="grid-2">
            <div className="form-group">
              <label className="form-label">First Name</label>
              <input className={`form-input${errors.firstname ? ' error' : ''}`} placeholder="John"
                {...register('firstname', { required: 'Required' })} />
              {errors.firstname && <span className="form-error">{errors.firstname.message}</span>}
            </div>
            <div className="form-group">
              <label className="form-label">Last Name</label>
              <input className={`form-input${errors.lastname ? ' error' : ''}`} placeholder="Doe"
                {...register('lastname', { required: 'Required' })} />
              {errors.lastname && <span className="form-error">{errors.lastname.message}</span>}
            </div>
          </div>

          <div className="form-group">
            <label className="form-label">Email</label>
            <input className={`form-input${errors.email ? ' error' : ''}`} type="email" placeholder="name@example.com"
              {...register('email', { required: 'Required' })} />
            {errors.email && <span className="form-error">{errors.email.message}</span>}
          </div>

          <div className="form-group">
            <label className="form-label">Password</label>
            <input className={`form-input${errors.password ? ' error' : ''}`} type="password" placeholder="Min 8 characters"
              {...register('password', { required: 'Required', minLength: { value: 8, message: 'Min 8 chars' } })} />
            {errors.password && <span className="form-error">{errors.password.message}</span>}
          </div>

          <div className="form-group">
            <label className="form-label">Confirm Password</label>
            <input className={`form-input${errors.password_confirmation ? ' error' : ''}`} type="password" placeholder="Confirm Password"
              {...register('password_confirmation', { 
                required: 'Required',
                validate: value => value === password || "The passwords do not match"
              })} />
            {errors.password_confirmation && <span className="form-error">{errors.password_confirmation.message}</span>}
          </div>

          <div className="form-group">
            <label className="form-label">Date of Birth</label>
            <input className={`form-input${errors.date_birth ? ' error' : ''}`} type="date"
              {...register('date_birth', { required: 'Required' })} />
            {errors.date_birth && <span className="form-error">{errors.date_birth.message}</span>}
          </div>

          <div className="form-group">
            <label className="form-label">Phone</label>
            <input className={`form-input${errors.phone ? ' error' : ''}`} placeholder="+1 234 567 8900"
              {...register('phone', { required: 'Required' })} />
            {errors.phone && <span className="form-error">{errors.phone.message}</span>}
          </div>

          <div className="form-group">
            <label className="form-label">Address</label>
            <input className={`form-input${errors.address ? ' error' : ''}`} placeholder="123 Main St, City"
              {...register('address', { required: 'Required' })} />
            {errors.address && <span className="form-error">{errors.address.message}</span>}
          </div>

          <div className="form-group">
            <label className="form-label">Insurance Info</label>
            <input className={`form-input${errors.insurance_info ? ' error' : ''}`} placeholder="Policy number or provider"
              {...register('insurance_info', { required: 'Required' })} />
            {errors.insurance_info && <span className="form-error">{errors.insurance_info.message}</span>}
          </div>

          <button className="btn btn-primary btn-block btn-lg" disabled={loading}>
            {loading ? <span className="spinner spinner-sm" /> : 'Create Account'}
          </button>
        </form>

        <p className="auth-link">
          Already have an account? <Link to="/login">Sign in</Link>
        </p>
      </div>
    </div>
  );
}
