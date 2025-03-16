import React from 'react';
import PropTypes from 'prop-types';
import { Link } from 'react-router-dom';
import Card from '../../common/Card';
import Tag from '../../common/Tag';
import './ImageCard.css';

const ImageCard = ({ image, onDelete }) => {
  const handleDelete = (e) => {
    e.preventDefault(); 
    e.stopPropagation(); 
    
    if (window.confirm('Are you sure you want to delete this image?')) {
      onDelete(image.id);
    }
  };

  const cardFooter = (
    <div className="image-card-actions">
      <Link to={`/image/edit/${image.id}`} className="image-card-edit-link">
        Edit
      </Link>
      <button 
        className="image-card-delete-button"
        onClick={handleDelete}
      >
        Delete
      </button>
    </div>
  );

  return (
    <Card 
      className="image-card"
      elevated
      hoverable
      footer={cardFooter}
    >
      <Link to={`/image/${image.id}`} className="image-card-link">
        <div className="image-card-image-wrapper">
          <img 
            src={image.file_path} 
            alt={image.title} 
            className="image-card-image" 
          />
        </div>
        
        <div className="image-card-content">
          <h3 className="image-card-title">{image.title}</h3>
          
          {image.tags && image.tags.length > 0 && (
            <div className="image-card-tags">
              {image.tags.map(tag => (
                <Tag 
                  key={tag.id} 
                  text={tag.name}
                  size="small"
                />
              ))}
            </div>
          )}
        </div>
      </Link>
    </Card>
  );
};

ImageCard.propTypes = {
  image: PropTypes.shape({
    id: PropTypes.number.isRequired,
    title: PropTypes.string.isRequired,
    file_path: PropTypes.string.isRequired,
    tags: PropTypes.arrayOf(
      PropTypes.shape({
        id: PropTypes.number.isRequired,
        name: PropTypes.string.isRequired
      })
    )
  }).isRequired,
  onDelete: PropTypes.func.isRequired
};

export default ImageCard;