export function StatusBadge({ status }) {
  if (!status) return null;
  return <span className={`badge badge-${status.toLowerCase()}`}>{status.replace('_', ' ')}</span>;
}

export function LoadingSpinner({ fullPage = false, size = '' }) {
  if (fullPage) {
    return (
      <div className="loading-center" style={{ minHeight: '60vh' }}>
        <div className={`spinner ${size}`} />
      </div>
    );
  }
  return <div className={`spinner ${size}`} />;
}

export function EmptyState({ icon, title = 'No data found', description = '' }) {
  return (
    <div className="empty-state">
      {icon && <div style={{ marginBottom: '0.75rem', opacity: 0.4 }}>{icon}</div>}
      <p style={{ fontWeight: 600, fontSize: '1rem', color: 'var(--text-primary)', marginBottom: '0.25rem' }}>{title}</p>
      {description && <p>{description}</p>}
    </div>
  );
}

export function ConfirmDialog({ open, title, message, onConfirm, onCancel, loading }) {
  if (!open) return null;
  return (
    <div className="modal-overlay" onClick={onCancel}>
      <div className="modal" style={{ maxWidth: 400 }} onClick={e => e.stopPropagation()}>
        <div className="modal-header">
          <h3 className="modal-title">{title}</h3>
        </div>
        <p style={{ color: 'var(--text-secondary)', fontSize: '0.9rem' }}>{message}</p>
        <div className="modal-footer">
          <button className="btn btn-secondary" onClick={onCancel}>Cancel</button>
          <button className="btn btn-danger" onClick={onConfirm} disabled={loading}>
            {loading ? <span className="spinner spinner-sm" /> : 'Confirm'}
          </button>
        </div>
      </div>
    </div>
  );
}

export function Modal({ open, title, onClose, children }) {
  if (!open) return null;
  return (
    <div className="modal-overlay" onClick={onClose}>
      <div className="modal" onClick={e => e.stopPropagation()}>
        <div className="modal-header">
          <h3 className="modal-title">{title}</h3>
          <button className="btn btn-ghost btn-icon" onClick={onClose}>✕</button>
        </div>
        <div className="modal-body">{children}</div>
      </div>
    </div>
  );
}
