import React from 'react';
import { Routes, Route, Navigate } from 'react-router-dom';
import Login from './features/auth/Login';
import Register from './features/auth/Register';

function App() {
  return (
    <div className="min-h-screen bg-[#f7f9f7]">
      <Routes>
        <Route path="/" element={<Navigate to="/login" />} />
        <Route path="/login" element={<Login />} />
        <Route path="/register" element={<Register />} />
        {/* Placeholder para la vista de logueado (Tarea 5 en el futuro) */}
        <Route path="/dashboard" element={<div className="p-8 text-center text-bbrown">Dashboard Placeholder</div>} />
      </Routes>
    </div>
  );
}

export default App;
