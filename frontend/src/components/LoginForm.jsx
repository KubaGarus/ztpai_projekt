import React, { useState } from "react";
import axios from "axios";
import "./../styles/LoginForm.css";

const LoginForm = () => {
    const [credentials, setCredentials] = useState({ login: "", password: "" });
    const [responseData, setResponseData] = useState(null); // Nowy stan na odpowiedź z API

    const handleChange = (e) => {
        setCredentials({ ...credentials, [e.target.name]: e.target.value });
    };

    const handleSubmit = async (e) => {
        e.preventDefault();
        console.log(credentials);
        try {
            const response = await axios.post("http://localhost:8000/api/login", credentials, {
                headers: { "Content-Type": "application/json" }
            });

            // Zapisz token i ustaw dane odpowiedzi w stanie
            localStorage.setItem("jwt", response.data.token);
            setResponseData(response.data);
            alert("Zalogowano pomyślnie!");
        } catch (err) {
            alert("Błąd logowania!");
            console.error("Login error:", err.response ? err.response.data : err.message);
        }
    };
    return (
        <div>
            <form className="login-form" onSubmit={handleSubmit}>
                <h2>Logowanie</h2>
                <input name="login" placeholder="Login" onChange={handleChange} />
                <input name="password" type="password" placeholder="Hasło" onChange={handleChange} />
                <button type="submit">Zaloguj</button>
            </form>

            {/* Wyświetlenie odpowiedzi JSON, jeśli istnieje */}
            {responseData && (
                <div className="response-container">
                    <h3>Odpowiedź z serwera:</h3>
                    <pre>{JSON.stringify(responseData, null, 2)}</pre>
                </div>
            )}
        </div>
    );
};

export default LoginForm;
