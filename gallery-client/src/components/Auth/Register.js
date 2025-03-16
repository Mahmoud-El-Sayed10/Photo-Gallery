import React, {useState} from 'react';
import {Link, useNavigate} from 'react-router-dom';
import axios from 'axios';
import './Auth.css';
import api from '../../services/api'; 

const Register = ({onLogin}) => {
    const [formData, setFormData] = useState({
        fullName: '',
        email:'',
        password:'',
        confirmPassword:''
    });

    const [error, setError] = useState('');
    const [loading, setLoading] = useState(false);

    const navigate = useNavigate();

    const handleChange = (e) => {
        const {name, value} = e.target;
        setFormData(prevState => ({
            ...prevState, [name]: value
        }));
    };

    const handleSubmit = async(e) =>{
        e.preventDefault();

        if(!formData.fullName || !formData.email || !formData.password){
            setError('Please fill in all fields');
            return;
        }

        if (formData.password !== formData.confirmPassword){
            setError('Password do not match');
            return;
        }

        if (formData.password.length < 6){
            setError('Password must be atleast 6 characters');
            return;
        }

        setLoading(true);
        setError('');

        try{
            const registerData= {
                fullName: formData.fullName,
                email: formData.email,
                password: formData.password
            };

            await axios.post('http://localhost/Gallery-System/gallery-server/users/register', registerData);

            try{
                const loginData = {
                    email: formData.email,
                    password: formData.password
                };
                
                const loginResponse = await axios.post('http://localhost/Gallery-System/gallery-server/users/login', loginData,{
                headers: {
                  'Content-Type': 'application/json',
                  'Accept': 'application/json'
              }
            }
            );

                onLogin({
                    userId: loginResponse.data.userId,
                    fullName: loginResponse.data.fullName,
                    email: loginResponse.data.email
                });

                navigate('/');
            } catch (loginErr){
                navigate ('/login');
            }
        } catch (err){
            console.error('Registration error: ', err);
            setError(err.response?.data?.error || 'Registration failed. Please try again.');
        } finally {
            setLoading(false);
        }
    };

    return (
        <div className="auth-container">
            <form className='auth-form' onSubmit={handleSubmit}>
                <h2 className="auth-title"> Create an Account </h2>
                {error && <div className="auth-error">{error}</div>}
        
                <div className="form-group">
                    <label htmlFor="fullName">Full Name</label>
                    <input type="text" id="fullName" name="fullName" className="form-control" value={formData.fullName} onChange={handleChange} required/>
                </div>
                <div className="form-group">
                    <label htmlFor="email">Email</label>
                    <input type="email" id="email" name="email" className="form-control" value={formData.email} onChange={handleChange} required/>
                </div>
                <div className="form-group">
                    <label htmlFor="password">Password</label>
                    <input type="password" id="password" name="password" className="form-control" value={formData.password} onChange={handleChange} required/>
                </div>
                <div className="form-group">
                    <label htmlFor="confirmPassword">Confirm Password</label>
                    <input type="password" id="confirmPassword" name="confirmPassword" className="form-control" value={formData.confirmPassword} onChange={handleChange} required/>
                </div>
                <button type="submit" className="btn btn-primary auth-button" disabled={loading}> 
                    {loading ? 'Registering...' : 'Register'}
                </button>
                <div className="auth-footer"> Already have an account? <Link to="/login">Login</Link>
                </div>
            </form>
        </div>
    );
};

export default Register;