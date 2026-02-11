import React from 'react';
import styles from './Spinner.module.scss';

interface SpinnerProps {
  /** Text shown below the spinner */
  label?: string;
  /** Diameter in pixels (default: 24) */
  size?: number;
}

export const Spinner: React.FC<SpinnerProps> = ({ label, size = 24 }) => {
  const spinnerStyle = {
    width: size,
    height: size,
    borderWidth: Math.max(2, Math.round(size / 8)),
  };

  return (
    <div className={styles.wrapper} role="status" aria-label={label || 'Loading'}>
      <div className={styles.spinner} style={spinnerStyle} />
      {label && <span className={styles.label}>{label}</span>}
    </div>
  );
};
