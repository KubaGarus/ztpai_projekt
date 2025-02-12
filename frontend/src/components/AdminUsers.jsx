import React, { useEffect, useState } from "react";
import { useNavigate } from "react-router-dom";
import axios from "axios";
import "./../styles/AdminUsers.css";

const AdminUsers = () => {
    const [users, setUsers] = useState([]);
    const navigate = useNavigate();

    useEffect(() => {
        const fetchUsers = async () => {
            try {
                const token = localStorage.getItem("jwt");
                const response = await axios.get("http://localhost:8000/api/users", {
                    headers: { Authorization: `Bearer ${token}` }
                });
                setUsers(response.data);
            } catch (err) {
                console.error("Błąd pobierania użytkowników:", err);
                navigate("/");
            }
        };
        fetchUsers();
    }, [navigate]);

    const handleDelete = async (userId) => {
        if (!window.confirm("Czy na pewno chcesz usunąć tego użytkownika?")) return;

        try {
            const token = localStorage.getItem("jwt");
            await axios.delete(`http://localhost:8000/api/users/${userId}`, {
                headers: { Authorization: `Bearer ${token}` }
            });
            setUsers(users.filter(user => user.id !== userId));
        } catch (err) {
            console.error("Błąd usuwania użytkownika:", err);
        }
    };

    return (
        <div className="admin-container">
            <h1>Zarządzanie użytkownikami</h1>
            <button className="back-button" onClick={() => navigate("/dashboard")}>Powrót</button>
            <table>
                <thead>
                    <tr>
                        <th>ID</th>
                        <th>Login</th>
                        <th>Imię</th>
                        <th>Nazwisko</th>
                        <th>Role</th>
                        <th>Akcje</th>
                    </tr>
                </thead>
                <tbody>
                    {users.map(user => (
                        <tr key={user.id}>
                            <td>{user.id}</td>
                            <td>{user.login}</td>
                            <td>{user.imie}</td>
                            <td>{user.nazwisko}</td>
                            <td>{user.roles.join(", ")}</td>
                            <td>
                                {/* Przycisk usuwania pojawi się tylko jeśli użytkownik nie ma roli ADMIN */}
                                {!user.roles.includes("ROLE_ADMIN") && (
                                    <button className="delete-button" onClick={() => handleDelete(user.id)}>
                                        Usuń
                                    </button>
                                )}
                            </td>
                        </tr>
                    ))}
                </tbody>
            </table>
        </div>
    );
};

export default AdminUsers;
