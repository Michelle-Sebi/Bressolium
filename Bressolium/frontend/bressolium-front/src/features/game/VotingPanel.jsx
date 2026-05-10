import Button from '../../components/ui/Button';
import { useVoting } from './useVoting';

const SECTION_HEADER = {
    padding:         '4px 8px',
    backgroundColor: '#8B7355',
    color:           '#fff',
    fontWeight:      'bold',
    textTransform:   'uppercase',
    letterSpacing:   '0.08em',
    fontSize:        '10px',
    marginBottom:    '2px',
};

function SectionHeader({ label }) {
    return <div role="heading" aria-level={3} style={SECTION_HEADER}>{label}</div>;
}

function VoteItem({ name, canVote, missing, quantity, onClick }) {
    return (
        <button
            onClick={canVote ? onClick : undefined}
            disabled={!canVote}
            style={{
                width:           '100%',
                padding:         '6px 8px',
                textAlign:       'left',
                backgroundColor: canVote ? '#f7f9f7' : '#e8e4df',
                color:           canVote ? '#8B7355' : '#b0a898',
                border:          '1px solid #C1CDC1',
                borderLeft:      canVote ? '3px solid #458B74' : '3px solid #C1CDC1',
                marginBottom:    '2px',
                cursor:          canVote ? 'pointer' : 'default',
                fontSize:        '11px',
                fontWeight:      'bold',
                textTransform:   'uppercase',
                opacity:         canVote ? 1 : 0.55,
                display:         'flex',
                justifyContent:  'space-between',
                alignItems:      'flex-start',
            }}
        >
            <span>
                {name}
                {missing.length > 0 && (
                    <span
                        aria-label={`Faltan: ${missing.map((m) => m.name).join(', ')}`}
                        style={{ fontSize: '9px', color: '#c0826b', marginLeft: '6px', fontWeight: 'normal' }}
                    >
                        falta: {missing.map((m) => m.name).join(', ')}
                    </span>
                )}
            </span>
            {quantity > 0 && (
                <span
                    aria-label={`Cantidad: ${quantity}`}
                    style={{ fontSize: '10px', color: '#458B74', fontWeight: 'bold', marginLeft: '6px', flexShrink: 0 }}
                >
                    ×{quantity}
                </span>
            )}
        </button>
    );
}

function VotingPanel({ gameId }) {
    const { technologies, inventions, userActions, currentRound, lastRoundResult, isLoading, isClosing, hasVoted, votedName, vote, abstain, closeRound } = useVoting(gameId);

    return (
        <div style={{ display: 'flex', flexDirection: 'column', gap: '4px', padding: '8px', overflowY: 'auto' }}>

            {/* Contador de acciones y jornada (timer de fase) */}
            <div style={{
                display:         'flex',
                justifyContent:  'space-between',
                alignItems:      'center',
                padding:         '5px 8px',
                backgroundColor: '#C1CDC1',
                fontSize:        '11px',
                fontWeight:      'bold',
                color:           '#5a4a35',
                textTransform:   'uppercase',
                letterSpacing:   '0.06em',
                marginBottom:    '4px',
            }}>
                <span>Acciones: {userActions}</span>
                {currentRound && <span>Jornada {currentRound.number}</span>}
            </div>

            {/* Resultado de la jornada anterior: empate resuelto al azar */}
            {lastRoundResult?.no_consensus && (
                <div style={{
                    padding:         '6px 8px',
                    backgroundColor: '#fff3e0',
                    border:          '1px solid #e6961a',
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
                    padding:         '6px 8px',
                    backgroundColor: '#e8f5e9',
                    border:          '1px solid #458B74',
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

            {/* Zona Tecnologías */}
            <SectionHeader label="Tecnologías" />
            {isLoading && (
                <div style={{ fontSize: '11px', color: '#b0a898', padding: '4px 8px', fontStyle: 'italic' }}>
                    Cargando…
                </div>
            )}
            {!isLoading && technologies.length === 0 && (
                <div style={{
                    fontSize:        '10px',
                    color:           '#458B74',
                    padding:         '4px 8px',
                    fontStyle:       'italic',
                    borderLeft:      '3px solid #458B74',
                    backgroundColor: '#f0f9f5',
                }}>
                    Todas las tecnologías investigadas
                </div>
            )}
            {technologies.map((tech) => (
                <VoteItem
                    key={tech.id}
                    name={tech.name}
                    canVote={tech.canVote}
                    missing={tech.missing}
                    onClick={() => vote({ technology_id: tech.id }, tech.name)}
                />
            ))}
            {!isLoading && technologies.length > 0 && !hasVoted && (
                <button
                    onClick={abstain}
                    aria-label="Abstenerse: no investigar esta jornada"
                    style={{
                        width:           '100%',
                        padding:         '5px 8px',
                        textAlign:       'left',
                        backgroundColor: '#f7f9f7',
                        color:           '#8B7355',
                        border:          '1px dashed #C1CDC1',
                        borderLeft:      '3px dashed #C1CDC1',
                        marginBottom:    '2px',
                        cursor:          'pointer',
                        fontSize:        '10px',
                        fontWeight:      'normal',
                        textTransform:   'uppercase',
                        letterSpacing:   '0.05em',
                        opacity:         0.8,
                    }}
                >
                    Abstenerse — no investigar esta jornada
                </button>
            )}

            {/* Zona Inventos */}
            <SectionHeader label="Inventos" />
            {!isLoading && inventions.length === 0 && (
                <div style={{ fontSize: '11px', color: '#b0a898', padding: '4px 8px', fontStyle: 'italic' }}>
                    Sin inventos disponibles
                </div>
            )}
            {inventions.map((inv) => (
                <VoteItem
                    key={inv.id}
                    name={inv.name}
                    quantity={inv.quantity}
                    canVote={inv.canVote}
                    missing={inv.missing}
                    onClick={() => vote({ invention_id: inv.id }, inv.name)}
                />
            ))}

            <Button onClick={closeRound} disabled={isClosing} style={{ marginTop: '8px', width: '100%' }}>
                Finalizar Turno
            </Button>

        </div>
    );
}

export default VotingPanel;
