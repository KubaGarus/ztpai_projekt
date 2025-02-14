import React, { useEffect, useState } from "react";
import axios from "axios";
import "./../styles/PromotorPanel.css";

const PromotorPanel = ({ setSelectedDocumentId }) => {
    const [documents, setDocuments] = useState([]);
    const [error, setError] = useState("");

    useEffect(() => {
        const fetchPromotedDocuments = async () => {
            try {
                const token = localStorage.getItem("jwt");
                const response = await axios.get("http://localhost:8000/api/documents/promoted", {
                    headers: { Authorization: `Bearer ${token}` }
                });

                if (response.data.error) {
                    setError(response.data.error);
                    setDocuments([]);
                } else {
                    setDocuments(response.data);
                }
            } catch (err) {
                setError("Błąd pobierania danych.");
            }
        };

        fetchPromotedDocuments();
    }, []);

    const getStatusText = (status) => {
        switch (status) {
            case 1: return "Zaakceptowany";
            case 2: return "Czeka na akceptację";
            case 3: return "Odrzucony";
            case 4: return "W trakcie";
            default: return "Nieznany status";
        }
    }
    return (
        <div className="promotor-panel-container">
            <h2>Prace pod moją opieką</h2>
            {error && <p className="error-message">{error}</p>}
            {documents.length > 0 ? (
                <ul className="documents-list">
                    {documents.map((doc) => (
                        <li key={doc.id} className="document-item" onClick={() => setSelectedDocumentId(doc.id)}>
                            <strong>{doc.title}</strong> – {doc.student}  
                            <span className={`status status-${doc.status}`}>Status: {getStatusText(doc.status)}</span>
                        </li>
                    ))}
                </ul>
            ) : (
                <p>Nie masz przypisanych prac.</p>
            )}
        </div>
    );
};

export default PromotorPanel;
