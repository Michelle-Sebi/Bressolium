import { useState, useEffect } from 'react';
import BoardGrid      from '../board/BoardGrid';
import InventoryPanel from '../inventory/InventoryPanel';
import VotingPanel    from './VotingPanel';
import VictoryModal   from './VictoryModal';
import { useGames }   from './useGames';
import { useVoting }  from './useVoting';
import { useInventory } from '../inventory/useInventory';
import { TECHNOLOGY_ICON_MAP, technologyNameToKey, TECHNOLOGY_COLORS } from '../../constants/technologyAssets';
import { INVENTION_ICON_MAP, inventionNameToKey, INVENTION_COLORS }     from '../../constants/inventionAssets';
import { MATERIAL_ICON_MAP, MATERIAL_COLORS }                           from '../../constants/materialAssets';

// ─── Mobile detection ─────────────────────────────────────────────────────────

function useIsMobile() {
    const [isMobile, setIsMobile] = useState(() => window.innerWidth < 768);
    useEffect(() => {
        const mq      = window.matchMedia('(max-width: 767px)');
        const handler = (e) => setIsMobile(e.matches);
        mq.addEventListener('change', handler);
        return () => mq.removeEventListener('change', handler);
    }, []);
    return isMobile;
}

// ─── Helpers compartidos ───────────────────────────────────────────────────────

const ROUND_DURATION_MS = 2 * 60 * 60 * 1000;

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

// ─── Icono compacto para la vista móvil ───────────────────────────────────────

function IconChip({ iconSrc, bgColor, name, isSelected, isVoted, canSelect, quantity, onClick }) {
    return (
        <button
            onClick={onClick}
            disabled={!canSelect}
            style={{
                display:         'flex',
                flexDirection:   'column',
                alignItems:      'center',
                gap:             '3px',
                padding:         '4px',
                border:          isSelected ? '2px solid #458B74' : '2px solid transparent',
                backgroundColor: isSelected ? '#e8f5e9' : 'transparent',
                cursor:          canSelect ? 'pointer' : 'default',
                opacity:         (!canSelect && !isVoted) ? 0.45 : 1,
                borderRadius:    '4px',
                minWidth:        '48px',
            }}
        >
            <div style={{
                width:           '36px',
                height:          '36px',
                borderRadius:    '6px',
                backgroundColor: bgColor,
                display:         'flex',
                alignItems:      'center',
                justifyContent:  'center',
                position:        'relative',
                flexShrink:      0,
            }}>
                {iconSrc && (
                    <img src={iconSrc} alt="" aria-hidden="true"
                        style={{ width: '65%', height: '65%', objectFit: 'contain' }} />
                )}
                {quantity != null && quantity > 0 && (
                    <span style={{
                        position:        'absolute',
                        top:             '-4px',
                        right:           '-4px',
                        fontSize:        '8px',
                        backgroundColor: '#fff',
                        color:           '#333',
                        borderRadius:    '50%',
                        width:           '14px',
                        height:          '14px',
                        display:         'flex',
                        alignItems:      'center',
                        justifyContent:  'center',
                        fontWeight:      'bold',
                        border:          '1px solid #ddd',
                    }}>
                        {quantity}
                    </span>
                )}
                {isVoted && (
                    <span style={{
                        position:        'absolute',
                        inset:           0,
                        borderRadius:    '6px',
                        backgroundColor: 'rgba(69,139,116,0.65)',
                        display:         'flex',
                        alignItems:      'center',
                        justifyContent:  'center',
                        fontSize:        '14px',
                        color:           '#fff',
                    }}>✓</span>
                )}
            </div>
            <span style={{
                fontSize:      '7px',
                textAlign:     'center',
                fontWeight:    'bold',
                textTransform: 'uppercase',
                lineHeight:    1.1,
                color:         'rgba(0,0,0,0.65)',
                maxWidth:      '48px',
                overflow:      'hidden',
                textOverflow:  'ellipsis',
                whiteSpace:    'nowrap',
            }}>
                {name}
            </span>
        </button>
    );
}

// ─── Layout móvil ─────────────────────────────────────────────────────────────

