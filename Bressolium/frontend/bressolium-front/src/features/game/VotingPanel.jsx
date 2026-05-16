import { useState, useEffect } from 'react';
import ItemCard from '../../components/ui/ItemCard';
import Button from '../../components/ui/Button';
import { useVoting } from './useVoting';
import { INVENTION_COLORS, INVENTION_ICON_MAP, inventionNameToKey } from '../../constants/inventionAssets';
import { TECHNOLOGY_COLORS, TECHNOLOGY_ICON_MAP, technologyNameToKey } from '../../constants/technologyAssets';

// ─── Subtítulos ────────────────────────────────────────────────────────────────

function missingSubtitle(missing) {
    if (!missing || missing.length === 0) return null;
    return 'Falta: ' + missing
        .map(m => m.required > 1 ? `${m.name} ×${m.required}` : m.name)
        .join(', ');
}

function costsSubtitle(costs) {
    if (!costs || costs.length === 0) return null;
    return costs
        .map(c => c.required > 1 ? `${c.name} ×${c.required}` : c.name)
        .join(', ');
}

// ─── Cabecera de sección ───────────────────────────────────────────────────────

function SectionHeader({ label, isOpen, onToggle }) {
    return (
        <button
            onClick={onToggle}
            aria-expanded={isOpen}
            style={{
                width:           '100%',
                display:         'flex',
                justifyContent:  'space-between',
                alignItems:      'center',
                padding:         '8px 10px',
                backgroundColor: '#f0f0f0',
                color:           'rgba(0,0,0,0.75)',
                fontWeight:      'bold',
                textTransform:   'uppercase',
                letterSpacing:   '0.08em',
                fontSize:        '10px',
                border:          'none',
                borderBottom:    '1px solid #e8e8e8',
                cursor:          'pointer',
                textAlign:       'left',
            }}
        >
            {label}
            <svg
                width="12" height="12" viewBox="0 0 12 12" fill="none" aria-hidden="true"
                style={{ transform: isOpen ? 'rotate(180deg)' : 'rotate(0deg)', transition: 'transform 0.2s', flexShrink: 0 }}
            >
                <path d="M2 4l4 4 4-4" stroke="currentColor" strokeWidth="1.5" strokeLinecap="round" strokeLinejoin="round"/>
            </svg>
        </button>
    );
}

// ─── Icono check SVG ──────────────────────────────────────────────────────────

function CheckIcon({ color = '#fff', size = 12 }) {
    return (
        <svg width={size} height={size} viewBox="0 0 12 12" fill="none" aria-hidden="true">
            <path d="M2 6l3 3 5-5" stroke={color} strokeWidth="1.8" strokeLinecap="round" strokeLinejoin="round"/>
        </svg>
    );
}

// ─── Panel principal ───────────────────────────────────────────────────────────

