/**
 * @module GameBoard
 * @description Pantalla principal de juego. Layout de 3 columnas:
 * izquierda (votaciones/acciones), centro (tablero 15×15), derecha (inventario).
 * @see Mockup: mockup_dashboard.png
 */

import BoardGrid     from '../board/BoardGrid';
import InventoryPanel from '../inventory/InventoryPanel';

function GameBoard() {
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
            {/* Panel izquierdo — Votaciones (T11/T12) */}
            <div
                style={{
                    backgroundColor: '#f7f9f7',
                    border:          '2px solid #8B7355',
                    display:         'flex',
                    flexDirection:   'column',
                    overflow:        'hidden',
                }}
            >
                <div
                    style={{
                        padding:         '10px 14px',
                        backgroundColor: '#8B7355',
                        color:           '#fff',
                        fontWeight:      'bold',
                        textTransform:   'uppercase',
                        letterSpacing:   '0.08em',
                        fontSize:        '13px',
                    }}
                >
                    Votaciones
                </div>
                <div style={{ flex: 1, padding: '12px', color: '#8B7355', fontSize: '13px' }}>
                    — Pendiente T12 —
                </div>
            </div>

            {/* Centro — Tablero */}
            <div
                style={{
                    border:    '2px solid #8B7355',
                    overflowY: 'auto',
                }}
            >
                <BoardGrid />
            </div>

            {/* Panel derecho — Inventario */}
            <div
                style={{
                    backgroundColor: '#f7f9f7',
                    border:          '2px solid #8B7355',
                    display:         'flex',
                    flexDirection:   'column',
                    overflow:        'hidden',
                }}
            >
                <div
                    style={{
                        padding:         '10px 14px',
                        backgroundColor: '#8B7355',
                        color:           '#fff',
                        fontWeight:      'bold',
                        textTransform:   'uppercase',
                        letterSpacing:   '0.08em',
                        fontSize:        '13px',
                    }}
                >
                    Inventario
                </div>
                <div style={{ flex: 1, overflowY: 'auto' }}>
                    <InventoryPanel />
                </div>
            </div>
        </div>
    );
}

export default GameBoard;
