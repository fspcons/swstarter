import React from 'react';
import { useNavigate } from 'react-router-dom';
import { useSearch } from '../../context/SearchContext';
import { SearchResult, isPersonSummary } from '../../types';
import { Spinner } from '../Spinner/Spinner';
import { SectionHeader } from '../SectionHeader/SectionHeader';
import styles from './ResultsBox.module.scss';

export const ResultsBox: React.FC = () => {
  const { results, isLoading, searchType, pagination, performSearch } = useSearch();
  const navigate = useNavigate();

  const handleSeeDetails = (uid: string) => {
    navigate(`/details/${searchType}/${uid}`);
  };

  const getDisplayName = (item: SearchResult): string => {
    return isPersonSummary(item) ? item.name : item.title;
  };

  return (
    <div className={styles.container}>
      <SectionHeader level={2}>Results</SectionHeader>

      {isLoading && (
        <div className={styles.emptyState}>
          <Spinner label="Searching..." size={24} />
        </div>
      )}

      {!isLoading && results.length === 0 && (
        <div className={styles.emptyState}>
          <p>There are zero matches.</p>
          <p>Use the form to search for People or Movies.</p>
        </div>
      )}

      {!isLoading && results.length > 0 && (
        <>
          <div className={styles.resultsList}>
            {results.map((item) => (
              <div key={item.uid} className={styles.resultRow}>
                <span className={styles.resultName}>{getDisplayName(item)}</span>
                <button className={styles.detailsButton} onClick={() => handleSeeDetails(item.uid)}>
                  SEE DETAILS
                </button>
              </div>
            ))}
          </div>

          {pagination && pagination.total_pages > 1 && (
            <div className={styles.pagination}>
              {Array.from({ length: pagination.total_pages }, (_, i) => (
                <button
                  key={i + 1}
                  className={`${styles.pageButton} ${
                    pagination.page === i + 1 ? styles.pageButtonActive : ''
                  }`}
                  onClick={() => performSearch(i + 1)}
                >
                  {i + 1}
                </button>
              ))}
            </div>
          )}
        </>
      )}
    </div>
  );
};
