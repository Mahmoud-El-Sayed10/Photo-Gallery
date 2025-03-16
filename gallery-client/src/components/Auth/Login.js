import React, {useState} from 'react';
import {Link, useNavigate} from 'react-router-dom';
import './Auth.css';
import api from '../../services/api'; 

const Login = ({onLogin}) => {
    const [email, setEmail] = useState('');
    const [password, setPassword] = useState('');
    const [error, setError] = useState('');
    const [loading, setLoading] = useState(false);

    const navigate = useNavigate();

    const handleSubmit = async (e) => {
        e.preventDefault();

        if (!email || !password) {
            setError('Please enter both email and password');
            return;
        }

        setLoading(true);
        setError('');

        try {
            // Remove withCredentials since we're not using cookies for session management
            const response = await api.post('/users/login', {email, password});

            if (response.data && response.data.userId) {
                onLogin({
                    userId: response.data.userId,
                    fullName: response.data.fullName,
                    email: response.data.email
                });

                navigate('/');
            } else {
                setError('Invalid response from server');
            }
        } catch (err) {
            console.error('Login error:', err);
            if (err.response) {
                if (err.response.status === 401) {
                    setError('Invalid email or password. Please try again');
                } else {
                    setError(err.response.data?.error || 'Login failed. Please try again');
                }
            } else if (err.request) {
                setError('No response from server. Please check your connection.');
            } else {
                setError('An error occurred. Please try again.');
            }
        } finally {
            setLoading(false);
        }
    };

    return (
        <div className='auth-container'>
            <form className='auth-form' onSubmit={handleSubmit}>
                <h2 className='auth-title'>Login to Photo Gallery</h2>

                {error && <div className="auth-error">{error}</div>}

                <div className='form-group'>
                    <label htmlFor='email'>Email</label>
                    <input type='email' id='email' className='form-control' value={email} onChange={(e) => setEmail(e.target.value)} required/>
                </div>

                <div className='form-group'>
                    <label htmlFor='password'>Password</label>
                    <input type='password' id='password' className='form-control' value={password} onChange={(e)=> setPassword(e.target.value)} required/>
                </div>

                <button type='submit' className='auth-button' disabled={loading}>
                    {loading ? 'Logging in...' : 'Login'}
                </button>

                <div className='auth-footer'>
                    Don't have an account? <Link to="/register">Register</Link>
                </div>
            </form>
        </div>
    );
};

export default Login;