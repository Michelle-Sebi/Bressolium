/**
 * @module BoardGrid
 * @description Cuadrícula 15×15 del tablero de juego.
 * Muestra el mapa con niebla de guerra, colores por tipo de terreno e iconos de casilla.
 * Solo permite explorar casillas adyacentes (4 ortogonales) a casillas ya exploradas.
 * @see Tarea 9 - Board Grid Component y Frontend UI (HU 2.1, 2.2, 2.6)
 */

import { useState, useMemo, useContext, useCallback } from 'react';
import { ReactReduxContext } from 'react-redux';
import { useBoard } from './useBoard';
import { useAuth } from '../auth/useAuth';
import { useGames } from '../game/useGames';
import { useTechTree } from '../techtree/useTechTree';
import { useVoting } from '../game/useVoting';
import TechTreeModal from '../techtree/TechTreeModal';
import Badge from '../../components/ui/Badge';

import bosqueIcon  from '../../assets/icons/tiles/bosque.png';
import canteraIcon from '../../assets/icons/tiles/cantera.png';
import rioIcon     from '../../assets/icons/tiles/rio.png';
import pradoIcon   from '../../assets/icons/tiles/prado.png';
import minaIcon    from '../../assets/icons/tiles/mina.png';
import puebloIcon  from '../../assets/icons/tiles/pueblo.png';

const TYPE_LABELS = {
    bosque:  'Bosque',
    cantera: 'Cantera',
    prado:   'Prado',
    rio:     'Río',
    mina:    'Mina',
    pueblo:  'Pueblo',
};

const MATERIAL_LABELS = {
    'roble':               'Roble',
    'pino':                'Pino',
    'carbon-natural':      'Carbón',
    'pieles':              'Pieles',
    'latex':               'Látex',
    'resinas-inflamables': 'Resinas',
    'mat-aisl-nat':        'Mat. Aislante',
    'silex':               'Sílex',
    'granito':             'Granito',
    'obsidiana':           'Obsidiana',
    'arena-de-silice':     'Arena Sílice',
    'arena-de-cuarzo':     'Arena Cuarzo',
    'cristales-nat':       'Cristales',
    'silicio':             'Silicio',
    'min-semi':            'Min. Semicon.',
    'agua':                'Agua',
    'cana-comun':          'Caña',
    'tierras-fertiles':    'T. Fértiles',
    'hidrogeno':           'Hidrógeno',
    'gases-naturales':     'Gases Nat.',
    'lino':                'Lino',
    'yute':                'Yute',
    'canamo':              'Cáñamo',
    'lana':                'Lana',
    'cobre':               'Cobre',
    'hierro':              'Hierro',
    'estano':              'Estaño',
    'grafito':             'Grafito',
    'oro':                 'Oro',
    'mat-mag-nat':         'Mat. Magnético',
};

