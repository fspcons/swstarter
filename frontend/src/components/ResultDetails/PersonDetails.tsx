import React from 'react';
import { useNavigate } from 'react-router-dom';
import { PersonDetails as PersonDetailsType } from '../../types';
import { getPersonDetails } from '../../services/api';
import { useDetailsLoader } from '../../hooks/useDetailsLoader';
import { Spinner } from '../Spinner/Spinner';
import { SectionHeader } from '../SectionHeader/SectionHeader';
import { LinksList } from '../LinksList/LinksList';
import styles from './ResultDetails.module.scss';

export const PersonDetailsView: React.FC = () => {
  const navigate = useNavigate();
  const { data: person, loading } = useDetailsLoader<PersonDetailsType>(
    getPersonDetails,
    'Failed to load person details.',
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

  if (!person) return null;

  return (
    <div className={styles.container}>
      <h1 className={styles.title}>{person.name}</h1>

      <div className={styles.columns}>
        <div className={styles.detailsColumn}>
          <SectionHeader>Details</SectionHeader>
          <div className={styles.detailText}>
            <p>Birth Year: {person.birth_year}</p>
            <p>Gender: {person.gender}</p>
            <p>Eye Color: {person.eye_color}</p>
            <p>Hair Color: {person.hair_color}</p>
            <p>Height: {person.height}</p>
            <p>Mass: {person.mass}</p>
          </div>
        </div>

        <div className={styles.linksColumn}>
          <SectionHeader>Movies</SectionHeader>
          <LinksList
            items={person.films.map((f) => ({ uid: f.uid, label: f.title }))}
            routeType="films"
            emptyMessage="No films linked."
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
