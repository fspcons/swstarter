import React, { createContext, useContext, useState, useCallback } from 'react';
import { SearchType, SearchResult, Pagination, ToastMessage } from '../types';
import { searchApi } from '../services/api';
import { extractErrorMessage } from '../utils/extractErrorMessage';

interface SearchContextValue {
  searchType: SearchType;
  setSearchType: (type: SearchType) => void;
  query: string;
  setQuery: (q: string) => void;
  results: SearchResult[];
  pagination: Pagination | null;
  isLoading: boolean;
  toasts: ToastMessage[];
  performSearch: (page?: number) => Promise<void>;
  addToast: (message: string, type?: 'error' | 'info') => void;
  dismissToast: (id: string) => void;
}

const SearchContext = createContext<SearchContextValue | undefined>(undefined);

export const SearchProvider: React.FC<{ children: React.ReactNode }> = ({ children }) => {
  const [searchType, setSearchType] = useState<SearchType>('people');
  const [query, setQuery] = useState('');
  const [results, setResults] = useState<SearchResult[]>([]);
  const [pagination, setPagination] = useState<Pagination | null>(null);
  const [isLoading, setIsLoading] = useState(false);
  const [toasts, setToasts] = useState<ToastMessage[]>([]);

  const addToast = useCallback((message: string, type: 'error' | 'info' = 'error') => {
    const id = `${Date.now()}-${Math.random().toString(36).slice(2, 9)}`;
    setToasts((prev) => [...prev, { id, message, type }]);
    // Auto-dismiss after 5 seconds
    setTimeout(() => {
      setToasts((prev) => prev.filter((t) => t.id !== id));
    }, 5000);
  }, []);

  const dismissToast = useCallback((id: string) => {
    setToasts((prev) => prev.filter((t) => t.id !== id));
  }, []);

  const performSearch = useCallback(
    async (page = 1) => {
      if (!query.trim()) return;

      setIsLoading(true);
      try {
        const response = await searchApi(searchType, query.trim(), page);
        setResults(response.data);
        setPagination(response.pagination);
      } catch (err: unknown) {
        addToast(extractErrorMessage(err, 'Something went wrong. Please try again.'));
        setResults([]);
        setPagination(null);
      } finally {
        setIsLoading(false);
      }
    },
    [searchType, query, addToast],
  );

  return (
    <SearchContext.Provider
      value={{
        searchType,
        setSearchType,
        query,
        setQuery,
        results,
        pagination,
        isLoading,
        toasts,
        performSearch,
        addToast,
        dismissToast,
      }}
    >
      {children}
    </SearchContext.Provider>
  );
};

export const useSearch = (): SearchContextValue => {
  const ctx = useContext(SearchContext);
  if (!ctx) {
    throw new Error('useSearch must be used within a SearchProvider');
  }
  return ctx;
};
