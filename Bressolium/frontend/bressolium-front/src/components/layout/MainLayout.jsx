import React from 'react';
import { Outlet, Navigate } from 'react-router-dom';
import { useSelector } from 'react-redux';
import TopBar from './TopBar';

/**
 * @component MainLayout
 * @description Layout principal que incluye la TopBar y protege las rutas.
 * Si el usuario no está autenticado, redirige al login.
 */
const MainLayout = () => {
  const { status } = useSelector((state) => state.auth);
  
  // Verificamos si hay sesión activa
  // En este MVP consideramos LOGGED_IN como estado válido.
  if (status !== 'LOGGED_IN') {
    return <Navigate to="/login" replace />;
  }

  return (
    <div className="h-screen overflow-hidden flex flex-col bg-[#f7f9f7]">
      <TopBar />
      <main className="flex-1 flex flex-col overflow-hidden">
        <Outlet />
      </main>
    </div>
  );
};

export default MainLayout;
