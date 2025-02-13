import React, { useState } from "react";
import axios from "axios";
import "./../styles/LoginForm.css";
import { useNavigate } from "react-router-dom"; 

const RegisterForm = ({ setShowRegister }) => {
    const navigate = useNavigate();
    const [formData, setFormData] = useState({
        imie: "",
        nazwisko: "",
        login: "",
        password: "",
        isPromotor: false, // Nowe pole dla checkboxa
    });

    const [error, setError] = useState("");

    const handleChange = (e) => {
        const { name, value, type, checked } = e.target;
        setFormData(prevState => ({
            ...prevState,
            [name]: type === "checkbox" ? checked : value
        }));
    };

    const handleSubmit = async (e) => {
        e.preventDefault();
        setError(""); // Resetowanie błędów

        // Walidacja po stronie klienta
        if (!formData.imie || !formData.nazwisko || !formData.login || !formData.password) {
            setError("Wszystkie pola są wymagane.");
            return;
        }
        if (formData.login.length < 4) {
            setError("Login musi mieć co najmniej 4 znaki.");
            return;
        }
        if (formData.password.length < 6) {
            setError("Hasło musi mieć co najmniej 6 znaków.");
            return;
        }

        // Określenie roli użytkownika
        const roles = formData.isPromotor ? ["ROLE_PROMOTOR"] : ["ROLE_USER"];

        try {
            await axios.post("http://localhost:8000/api/register", { 
                imie: formData.imie, 
                nazwisko: formData.nazwisko, 
                login: formData.login, 
                password: formData.password, 
                roles 
            }, {
                headers: { "Content-Type": "application/json" }
            });

            alert("Rejestracja udana! Możesz się teraz zalogować.");
            navigate("/login");
        } catch (err) {
            setError(err.response?.data?.error || "Błąd rejestracji!");
        }
    };

    return (
        <div className="login-form-container">
            <form className="login-form" onSubmit={handleSubmit}>
                <h2>Rejestracja</h2>
                {error && <p className="error-message">{error}</p>}
                <input name="imie" placeholder="Imię" onChange={handleChange} required />
                <input name="nazwisko" placeholder="Nazwisko" onChange={handleChange} required />
                <input name="login" placeholder="Login" onChange={handleChange} required />
                <input name="password" type="password" placeholder="Hasło" onChange={handleChange} required />
                
                {/* Checkbox dla promotora */}
                <label className="checkbox-label">
                    <input 
                        type="checkbox" 
                        name="isPromotor" 
                        checked={formData.isPromotor} 
                        onChange={handleChange} 
                    />
                    Jestem promotorem
                </label>

                <button type="submit">Zarejestruj</button>
                <p className="switch-form" onClick={() => navigate("/login")}>
                    Masz już konto? Zaloguj się
                </p>
            </form>
        </div>
    );
};

export default RegisterForm;
