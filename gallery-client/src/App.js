import React, { useState, useEffect } from 'react';
import { BrowserRouter as Router, Routes, Route, Navigate } from 'react-router-dom';
import './App.css';

// Corrected Component Imports based on the exact folder structure
import Login from './components/Auth/Login.js';
import Register from './components/Auth/Register.js';
import Header from './components/layout/Header/Header.js';
import Gallery from './components/gallery/Gallery.js';
import ImageUpload from './components/Image/ImageUpload.js';
import ImageEdit from './components/Image/ImageEdit.js';
import ImageView from './components/Image/ImageView/ImageView.js';

/**
 * App Component
 * Main application container
 */
function App() {
  // User authentication state
  const [user, setUser] = useState(null);
  const [loading, setLoading] = useState(true);

  // Check if user is logged in on initial load
  useEffect(() => {
    const storedUser = localStorage.getItem('user');
    if (storedUser) {
      setUser(JSON.parse(storedUser));
    }
    setLoading(false);
  }, []);

  /**
   * Login handler
   * @param {Object} userData - User data from successful login
   */
  const handleLogin = (userData) => {
    setUser(userData);
    localStorage.setItem('user', JSON.stringify(userData));
  };

  /**
   * Logout handler
   */
  const handleLogout = () => {
    setUser(null);
    localStorage.removeItem('user');
  };

  /**
   * Protected route component
   * Redirects to login if user is not authenticated
   */
  const ProtectedRoute = ({ children }) => {
    if (loading) {
      return (
        <div className="loading-container">
          <div className="loading-spinner"></div>
          <p>Loading...</p>
        </div>
      );
    }
    return user ? children : <Navigate to="/login" />;
  };

  return (
    <Router>
      <div className="app">
        <Header user={user} onLogout={handleLogout} />
        <main className="content">
          <Routes>
            {/* Public routes */}
            <Route path="/login" element={<Login onLogin={handleLogin} />} />
            <Route path="/register" element={<Register onLogin={handleLogin} />} />
            
            {/* Protected routes */}
            <Route 
              path="/" 
              element={
                <ProtectedRoute>
                  <Gallery user={user} />
                </ProtectedRoute>
              } 
            />
            <Route 
              path="/upload" 
              element={
                <ProtectedRoute>
                  <ImageUpload user={user} />
                </ProtectedRoute>
              } 
            />
            <Route 
              path="/image/edit/:id" 
              element={
                <ProtectedRoute>
                  <ImageEdit user={user} />
                </ProtectedRoute>
              } 
            />
            <Route 
              path="/image/:id" 
              element={
                <ProtectedRoute>
                  <ImageView user={user} />
                </ProtectedRoute>
              } 
            />

            {/* Catch-all redirect */}
            <Route path="*" element={<Navigate to="/" />} />
          </Routes>
        </main>
      </div>
    </Router>
  );
}

export default App;