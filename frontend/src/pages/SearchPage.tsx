import React from 'react';
import { SearchBox } from '../components/SearchBox/SearchBox';
import { ResultsBox } from '../components/ResultsBox/ResultsBox';
import styles from './SearchPage.module.scss';

export const SearchPage: React.FC = () => {
  return (
    <div className={styles.searchPage}>
      <div className={styles.searchColumn}>
        <SearchBox />
      </div>
      <div className={styles.resultsColumn}>
        <ResultsBox />
      </div>
    </div>
  );
};