/** Materiales que produce cada casilla (= coste de subir AL nivel indicado) */
const PRODUCTION_DATA = {
    bosque: {
        1: [{ m: 'roble', q: 5 }],
        2: [{ m: 'roble', q: 8 }, { m: 'pino', q: 8 }],
        3: [{ m: 'roble', q: 8 }, { m: 'pino', q: 8 }, { m: 'carbon-natural', q: 8 }],
        4: [{ m: 'roble', q: 9 }, { m: 'pino', q: 9 }, { m: 'carbon-natural', q: 9 }, { m: 'pieles', q: 9 }],
        5: [{ m: 'latex', q: 8 }, { m: 'resinas-inflamables', q: 8 }, { m: 'mat-aisl-nat', q: 4 }],
    },
    cantera: {
        1: [{ m: 'silex', q: 5 }],
        2: [{ m: 'silex', q: 8 }, { m: 'granito', q: 8 }],
        3: [{ m: 'silex', q: 8 }, { m: 'granito', q: 8 }, { m: 'obsidiana', q: 8 }],
        4: [{ m: 'silex', q: 9 }, { m: 'granito', q: 9 }, { m: 'obsidiana', q: 9 }],
        5: [{ m: 'arena-de-silice', q: 8 }, { m: 'arena-de-cuarzo', q: 8 }, { m: 'cristales-nat', q: 8 }, { m: 'silicio', q: 10 }, { m: 'min-semi', q: 8 }],
    },
    rio: {
        1: [{ m: 'agua', q: 5 }],
        2: [{ m: 'agua', q: 8 }, { m: 'cana-comun', q: 8 }],
        3: [{ m: 'agua', q: 8 }, { m: 'cana-comun', q: 8 }, { m: 'tierras-fertiles', q: 8 }],
        4: [{ m: 'agua', q: 9 }, { m: 'cana-comun', q: 9 }, { m: 'tierras-fertiles', q: 9 }],
        5: [{ m: 'hidrogeno', q: 10 }, { m: 'gases-naturales', q: 8 }],
    },
    prado: {
        1: [{ m: 'lino', q: 5 }],
        2: [{ m: 'lino', q: 8 }, { m: 'yute', q: 8 }],
        3: [{ m: 'lino', q: 8 }, { m: 'yute', q: 8 }, { m: 'canamo', q: 8 }],
        4: [{ m: 'lino', q: 9 }, { m: 'yute', q: 9 }, { m: 'canamo', q: 9 }, { m: 'lana', q: 9 }],
        5: [{ m: 'tierras-fertiles', q: 8 }],
    },
    mina: {
        1: [{ m: 'cobre', q: 5 }],
        2: [{ m: 'cobre', q: 8 }, { m: 'hierro', q: 8 }],
        3: [{ m: 'cobre', q: 10 }, { m: 'hierro', q: 10 }, { m: 'estano', q: 10 }],
        4: [{ m: 'cobre', q: 12 }, { m: 'hierro', q: 12 }, { m: 'estano', q: 12 }, { m: 'grafito', q: 12 }],
        5: [{ m: 'oro', q: 8 }, { m: 'mat-mag-nat', q: 8 }],
    },
};

function MaterialList({ items }) {
    if (!items || items.length === 0) return <span style={{ opacity: 0.6 }}>—</span>;
    return (
        <span>
            {items.map(({ m, q }, i) => (
                <span key={m}>
                    {i > 0 && <span style={{ opacity: 0.4 }}> · </span>}
                    <span style={{ color: '#e8d5a0' }}>{MATERIAL_LABELS[m] ?? m}</span>
                    <span style={{ opacity: 0.7 }}> ×{q}</span>
                </span>
            ))}
        </span>
    );
}