function VotingPanel({ gameId, playersCount: playersCountProp }) {
    const {
        technologies, inventions, userActions, currentRound,
        lastRoundResult, isLoading, isClosing,
        hasVotedTech, hasVotedInv, hasFinished, votedName,
        playersCount: playersCountSync,
        vote, closeRound,
    } = useVoting(gameId);

    const playersCount = playersCountSync ?? playersCountProp ?? 1;

    const [selectedTech, setSelectedTech]     = useState(null);
    const [selectedInv,  setSelectedInv]      = useState(null);
    const [isVoting,     setIsVoting]         = useState(false);
    const [timeLeft, setTimeLeft]             = useState(null);
    const [techsOpen, setTechsOpen]           = useState(true);
    const [inventionsOpen, setInventionsOpen] = useState(true);

    const ROUND_DURATION_MS = 2 * 60 * 60 * 1000;

    useEffect(() => {
        if (!currentRound?.start_date) return;
        // Normaliza "2026-05-11 10:00:00" → "2026-05-11T10:00:00Z" para parsing correcto
        const iso = String(currentRound.start_date).replace(' ', 'T').replace(/([^Z+\-]\d{2}:\d{2}:\d{2})$/, '$1Z');
        const endsAt = new Date(iso).getTime() + ROUND_DURATION_MS;
        if (isNaN(endsAt)) { setTimeLeft(null); return; }
        const tick = () => setTimeLeft(Math.max(0, endsAt - Date.now()));
        tick();
        const id = setInterval(tick, 1000);
        return () => clearInterval(id);
    }, [currentRound?.start_date]);

    function formatTime(ms) {
        if (ms === null) return '--:--';
        const total = Math.floor(ms / 1000);
        const h = Math.floor(total / 3600);
        const m = Math.floor((total % 3600) / 60);
        const s = total % 60;
        return h > 0
            ? `${h}h ${String(m).padStart(2, '0')}m`
            : `${String(m).padStart(2, '0')}:${String(s).padStart(2, '0')}`;
    }

    const isUrgent   = timeLeft !== null && timeLeft > 0 && timeLeft < 30 * 60 * 1000;
    const isExpired  = timeLeft === 0;

    const sortedTechs = [...technologies].sort((a, b) => (b.canVote ? 1 : 0) - (a.canVote ? 1 : 0));
    const sortedInvs  = [...inventions].sort((a, b) => (b.canVote ? 1 : 0) - (a.canVote ? 1 : 0));

    const hasAnyPendingVote = (selectedTech && !hasVotedTech) || (selectedInv && !hasVotedInv);

    async function handleVote() {
        if (!hasAnyPendingVote || isVoting) return;
        setIsVoting(true);
        try {
            if (selectedTech && !hasVotedTech) {
                await vote({ technology_id: selectedTech.id }, selectedTech.name);
                setSelectedTech(null);
            }
            if (selectedInv && !hasVotedInv) {
                await vote({ invention_id: selectedInv.id }, selectedInv.name);
                setSelectedInv(null);
            }
        } finally {
            setIsVoting(false);
        }
    }

    return (
        <div style={{ display: 'flex', flexDirection: 'column', height: '100%', backgroundColor: '#fff' }}>

            {/* ── Tarjeta fija superior ── */}
            <div style={{
                flexShrink:      0,
                backgroundColor: '#fff',
            }}>
                {/* Fila 1: Jornada N */}
                {currentRound && (
                    <div style={{ padding: '10px 12px', display: 'flex', alignItems: 'center', gap: '8px', borderBottom: '3px solid #C1CDC1' }}>
                        <img
                            src={new URL('../../assets/icons/utils/Toolbox-Open-2--Streamline-Ultimate.png', import.meta.url).href}
                            alt=""
                            aria-hidden="true"
                            style={{ width: '20px', height: '20px', objectFit: 'contain' }}
                        />
                        <span style={{
                            fontSize:      '15px',
                            fontWeight:    'bold',
                            color:         'rgba(0,0,0,0.85)',
                            textTransform: 'uppercase',
                            letterSpacing: '0.06em',
                        }}>
                            Jornada {currentRound.number}
                        </span>
                    </div>
                )}

                {/* Fila 2: tiempo restante + botón / estado de espera */}
                {(hasFinished || isExpired) ? (
                    <div
                        aria-live="polite"
                        style={{
                            display:         'flex',
                            alignItems:      'center',
                            gap:             '8px',
                            padding:         '8px 10px',
                            backgroundColor: '#f0f7f4',
                        }}
                    >
                        <img
                            src={new URL('../../assets/icons/utils/Time-Rest-Time-1--Streamline-Ultimate.png', import.meta.url).href}
                            alt=""
                            aria-hidden="true"
                            style={{ width: '18px', height: '18px', objectFit: 'contain', flexShrink: 0 }}
                        />
                        <span style={{
                            fontSize:      '10px',
                            fontWeight:    'bold',
                            color:         'var(--color-bgreen)',
                            textTransform: 'uppercase',
                            letterSpacing: '0.05em',
                        }}>
                            Esperando a que empiece un nuevo día…
                        </span>
                    </div>
                ) : (
                    <div style={{
                        display:         'flex',
                        alignItems:      'center',
                        justifyContent:  'space-between',
                        padding:         '8px 10px',
                        backgroundColor: isUrgent ? '#fff3e0' : '#fafafa',
                        gap:             '8px',
                    }}>
                        <div style={{ display: 'flex', alignItems: 'center', gap: '6px' }}>
                            <svg width="13" height="13" viewBox="0 0 12 12" fill="none" aria-hidden="true">
                                <circle cx="6" cy="6" r="5" stroke={isUrgent ? '#e6961a' : '#a0a0a0'} strokeWidth="1.5"/>
                                <path d="M6 3v3l2 1.5" stroke={isUrgent ? '#e6961a' : '#a0a0a0'} strokeWidth="1.5" strokeLinecap="round"/>
                            </svg>
                            <span
                                role="timer"
                                aria-label={`Tiempo restante: ${formatTime(timeLeft)}`}
                                style={{
                                    fontSize:           '13px',
                                    fontWeight:         'bold',
                                    color:              isUrgent ? '#b85e00' : 'rgba(0,0,0,0.75)',
                                    fontVariantNumeric: 'tabular-nums',
                                }}
                            >
                                {formatTime(timeLeft)}
                            </span>
                        </div>
                        <Button
                            onClick={closeRound}
                            disabled={isClosing}
                            style={{
                                width:         'auto',
                                padding:       '5px 12px',
                                fontSize:      '10px',
                                textTransform: 'uppercase',
                                letterSpacing: '0.06em',
                                flexShrink:    0,
                            }}
                        >
                            {isClosing ? 'Finalizando…' : 'Finalizar Día'}
                        </Button>
                    </div>
                )}

                {/* Fila 3: acciones restantes + votar */}
                <div style={{
                    display:       'flex',
                    alignItems:    'center',
                    justifyContent:'space-between',
                    padding:       '4px 12px 8px',
                    borderBottom:  '3px solid #C1CDC1',
                }}>
                    <div style={{ display: 'flex', alignItems: 'center', gap: '6px' }}>
                        <span style={{ fontSize: '10px', fontWeight: 'bold', color: 'rgba(0,0,0,0.8)', textTransform: 'uppercase', letterSpacing: '0.05em' }}>
                            Acciones restantes: {Math.max(0, 2 - userActions)}/2
                        </span>
                        {hasVotedTech && (
                            <span style={{ fontSize: '9px', color: 'var(--color-bgreen)', fontWeight: 'bold', backgroundColor: '#e8f5e9', padding: '1px 5px', border: '1px solid #b2dfdb' }}>
                                ✓ Tec
                            </span>
                        )}
                        {hasVotedInv && (
                            <span style={{ fontSize: '9px', color: 'var(--color-bgreen)', fontWeight: 'bold', backgroundColor: '#e8f5e9', padding: '1px 5px', border: '1px solid #b2dfdb' }}>
                                ✓ Inv
                            </span>
                        )}
                    </div>
                    {!(hasVotedTech && hasVotedInv) && (
                        <button
                            onClick={handleVote}
                            disabled={!hasAnyPendingVote || isVoting}
                            style={{
                                padding:         '4px 12px',
                                backgroundColor: hasAnyPendingVote && !isVoting ? 'var(--color-bgreen)' : '#c0c0c0',
                                color:           '#fff',
                                border:          'none',
                                fontWeight:      'bold',
                                fontSize:        '10px',
                                textTransform:   'uppercase',
                                letterSpacing:   '0.06em',
                                cursor:          hasAnyPendingVote && !isVoting ? 'pointer' : 'default',
                                flexShrink:      0,
                            }}
                        >
                            {isVoting ? 'Votando…' : 'Votar'}
                        </button>
                    )}
                </div>
            </div>

            {/* ── Zona inferior: título estático + lista scrollable ── */}
            <div style={{ flex: 1, display: 'flex', flexDirection: 'column', backgroundColor: '#C1CDC1', overflow: 'hidden' }}>

            <div style={{
                flexShrink:      0,
                padding:         '10px 14px',
                marginTop:       '10px',
                backgroundColor: '#fff',
                color:           'rgba(0,0,0,0.8)',
                fontWeight:      'bold',
                textTransform:   'uppercase',
                letterSpacing:   '0.08em',
                fontSize:        '13px',
                borderBottom:    '3px solid #C1CDC1',
            }}>
                Votaciones
            </div>

            {/* ── Lista scrollable ── */}
            <div style={{ flex: 1, overflowY: 'auto', backgroundColor: '#C1CDC1' }}>

            {/* Resultados de la jornada anterior: empates resueltos al azar */}
            <div aria-live="polite" aria-atomic="true">
            {lastRoundResult?.no_consensus_inv && (
                <div style={{
                    padding:         '8px 10px',
                    backgroundColor: '#fff3e0',
                    borderBottom:    '1px solid #e6961a',
                    borderLeft:      '3px solid #e6961a',
                    fontSize:        '10px',
                    fontWeight:      'bold',
                    color:           '#b85e00',
                    textTransform:   'uppercase',
                    letterSpacing:   '0.05em',
                }}>
                    Sin consenso — se construyó el invento {lastRoundResult.built_inv_name} al azar
                </div>
            )}
            {lastRoundResult?.no_consensus_tech && (
                <div style={{
                    padding:         '8px 10px',
                    backgroundColor: '#fff3e0',
                    borderBottom:    '1px solid #e6961a',
                    borderLeft:      '3px solid #e6961a',
                    fontSize:        '10px',
                    fontWeight:      'bold',
                    color:           '#b85e00',
                    textTransform:   'uppercase',
                    letterSpacing:   '0.05em',
                }}>
                    Sin consenso — se investigó la tecnología {lastRoundResult.built_tech_name} al azar
                </div>
            )}
            </div>

            {/* ── Tecnologías ── */}
            <SectionHeader label="Tecnologías" isOpen={techsOpen} onToggle={() => setTechsOpen(o => !o)} />
            {techsOpen && isLoading && (
                <div style={{ fontSize: '11px', color: '#555', padding: '8px 10px', fontStyle: 'italic' }}>
                    Cargando…
                </div>
            )}
            {techsOpen && !isLoading && technologies.length === 0 && (
                <div style={{ fontSize: '10px', color: 'var(--color-bgreen)', padding: '8px 10px', fontStyle: 'italic' }}>
                    Todas las tecnologías investigadas
                </div>
            )}
            {techsOpen && sortedTechs.map((tech) => {
                const canSelect  = tech.canVote && !hasVotedTech;
                const isSelected = selectedTech?.id === tech.id;
                const subtitle   = tech.canVote ? 'Lista para investigar' : missingSubtitle(tech.missing);
                return (
                    <button
                        key={tech.id}
                        onClick={() => { if (canSelect) setSelectedTech(prev => prev?.id === tech.id ? null : { id: tech.id, name: tech.name }); }}
                        disabled={!canSelect}
                        aria-label={canSelect ? (isSelected ? `Deseleccionar ${tech.name}` : `Seleccionar ${tech.name} para votar`) : tech.name}
                        style={{
                            display:    'flex',
                            alignItems: 'stretch',
                            width:      '100%',
                            background: 'none',
                            border:     'none',
                            padding:    0,
                            cursor:     canSelect ? 'pointer' : 'default',
                            textAlign:  'left',
                        }}
                    >
                        <div style={{ flex: 1, minWidth: 0 }}>
                            <ItemCard
                                iconSrc={TECHNOLOGY_ICON_MAP[technologyNameToKey(tech.name)] ?? ''}
                                iconBgColor={TECHNOLOGY_COLORS[technologyNameToKey(tech.name)] ?? '#a0a0a0'}
                                name={tech.name}
                                subtitle={subtitle}
                                isActive={tech.canVote}
                            />
                        </div>
                        {canSelect && (
                            <div style={{
                                flexShrink:      0,
                                width:           '44px',
                                backgroundColor: isSelected ? 'var(--color-bgreen)' : '#fafafa',
                                borderLeft:      '1px solid #e8e8e8',
                                display:         'flex',
                                alignItems:      'center',
                                justifyContent:  'center',
                            }}>
                                {isSelected
                                    ? <CheckIcon color="#fff" size={14} />
                                    : <div style={{ width: 16, height: 16, borderRadius: '50%', border: '1.5px solid #c0c0c0' }} />
                                }
                            </div>
                        )}
                    </button>
                );
            })}
            {/* ── Inventos ── */}
            <SectionHeader label="Inventos" isOpen={inventionsOpen} onToggle={() => setInventionsOpen(o => !o)} />
            {inventionsOpen && !isLoading && inventions.length === 0 && (
                <div style={{ fontSize: '10px', color: '#555', padding: '8px 10px', fontStyle: 'italic' }}>
                    Sin inventos disponibles
                </div>
            )}
            {inventionsOpen && sortedInvs.map((inv) => {
                const canSelect  = inv.canVote && !hasVotedInv;
                const isSelected = selectedInv?.id === inv.id;
                const iconKey    = inventionNameToKey(inv.name);
                const subtitle   = missingSubtitle(inv.missing) ?? costsSubtitle(inv.costs) ?? 'Listo para construir';
                const itemCard   = (
                    <ItemCard
                        iconSrc={INVENTION_ICON_MAP[iconKey] ?? ''}
                        iconBgColor={INVENTION_COLORS[iconKey] ?? '#a0a0a0'}
                        name={inv.name}
                        subtitle={subtitle}
                        quantity={inv.quantity > 0 ? inv.quantity : undefined}
                        isActive={inv.canVote}
                    />
                );
                if (inv.quantity === 0) {
                    return (
                        <button
                            key={inv.id}
                            onClick={() => { if (canSelect) setSelectedInv(prev => prev?.id === inv.id ? null : { id: inv.id, name: inv.name }); }}
                            disabled={!canSelect}
                            aria-label={canSelect ? (isSelected ? `Deseleccionar ${inv.name}` : `Seleccionar ${inv.name} para votar`) : inv.name}
                            style={{
                                display:    'flex',
                                alignItems: 'stretch',
                                width:      '100%',
                                background: 'none',
                                border:     'none',
                                padding:    0,
                                cursor:     canSelect ? 'pointer' : 'default',
                                textAlign:  'left',
                            }}
                        >
                            <div style={{ flex: 1, minWidth: 0 }}>{itemCard}</div>
                            {canSelect && (
                                <div style={{
                                    flexShrink:      0,
                                    width:           '44px',
                                    backgroundColor: isSelected ? 'var(--color-bgreen)' : '#fafafa',
                                    borderLeft:      '1px solid #e8e8e8',
                                    display:         'flex',
                                    alignItems:      'center',
                                    justifyContent:  'center',
                                }}>
                                    {isSelected
                                        ? <CheckIcon color="#fff" size={14} />
                                        : <div style={{ width: 16, height: 16, borderRadius: '50%', border: '1.5px solid #c0c0c0' }} />
                                    }
                                </div>
                            )}
                        </button>
                    );
                }
                return (
                    <div key={inv.id} style={{ display: 'flex', alignItems: 'stretch' }}>
                        <div style={{ flex: 1, minWidth: 0 }}>{itemCard}</div>
                    </div>
                );
            })}

            </div>{/* fin lista scrollable */}
            </div>{/* fin zona inferior */}
        </div>
    );
}

export default VotingPanel;
