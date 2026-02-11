import React from 'react';
import { useSearch } from '../../context/SearchContext';
import styles from './Toast.module.scss';

export const ToastContainer: React.FC = () => {
  const { toasts, dismissToast } = useSearch();

  if (toasts.length === 0) return null;

  return (
    <div className={styles.container} role="alert" aria-live="polite">
      {toasts.map((toast) => (
        <div
          key={toast.id}
          className={`${styles.toast} ${styles[toast.type]}`}
          onClick={() => dismissToast(toast.id)}
        >
          <span className={styles.message}>{toast.message}</span>
          <button
            className={styles.closeButton}
            onClick={(e) => {
              e.stopPropagation();
              dismissToast(toast.id);
            }}
            aria-label="Dismiss"
          >
            &times;
          </button>
        </div>
      ))}
    </div>
  );
};
