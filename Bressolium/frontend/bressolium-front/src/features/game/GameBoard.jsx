/**
 * @module GameBoard
 * @description Pantalla principal de juego. Layout de 3 columnas:
 * izquierda (votaciones/acciones), centro (tablero 15×15), derecha (inventario).
 * @see Mockup: mockup_dashboard.png
 */

import BoardGrid     from '../board/BoardGrid';
import InventoryPanel from '../inventory/InventoryPanel';
import VotingPanel    from './VotingPanel';
import VictoryModal   from './VictoryModal';
import { useGames }   from './useGames';
import { useVoting }  from './useVoting';

function GameBoard() {
    const { currentGame } = useGames();
    const { gameStatus }  = useVoting(currentGame?.id);
    return (
        <div
            style={{
                display:             'grid',
                gridTemplateColumns: '280px 1fr 260px',
                gap:                 '16px',
                padding:             '16px',
                height:              'calc(100vh - 4rem)',
                backgroundColor:     '#C1CDC1',
                boxSizing:           'border-box',
            }}
        >
            {gameStatus === 'FINISHED' && (
                <VictoryModal teamName={currentGame?.name} />
            )}

            {/* Panel izquierdo — Votaciones (T11/T12) */}
            <div
                style={{
                    backgroundColor: '#f7f9f7',
                    display:         'flex',
                    flexDirection:   'column',
                    overflow:        'hidden',
                }}
            >
                <VotingPanel gameId={currentGame?.id} />
            </div>

            {/* Centro — Tablero */}
            <div
                style={{
                    border:    '2px solid #fff',
                    overflow:  'hidden',
                    alignSelf: 'start',
                }}
            >
                <BoardGrid />
            </div>

            {/* Panel derecho — Inventario */}
            <div
                style={{
                    backgroundColor: '#f7f9f7',
                    display:         'flex',
                    flexDirection:   'column',
                    overflow:        'hidden',
                }}
            >
                <div
                    style={{
                        padding:         '10px 14px',
                        backgroundColor: '#fff',
                        color:           'rgba(0,0,0,0.8)',
                        fontWeight:      'bold',
                        textTransform:   'uppercase',
                        letterSpacing:   '0.08em',
                        fontSize:        '13px',
                        borderBottom:    '3px solid #C1CDC1',
                    }}
                >
                    Inventario
                </div>
                <div style={{ flex: 1, overflowY: 'auto', backgroundColor: '#C1CDC1' }}>
                    <InventoryPanel />
                </div>
            </div>
        </div>
    );
}

export default GameBoard;