function MobileGameBoard({ currentGame }) {
    const {
        technologies, inventions, currentRound,
        hasVotedTech, hasVotedInv, hasFinished,
        isClosing, vote, closeRound,
    } = useVoting(currentGame?.id);

    const { gameStatus } = useVoting(currentGame?.id);

    const { materials, inventions: ownedInvs, technologies: ownedTechs } = useInventory(currentGame?.id);

    const [selectedTech, setSelectedTech] = useState(null);
    const [selectedInv,  setSelectedInv]  = useState(null);
    const [isVoting,     setIsVoting]     = useState(false);
    const [timeLeft,     setTimeLeft]     = useState(null);

    useEffect(() => {
        if (!currentRound?.start_date) { setTimeLeft(null); return; }
        const iso    = String(currentRound.start_date).replace(' ', 'T').replace(/([^Z+\-]\d{2}:\d{2}:\d{2})$/, '$1Z');
        const endsAt = new Date(iso).getTime() + ROUND_DURATION_MS;
        if (isNaN(endsAt)) { setTimeLeft(null); return; }
        const tick = () => setTimeLeft(Math.max(0, endsAt - Date.now()));
        tick();
        const id = setInterval(tick, 1000);
        return () => clearInterval(id);
    }, [currentRound?.start_date]);

    const isUrgent          = timeLeft !== null && timeLeft > 0 && timeLeft < 30 * 60 * 1000;
    const isExpired         = timeLeft === 0;
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
        <div style={{ display: 'flex', flexDirection: 'column', height: 'calc(100vh - 4rem)', backgroundColor: '#C1CDC1', overflow: 'hidden' }}>
            {gameStatus === 'FINISHED' && <VictoryModal teamName={currentGame?.name} />}

            {/* Tablero — limitado a 55vh para dejar espacio a los paneles */}
            <div style={{ flexShrink: 0, display: 'flex', justifyContent: 'center', backgroundColor: '#C1CDC1' }}>
                <div style={{ width: 'min(100%, 55vh)' }}>
                    <BoardGrid />
                </div>
            </div>

            {/* Tira de jornada */}
            {currentRound && (
                <div style={{ flexShrink: 0, backgroundColor: '#fff', borderBottom: '3px solid #C1CDC1' }}>
                    {(hasFinished || isExpired) ? (
                        <div style={{ display: 'flex', alignItems: 'center', gap: '6px', padding: '6px 10px', backgroundColor: '#f0f7f4' }}>
                            <img
                                src={new URL('../../assets/icons/utils/Time-Rest-Time-1--Streamline-Ultimate.png', import.meta.url).href}
                                alt="" aria-hidden="true"
                                style={{ width: '16px', height: '16px', objectFit: 'contain', flexShrink: 0 }}
                            />
                            <span style={{ fontSize: '10px', fontWeight: 'bold', color: 'var(--color-bgreen)', textTransform: 'uppercase', letterSpacing: '0.05em' }}>
                                Esperando un nuevo día…
                            </span>
                        </div>
                    ) : (
                        <div style={{ display: 'flex', alignItems: 'center', justifyContent: 'space-between', padding: '6px 10px', gap: '6px', flexWrap: 'wrap' }}>
                            <div style={{ display: 'flex', alignItems: 'center', gap: '8px' }}>
                                <span style={{ fontSize: '13px', fontWeight: 'bold', color: 'rgba(0,0,0,0.85)', textTransform: 'uppercase', letterSpacing: '0.05em' }}>
                                    Jornada {currentRound.number}
                                </span>
                                <span style={{ fontSize: '12px', fontWeight: 'bold', color: isUrgent ? '#b85e00' : 'rgba(0,0,0,0.6)', fontVariantNumeric: 'tabular-nums' }}>
                                    {formatTime(timeLeft)}
                                </span>
                                {hasVotedTech && <span style={{ fontSize: '9px', color: 'var(--color-bgreen)', fontWeight: 'bold', backgroundColor: '#e8f5e9', padding: '1px 5px', border: '1px solid #b2dfdb' }}>✓ Tec</span>}
                                {hasVotedInv  && <span style={{ fontSize: '9px', color: 'var(--color-bgreen)', fontWeight: 'bold', backgroundColor: '#e8f5e9', padding: '1px 5px', border: '1px solid #b2dfdb' }}>✓ Inv</span>}
                            </div>
                            <div style={{ display: 'flex', gap: '6px', flexShrink: 0 }}>
                                {!(hasVotedTech && hasVotedInv) && (
                                    <button
                                        onClick={handleVote}
                                        disabled={!hasAnyPendingVote || isVoting}
                                        style={{
                                            padding:         '4px 10px',
                                            fontSize:        '10px',
                                            fontWeight:      'bold',
                                            textTransform:   'uppercase',
                                            backgroundColor: hasAnyPendingVote && !isVoting ? 'var(--color-bgreen)' : '#c0c0c0',
                                            color:           '#fff',
                                            border:          'none',
                                            cursor:          hasAnyPendingVote ? 'pointer' : 'default',
                                        }}
                                    >
                                        {isVoting ? 'Votando…' : 'Votar'}
                                    </button>
                                )}
                                <button
                                    onClick={closeRound}
                                    disabled={isClosing}
                                    style={{
                                        padding:         '4px 10px',
                                        fontSize:        '10px',
                                        fontWeight:      'bold',
                                        textTransform:   'uppercase',
                                        backgroundColor: isClosing ? '#a0a0a0' : 'var(--color-bgreen)',
                                        color:           '#fff',
                                        border:          'none',
                                        cursor:          isClosing ? 'default' : 'pointer',
                                    }}
                                >
                                    {isClosing ? 'Finalizando…' : 'Finalizar Día'}
                                </button>
                            </div>
                        </div>
                    )}
                </div>
            )}

            {/* Dos columnas de iconos */}
            <div style={{ flex: 1, display: 'flex', overflow: 'hidden', gap: '2px' }}>

                {/* Columna votaciones */}
                <div style={{ flex: 1, overflowY: 'auto', backgroundColor: '#f7f9f7', padding: '6px' }}>
                    <div style={{ fontSize: '8px', fontWeight: 'bold', textTransform: 'uppercase', letterSpacing: '0.08em', color: 'rgba(0,0,0,0.35)', marginBottom: '6px' }}>
                        Votaciones
                    </div>
                    <div style={{ display: 'flex', flexWrap: 'wrap', gap: '2px' }}>
                        {technologies.map(tech => {
                            const isSelected = selectedTech?.id === tech.id;
                            const canSelect  = tech.canVote && !hasVotedTech;
                            return (
                                <IconChip
                                    key={tech.id}
                                    iconSrc={TECHNOLOGY_ICON_MAP[technologyNameToKey(tech.name)] ?? ''}
                                    bgColor={TECHNOLOGY_COLORS[technologyNameToKey(tech.name)] ?? '#a0a0a0'}
                                    name={tech.name}
                                    isSelected={isSelected}
                                    isVoted={hasVotedTech && isSelected}
                                    canSelect={canSelect}
                                    onClick={canSelect ? () => setSelectedTech(prev => prev?.id === tech.id ? null : { id: tech.id, name: tech.name }) : undefined}
                                />
                            );
                        })}
                        {inventions.map(inv => {
                            const isSelected = selectedInv?.id === inv.id;
                            const canSelect  = inv.canVote && !hasVotedInv;
                            const iconKey    = inventionNameToKey(inv.name);
                            return (
                                <IconChip
                                    key={inv.id}
                                    iconSrc={INVENTION_ICON_MAP[iconKey] ?? ''}
                                    bgColor={INVENTION_COLORS[iconKey] ?? '#a0a0a0'}
                                    name={inv.name}
                                    isSelected={isSelected}
                                    isVoted={hasVotedInv && isSelected}
                                    canSelect={canSelect}
                                    onClick={canSelect ? () => setSelectedInv(prev => prev?.id === inv.id ? null : { id: inv.id, name: inv.name }) : undefined}
                                />
                            );
                        })}
                    </div>
                </div>

                {/* Columna inventario */}
                <div style={{ flex: 1, overflowY: 'auto', backgroundColor: '#fff', padding: '6px' }}>
                    <div style={{ fontSize: '8px', fontWeight: 'bold', textTransform: 'uppercase', letterSpacing: '0.08em', color: 'rgba(0,0,0,0.35)', marginBottom: '6px' }}>
                        Inventario
                    </div>
                    <div style={{ display: 'flex', flexWrap: 'wrap', gap: '2px' }}>
                        {[...materials]
                            .filter(m => m.quantity > 0)
                            .sort((a, b) => b.quantity - a.quantity)
                            .map(mat => (
                                <IconChip
                                    key={mat.id}
                                    iconSrc={MATERIAL_ICON_MAP[mat.name] ?? ''}
                                    bgColor={MATERIAL_COLORS[mat.name] ?? '#a0a0a0'}
                                    name={mat.name}
                                    quantity={mat.quantity}
                                    canSelect={false}
                                />
                            ))
                        }
                        {ownedTechs.map(tech => {
                            const k = technologyNameToKey(tech.name);
                            return (
                                <IconChip
                                    key={tech.id}
                                    iconSrc={TECHNOLOGY_ICON_MAP[k] ?? ''}
                                    bgColor={TECHNOLOGY_COLORS[k] ?? '#a0a0a0'}
                                    name={tech.name}
                                    canSelect={false}
                                />
                            );
                        })}
                        {ownedInvs.filter(i => i.quantity > 0).map(inv => {
                            const k = inventionNameToKey(inv.name);
                            return (
                                <IconChip
                                    key={inv.id}
                                    iconSrc={INVENTION_ICON_MAP[k] ?? ''}
                                    bgColor={INVENTION_COLORS[k] ?? '#a0a0a0'}
                                    name={inv.name}
                                    quantity={inv.quantity}
                                    canSelect={false}
                                />
                            );
                        })}
                    </div>
                </div>

            </div>
        </div>
    );
}

