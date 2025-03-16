import React from 'react';
import { Link, useNavigate } from 'react-router-dom';
import './Header.css';
import api from '../../../services/api'; 

const Header = ({ user, onLogout }) => {
  const navigate = useNavigate();

  const handleLogout = () => {
    onLogout();
    navigate('/login');
  };

  return (
    <header className="header">
      <div className="header-container">
        <div className="logo">
          <Link to="/">Photo Gallery</Link>
        </div>
        
        <nav className="navigation">
          {user ? (
            <>
              <Link to="/" className="nav-link">Gallery</Link>
              <Link to="/upload" className="nav-link">Upload</Link>
              <div className="user-menu">
                <span className="user-name">{user.fullName}</span>
                <button onClick={handleLogout} className="logout-button">
                  Logout
                </button>
              </div>
            </>
          ) : (
            <>
              <Link to="/login" className="nav-link">Login</Link>
              <Link to="/register" className="nav-link">Register</Link>
            </>
          )}
        </nav>
      </div>
    </header>
  );
};

export default Header;