import React from 'react';
import { useNavigate } from 'react-router-dom';
import { FilmDetails as FilmDetailsType } from '../../types';
import { getFilmDetails } from '../../services/api';
import { useDetailsLoader } from '../../hooks/useDetailsLoader';
import { Spinner } from '../Spinner/Spinner';
import { SectionHeader } from '../SectionHeader/SectionHeader';
import { LinksList } from '../LinksList/LinksList';
import styles from './ResultDetails.module.scss';

export const FilmDetailsView: React.FC = () => {
  const navigate = useNavigate();
  const { data: film, loading } = useDetailsLoader<FilmDetailsType>(
    getFilmDetails,
    'Failed to load film details.',
  );

  if (loading) {
    return (
      <div className={styles.container}>
        <div className={styles.loadingState}>
          <Spinner label="Loading details..." size={28} />
        </div>
      </div>
    );
  }

  if (!film) return null;

  return (
    <div className={styles.container}>
      <h1 className={styles.title}>{film.title}</h1>

      <div className={styles.columns}>
        <div className={styles.detailsColumn}>
          <SectionHeader>Opening Crawl</SectionHeader>
          <div className={styles.crawlText}>{film.opening_crawl}</div>
        </div>

        <div className={styles.linksColumn}>
          <SectionHeader>Characters</SectionHeader>
          <LinksList
            items={film.characters.map((c) => ({ uid: c.uid, label: c.name }))}
            routeType="people"
            emptyMessage="No characters linked."
            inline
          />
        </div>
      </div>

      <button className={styles.backButton} onClick={() => navigate('/')}>
        BACK TO SEARCH
      </button>
    </div>
  );
};
