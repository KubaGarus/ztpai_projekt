import React, { useState, useEffect } from "react";
import axios from "axios";
import { useNavigate } from "react-router-dom";
import "./../styles/Dashboard.css";
import MyDocuments from "./MyDocuments";
import NewDocument from "./NewDocument";

const Dashboard = () => {
    const [user, setUser] = useState(null);
    const [error, setError] = useState("");
    const [activePanel, setActivePanel] = useState("moje-prace"); // Domyślny widok
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
                <div className="welcome">
                    {user ? `Witaj, ${user.imie} ${user.nazwisko}!` : "Ładowanie danych..."}
                </div>
                <div className="nav-buttons">
                    {user && user.roles.includes("ROLE_ADMIN") && (
                        <button className="admin-button" onClick={() => navigate("/admin/users")}>
                            Zarządzanie użytkownikami
                        </button>
                    )}
                    <button className="logout-button" onClick={handleLogout}>
                        Wyloguj się
                    </button>
                </div>
            </div>

            {/* Główna zawartość */}
            <div className="content">
                {/* Menu boczne */}
                <div className="sidebar">
                    <ul>
                        <li
                            className={activePanel === "moje-prace" ? "active" : ""}
                            onClick={() => setActivePanel("moje-prace")}
                        >
                            Moje prace
                        </li>
                        <li
                            className={activePanel === "panel-promotora" ? "active" : ""}
                            onClick={() => setActivePanel("panel-promotora")}
                        >
                            Panel promotora
                        </li>
                    </ul>
                </div>

                {/* Główna część strony */}
                <div className="main-content">
                    {activePanel === "moje-prace" && <MyDocuments setActivePanel={setActivePanel} />}
                    {activePanel === "nowa-praca" && <NewDocument setActivePanel={setActivePanel} />}
                    {activePanel === "panel-promotora" && <p>Panel promotora (w budowie)</p>}
                </div>
            </div>
        </div>
    );
};

export default Dashboard;
