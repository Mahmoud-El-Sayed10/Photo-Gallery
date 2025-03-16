import React, { useState, useEffect, useRef } from 'react';
import { useNavigate } from 'react-router-dom';
import api from '../../services/api'; 
import './ImageForms.css';

const ImageUpload = ({ user }) => {
  const [formData, setFormData] = useState({
    title: '',
    description: '',
    selectedTags: []
  });
  const [imagePreview, setImagePreview] = useState('');
  const [imageFile, setImageFile] = useState(null);
  const [availableTags, setAvailableTags] = useState([]);
  const [newTag, setNewTag] = useState('');
  const [loading, setLoading] = useState(false);
  const [error, setError] = useState('');
  const [dragActive, setDragActive] = useState(false);
  
  const fileInputRef = useRef(null);
  const navigate = useNavigate();

  useEffect(() => {
    const fetchTags = async () => {
      try {
        const response = await api.get('/tags');
        setAvailableTags(response.data.tags || []);
      } catch (err) {
        console.error('Error fetching tags:', err);
      }
    };
    
    fetchTags();
  }, []);
  
  const handleChange = (e) => {
    const { name, value } = e.target;
    setFormData(prev => ({ ...prev, [name]: value }));
  };
  
  const handleFileChange = (e) => {
    const file = e.target.files[0];
    handleFileSelection(file);
  };
  
  const handleFileSelection = (file) => {
    if (!file) return;
    if (!file.type.match('image.*')) {
      setError('Please select an image file (JPEG, PNG, GIF)');
      return;
    }
    if (file.size > 5 * 1024 * 1024) {
      setError('Image size should not exceed 5MB');
      return;
    }
    
    setImageFile(file);
    setError('');
    
    const reader = new FileReader();
    reader.onloadend = () => {
      setImagePreview(reader.result);
    };
    reader.readAsDataURL(file);
  };
  
  const handleDrag = (e) => {
    e.preventDefault();
    e.stopPropagation();
    
    if (e.type === 'dragenter' || e.type === 'dragover') {
      setDragActive(true);
    } else if (e.type === 'dragleave') {
      setDragActive(false);
    }
  };
  
  const handleDrop = (e) => {
    e.preventDefault();
    e.stopPropagation();
    setDragActive(false);
    
    if (e.dataTransfer.files && e.dataTransfer.files[0]) {
      handleFileSelection(e.dataTransfer.files[0]);
    }
  };
  
  const triggerFileInput = () => {
    fileInputRef.current.click();
  };
  
  const handleTagToggle = (tagId) => {
    setFormData(prev => {
      const selectedTags = [...prev.selectedTags];
      
      if (selectedTags.includes(tagId)) {
        return {
          ...prev,
          selectedTags: selectedTags.filter(id => id !== tagId)
        };
      } else {
        return {
          ...prev,
          selectedTags: [...selectedTags, tagId]
        };
      }
    });
  };
  
  const handleAddNewTag = async (e) => {
    e.preventDefault();
    
    if (!newTag.trim()) return;
    
    try {
      const response = await api.post('/tags/create', {
        name: newTag.trim()
      });

      setAvailableTags(prev => [
        ...prev, 
        { id: response.data.tagId, name: newTag.trim() }
      ]);
      
      setFormData(prev => ({
        ...prev,
        selectedTags: [...prev.selectedTags, response.data.tagId]
      }));
      
      setNewTag('');
    } catch (err) {
      console.error('Error creating tag:', err);
      setError('Failed to create new tag. Please try again.');
    }
  };
  
  const handleSubmit = async (e) => {
    e.preventDefault();
    
    if (!imageFile) {
      setError('Please select an image to upload');
      return;
    }
    
    if (!formData.title.trim()) {
      setError('Please enter a title for the image');
      return;
    }
    
    setLoading(true);
    setError('');
    
    try {
      // Convert image to base64
      const base64Image = await convertFileToBase64(imageFile);
      
      // Prepare data for API
      const uploadData = {
        userId: user.userId,
        title: formData.title,
        description: formData.description,
        base64Image,
        tags: formData.selectedTags.map(tagId => {
          const tag = availableTags.find(t => t.id === tagId);
          return tag ? tag.name : '';
        }).filter(Boolean)
      };
      
      // Send to API
      await api.post('/images/upload', uploadData);
      
      // Redirect to gallery on success
      navigate('/');
    } catch (err) {
      console.error('Upload error:', err);
      setError(err.response?.data?.error || 'Failed to upload image. Please try again.');
    } finally {
      setLoading(false);
    }
  };
  
  const convertFileToBase64 = (file) => {
    return new Promise((resolve, reject) => {
      const reader = new FileReader();
      reader.onloadend = () => resolve(reader.result);
      reader.onerror = reject;
      reader.readAsDataURL(file);
    });
  };
  
  const cancelUpload = () => {
    navigate('/');
  };

  return (
    <div className="image-form-container">
      <h2 className="form-title">Upload New Image</h2>
      
      {error && <div className="form-error">{error}</div>}
      
      <form className="image-form" onSubmit={handleSubmit}>
        <div 
          className={`image-upload-area ${dragActive ? 'drag-active' : ''}`}
          onDragEnter={handleDrag}
          onDragOver={handleDrag}
          onDragLeave={handleDrag}
          onDrop={handleDrop}
          onClick={triggerFileInput}
        >
          {imagePreview ? (
            <div className="image-preview-container">
              <img src={imagePreview} alt="Preview" className="image-preview" />
              <button type="button" className="change-image-btn"
                onClick={(e) => {e.stopPropagation(); triggerFileInput();}}>
                Change Image
              </button>
            </div>
          ) : (
            <div className="upload-placeholder">
              <div className="upload-icon">+</div>
              <p>Drag and drop an image here, or click to select</p>
              <small>JPEG, PNG or GIF • Max 5MB</small>
            </div>
          )}
    
          <input type="file" ref={fileInputRef} onChange={handleFileChange} accept="image/*" style={{ display: 'none' }}/>
        </div>
        
        <div className="form-group">
          <label htmlFor="title">Title</label>
          <input type="text" id="title" name="title" value={formData.title} onChange={handleChange} className="form-control"required/>
        </div>
        
        <div className="form-group">
          <label htmlFor="description">Description</label>
          <textarea id="description" name="description" value={formData.description}
            onChange={handleChange} className="form-control" rows="4">
          </textarea>
        </div>
        
        <div className="form-group">
          <label>Tags</label>
          <div className="tags-container">
            {availableTags.map(tag => (
              <button key={tag.id} type="button"
                className={`tag-select-btn ${formData.selectedTags.includes(tag.id) ? 'selected' : ''}`}
                onClick={() => handleTagToggle(tag.id)}>
                {tag.name}
              </button>
            ))}
          </div>
          
          <div className="add-tag-container">
            <input type="text" placeholder="Add a new tag" value={newTag} onChange={(e) => setNewTag(e.target.value)} 
            className="form-control add-tag-input"/>
            <button type="button" className="add-tag-btn" onClick={handleAddNewTag} disabled={!newTag.trim()}>
              Add
            </button>
          </div>
        </div>
        
        <div className="form-actions">
          <button type="button" className="btn-secondary" onClick={cancelUpload}>
            Cancel
          </button>
          <button type="submit" className="btn-primary" disabled={loading}>
            {loading ? 'Uploading...' : 'Upload Image'}
          </button>
        </div>
      </form>
    </div>
  );
};

export default ImageUpload;