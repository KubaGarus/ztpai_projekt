import React, { useEffect, useState } from "react";
import axios from "axios";
import "./../styles/DocumentDetails.css";

const DocumentDetails = ({ documentId, setSelectedDocumentId }) => {
    const [document, setDocument] = useState(null);
    const [messages, setMessages] = useState([]);
    const [newMessage, setNewMessage] = useState("");
    const [error, setError] = useState("");
    const [user, setUser] = useState(null);

    useEffect(() => {
        const fetchUserData = async () => {
            try {
                const token = localStorage.getItem("jwt");
                const response = await axios.get("http://localhost:8000/api/dashboard", {
                    headers: { Authorization: `Bearer ${token}` }
                });
                setUser(response.data.user);
            } catch (err) {
                setError("Błąd pobierania użytkownika.");
            }
        };

        const fetchDocumentDetails = async () => {
            try {
                const token = localStorage.getItem("jwt");
                const response = await axios.get(`http://localhost:8000/api/documents/${documentId}`, {
                    headers: { Authorization: `Bearer ${token}` }
                });
                setDocument(response.data);
            } catch (err) {
                setError("Błąd pobierania dokumentu.");
            }
        };

        const fetchMessages = async () => {
            try {
                const token = localStorage.getItem("jwt");
                const response = await axios.get(`http://localhost:8000/api/conversations/${documentId}`, {
                    headers: { Authorization: `Bearer ${token}` }
                });
                setMessages(response.data);
            } catch (err) {
                setError("Błąd pobierania wiadomości.");
            }
        };

        fetchUserData();
        fetchDocumentDetails();
        fetchMessages();
    }, [documentId]);

    const sendMessage = async () => {
        if (!newMessage.trim()) return;
        try {
            const token = localStorage.getItem("jwt");
            await axios.post(`http://localhost:8000/api/conversations/send`, 
                { document_id: documentId, content: newMessage }, 
                { headers: { Authorization: `Bearer ${token}`, "Content-Type": "application/json" } }
            );
            setMessages([...messages, { content: newMessage, user_id: user?.id, date: new Date().toISOString() }]);
            setNewMessage("");
        } catch (err) {
            setError("Błąd wysyłania wiadomości.");
        }
    };

    const updateDocumentStatus = async (status) => {
        try {
            const token = localStorage.getItem("jwt");
            await axios.patch(`http://localhost:8000/api/documents/${documentId}/status`, 
                { status }, 
                { headers: { Authorization: `Bearer ${token}`, "Content-Type": "application/json" } }
            );
            setDocument({ ...document, status });
        } catch (err) {
            setError("Błąd zmiany statusu dokumentu.");
        }
    };

    if (!document || !user) return <p>Ładowanie...</p>;
    return (
        <div className="document-details-container">
            <button onClick={() => setSelectedDocumentId(null)} className="back-button">Powrót</button>
            <h2>{document.title}</h2>
            <p className="message-main-content">{document.content}</p>
            <p className="upload-date">Data utworzenia: {document.upload_date}</p>
            <div className="messages-section">
                <h3>Rozmowa</h3>
                <div className="messages">
                    {messages.map((msg, index) => (
                        <div key={index} className={`message ${msg.user_id === user.id ? "my-message" : "other-message"}`}>
                            <p>{msg.content}</p>
                            <span className="message-date">{new Date(msg.date).toLocaleString()}</span>
                        </div>
                    ))}
                </div>
                <div className="message-input">
                    <textarea 
                        value={newMessage}
                        onChange={(e) => setNewMessage(e.target.value)}
                        placeholder="Napisz wiadomość..."
                    />
                    <button onClick={sendMessage}>Wyślij</button>
                </div>
            </div>
            {(document.status !== 1 && document.status !== 3) && (
                <div className="document-actions">
                    {(user.roles.includes("ROLE_PROMOTOR") || user.roles.includes("ROLE_ADMIN")) && document.status === 2 && (
                        <button onClick={() => updateDocumentStatus(1)} className="accept-button">
                            Akceptuj
                        </button>
                    )}
                    {user.roles.includes("ROLE_USER") && document.status === 4 && (
                        <>
                            <button onClick={() => updateDocumentStatus(2)} className="request-approval-button">
                                Wyślij do akceptacji
                            </button>
                            <button onClick={() => updateDocumentStatus(3)} className="reject-button">
                                Odrzuć
                            </button>
                        </>
                    )}
                </div>
            )}

            {error && <p className="error-message">{error}</p>}
        </div>
    );
};

export default DocumentDetails;
