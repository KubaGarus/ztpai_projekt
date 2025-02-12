import React, { useState } from "react";
import { BrowserRouter as Router, Routes, Route } from "react-router-dom";
import LoginForm from "./components/LoginForm";
import RegisterForm from "./components/RegisterForm";
import Dashboard from "./components/Dashboard";
import AdminUsers from "./components/AdminUsers";

const App = () => {
    const [showRegister, setShowRegister] = useState(false);

    return (
        <Router>
            <Routes>
                <Route path="/login" element={<LoginForm setShowRegister={setShowRegister} />} />
                <Route path="/register" element={<RegisterForm setShowRegister={setShowRegister} />} />
                <Route path="/dashboard" element={<Dashboard />} />
                <Route path="/admin/users" element={<AdminUsers />} />
                <Route path="*" element={<LoginForm setShowRegister={setShowRegister} />} />
            </Routes>
        </Router>
    );
};

export default App;
