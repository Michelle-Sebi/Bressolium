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
    return <div style={SECTION_HEADER}>{label}</div>;
}

function VoteItem({ name, canVote, missing, onClick }) {
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
            }}
        >
            {name}
            {missing.length > 0 && (
                <span style={{ fontSize: '9px', color: '#c0826b', marginLeft: '6px', fontWeight: 'normal' }}>
                    falta: {missing.map((m) => m.name).join(', ')}
                </span>
            )}
        </button>
    );
}

function VotingPanel({ gameId }) {
    const { technologies, inventions, userActions, currentRound, isLoading, vote } = useVoting(gameId);

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

            {/* Zona Tecnologías */}
            <SectionHeader label="Tecnologías" />
            {isLoading && (
                <div style={{ fontSize: '11px', color: '#b0a898', padding: '4px 8px', fontStyle: 'italic' }}>
                    Cargando…
                </div>
            )}
            {!isLoading && technologies.length === 0 && (
                <div style={{ fontSize: '11px', color: '#b0a898', padding: '4px 8px', fontStyle: 'italic' }}>
                    Sin tecnologías disponibles
                </div>
            )}
            {technologies.map((tech) => (
                <VoteItem
                    key={tech.id}
                    name={tech.name}
                    canVote={tech.canVote}
                    missing={tech.missing}
                    onClick={() => vote({ technology_id: tech.id })}
                />
            ))}

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
                    canVote={inv.canVote}
                    missing={inv.missing}
                    onClick={() => vote({ invention_id: inv.id })}
                />
            ))}

            {/* Botón finalizar turno */}
            <button
                onClick={() => {}}
                style={{
                    marginTop:       '8px',
                    padding:         '8px 12px',
                    backgroundColor: '#8B7355',
                    color:           '#fff',
                    border:          'none',
                    fontWeight:      'bold',
                    textTransform:   'uppercase',
                    fontSize:        '11px',
                    letterSpacing:   '0.08em',
                    cursor:          'pointer',
                }}
            >
                Finalizar Turno
            </button>
        </div>
    );
}

export default VotingPanel;
