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

    const handleLogout = () => {
        localStorage.removeItem("jwt");
        navigate("/login");
    };

    return (
        <div className="dashboard-container">
            {/* Pasek nawigacyjny */}
            <div className="navbar">
                {user ? (
                    <div className="welcome">
                        Witaj, {user.imie} {user.nazwisko}!
                    </div>
                ) : (
                    <div className="welcome">Ładowanie danych...</div>
                )}
                <button className="logout-button" onClick={handleLogout}>
                    Wyloguj się
                </button>
            </div>

            {/* Główna zawartość */}
            <div className="content">
                <p>Tutaj znajdzie się główna zawartość strony.</p>
            </div>
        </div>
    );
};

export default Dashboard;
