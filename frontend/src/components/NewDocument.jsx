import React, { useState, useEffect } from "react";
import "./../styles/NewDocument.css";

const NewDocument = ({ setActivePanel }) => {
    const [title, setTitle] = useState("");
    const [content, setContent] = useState("");
    const [promotorId, setPromotorId] = useState("");
    const [promotors, setPromotors] = useState([]);

    useEffect(() => {
        const fetchUsers = async () => {
            try {
                const token = localStorage.getItem("jwt");
                const response = await fetch("http://localhost:8000/api/users/all", {
                    headers: { Authorization: `Bearer ${token}` },
                });
                const users = await response.json();

                const filteredPromotors = users.filter(user => 
                    user.roles.includes("ROLE_PROMOTOR") || user.roles.includes("ROLE_ADMIN")
                );

                setPromotors(filteredPromotors);
            } catch (err) {
                console.error("Błąd pobierania użytkowników:", err);
            }
        };

        fetchUsers();
    }, []);

    const handleSubmit = async (e) => {
        e.preventDefault();
        try {
            const token = localStorage.getItem("jwt");
            await fetch("http://localhost:8000/api/documents/create", {
                method: "POST",
                headers: {
                    "Content-Type": "application/json",
                    Authorization: `Bearer ${token}`,
                },
                body: JSON.stringify({ title, content, promotor_id: promotorId }),
            });
            setActivePanel("moje-prace");
        } catch (err) {
            console.error("Błąd dodawania pracy:", err);
        }
    };

    return (
        <div className="new-document-container">
            <h2>Nowa Praca</h2>
            <form onSubmit={handleSubmit}>
                <input 
                    type="text" 
                    placeholder="Tytuł" 
                    value={title} 
                    onChange={(e) => setTitle(e.target.value)} 
                    required 
                />
                <textarea 
                    placeholder="Treść" 
                    value={content} 
                    onChange={(e) => setContent(e.target.value)} 
                />
                <select 
                    value={promotorId} 
                    onChange={(e) => setPromotorId(e.target.value)}
                >
                    <option value="">Wybierz promotora</option>
                    {promotors.map(promotor => (
                        <option key={promotor.id} value={promotor.id}>
                            {promotor.imie} {promotor.nazwisko}
                        </option>
                    ))}
                </select>

                <button type="submit">Dodaj</button>
                <button type="button" onClick={() => setActivePanel("moje-prace")}>Powrót</button>
            </form>
        </div>
    );
};

export default NewDocument;
