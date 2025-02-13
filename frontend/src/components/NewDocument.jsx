import React, { useState } from "react";
import "./../styles/NewDocument.css";

const NewDocument = ({ setActivePanel }) => {
    const [title, setTitle] = useState("");
    const [content, setContent] = useState("");
    const [promotorId, setPromotorId] = useState("");

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
            setActivePanel("moje-prace"); // Po dodaniu wracamy do listy prac
        } catch (err) {
            console.error("Błąd dodawania pracy:", err);
        }
    };

    return (
        <div className="new-document-container">
            <h2>Nowa Praca</h2>
            <form onSubmit={handleSubmit}>
                <input type="text" placeholder="Tytuł" value={title} onChange={(e) => setTitle(e.target.value)} required />
                <textarea placeholder="Treść" value={content} onChange={(e) => setContent(e.target.value)} />
                <input type="number" placeholder="ID promotora (opcjonalnie)" value={promotorId} onChange={(e) => setPromotorId(e.target.value)} />
                <button type="submit">Dodaj</button>
                <button type="button" onClick={() => setActivePanel("moje-prace")}>Powrót</button>
            </form>
        </div>
    );
};

export default NewDocument;
