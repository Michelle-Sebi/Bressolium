import { useState, useEffect } from 'react';
import ItemCard from '../../components/ui/ItemCard';
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
                color:           'rgba(0,0,0,0.5)',
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
                width="12" height="12" viewBox="0 0 12 12" fill="none"
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
        <svg width={size} height={size} viewBox="0 0 12 12" fill="none">
            <path d="M2 6l3 3 5-5" stroke={color} strokeWidth="1.8" strokeLinecap="round" strokeLinejoin="round"/>
        </svg>
    );
}

// ─── Panel principal ───────────────────────────────────────────────────────────

function VotingPanel({ gameId }) {
    const {
        technologies, inventions, userActions, currentRound,
        lastRoundResult, isLoading, isClosing, hasVoted, hasFinished, votedName,
        vote, abstain, closeRound,
    } = useVoting(gameId);

    const [selectedVote, setSelectedVote]   = useState(null);
    const [timeLeft, setTimeLeft]           = useState(null);
    const [techsOpen, setTechsOpen]         = useState(true);
    const [inventionsOpen, setInventionsOpen] = useState(true);

    const ROUND_DURATION_MS = 2 * 60 * 60 * 1000;

    useEffect(() => {
        if (!currentRound?.start_date) { setTimeLeft(null); return; }
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

    function toggleSelect(type, id, name) {
        setSelectedVote(prev => prev?.id === id ? null : { type, id, name });
    }

    async function handleVote() {
        if (!selectedVote) return;
        const voteData = selectedVote.type === 'technology'
            ? { technology_id: selectedVote.id }
            : { invention_id: selectedVote.id };
        await vote(voteData, selectedVote.name);
        setSelectedVote(null);
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
                {currentRound && (
                    (hasFinished || isExpired) ? (
                        <div style={{
                            display:         'flex',
                            alignItems:      'center',
                            gap:             '8px',
                            padding:         '8px 10px',
                            backgroundColor: '#f0f7f4',
                            borderLeft:      '3px solid #458B74',
                        }}>
                            <img
                                src={new URL('../../assets/icons/utils/Time-Rest-Time-1--Streamline-Ultimate.png', import.meta.url).href}
                                alt=""
                                aria-hidden="true"
                                style={{ width: '18px', height: '18px', objectFit: 'contain', flexShrink: 0 }}
                            />
                            <span style={{
                                fontSize:      '10px',
                                fontWeight:    'bold',
                                color:         '#2e7d5a',
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
                                <svg width="13" height="13" viewBox="0 0 12 12" fill="none">
                                    <circle cx="6" cy="6" r="5" stroke={isUrgent ? '#e6961a' : '#a0a0a0'} strokeWidth="1.5"/>
                                    <path d="M6 3v3l2 1.5" stroke={isUrgent ? '#e6961a' : '#a0a0a0'} strokeWidth="1.5" strokeLinecap="round"/>
                                </svg>
                                <span style={{
                                    fontSize:           '13px',
                                    fontWeight:         'bold',
                                    color:              isUrgent ? '#b85e00' : 'rgba(0,0,0,0.75)',
                                    fontVariantNumeric: 'tabular-nums',
                                }}>
                                    {formatTime(timeLeft)}
                                </span>
                            </div>
                            <button
                                onClick={closeRound}
                                disabled={isClosing}
                                style={{
                                    padding:         '5px 12px',
                                    backgroundColor: isClosing ? '#a0a0a0' : '#458B74',
                                    color:           '#fff',
                                    border:          'none',
                                    fontWeight:      'bold',
                                    fontSize:        '10px',
                                    textTransform:   'uppercase',
                                    letterSpacing:   '0.06em',
                                    cursor:          isClosing ? 'default' : 'pointer',
                                    flexShrink:      0,
                                }}
                            >
                                {isClosing ? 'Finalizando…' : 'Finalizar Día'}
                            </button>
                        </div>
                    )
                )}

                {/* Fila 3: acciones restantes */}
                <div style={{
                    padding:       '4px 12px 8px',
                    fontSize:      '10px',
                    fontWeight:    'bold',
                    color:         'rgba(0,0,0,0.8)',
                    textTransform: 'uppercase',
                    letterSpacing: '0.05em',
                }}>
                    Acciones restantes: {Math.max(0, 2 - userActions)}/2
                </div>

                {/* Fila 4: Votar */}
                <div style={{
                    padding:      '8px 10px',
                    borderTop:    '1px solid #e8e8e8',
                    borderBottom: '3px solid #C1CDC1',
                    display:      'flex',
                    alignItems:   'center',
                    gap:          '8px',
                }}>
                    {selectedVote && (
                        <span style={{
                            flex:         1,
                            fontSize:     '11px',
                            fontWeight:   'bold',
                            color:        'rgba(0,0,0,0.75)',
                            overflow:     'hidden',
                            textOverflow: 'ellipsis',
                            whiteSpace:   'nowrap',
                        }}>
                            {selectedVote.name}
                        </span>
                    )}
                    <button
                        onClick={handleVote}
                        disabled={!selectedVote || hasVoted}
                        style={{
                            marginLeft:      selectedVote ? 0 : 'auto',
                            padding:         '6px 16px',
                            backgroundColor: selectedVote && !hasVoted ? '#458B74' : '#a0a0a0',
                            color:           '#fff',
                            border:          'none',
                            fontWeight:      'bold',
                            fontSize:        '11px',
                            textTransform:   'uppercase',
                            letterSpacing:   '0.06em',
                            cursor:          selectedVote && !hasVoted ? 'pointer' : 'default',
                        }}
                    >
                        Votar
                    </button>
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

            {/* Resultado de la jornada anterior: empate resuelto al azar */}
            {lastRoundResult?.no_consensus && (
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
                    Sin consenso — se construyó {lastRoundResult.built_name} al azar
                </div>
            )}

            {/* Confirmación de voto */}
            {hasVoted && (
                <div style={{
                    padding:         '8px 10px',
                    backgroundColor: '#e8f5e9',
                    borderBottom:    '1px solid #458B74',
                    borderLeft:      '3px solid #458B74',
                    fontSize:        '10px',
                    fontWeight:      'bold',
                    color:           '#2e7d5a',
                    textTransform:   'uppercase',
                    letterSpacing:   '0.05em',
                }}>
                    Voto registrado{votedName ? `: ${votedName}` : ''} — esperando al resto de jugadores
                </div>
            )}

            {/* ── Tecnologías ── */}
            <SectionHeader label="Tecnologías" isOpen={techsOpen} onToggle={() => setTechsOpen(o => !o)} />
            {techsOpen && isLoading && (
                <div style={{ fontSize: '11px', color: 'rgba(0,0,0,0.4)', padding: '8px 10px', fontStyle: 'italic' }}>
                    Cargando…
                </div>
            )}
            {techsOpen && !isLoading && technologies.length === 0 && (
                <div style={{ fontSize: '10px', color: '#458B74', padding: '8px 10px', fontStyle: 'italic' }}>
                    Todas las tecnologías investigadas
                </div>
            )}
            {techsOpen && sortedTechs.map((tech) => {
                const canSelect  = tech.canVote && !hasVoted;
                const isSelected = selectedVote?.id === tech.id;
                const subtitle   = tech.canVote ? 'Lista para investigar' : missingSubtitle(tech.missing);
                return (
                    <div key={tech.id} style={{ display: 'flex', alignItems: 'stretch' }}>
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
                            <button
                                onClick={() => toggleSelect('technology', tech.id, tech.name)}
                                aria-label={isSelected ? `Deseleccionar ${tech.name}` : `Seleccionar ${tech.name} para votar`}
                                style={{
                                    flexShrink:      0,
                                    width:           '44px',
                                    backgroundColor: isSelected ? '#458B74' : '#fafafa',
                                    border:          'none',
                                    borderLeft:      '1px solid #e8e8e8',
                                    cursor:          'pointer',
                                    display:         'flex',
                                    alignItems:      'center',
                                    justifyContent:  'center',
                                }}
                            >
                                {isSelected
                                    ? <CheckIcon color="#fff" size={14} />
                                    : <div style={{ width: 16, height: 16, borderRadius: '50%', border: '1.5px solid #c0c0c0' }} />
                                }
                            </button>
                        )}
                    </div>
                );
            })}
            {techsOpen && !isLoading && technologies.length > 0 && !hasVoted && (
                <button
                    onClick={abstain}
                    aria-label="Abstenerse: no investigar esta jornada"
                    style={{
                        width:           '100%',
                        padding:         '8px 10px',
                        textAlign:       'left',
                        backgroundColor: '#fff',
                        color:           'rgba(0,0,0,0.35)',
                        border:          'none',
                        borderBottom:    '1px dashed #e8e8e8',
                        cursor:          'pointer',
                        fontSize:        '10px',
                        fontStyle:       'italic',
                        textTransform:   'uppercase',
                        letterSpacing:   '0.05em',
                    }}
                >
                    Abstenerse — no investigar esta jornada
                </button>
            )}

            {/* ── Inventos ── */}
            <SectionHeader label="Inventos" isOpen={inventionsOpen} onToggle={() => setInventionsOpen(o => !o)} />
            {inventionsOpen && !isLoading && inventions.length === 0 && (
                <div style={{ fontSize: '10px', color: 'rgba(0,0,0,0.4)', padding: '8px 10px', fontStyle: 'italic' }}>
                    Sin inventos disponibles
                </div>
            )}
            {inventionsOpen && sortedInvs.map((inv) => {
                const canSelect  = inv.canVote && !hasVoted;
                const isSelected = selectedVote?.id === inv.id;
                const iconKey    = inventionNameToKey(inv.name);
                const subtitle   = missingSubtitle(inv.missing) ?? costsSubtitle(inv.costs) ?? 'Listo para construir';
                return (
                    <div key={inv.id} style={{ display: 'flex', alignItems: 'stretch' }}>
                        <div style={{ flex: 1, minWidth: 0 }}>
                            <ItemCard
                                iconSrc={INVENTION_ICON_MAP[iconKey] ?? ''}
                                iconBgColor={INVENTION_COLORS[iconKey] ?? '#a0a0a0'}
                                name={inv.name}
                                subtitle={subtitle}
                                quantity={inv.quantity > 0 ? inv.quantity : undefined}
                                isActive={inv.canVote}
                            />
                        </div>
                        {canSelect && (
                            <button
                                onClick={() => toggleSelect('invention', inv.id, inv.name)}
                                aria-label={isSelected ? `Deseleccionar ${inv.name}` : `Seleccionar ${inv.name} para votar`}
                                style={{
                                    flexShrink:      0,
                                    width:           '44px',
                                    backgroundColor: isSelected ? '#458B74' : '#fafafa',
                                    border:          'none',
                                    borderLeft:      '1px solid #e8e8e8',
                                    cursor:          'pointer',
                                    display:         'flex',
                                    alignItems:      'center',
                                    justifyContent:  'center',
                                }}
                            >
                                {isSelected
                                    ? <CheckIcon color="#fff" size={14} />
                                    : <div style={{ width: 16, height: 16, borderRadius: '50%', border: '1.5px solid #c0c0c0' }} />
                                }
                            </button>
                        )}
                    </div>
                );
            })}

            </div>{/* fin lista scrollable */}
            </div>{/* fin zona inferior */}
        </div>
    );
}

export default VotingPanel;
