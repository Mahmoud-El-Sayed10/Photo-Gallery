import axios from 'axios';

const api = axios.create({
  baseURL: 'http://localhost/Gallery-System/gallery-server',
  headers: {
    'Content-Type': 'application/json',
    'Accept': 'application/json'
  }
});

export default api;