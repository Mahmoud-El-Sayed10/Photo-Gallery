import React, { useState, useEffect } from 'react';
import { Link, useParams, useNavigate } from 'react-router-dom';
import './ImageView.css';
import api from '../../../services/api'; 

const ImageView = ({ user }) => {
  const [image, setImage] = useState(null);
  const [loading, setLoading] = useState(true);
  const [error, setError] = useState('');
  
  const { id } = useParams();
  const navigate = useNavigate();
  
  useEffect(() => {
    const fetchImage = async () => {
      try {
        const response = await api.get(`/images/get`, {
          params: { id }
        });
        
        setImage(response.data.image);
      } catch (err) {
        console.error('Error loading image:', err);
        
        if (err.response && err.response.status === 404) {
          setError('Image not found');
        } else {
          setError('Failed to load image details');
        }
      } finally {
        setLoading(false);
      }
    };
    
    fetchImage();
  }, [id]);
  
  const handleDelete = async () => {
    if (!window.confirm('Are you sure you want to delete this image?')) {
      return;
    }
    
    try {
      await api.delete('/images/delete', {
        params: { id }
      });
      navigate('/');
    } catch (err) {
      console.error('Error deleting image:', err);
      setError('Failed to delete image');
    }
  };
  
  if (loading) {
    return (
      <div className="image-view-container loading-container">
        <div className="loading-spinner"></div>
        <p>Loading image...</p>
      </div>
    );
  }
  
  if (error) {
    return (
      <div className="image-view-container">
        <div className="error-message">{error}</div>
        <Link to="/" className="back-link">
          ← Back to Gallery
        </Link>
      </div>
    );
  }
  
  if (!image) {
    return (
      <div className="image-view-container">
        <div className="error-message">Image not found</div>
        <Link to="/" className="back-link">
          ← Back to Gallery
        </Link>
      </div>
    );
  }

  return (
    <div className="image-view-container">
      <div className="image-view-header">
        <Link to="/" className="back-link">
          ← Back to Gallery
        </Link>
        <div className="image-actions">
          <Link to={`/image/edit/${image.id}`} className="edit-link">
            Edit Image
          </Link>
          <button className="delete-button" onClick={handleDelete}>
            Delete Image
          </button>
        </div>
      </div>
      
      <div className="image-view-content">
        <div className="image-container">
          <img src={image.file_path} alt={image.title} className="full-image"/>
        </div>
        
        <div className="image-details">
          <h1 className="image-title">{image.title}</h1>
          
          {image.description && (
            <div className="image-description">
              <h3>Description</h3>
              <p>{image.description}</p>
            </div>
          )}
          
          {image.tags && image.tags.length > 0 && (
            <div className="image-tags">
              <h3>Tags</h3>
              <div className="tags-list">
                {image.tags.map(tag => (
                  <Link to={`/?tag=${tag.id}`} key={tag.id} className="image-tag">
                    {tag.name}
                  </Link>
                ))}
              </div>
            </div>
          )}
          
          <div className="image-meta">
            <p>
              <strong>Uploaded by:</strong> {user.fullName}
            </p>
          </div>
        </div>
      </div>
    </div>
  );
};

export default ImageView;