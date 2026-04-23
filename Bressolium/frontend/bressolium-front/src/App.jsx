import { Routes, Route, Navigate } from 'react-router-dom';
import Login from './features/auth/Login';
import Register from './features/auth/Register';
import Dashboard from './features/dashboard/Dashboard';
import GameBoard  from './features/game/GameBoard';

import MainLayout from './components/layout/MainLayout';

function App() {
  return (
    <div className="min-h-screen bg-[#f7f9f7]">
      <Routes>
        <Route path="/" element={<Navigate to="/login" />} />
        <Route path="/login" element={<Login />} />
        <Route path="/register" element={<Register />} />
        
        {/* Rutas Protegidas bajo MainLayout */}
        <Route element={<MainLayout />}>
          <Route path="/dashboard" element={<Dashboard />} />
          <Route path="/board" element={<GameBoard />} />
        </Route>
      </Routes>
    </div>
  );
}

export default App;