function TileTooltip({ tile, pos }) {
    if (!tile || !tile.explored) return null;

    const baseType   = tile.type?.base_type;
    const level      = tile.type?.level ?? 0;
    const tileName   = tile.type?.name ?? TYPE_LABELS[baseType] ?? baseType;
    const production = PRODUCTION_DATA[baseType]?.[level] ?? null;
    const upgradeCost = baseType !== 'pueblo' && level < 5
        ? (PRODUCTION_DATA[baseType]?.[level + 1] ?? null)
        : null;

    const TOOLTIP_W = 200;
    const TOOLTIP_H = 130;
    const margin    = 10;

    const left = pos.x + 12 + TOOLTIP_W > window.innerWidth
        ? pos.x - TOOLTIP_W - 12
        : pos.x + 12;
    const top  = pos.y + TOOLTIP_H > window.innerHeight - margin
        ? pos.y - TOOLTIP_H
        : pos.y;

    const row = (label, content) => (
        <div style={{ display: 'flex', gap: '4px', marginBottom: '3px', flexWrap: 'wrap' }}>
            <span style={{ opacity: 0.55, textTransform: 'uppercase', letterSpacing: '0.06em', minWidth: '60px', flexShrink: 0 }}>
                {label}
            </span>
            <span style={{ flex: 1 }}>{content}</span>
        </div>
    );

    return (
        <div
            aria-hidden="true"
            style={{
                position:      'fixed',
                left,
                top,
                zIndex:        9999,
                pointerEvents: 'none',
                backgroundColor: 'rgba(20,20,20,0.95)',
                color:         '#f0f0f0',
                fontSize:      '11px',
                lineHeight:    1.4,
                padding:       '8px 10px',
                minWidth:      `${TOOLTIP_W}px`,
                maxWidth:      '240px',
                border:        '1px solid rgba(255,255,255,0.1)',
            }}
        >
            <div style={{ fontWeight: 'bold', marginBottom: '6px', fontSize: '12px', color: '#fff' }}>
                {tileName}
            </div>
            {row('Tipo',  TYPE_LABELS[baseType] ?? baseType)}
            {row('Nivel', baseType === 'pueblo' ? '—' : `${level} / 5`)}
            {baseType !== 'pueblo' && row('Produce', <MaterialList items={production} />)}
            {baseType !== 'pueblo' && row(
                'Subir Nv.',
                upgradeCost
                    ? <MaterialList items={upgradeCost} />
                    : <span style={{ opacity: 0.6 }}>Nivel máximo</span>
            )}
        </div>
    );
}

/** @type {Record<string, string>} Color de fondo por tipo de terreno */
const TILE_BG_COLORS = {
    bosque:  '#458B74',
    cantera: '#696969',
    prado:   '#8FBC8F',
    rio:     '#4682B4',
    mina:    '#DAA520',
    pueblo:  '#C1CDC1',
};

const FOG_BG_COLOR        = '#a0a0a0';
const EXPLORABLE_BG_COLOR = '#c0c0c0';
const DEFAULT_BORDER      = '2px solid #fff';

/** @type {Record<string, string>} Icono por tipo de terreno base */
const TILE_ICONS = {
    bosque:  bosqueIcon,
    cantera: canteraIcon,
    rio:     rioIcon,
    prado:   pradoIcon,
    mina:    minaIcon,
    pueblo:  puebloIcon,
};

/**
 * Calcula el conjunto de coordenadas "coord_x,coord_y" adyacentes a tiles explorados.
 * Si no hay ningún tile explorado, devuelve null (sin restricción de adyacencia).
 * @param {object[]} tiles
 * @returns {Set<string>|null}
 */
function buildExplorableCoordSet(tiles) {
    const exploredCoords = tiles
        .filter((t) => t.explored)
        .map((t) => `${t.coord_x},${t.coord_y}`);

    if (exploredCoords.length === 0) return null;

    const exploredSet = new Set(exploredCoords);
    const explorable  = new Set();

    for (const coord of exploredSet) {
        const [x, y] = coord.split(',').map(Number);
        const neighbors = [
            `${x - 1},${y}`,
            `${x + 1},${y}`,
            `${x},${y - 1}`,
            `${x},${y + 1}`,
        ];
        for (const neighbor of neighbors) {
            if (!exploredSet.has(neighbor)) explorable.add(neighbor);
        }
    }

    return explorable;
}

/**
 * Casilla individual del tablero.
 *
 * Estructura de DOM (dos capas para satisfacer los requisitos de testid):
 * - Capa exterior: `data-testid="tile"` con clase `tile--fog` cuando no está explorada.
 * - Capa interior: `data-testid="tile-{x}-{y}"` con atributos de coordenadas, dueño y tipo de terreno.
 *
 * @param {{ tile: object, currentUserId: string, isExplorable: boolean, onTileClick: Function }} props
 */
