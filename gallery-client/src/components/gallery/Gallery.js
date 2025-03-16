import React, { useState, useEffect, useCallback } from 'react';
import { Link } from 'react-router-dom';
import api from '../../services/api';
import './Gallery.css';

const Gallery = ({ user }) => {
  const [images, setImages] = useState([]);
  const [tags, setTags] = useState([]);
  const [loading, setLoading] = useState(true);
  const [error, setError] = useState('');
  const [selectedTag, setSelectedTag] = useState(null);
  const [searchQuery, setSearchQuery] = useState('');
  
  const fetchTags = useCallback(async () => {
    try {
      const response = await api.get('/tags', { 
        params: { withCount: true } 
      });
      
      setTags(response.data.tags || []);
    } catch (err) {
      console.error('Error fetching tags:', err);
    }
  }, []);
  
  const fetchImages = useCallback(async () => {
    setLoading(true);
    setError('');
    
    try {
      const params = {};
      
      if (selectedTag) {
        params.tag_id = selectedTag;
      }
      
      if (searchQuery) {
        params.search = searchQuery;
      }
      
      if (user) {
        params.user_id = user.userId;
      }
      
      const response = await api.get('/images', { params });
      
      setImages(response.data.images || []);
    } catch (err) {
      setError('Could not load images. Please try again later.');
      console.error('Error fetching images:', err);
    } finally {
      setLoading(false);
    }
  }, [selectedTag, searchQuery, user]);
  
  useEffect(() => {
    fetchTags();
    fetchImages();
  }, [fetchTags, fetchImages]);
  
  const handleTagClick = (tagId) => {
    setSelectedTag(selectedTag === tagId ? null : tagId);
  };
  
  const handleSearchChange = (e) => {
    setSearchQuery(e.target.value);
  };

  const handleSearchSubmit = (e) => {
    e.preventDefault();
    fetchImages();
  };

  const clearFilters = () => {
    setSelectedTag(null);
    setSearchQuery('');
  };

  const handleDeleteImage = async (imageId) => {
    if (!window.confirm('Are you sure you want to delete this image?')) {
      return;
    }
    
    try {
      await api.delete('/images/delete', {
        params: { id: imageId }
      });
    
      fetchImages();
    } catch (error) {
      console.error('Error deleting image:', error);
      alert('Failed to delete image. Please try again.');
    }
  };

  return (
    <div className="gallery-container">
      <div className="gallery-header">
        <h1 className="gallery-title">Your Photo Gallery</h1>
        <Link to="/upload" className="upload-btn">
          Upload New Image
        </Link>
      </div>
      
      <div className="filters-container">
        <div className="tags-filter">
          <h3>Filter by Tags</h3>
          <div className="tags-list">
            {tags.map(tag => (
              <button
                key={tag.id}
                className={`tag-btn ${selectedTag === tag.id ? 'active' : ''}`}
                onClick={() => handleTagClick(tag.id)}
              >
                {tag.name}
                {tag.image_count && <span className="tag-count">{tag.image_count}</span>}
              </button>
            ))}
          </div>
        </div>
        
        <div className="search-filter">
          <form onSubmit={handleSearchSubmit}>
            <input type="text" placeholder="Search images..." value={searchQuery} onChange={handleSearchChange} className="search-input"/>
            <button type="submit" className="search-btn">
              Search
            </button>
          </form>
        </div>
        
        {(selectedTag || searchQuery) && (
          <button className="clear-filters-btn" onClick={clearFilters}>
            Clear Filters
          </button>
        )}
      </div>
      
      {error && <div className="error-message">{error}</div>}
      
      {loading ? (
        <div className="loading-container">
          <div className="loading-spinner"></div>
          <p>Loading images...</p>
        </div>
      ) : (
        <div className="image-grid">
          {images.length === 0 ? (
            <div className="no-images">
              <p>No images found. Try adjusting your filters or upload a new image.</p>
              <Link to="/upload" className="upload-btn">
                Upload New Image
              </Link>
            </div>
          ) : (
            images.map(image => (
              <div className="image-card" key={image.id}>
                <Link to={`/image/${image.id}`} className="image-link">
                  <div className="image-wrapper">
                    <img 
                      src={image.file_path} 
                      alt={image.title} 
                      className="gallery-image" 
                    />
                  </div>
                  <div className="image-info">
                    <h3 className="image-title">{image.title}</h3>
                    {image.tags && image.tags.length > 0 && (
                      <div className="image-tags">
                        {image.tags.map(tag => (
                          <span key={tag.id} className="image-tag">
                            {tag.name}
                          </span>
                        ))}
                      </div>
                    )}
                  </div>
                </Link>
                <div className="image-actions">
                  <Link to={`/image/edit/${image.id}`} className="edit-btn">
                    Edit
                  </Link>
                  <button 
                    className="delete-btn"
                    onClick={() => handleDeleteImage(image.id)}
                  >
                    Delete
                  </button>
                </div>
              </div>
            ))
          )}
        </div>
      )}
    </div>
  );
};

export default Gallery;