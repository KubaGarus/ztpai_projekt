import React, { useState, useEffect } from "react";
import axios from "axios";
import { useNavigate } from "react-router-dom";
import "./../styles/Dashboard.css";

const Dashboard = () => {
    const [user, setUser] = useState(null);
    const [error, setError] = useState("");
    const navigate = useNavigate();

    useEffect(() => {
        const fetchUserData = async () => {
            const token = localStorage.getItem("jwt");
            if (!token) {
                navigate("/login");
                return;
            }

            try {
                const response = await axios.get("http://localhost:8000/api/dashboard", {
                    headers: { Authorization: `Bearer ${token}` }
                });
                setUser(response.data.user);
            } catch (err) {
                setError("Błąd autoryzacji. Zaloguj się ponownie.");
                localStorage.removeItem("jwt");
                navigate("/login");
            }
        };

        fetchUserData();
    }, [navigate]);

    return (
        <div className="dashboard-container">
            {error && <p className="error-message">{error}</p>}
            {user ? (
                <>
                    <h2>Witaj, {user.imie} {user.nazwisko}!</h2>
                    <p>Twój login: {user.login}</p>
                    <p>Twoje role: {user.roles.join(", ")}</p>
                    <button onClick={() => {
                        localStorage.removeItem("jwt");
                        navigate("/login");
                    }}>
                        Wyloguj się
                    </button>
                </>
            ) : (
                <p>Ładowanie danych...</p>
            )}
        </div>
    );
};

export default Dashboard;