function Tile({ tile, currentUserId, isExplorable, onTileClick, onHoverEnter, onHoverLeave }) {
    const isExplored = tile.explored;
    const isOwnTile  = !tile.assigned_player || tile.assigned_player === currentUserId;
    const baseType   = tile.type?.base_type;
    const level      = tile.type?.level ?? 0;

    const isClickable     = isOwnTile && (isExplored || isExplorable);
    const backgroundColor = isExplored
        ? (TILE_BG_COLORS[baseType] ?? FOG_BG_COLOR)
        : isExplorable ? EXPLORABLE_BG_COLOR : FOG_BG_COLOR;
    const tileIcon        = isExplored ? TILE_ICONS[baseType] : null;
    const borderStyle     = DEFAULT_BORDER;

    const specificTestIdAttributes = {
        'data-x':     tile.coord_x,
        'data-y':     tile.coord_y,
        'data-owner': tile.assigned_player,
        ...(isExplored ? { 'data-base-type': baseType } : {}),
    };

    const isPueblo = baseType === 'pueblo';

    const tileLabel = isExplored
        ? `Casilla ${baseType}${level > 0 ? `, nivel ${level}` : ''}, coordenadas ${tile.coord_x},${tile.coord_y}${isExplored ? '. Mejorable.' : ''}`
        : isExplorable
            ? `Casilla desconocida explorable, coordenadas ${tile.coord_x},${tile.coord_y}`
            : `Casilla en niebla de guerra, coordenadas ${tile.coord_x},${tile.coord_y}`;

    return (
        <div
            data-testid="tile"
            className={`tile${isExplored ? '' : ' tile--fog'}${isPueblo ? ' tile--pueblo' : ''}`}
            style={{
                backgroundColor,
                width: '100%',
                aspectRatio: '1 / 1',
                border: borderStyle,
            }}
            onMouseEnter={isExplored ? (e) => onHoverEnter(tile, e) : undefined}
            onMouseLeave={isExplored ? onHoverLeave : undefined}
        >
            <div
                data-testid={`tile-${tile.coord_x}-${tile.coord_y}`}
                {...specificTestIdAttributes}
                role={isClickable ? 'button' : undefined}
                tabIndex={isClickable ? 0 : -1}
                aria-label={tileLabel}
                style={{
                    position: 'relative',
                    width: '100%',
                    height: '100%',
                    display: 'flex',
                    alignItems: 'center',
                    justifyContent: 'center',
                    cursor: isClickable ? 'pointer' : 'default',
                }}
                onClick={() => onTileClick(tile, isOwnTile, isExplorable)}
                onKeyDown={(e) => {
                    if ((e.key === 'Enter' || e.key === ' ') && isClickable) {
                        e.preventDefault();
                        onTileClick(tile, isOwnTile, isExplorable);
                    }
                }}
            >
                {tileIcon && (
                    <img
                        src={tileIcon}
                        alt=""
                        aria-hidden="true"
                        style={{ width: '60%', height: '60%', objectFit: 'contain' }}
                    />
                )}
                {isExplored && level > 0 && (
                    <Badge
                        count={level}
                        style={{
                            position:        'absolute',
                            bottom:          '1px',
                            right:           '2px',
                            fontSize:        '8px',
                            backgroundColor: 'transparent',
                            color:           '#fff',
                            textShadow:      '0 0 2px rgba(0,0,0,0.8)',
                            padding:         0,
                            minWidth:        'auto',
                            lineHeight:      1,
                            pointerEvents:   'none',
                        }}
                    />
                )}
            </div>
        </div>
    );
}

/**
 * Renders TechTreeModal connected to store data. Only used when Redux is available.
 */
function TechTreeWithStore({ gameId, onClose }) {
    const { completed, available, blocked } = useTechTree(gameId);
    const { vote } = useVoting(gameId);
    return (
        <TechTreeModal
            isOpen
            onClose={onClose}
            completed={completed}
            available={available}
            blocked={blocked}
            onVote={vote}
        />
    );
}

/**
 * Renders TechTreeModal with store data if Redux is available, or with empty data otherwise.
 * This allows BoardGrid to work in test environments without a Redux Provider.
 */
