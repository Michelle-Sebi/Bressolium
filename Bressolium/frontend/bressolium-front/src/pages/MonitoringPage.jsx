import { useEffect, useState } from 'react';
import {
    BarChart, Bar, XAxis, YAxis, Tooltip, ResponsiveContainer,
    PieChart, Pie, Cell, Legend,
} from 'recharts';
import httpClient from '../lib/httpClient';

const C_BROWN  = '#a0a0a0';
const C_GREEN  = 'var(--color-bgreen)';
const C_RED    = '#CD4F39';
const C_GRAY   = '#C1CDC1';
const C_BG     = '#f7f9f7';
const C_BORDER = '#C1CDC1';

const PIE_COLORS = { WAITING: C_GRAY, ACTIVE: C_GREEN, FINISHED: C_BROWN };

// ─── Small metric card ────────────────────────────────────────────────────────

function Card({ label, testId, value, accent }) {
    return (
        <div style={{ border: `2px solid ${accent ?? C_BORDER}`, padding: '1rem 1.25rem', backgroundColor: C_BG, flex: '1 1 160px' }}>
            <div style={{ fontSize: '11px', fontWeight: 'bold', textTransform: 'uppercase', letterSpacing: '0.08em', color: C_BROWN, marginBottom: '6px' }}>
                {label}
            </div>
            <div data-testid={testId} style={{ fontSize: '22px', fontWeight: 'bold', color: accent ?? C_BROWN }}>
                {value}
            </div>
        </div>
    );
}

// ─── Section header ───────────────────────────────────────────────────────────

function SectionTitle({ children }) {
    return (
        <h2 style={{ fontSize: '13px', fontWeight: 'bold', textTransform: 'uppercase', letterSpacing: '0.1em', color: C_BROWN, margin: '2rem 0 1rem', borderBottom: `2px solid ${C_BORDER}`, paddingBottom: '0.4rem' }}>
            {children}
        </h2>
    );
}

// ─── Main page ────────────────────────────────────────────────────────────────

export default function MonitoringPage() {
    const [stats, setStats]     = useState(null);
    const [loading, setLoading] = useState(true);
    const [error, setError]     = useState(null);

    useEffect(() => {
        httpClient.get('/stats')
            .then(res => setStats(res?.data?.data ?? res?.data ?? res))
            .catch(err => setError(err?.message ?? 'Error'))
            .finally(() => setLoading(false));
    }, []);

    if (loading) {
        return (
            <div data-testid="monitoring-loading" role="status"
                style={{ padding: '2rem', color: C_BROWN, fontWeight: 'bold', textTransform: 'uppercase', letterSpacing: '0.08em' }}>
                Cargando…
            </div>
        );
    }

    if (error) {
        return (
            <div data-testid="monitoring-error" role="alert"
                style={{ padding: '2rem', border: `2px solid ${C_RED}`, color: C_RED, fontWeight: 'bold', textTransform: 'uppercase', letterSpacing: '0.08em' }}>
                Error al cargar métricas: {error}
            </div>
        );
    }

    const sys  = stats?.system ?? {};
    const game = stats?.game   ?? {};

    const pieData = [
        { name: 'Esperando', value: game.waiting_games  ?? 0, status: 'WAITING'  },
        { name: 'Activas',   value: game.active_games   ?? 0, status: 'ACTIVE'   },
        { name: 'Acabadas',  value: game.finished_games ?? 0, status: 'FINISHED' },
    ];

    const players = game.players ?? [];

    return (
        <div style={{ padding: '2rem', backgroundColor: C_BG, minHeight: '100%', overflowY: 'auto' }}>
            <h1 style={{ fontSize: '18px', fontWeight: 'bold', textTransform: 'uppercase', letterSpacing: '0.1em', color: C_BROWN, marginBottom: '0.25rem' }}>
                Monitoreo
            </h1>

            {/* ── Sistema ── */}
            <SectionTitle>Sistema</SectionTitle>
            <div style={{ display: 'flex', flexWrap: 'wrap', gap: '1rem' }}>
                <Card label="Uptime (s)"       testId="metric-uptime"    value={sys.uptime ?? 0} />
                <Card label="Base de datos"    testId="metric-database"  value={sys.database ?? '—'} accent={sys.database === 'ok' ? C_GREEN : C_RED} />
                <Card label="Peticiones / min" testId="metric-requests"  value={sys.requests_per_minute ?? 0} />
                <Card label="Errores / min"    testId="metric-errors"    value={sys.errors_per_minute ?? 0} accent={(sys.errors_per_minute ?? 0) > 0 ? C_RED : undefined} />
                <Card label="Latencia p95 (ms)" testId="metric-latency" value={sys.latency_p95 ?? 0} />
            </div>

            {/* ── Juego ── */}
            <SectionTitle>Juego</SectionTitle>
            <div style={{ display: 'flex', flexWrap: 'wrap', gap: '1rem', marginBottom: '2rem' }}>
                <Card label="Partidas totales"  testId="metric-total-games"    value={game.total_games    ?? 0} />
                <Card label="Jugadores"         testId="metric-total-players"  value={game.total_players  ?? 0} />
                <Card label="Rondas jugadas"    testId="metric-total-rounds"   value={game.total_rounds   ?? 0} />
            </div>

            <div style={{ display: 'flex', flexWrap: 'wrap', gap: '2rem', alignItems: 'flex-start' }}>

                {/* Pie — estado de partidas */}
                <div style={{ flex: '0 0 280px' }}>
                    <div style={{ fontSize: '11px', fontWeight: 'bold', textTransform: 'uppercase', letterSpacing: '0.08em', color: C_BROWN, marginBottom: '0.75rem' }}>
                        Estado de partidas
                    </div>
                    <PieChart width={260} height={220}>
                        <Pie data={pieData} dataKey="value" nameKey="name" cx="50%" cy="50%" outerRadius={80} label={({ name, value }) => value > 0 ? `${name}: ${value}` : ''}>
                            {pieData.map(entry => (
                                <Cell key={entry.status} fill={PIE_COLORS[entry.status]} />
                            ))}
                        </Pie>
                        <Legend />
                        <Tooltip />
                    </PieChart>
                </div>

                {/* Bar — partidas por jugador */}
                {players.length > 0 && (
                    <div style={{ flex: '1 1 320px' }}>
                        <div style={{ fontSize: '11px', fontWeight: 'bold', textTransform: 'uppercase', letterSpacing: '0.08em', color: C_BROWN, marginBottom: '0.75rem' }}>
                            Partidas por jugador
                        </div>
                        <ResponsiveContainer width="100%" height={220}>
                            <BarChart data={players} margin={{ top: 4, right: 8, left: 0, bottom: 40 }}>
                                <XAxis dataKey="name" tick={{ fontSize: 11, fill: C_BROWN }} angle={-35} textAnchor="end" interval={0} />
                                <YAxis allowDecimals={false} tick={{ fontSize: 11, fill: C_BROWN }} />
                                <Tooltip />
                                <Bar dataKey="games_count" name="Partidas" fill={C_GREEN} />
                            </BarChart>
                        </ResponsiveContainer>
                    </div>
                )}
            </div>
        </div>
    );
}
