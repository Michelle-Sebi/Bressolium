import React, { lazy, Suspense } from 'react';
import { Routes, Route, Navigate, Outlet } from 'react-router-dom';
import ProtectedRoute from './ProtectedRoute';
import TopBar from '../components/layout/TopBar';

const LoginPage    = lazy(() => import('../pages/LoginPage'));
const RegisterPage = lazy(() => import('../pages/RegisterPage'));
const DashboardPage  = lazy(() => import('../pages/DashboardPage'));
const GameBoardPage  = lazy(() => import('../pages/GameBoardPage'));

function AppRoutes() {
    return (
        <Suspense fallback={<div style={{ padding: '2rem', textAlign: 'center' }}>Cargando…</div>}>
            <Routes>
                <Route path="/" element={<Navigate to="/login" replace />} />
                <Route path="/login"    element={<LoginPage />} />
                <Route path="/register" element={<RegisterPage />} />

                <Route element={<ProtectedRoute />}>
                    <Route element={<ProtectedLayout />}>
                        <Route path="/dashboard" element={<DashboardPage />} />
                        <Route path="/board"     element={<GameBoardPage />} />
                    </Route>
                </Route>
            </Routes>
        </Suspense>
    );
}

function ProtectedLayout() {
    return (
        <div className="h-screen overflow-hidden flex flex-col bg-[#f7f9f7]">
            <TopBar />
            <main className="flex-1 flex flex-col overflow-hidden">
                <Outlet />
            </main>
        </div>
    );
}

export default AppRoutes;
