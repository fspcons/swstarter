import React from 'react';
import { useParams } from 'react-router-dom';
import { PersonDetailsView } from '../components/ResultDetails/PersonDetails';
import { FilmDetailsView } from '../components/ResultDetails/FilmDetails';

export const DetailsPage: React.FC = () => {
  const { type } = useParams<{ type: string }>();

  if (type === 'people') {
    return <PersonDetailsView />;
  }

  if (type === 'films') {
    return <FilmDetailsView />;
  }

  return <div>Unknown resource type.</div>;
};
