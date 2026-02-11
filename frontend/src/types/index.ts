export type SearchType = 'people' | 'films';

export interface PersonSummary {
  uid: string;
  name: string;
}

export interface FilmSummary {
  uid: string;
  title: string;
}

export type SearchResult = PersonSummary | FilmSummary;

export interface PersonDetails {
  uid: string;
  name: string;
  birth_year: string;
  gender: string;
  eye_color: string;
  hair_color: string;
  height: string;
  mass: string;
  films: FilmSummary[];
}

export interface FilmDetails {
  uid: string;
  title: string;
  opening_crawl: string;
  characters: { uid: string; name: string }[];
}

export interface Pagination {
  page: number;
  per_page: number;
  total: number;
  total_pages: number;
}

export interface SearchResponse {
  data: SearchResult[];
  pagination: Pagination;
}

export interface ToastMessage {
  id: string;
  message: string;
  type: 'error' | 'info';
}

export function isPersonSummary(result: SearchResult): result is PersonSummary {
  return 'name' in result;
}
