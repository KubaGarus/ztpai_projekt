import React from "react";
import { BrowserRouter as Router, Routes, Route } from "react-router-dom";
import LoginForm from "./components/LoginForm";

const App = () => (
    <Router>
        <Routes>
            <Route path="/" element={<LoginForm />} />
        </Routes>
    </Router>
);

export default App;
