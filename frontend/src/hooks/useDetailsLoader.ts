import { useEffect, useState } from 'react';
import { useParams, useNavigate } from 'react-router-dom';
import { useSearch } from '../context/SearchContext';
import { extractErrorMessage } from '../utils/extractErrorMessage';

/**
 * Generic hook that loads a single resource by its route :id param.
 *
 * On error it shows a toast and redirects to the search page.
 *
 * @param fetchFn   Async function that fetches the resource by id.
 * @param errorMsg  Default error message if the API doesn't provide one.
 */
export function useDetailsLoader<T>(
  fetchFn: (id: string) => Promise<T>,
  errorMsg: string,
): { data: T | null; loading: boolean } {
  const { id } = useParams<{ id: string }>();
  const navigate = useNavigate();
  const { addToast } = useSearch();
  const [data, setData] = useState<T | null>(null);
  const [loading, setLoading] = useState(true);

  useEffect(() => {
    if (!id) return;
    setLoading(true);
    fetchFn(id)
      .then(setData)
      .catch((err) => {
        addToast(extractErrorMessage(err, errorMsg));
        navigate('/');
      })
      .finally(() => setLoading(false));
  }, [id, navigate, addToast, fetchFn, errorMsg]);

  return { data, loading };
}
