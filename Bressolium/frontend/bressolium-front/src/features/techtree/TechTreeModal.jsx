/**
 * @typedef {{ id: string, name: string, missing?: MissingItem[] }} TechEntry
 * @typedef {{ name: string, type: string }} MissingItem
 */

import Badge from '../../components/ui/Badge';

// ─── Colores brutalistas del proyecto ────────────────────────────────────────
const COLOR_BROWN   = '#8B7355';
const COLOR_GREEN   = '#458B74';
const COLOR_BG      = '#f7f9f7';
const COLOR_BLOCKED = '#C1CDC1';

/**
 * Fila individual de tecnología dentro de una sección.
 * @param {{ tech: TechEntry, showMissing?: boolean }} props
 */
function TechRow({ tech, showMissing, showVote, onVote }) {
    return (
        <div
            style={{
                padding:       '8px 12px',
                borderBottom:  `1px solid ${COLOR_BLOCKED}`,
                display:       'flex',
                flexDirection: 'column',
                gap:           '4px',
            }}
        >
            <div style={{ display: 'flex', justifyContent: 'space-between', alignItems: 'center' }}>
                <span style={{ fontWeight: 'bold', color: COLOR_BROWN, fontSize: '13px' }}>
                    {tech.name}
                </span>
                {showVote && (
                    <button
                        onClick={() => onVote?.({ technology_id: tech.id }, tech.name)}
                        style={{
                            background:    COLOR_BROWN,
                            color:         '#fff',
                            border:        'none',
                            padding:       '3px 10px',
                            fontSize:      '11px',
                            fontWeight:    'bold',
                            cursor:        'pointer',
                            textTransform: 'uppercase',
                            letterSpacing: '0.06em',
                        }}
                    >
                        Votar
                    </button>
                )}
            </div>

            {showMissing && tech.missing?.length > 0 && (
                <div style={{ paddingLeft: '12px' }}>
                    {tech.missing.map(item => (
                        <span
                            key={item.name}
                            style={{
                                display:         'inline-block',
                                fontSize:        '11px',
                                color:           '#fff',
                                backgroundColor: COLOR_BROWN,
                                padding:         '1px 6px',
                                marginRight:     '4px',
                                marginTop:       '2px',
                            }}
                        >
                            {item.name}{item.quantity > 1 ? ` ×${item.quantity}` : ''}
                        </span>
                    ))}
                </div>
            )}
        </div>
    );
}

/**
 * Encabezado de sección del árbol tecnológico.
 * @param {{ label: string, color: string, count: number }} props
 */
function SectionHeader({ label, color, count }) {
    return (
        <div
            style={{
                padding:         '6px 12px',
                backgroundColor: color,
                color:           '#fff',
                fontWeight:      'bold',
                textTransform:   'uppercase',
                letterSpacing:   '0.08em',
                fontSize:        '12px',
                display:         'flex',
                justifyContent:  'space-between',
                alignItems:      'center',
            }}
        >
            <span>{label}</span>
            <Badge count={count} />
        </div>
    );
}

// ─── Componente principal ─────────────────────────────────────────────────────

/**
 * Modal del árbol tecnológico del equipo.
 * Muestra las tecnologías en tres categorías: completadas, disponibles y bloqueadas.
 * Se abre desde la casilla central Pueblo (T51).
 *
 * @param {{ isOpen: boolean, onClose: Function, completed: TechEntry[], available: TechEntry[], blocked: TechEntry[] }} props
 */
function TechTreeModal({ isOpen, onClose, completed = [], available = [], blocked = [], onVote = () => {} }) {
    if (!isOpen) return null;

    return (
        <div
            role="dialog"
            aria-modal="true"
            aria-label="Árbol Tecnológico"
            style={{
                position:        'fixed',
                inset:           0,
                backgroundColor: 'rgba(139,115,85,0.85)',
                display:         'flex',
                alignItems:      'center',
                justifyContent:  'center',
                padding:         '1rem',
                zIndex:          50,
            }}
        >
            <div
                style={{
                    backgroundColor: COLOR_BG,
                    width:           '100%',
                    maxWidth:        '42rem',
                    maxHeight:       '80vh',
                    display:         'flex',
                    flexDirection:   'column',
                    position:        'relative',
                    border:          `2px solid ${COLOR_BROWN}`,
                }}
            >
                {/* Cabecera */}
                <div
                    style={{
                        padding:         '12px 16px',
                        backgroundColor: COLOR_BROWN,
                        color:           '#fff',
                        fontWeight:      900,
                        fontSize:        '18px',
                        textTransform:   'uppercase',
                        letterSpacing:   '0.06em',
                        display:         'flex',
                        justifyContent:  'space-between',
                        alignItems:      'center',
                        flexShrink:      0,
                    }}
                >
                    <span>Árbol Tecnológico</span>
                    <button
                        onClick={onClose}
                        aria-label="Cerrar"
                        style={{
                            background: 'none',
                            border:     'none',
                            color:      '#fff',
                            fontSize:   '1.5rem',
                            fontWeight: 900,
                            cursor:     'pointer',
                            lineHeight: 1,
                            padding:    '0 4px',
                        }}
                    >
                        ×
                    </button>
                </div>

                {/* Cuerpo con scroll */}
                <div style={{ overflowY: 'auto', flex: 1 }}>

                    {/* ── Sección Completadas ── */}
                    <SectionHeader label="Completadas" color={COLOR_GREEN} count={completed.length} />
                    {completed.length === 0 ? (
                        <p style={{ padding: '8px 12px', color: COLOR_BROWN, fontSize: '12px', opacity: 0.6 }}>
                            Ninguna investigada todavía.
                        </p>
                    ) : (
                        completed.map(tech => <TechRow key={tech.id} tech={tech} />)
                    )}

                    {/* ── Sección Disponibles ── */}
                    <SectionHeader label="Disponibles" color="#5a8f7b" count={available.length} />
                    {available.length === 0 ? (
                        <p style={{ padding: '8px 12px', color: COLOR_BROWN, fontSize: '12px', opacity: 0.6 }}>
                            Nada listo para investigar aún.
                        </p>
                    ) : (
                        available.map(tech => <TechRow key={tech.id} tech={tech} showVote onVote={onVote} />)
                    )}

                    {/* ── Sección Bloqueadas ── */}
                    <SectionHeader label="Bloqueadas" color={COLOR_BROWN} count={blocked.length} />
                    {blocked.length === 0 ? (
                        <p style={{ padding: '8px 12px', color: COLOR_BROWN, fontSize: '12px', opacity: 0.6 }}>
                            Sin requisitos pendientes por cumplir.
                        </p>
                    ) : (
                        blocked.map(tech => <TechRow key={tech.id} tech={tech} showMissing />)
                    )}
                </div>
            </div>
        </div>
    );
}

export default TechTreeModal;
