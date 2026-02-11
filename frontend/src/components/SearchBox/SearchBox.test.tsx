import React from 'react';
import { render, screen, fireEvent } from '@testing-library/react';
import { BrowserRouter } from 'react-router-dom';
import { SearchBox } from './SearchBox';
import { SearchProvider } from '../../context/SearchContext';

const renderWithProviders = (ui: React.ReactElement) =>
  render(
    <BrowserRouter future={{ v7_startTransition: true, v7_relativeSplatPath: true }}>
      <SearchProvider>{ui}</SearchProvider>
    </BrowserRouter>,
  );

describe('SearchBox', () => {
  test('renders the search form title', () => {
    renderWithProviders(<SearchBox />);
    expect(screen.getByText('What are you searching for?')).toBeInTheDocument();
  });

  test('renders People and Movies radio buttons', () => {
    renderWithProviders(<SearchBox />);
    expect(screen.getByLabelText('People')).toBeInTheDocument();
    expect(screen.getByLabelText('Movies')).toBeInTheDocument();
  });

  test('People radio is selected by default', () => {
    renderWithProviders(<SearchBox />);
    const peopleRadio = screen.getByLabelText('People') as HTMLInputElement;
    expect(peopleRadio.checked).toBe(true);
  });

  test('search button is disabled when input is empty', () => {
    renderWithProviders(<SearchBox />);
    const button = screen.getByRole('button', { name: /search/i });
    expect(button).toBeDisabled();
  });

  test('search button enables when text is entered', () => {
    renderWithProviders(<SearchBox />);
    const input = screen.getByPlaceholderText(/chewbacca/i);
    fireEvent.change(input, { target: { value: 'luke' } });
    const button = screen.getByRole('button', { name: /search/i });
    expect(button).not.toBeDisabled();
  });

  test('search button is disabled when only whitespace is entered', () => {
    renderWithProviders(<SearchBox />);
    const input = screen.getByPlaceholderText(/chewbacca/i);
    fireEvent.change(input, { target: { value: '   ' } });
    const button = screen.getByRole('button', { name: /search/i });
    expect(button).toBeDisabled();
  });

  test('placeholder changes when Movies radio is selected', () => {
    renderWithProviders(<SearchBox />);
    const moviesRadio = screen.getByLabelText('Movies');
    fireEvent.click(moviesRadio);
    expect(screen.getByPlaceholderText(/new hope/i)).toBeInTheDocument();
  });
});
