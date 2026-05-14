import { useState } from 'react';
import { useForm } from 'react-hook-form';
import { Link, useNavigate } from 'react-router-dom';
import { Eye, EyeOff } from 'lucide-react';
import toast from 'react-hot-toast';
import { useAuth } from '../../context/AuthContext';
import { loginPatient, loginDoctor, loginAdmin } from '../../api/auth';

const ROLES = ['patient', 'doctor', 'admin'];

export default function Login() {
  const [role, setRole]       = useState('patient');
  const [showPw, setShowPw]   = useState(false);
  const [loading, setLoading] = useState(false);
  const { login }             = useAuth();
  const navigate              = useNavigate();

  const { register, handleSubmit, formState: { errors } } = useForm();

  const onSubmit = async (data) => {
    setLoading(true);
    try {
      let res;
      if (role === 'patient') res = await loginPatient(data);
      else if (role === 'doctor') res = await loginDoctor(data);
      else res = await loginAdmin(data);

      const { token, user: userData } = res.data.data;
      // inject role into user object
      login({ ...userData, role }, token);
      toast.success(`Welcome back, ${userData.firstname}!`);
      navigate(`/${role}/dashboard`);
    } catch (err) {
      const msg = err.response?.data?.message || 'Invalid credentials';
      toast.error(msg);
    } finally {
      setLoading(false);
    }
  };

  return (
    <div className="auth-page">
      <div className="auth-card">
        <div className="auth-logo">
          <div className="auth-logo-icon">C</div>
          <div>
            <div style={{ fontWeight: 700, fontSize: '1.1rem' }}>ClinicApp</div>
            <div style={{ fontSize: '0.72rem', color: 'var(--text-secondary)' }}>Healthcare Management</div>
          </div>
        </div>

        <div className="role-tabs">
          {ROLES.map(r => (
            <button
              key={r}
              className={`role-tab${role === r ? ' active' : ''}`}
              onClick={() => setRole(r)}
              type="button"
            >
              {r.charAt(0).toUpperCase() + r.slice(1)}
            </button>
          ))}
        </div>

        <h2 className="auth-title">Sign in</h2>
        <p className="auth-subtitle">Enter your credentials to access the {role} portal</p>

        <form className="auth-form" onSubmit={handleSubmit(onSubmit)}>
          <div className="form-group">
            <label className="form-label">Email</label>
            <input
              id="login-email"
              className={`form-input${errors.email ? ' error' : ''}`}
              type="email"
              placeholder="name@example.com"
              {...register('email', { required: 'Email is required' })}
            />
            {errors.email && <span className="form-error">{errors.email.message}</span>}
          </div>

          <div className="form-group">
            <label className="form-label">Password</label>
            <div style={{ position: 'relative' }}>
              <input
                id="login-password"
                className={`form-input${errors.password ? ' error' : ''}`}
                type={showPw ? 'text' : 'password'}
                placeholder="••••••••"
                style={{ paddingRight: '2.5rem' }}
                {...register('password', { required: 'Password is required' })}
              />
              <button
                type="button"
                onClick={() => setShowPw(v => !v)}
                style={{ position: 'absolute', right: '0.75rem', top: '50%', transform: 'translateY(-50%)', background: 'none', border: 'none', color: 'var(--text-muted)', cursor: 'pointer' }}
              >
                {showPw ? <EyeOff size={16} /> : <Eye size={16} />}
              </button>
            </div>
            {errors.password && <span className="form-error">{errors.password.message}</span>}
          </div>

          <button id="login-submit" className="btn btn-primary btn-block btn-lg" disabled={loading}>
            {loading ? <span className="spinner spinner-sm" /> : 'Sign In'}
          </button>
        </form>

        {role !== 'admin' && (
          <p className="auth-link">
            Don&apos;t have an account?{' '}
            <Link to={`/register/${role}`}>Register as {role}</Link>
          </p>
        )}
      </div>
    </div>
  );
}
