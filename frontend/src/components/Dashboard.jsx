import React, { useState, useEffect } from "react";
import axios from "axios";
import { useNavigate } from "react-router-dom";
import "./../styles/Dashboard.css";
import MyDocuments from "./MyDocuments";
import NewDocument from "./NewDocument";
import PromotorPanel from "./PromotorPanel";
import DocumentDetails from "./DocumentDetails";

const Dashboard = () => {
    const [user, setUser] = useState(null);
    const [error, setError] = useState("");
    const [activePanel, setActivePanel] = useState("moje-prace");
    const [selectedDocumentId, setSelectedDocumentId] = useState(null);

    const navigate = useNavigate();

    useEffect(() => {
        const fetchUserData = async () => {
            const token = localStorage.getItem("jwt");
            
    console.log(token);
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
            <div className="content">
                <div className="sidebar">
                    <ul>
                        <li
                            className={activePanel === "moje-prace" ? "active" : ""}
                            onClick={() => {
                                setSelectedDocumentId(null);
                                setActivePanel("moje-prace");
                            }}
                        >
                            Moje prace
                        </li>
                        {user && (user.roles.includes("ROLE_ADMIN") || user.roles.includes("ROLE_PROMOTOR")) && (
                            <li
                                className={activePanel === "panel-promotora" ? "active" : ""}
                                onClick={() => {
                                    setSelectedDocumentId(null);
                                    setActivePanel("panel-promotora");
                                }}
                            >
                                Panel promotora
                            </li>
                        )}
                    </ul>
                </div>
                <div className="main-content">
                    {selectedDocumentId ? (
                        <DocumentDetails
                            documentId={selectedDocumentId}
                            setSelectedDocumentId={setSelectedDocumentId}
                        />
                    ) : (
                        <>
                            {activePanel === "moje-prace" && (
                                <MyDocuments
                                    setActivePanel={setActivePanel}
                                    setSelectedDocumentId={setSelectedDocumentId}
                                />
                            )}
                            {activePanel === "nowa-praca" && <NewDocument setActivePanel={setActivePanel} />}
                            {activePanel === "panel-promotora" && (
                                <PromotorPanel setSelectedDocumentId={setSelectedDocumentId} />
                            )}
                        </>
                    )}
                </div>
            </div>
        </div>
    );
};

export default Dashboard;
