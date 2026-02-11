import React from 'react';
import { render, screen } from '@testing-library/react';
import { BrowserRouter } from 'react-router-dom';
import { ResultsBox } from './ResultsBox';
import { SearchProvider } from '../../context/SearchContext';

const renderWithProviders = (ui: React.ReactElement) =>
  render(
    <BrowserRouter future={{ v7_startTransition: true, v7_relativeSplatPath: true }}>
      <SearchProvider>{ui}</SearchProvider>
    </BrowserRouter>,
  );

describe('ResultsBox', () => {
  test('renders the Results header', () => {
    renderWithProviders(<ResultsBox />);
    expect(screen.getByText('Results')).toBeInTheDocument();
  });

  test('shows zero matches message when no results', () => {
    renderWithProviders(<ResultsBox />);
    expect(screen.getByText('There are zero matches.')).toBeInTheDocument();
    expect(screen.getByText('Use the form to search for People or Movies.')).toBeInTheDocument();
  });
});
