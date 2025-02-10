import React, { useState } from "react";
import axios from "axios";
import "./../styles/LoginForm.css";
import { useNavigate } from "react-router-dom"; 

const LoginForm = ({ setShowRegister }) => {
    const [credentials, setCredentials] = useState({ login: "", password: "" });
    const navigate = useNavigate();
    const handleChange = (e) => {
        setCredentials({ ...credentials, [e.target.name]: e.target.value });
    };

    const handleSubmit = async (e) => {
        e.preventDefault();
        try {
            const response = await axios.post("http://localhost:8000/api/login", credentials, {
                headers: { "Content-Type": "application/json" }
            });

            localStorage.setItem("jwt", response.data.token);
            alert("Zalogowano pomyślnie!");
            navigate("/dashboard");
        } catch (err) {
            alert("Błąd logowania!");
            console.error("Login error:", err.response ? err.response.data : err.message);
        }
    };

    return (
        <div className="login-form-container">
            <form className="login-form" onSubmit={handleSubmit}>
                <h2>Logowanie</h2>
                <input name="login" placeholder="Login" onChange={handleChange} />
                <input name="password" type="password" placeholder="Hasło" onChange={handleChange} />
                <button type="submit">Zaloguj</button>
                <p className="switch-form" onClick={() => setShowRegister(true)}>Nie masz konta? Zarejestruj się</p>
            </form>
        </div>
    );
};

export default LoginForm;