function TechTreePanel({ gameId, onClose }) {
    const storeCtx = useContext(ReactReduxContext);
    if (storeCtx) {
        return <TechTreeWithStore gameId={gameId} onClose={onClose} />;
    }
    return <TechTreeModal isOpen onClose={onClose} completed={[]} available={[]} blocked={[]} onVote={() => {}} />;
}

/**
 * Cuadrícula principal 15×15 del tablero de juego.
 */
function BoardGrid() {
    const { user: currentUser } = useAuth();
    const { currentGame } = useGames();
    const { tiles, isLoading, exploreTile, upgradeTile } = useBoard(currentGame?.id);
    const [isTechTreeOpen, setIsTechTreeOpen] = useState(false);
    const [hoveredTile, setHoveredTile]       = useState(null);
    const [tooltipPos, setTooltipPos]         = useState({ x: 0, y: 0 });

    const handleHoverEnter = useCallback((tile, e) => {
        const rect = e.currentTarget.getBoundingClientRect();
        setHoveredTile(tile);
        setTooltipPos({ x: rect.right, y: rect.top });
    }, []);

    const handleHoverLeave = useCallback(() => setHoveredTile(null), []);

    /** Tiles ordenados por coord_x → coord_y para renderizado correcto del CSS grid. */
    const sortedTiles = useMemo(
        () => [...tiles].sort((a, b) => a.coord_x * 15 + a.coord_y - (b.coord_x * 15 + b.coord_y)),
        [tiles]
    );

    /** Conjunto de coordenadas explorable en el turno actual. null = sin restricción. */
    const explorableCoordSet = useMemo(() => buildExplorableCoordSet(tiles), [tiles]);

    /**
     * Devuelve true si el tile es explorable según la regla de adyacencia.
     * Si no hay tiles explorados aún (partida nueva), todos son explorables.
     * @param {object} tile
     * @returns {boolean}
     */
    function isTileExplorable(tile) {
        if (explorableCoordSet === null) return true;
        return explorableCoordSet.has(`${tile.coord_x},${tile.coord_y}`);
    }

    /**
     * Explora la casilla si no está explorada y es adyacente; la evoluciona si ya está explorada.
     * Las casillas de otro jugador o no adyacentes no ejecutan ninguna acción.
     * @param {object} tile
     * @param {boolean} isOwnTile
     * @param {boolean} isExplorable
     */
    function handleTileClick(tile, isOwnTile, isExplorable) {
        if (tile.type?.base_type === 'pueblo') {
            setIsTechTreeOpen(true);
            return;
        }

        if (!isOwnTile) return;

        if (!tile.explored) {
            if (!isExplorable) return;
            exploreTile(tile.id);
        } else {
            upgradeTile(tile.id);
        }
    }

    if (isLoading) {
        return (
            <div
                data-testid="board-loading"
                className="flex items-center justify-center w-full h-full p-8"
            >
                <span className="font-bold uppercase tracking-widest" style={{ color: '#a0a0a0' }}>
                    Cargando tablero…
                </span>
            </div>
        );
    }

    return (
        <>
        {isTechTreeOpen && (
            <TechTreePanel
                gameId={currentGame?.id}
                onClose={() => setIsTechTreeOpen(false)}
            />
        )}
        <TileTooltip tile={hoveredTile} pos={tooltipPos} />
        <div
            data-testid="board-grid"
            style={{
                display: 'grid',
                gridTemplateColumns: 'repeat(15, 1fr)',
                width: '100%',
                gap: 0,
            }}
        >
            {sortedTiles.map((tile) => (
                <Tile
                    key={tile.id}
                    tile={tile}
                    currentUserId={currentUser?.id}
                    isExplorable={isTileExplorable(tile)}
                    onTileClick={handleTileClick}
                    onHoverEnter={handleHoverEnter}
                    onHoverLeave={handleHoverLeave}
                />
            ))}
        </div>
        </>
    );
}

export default BoardGrid;
