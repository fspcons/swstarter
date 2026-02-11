import React from 'react';
import { BrowserRouter, Routes, Route } from 'react-router-dom';
import { SearchProvider } from './context/SearchContext';
import { Layout } from './components/Layout/Layout';
import { ToastContainer } from './components/Toast/Toast';
import { SearchPage } from './pages/SearchPage';
import { DetailsPage } from './pages/DetailsPage';

const App: React.FC = () => (
  <BrowserRouter future={{ v7_startTransition: true, v7_relativeSplatPath: true }}>
    <SearchProvider>
      <Layout>
        <ToastContainer />
        <Routes>
          <Route path="/" element={<SearchPage />} />
          <Route path="/details/:type/:id" element={<DetailsPage />} />
        </Routes>
      </Layout>
    </SearchProvider>
  </BrowserRouter>
);

export default App;
