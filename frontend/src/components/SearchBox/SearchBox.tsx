import React from 'react';
import { useSearch } from '../../context/SearchContext';
import styles from './SearchBox.module.scss';

export const SearchBox: React.FC = () => {
  const { searchType, setSearchType, query, setQuery, isLoading, performSearch } = useSearch();

  const handleSubmit = (e: React.FormEvent) => {
    e.preventDefault();
    performSearch();
  };

  const isDisabled = !query.trim() || isLoading;

  return (
    <form className={styles.container} onSubmit={handleSubmit}>
      <h2 className={styles.title}>What are you searching for?</h2>

      <div className={styles.radioGroup}>
        <label className={styles.radioLabel}>
          <input
            type="radio"
            name="searchType"
            value="people"
            checked={searchType === 'people'}
            onChange={() => setSearchType('people')}
            disabled={isLoading}
          />
          <span>People</span>
        </label>
        <label className={styles.radioLabel}>
          <input
            type="radio"
            name="searchType"
            value="films"
            checked={searchType === 'films'}
            onChange={() => setSearchType('films')}
            disabled={isLoading}
          />
          <span>Movies</span>
        </label>
      </div>

      <input
        className={styles.input}
        type="text"
        placeholder={
          searchType === 'people' ? 'e.g. Chewbacca, Yoda, Boba Fett' : 'e.g. A New Hope, Empire'
        }
        value={query}
        onChange={(e) => setQuery(e.target.value)}
        disabled={isLoading}
      />

      <button className={styles.button} type="submit" disabled={isDisabled}>
        {isLoading ? 'SEARCHING...' : 'SEARCH'}
      </button>
    </form>
  );
};
