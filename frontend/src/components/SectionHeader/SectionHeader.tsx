import React from 'react';
import styles from './SectionHeader.module.scss';

interface SectionHeaderProps {
  /** Heading text */
  children: React.ReactNode;
  /** HTML heading level (default: 3) */
  level?: 2 | 3;
}

export const SectionHeader: React.FC<SectionHeaderProps> = ({ children, level = 3 }) => {
  const Tag = `h${level}` as const;
  const className = level === 2 ? styles.headerLarge : styles.headerSmall;

  return (
    <>
      <Tag className={className}>{children}</Tag>
      <hr className={styles.divider} />
    </>
  );
};
