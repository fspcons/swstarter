import React from 'react';
import { Link } from 'react-router-dom';
import styles from './LinksList.module.scss';

interface LinksListItem {
  uid: string;
  label: string;
}

interface LinksListProps {
  /** Items to render as links */
  items: LinksListItem[];
  /** Route type prefix, e.g. "people" or "films" */
  routeType: string;
  /** Message shown when the list is empty */
  emptyMessage: string;
  /** Whether to render items inline with comma separators (default: false = block list) */
  inline?: boolean;
}

export const LinksList: React.FC<LinksListProps> = ({
  items,
  routeType,
  emptyMessage,
  inline = false,
}) => {
  if (items.length === 0) {
    return <span className={styles.noLinks}>{emptyMessage}</span>;
  }

  return (
    <div className={`${styles.list} ${inline ? styles.listInline : ''}`}>
      {items.map((item, index) => (
        <React.Fragment key={item.uid}>
          <Link to={`/details/${routeType}/${item.uid}`} className={styles.link}>
            {item.label}
          </Link>
          {inline && index < items.length - 1 && <span className={styles.separator}>, </span>}
        </React.Fragment>
      ))}
    </div>
  );
};
