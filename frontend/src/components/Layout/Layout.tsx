import React from 'react';
import styles from './Layout.module.scss';

interface LayoutProps {
  children: React.ReactNode;
}

export const Layout: React.FC<LayoutProps> = ({ children }) => {
  return (
    <div className={styles.layout}>
      <header className={styles.header}>
        <h1 className={styles.logo}>SWStarter</h1>
      </header>
      <main className={styles.main}>{children}</main>
    </div>
  );
};
