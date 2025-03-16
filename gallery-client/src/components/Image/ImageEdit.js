import React, { useState, useEffect, useRef } from 'react';
import { useParams, useNavigate, Link } from 'react-router-dom';
import './ImageForms.css';
import api from '../../services/api'; 

const ImageEdit = ({ user }) => {
  const [formData, setFormData] = useState({
    title: '',
    description: '',
    selectedTags: []
  });
  const [imagePreview, setImagePreview] = useState('');
  const [originalImage, setOriginalImage] = useState('');
  const [imageFile, setImageFile] = useState(null);
  const [availableTags, setAvailableTags] = useState([]);
  const [newTag, setNewTag] = useState('');
  const [loading, setLoading] = useState(true);
  const [saving, setSaving] = useState(false);
  const [error, setError] = useState('');
  const [dragActive, setDragActive] = useState(false);
  
  const fileInputRef = useRef(null);
  const { id } = useParams();
  const navigate = useNavigate();
  
  useEffect(() => {
    const fetchData = async () => {
      try {
        // Fetch image details
        const imageResponse = await api.get('/images/get', {
          params: { id }
        });
        
        const image = imageResponse.data.image;
        
        // Fetch all available tags
        const tagsResponse = await api.get('/tags');
        
        // Set state with fetched data
        setAvailableTags(tagsResponse.data.tags || []);
        setFormData({
          title: image.title || '',
          description: image.description || '',
          selectedTags: image.tags ? image.tags.map(tag => tag.id) : []
        });
        setOriginalImage(image.file_path);
        setImagePreview(image.file_path);
        
      } catch (err) {
        console.error('Error loading image details:', err);
        setError(err.response?.data?.error || 'Failed to load image details');
      } finally {
        setLoading(false);
      }
    };
    
    fetchData();
  }, [id]);
  
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
    
    // Check if file is an image
    if (!file.type.match('image.*')) {
      setError('Please select an image file (JPEG, PNG, GIF)');
      return;
    }
    
    // Check file size (max 5MB)
    if (file.size > 5 * 1024 * 1024) {
      setError('Image size should not exceed 5MB');
      return;
    }
    
    setImageFile(file);
    setError('');
    
    // Create preview
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
  
  const resetToOriginalImage = () => {
    setImagePreview(originalImage);
    setImageFile(null);
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
      
      // Add new tag to available tags
      setAvailableTags(prev => [
        ...prev, 
        { id: response.data.tagId, name: newTag.trim() }
      ]);
      
      // Select the newly created tag
      setFormData(prev => ({
        ...prev,
        selectedTags: [...prev.selectedTags, response.data.tagId]
      }));
      
      // Clear the input
      setNewTag('');
    } catch (err) {
      console.error('Error creating tag:', err);
    }
  };
  
  const handleSubmit = async (e) => {
    e.preventDefault();
    
    if (!formData.title.trim()) {
      setError('Please enter a title for the image');
      return;
    }
    
    setSaving(true);
    setError('');
    
    try {
      // Prepare update data
      const updateData = {
        id: id,
        title: formData.title,
        description: formData.description,
        tags: formData.selectedTags.map(tagId => {
          const tag = availableTags.find(t => t.id === tagId);
          return tag ? tag.name : '';
        }).filter(Boolean)
      };
      
      // Add base64 image if a new image was selected
      if (imageFile) {
        updateData.base64Image = await convertFileToBase64(imageFile);
      }
      
      // Send to API
      await api.put('/images/update', updateData);
      
      // Navigate to the image view page
      navigate(`/image/${id}`);
    } catch (err) {
      console.error('Update error:', err);
      setError(err.response?.data?.error || 'Failed to update image');
      setSaving(false);
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
  
  const cancelEdit = () => {
    navigate(`/image/${id}`);
  };
  
  if (loading) {
    return (
      <div className="image-form-container">
        <div className="loading-container">
          <div className="loading-spinner"></div>
          <p>Loading image details...</p>
        </div>
      </div>
    );
  }
  
  if (error && !formData.title) {
    return (
      <div className="image-form-container">
        <div className="form-error">{error}</div>
        <Link to="/" className="back-link">
          ← Back to Gallery
        </Link>
      </div>
    );
  }

  return (
    <div className="image-form-container">
      <h2 className="form-title">Edit Image</h2>
      
      {error && <div className="form-error">{error}</div>}
      
      <form className="image-form" onSubmit={handleSubmit}>
        <div className={`image-upload-area ${dragActive ? 'drag-active' : ''}`}
          onDragEnter={handleDrag}
          onDragOver={handleDrag}
          onDragLeave={handleDrag}
          onDrop={handleDrop}
          onClick={triggerFileInput}>
          {imagePreview ? (
            <div className="image-preview-container">
              <img src={imagePreview} alt="Preview" className="image-preview"/>
              <div className="image-controls">
                <button type="button" className="change-image-btn"
                  onClick={(e) => {e.stopPropagation(); triggerFileInput();}}>
                  Change Image
                </button>
                
                {imageFile && (
                  <button type="button" className="reset-image-btn"
                    onClick={(e) => {e.stopPropagation(); resetToOriginalImage();}}>
                    Reset to Original
                  </button>
                )}
              </div>
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
          <input type="text" id="title" name="title"
            value={formData.title} onChange={handleChange} className="form-control" required
          />
        </div>
        
        <div className="form-group">
          <label htmlFor="description">Description</label>
          <textarea
            id="description"
            name="description"
            value={formData.description}
            onChange={handleChange}
            className="form-control"
            rows="4"
          ></textarea>
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
          <button type="button"c lassName="btn-secondary" onClick={cancelEdit}>
            Cancel
          </button>
          <button type="submit" className="btn-primary" disabled={saving}>
            {saving ? 'Saving...' : 'Save Changes'}
          </button>
        </div>
      </form>
    </div>
  );
};

export default ImageEdit;