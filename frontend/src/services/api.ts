import axios from 'axios';
import { SearchType, SearchResponse, PersonDetails, FilmDetails } from '../types';

const client = axios.create({
  baseURL: '/api',
  timeout: 15000,
  headers: {
    Accept: 'application/json',
  },
});

export async function searchApi(
  type: SearchType,
  query: string,
  page: number = 1,
  perPage: number = 10,
): Promise<SearchResponse> {
  const { data } = await client.get<SearchResponse>('/search', {
    params: { type, query, page, per_page: perPage },
  });
  return data;
}

export async function getPersonDetails(id: string): Promise<PersonDetails> {
  const { data } = await client.get<PersonDetails>(`/people/${id}`);
  return data;
}

export async function getFilmDetails(id: string): Promise<FilmDetails> {
  const { data } = await client.get<FilmDetails>(`/films/${id}`);
  return data;
}