// ─── Layout escritorio (3 columnas) ───────────────────────────────────────────

function DesktopGameBoard({ currentGame }) {
    const { gameStatus } = useVoting(currentGame?.id);

    return (
        <div
            style={{
                display:             'grid',
                gridTemplateColumns: '1fr 2.2fr 1fr',
                gap:                 '16px',
                padding:             '16px',
                height:              'calc(100vh - 4rem)',
                backgroundColor:     '#C1CDC1',
                boxSizing:           'border-box',
            }}
        >
            {gameStatus === 'FINISHED' && <VictoryModal teamName={currentGame?.name} />}

            {/* Panel izquierdo — Votaciones */}
            <div style={{ backgroundColor: '#f7f9f7', display: 'flex', flexDirection: 'column', overflow: 'hidden' }}>
                <VotingPanel gameId={currentGame?.id} />
            </div>

            {/* Centro — Tablero */}
            <div style={{ overflow: 'hidden', display: 'flex', alignItems: 'flex-start', justifyContent: 'center' }}>
                <div style={{ width: 'min(100%, calc(100vh - 4rem - 32px))' }}>
                    <BoardGrid />
                </div>
            </div>

            {/* Panel derecho — Inventario */}
            <div style={{ backgroundColor: '#f7f9f7', display: 'flex', flexDirection: 'column', overflow: 'hidden' }}>
                <div style={{
                    padding:         '10px 14px',
                    backgroundColor: '#fff',
                    color:           'rgba(0,0,0,0.8)',
                    fontWeight:      'bold',
                    textTransform:   'uppercase',
                    letterSpacing:   '0.08em',
                    fontSize:        '13px',
                    borderBottom:    '3px solid #C1CDC1',
                }}>
                    Inventario
                </div>
                <div style={{ flex: 1, overflowY: 'auto', backgroundColor: '#C1CDC1' }}>
                    <InventoryPanel />
                </div>
            </div>
        </div>
    );
}

// ─── Componente raíz ──────────────────────────────────────────────────────────

function GameBoard() {
    const { currentGame } = useGames();
    const isMobile        = useIsMobile();

    return isMobile
        ? <MobileGameBoard currentGame={currentGame} />
        : <DesktopGameBoard currentGame={currentGame} />;
}

export default GameBoard;
