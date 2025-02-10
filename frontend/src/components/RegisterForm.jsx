import React, { useState } from "react";
import axios from "axios";
import "./../styles/LoginForm.css";

const RegisterForm = ({ setShowRegister }) => {
    const [formData, setFormData] = useState({
        imie: "",
        nazwisko: "",
        login: "",
        password: "",
    });

    const [error, setError] = useState("");

    const handleChange = (e) => {
        setFormData({ ...formData, [e.target.name]: e.target.value });
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

        try {
            await axios.post("http://localhost:8000/api/register", { ...formData, roles: ["ROLE_USER"] }, {
                headers: { "Content-Type": "application/json" }
            });

            alert("Rejestracja udana! Możesz się teraz zalogować.");
            setShowRegister(false);
        } catch (err) {
            setError(err.response?.data?.error || "Błąd rejestracji!");
        }
    };

    return (
        <div className="login-form-container">
            <form className="login-form" onSubmit={handleSubmit}>
                <h2>Rejestracja</h2>
                {error && <p className="error-message">{error}</p>}
                <input name="imie" placeholder="Imię" onChange={handleChange} />
                <input name="nazwisko" placeholder="Nazwisko" onChange={handleChange} />
                <input name="login" placeholder="Login" onChange={handleChange} />
                <input name="password" type="password" placeholder="Hasło" onChange={handleChange} />
                <button type="submit">Zarejestruj</button>
                <p className="switch-form" onClick={() => setShowRegister(false)}>Masz już konto? Zaloguj się</p>
            </form>
        </div>
    );
};

export default RegisterForm;
