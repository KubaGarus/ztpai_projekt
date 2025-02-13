import React, { useEffect, useState } from "react";
import axios from "axios";
import "./../styles/MyDocuments.css";

const MyDocuments = ({ setActivePanel }) => {
    const [documents, setDocuments] = useState([]);
    const [error, setError] = useState("");

    useEffect(() => {
        const fetchDocuments = async () => {
            try {
                const token = localStorage.getItem("jwt");
                const response = await axios.get("http://localhost:8000/api/documents/my", {
                    headers: { Authorization: `Bearer ${token}` }
                });
                if (response.data.message) {
                    setDocuments([]);
                } else {
                    setDocuments(response.data);
                }
            } catch (err) {
                setError("Błąd pobierania danych.");
            }
        };

        fetchDocuments();
    }, []);

    return (
        <div className="documents-container">
            <h2>Moje Prace</h2>
            <button className="new-document-button" onClick={() => setActivePanel("nowa-praca")}>
                Dodaj nową pracę
            </button>
            {error && <p className="error-message">{error}</p>}
            {documents.length > 0 ? (
                <ul className="documents-list">
                    {documents.map((doc) => (
                        <li key={doc.id} className="document-item">
                            <strong>{doc.title}</strong> – Promotor: {doc.promotor}
                        </li>
                    ))}
                </ul>
            ) : (
                <p>Nie posiadasz żadnych prac.</p>
            )}
        </div>
    );
};

export default MyDocuments;
