/**
 * @module BoardGrid
 * @description Cuadrícula 15×15 del tablero de juego.
 * Muestra el mapa con niebla de guerra, colores por tipo de terreno e iconos de casilla.
 * Solo permite explorar casillas adyacentes (4 ortogonales) a casillas ya exploradas.
 * @see Tarea 9 - Board Grid Component y Frontend UI (HU 2.1, 2.2, 2.6)
 */

import { useState, useMemo, useCallback } from 'react';
import { useBoard } from './useBoard';
import { useAuth } from '../auth/useAuth';
import { useGames } from '../game/useGames';
import Badge from '../../components/ui/Badge';

import bosqueIcon  from '../../assets/icons/tiles/bosque.png';
import canteraIcon from '../../assets/icons/tiles/cantera.png';
import rioIcon     from '../../assets/icons/tiles/rio.png';
import pradoIcon   from '../../assets/icons/tiles/prado.png';
import minaIcon    from '../../assets/icons/tiles/mina.png';

const TYPE_LABELS = {
    bosque:  'Bosque',
    cantera: 'Cantera',
    prado:   'Prado',
    rio:     'Río',
    mina:    'Mina',
};

const MATERIAL_LABELS = {
    'roble':               'Roble',
    'pino':                'Pino',
    'carbon-natural':      'Carbón',
    'pieles':              'Pieles',
    'latex':               'Látex',
    'resinas-inflamables': 'Resinas',
    'materiales-aislantes':        'Mat. Aislante',
    'silex':               'Sílex',
    'granito':             'Granito',
    'obsidiana':           'Obsidiana',
    'arena-de-silice':     'Arena Sílice',
    'arena-de-cuarzo':     'Arena Cuarzo',
    'cristales-naturales':       'Cristales',
    'silicio':             'Silicio',
    'minerales-semiconductores':            'Min. Semicon.',
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
    'materiales-magneticos':         'Mat. Magnético',
};

/** Materiales que produce cada casilla por ronda según su nivel actual */
const PRODUCTION_DATA = {
    bosque: {
        1: [{ m: 'roble', q: 5 }],
        2: [{ m: 'roble', q: 8 }, { m: 'pino', q: 8 }],
        3: [{ m: 'roble', q: 8 }, { m: 'pino', q: 8 }, { m: 'carbon-natural', q: 8 }],
        4: [{ m: 'roble', q: 9 }, { m: 'pino', q: 9 }, { m: 'carbon-natural', q: 9 }, { m: 'pieles', q: 9 }],
        5: [{ m: 'latex', q: 8 }, { m: 'resinas-inflamables', q: 8 }, { m: 'materiales-aislantes', q: 4 }],
    },
    cantera: {
        1: [{ m: 'silex', q: 5 }],
        2: [{ m: 'silex', q: 8 }, { m: 'granito', q: 8 }],
        3: [{ m: 'silex', q: 8 }, { m: 'granito', q: 8 }, { m: 'obsidiana', q: 8 }],
        4: [{ m: 'silex', q: 9 }, { m: 'granito', q: 9 }, { m: 'obsidiana', q: 9 }],
        5: [{ m: 'arena-de-silice', q: 8 }, { m: 'arena-de-cuarzo', q: 8 }, { m: 'cristales-naturales', q: 8 }, { m: 'silicio', q: 10 }, { m: 'minerales-semiconductores', q: 8 }],
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
        5: [{ m: 'oro', q: 8 }, { m: 'materiales-magneticos', q: 8 }],
    },
};

/**
 * Coste para subir FROM el nivel indicado al siguiente.
 * Solo usa materiales producibles en ese nivel o inferior.
 */
const UPGRADE_COSTS = {
    bosque: {
        1: [{ m: 'roble', q: 10 }],
        2: [{ m: 'roble', q: 8 }, { m: 'pino', q: 8 }],
        3: [{ m: 'roble', q: 8 }, { m: 'pino', q: 8 }, { m: 'carbon-natural', q: 8 }],
        4: [{ m: 'roble', q: 9 }, { m: 'pino', q: 9 }, { m: 'carbon-natural', q: 9 }, { m: 'pieles', q: 9 }],
    },
    cantera: {
        1: [{ m: 'silex', q: 10 }],
        2: [{ m: 'silex', q: 8 }, { m: 'granito', q: 8 }],
        3: [{ m: 'silex', q: 8 }, { m: 'granito', q: 8 }, { m: 'obsidiana', q: 8 }],
        4: [{ m: 'silex', q: 9 }, { m: 'granito', q: 9 }, { m: 'obsidiana', q: 9 }],
    },
    rio: {
        1: [{ m: 'agua', q: 10 }],
        2: [{ m: 'agua', q: 8 }, { m: 'cana-comun', q: 8 }],
        3: [{ m: 'agua', q: 8 }, { m: 'cana-comun', q: 8 }, { m: 'tierras-fertiles', q: 8 }],
        4: [{ m: 'agua', q: 9 }, { m: 'cana-comun', q: 9 }, { m: 'tierras-fertiles', q: 9 }],
    },
    prado: {
        1: [{ m: 'lino', q: 10 }],
        2: [{ m: 'lino', q: 8 }, { m: 'yute', q: 8 }],
        3: [{ m: 'lino', q: 8 }, { m: 'yute', q: 8 }, { m: 'canamo', q: 8 }],
        4: [{ m: 'lino', q: 9 }, { m: 'yute', q: 9 }, { m: 'canamo', q: 9 }, { m: 'lana', q: 9 }],
    },
    mina: {
        1: [{ m: 'cobre', q: 10 }],
        2: [{ m: 'cobre', q: 8 }, { m: 'hierro', q: 8 }],
        3: [{ m: 'cobre', q: 10 }, { m: 'hierro', q: 10 }, { m: 'estano', q: 10 }],
        4: [{ m: 'cobre', q: 12 }, { m: 'hierro', q: 12 }, { m: 'estano', q: 12 }, { m: 'grafito', q: 12 }],
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
    const production  = PRODUCTION_DATA[baseType]?.[level] ?? null;
    const upgradeCost = level < 5
        ? (UPGRADE_COSTS[baseType]?.[level] ?? null)
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
            {row('Nivel', `${level} / 5`)}
            {row('Produce', <MaterialList items={production} />)}
            {row(
                'Subir Nv.',
                upgradeCost
                    ? <><MaterialList items={upgradeCost} /><span style={{ opacity: 0.45 }}> · 1 acción</span></>
                    : <span style={{ opacity: 0.6 }}>Nivel máximo</span>
            )}
        </div>
    );
}

/** @type {Record<string, string>} Color de fondo por tipo de terreno */
const TILE_BG_COLORS = {
    bosque:  '#458B74',
    cantera: '#8B7355',
    prado:   '#CD4F39',
    rio:     '#4682B4',
    mina:    '#DAA520',
};

const FOG_BG_COLOR        = '#a0a0a0';
const EXPLORABLE_BG_COLOR = '#c0c0c0';
const DEFAULT_BORDER      = '2px solid #fff';

/** Mezcla un color hex con blanco según el nivel (1=más claro, 5=color original) */
const LEVEL_WHITE_BLEND = { 1: 0.55, 2: 0.38, 3: 0.22, 4: 0.08, 5: 0 };
function tileColorForLevel(hex, level) {
    const factor = LEVEL_WHITE_BLEND[level] ?? 0;
    if (factor === 0) return hex;
    const r = parseInt(hex.slice(1, 3), 16);
    const g = parseInt(hex.slice(3, 5), 16);
    const b = parseInt(hex.slice(5, 7), 16);
    return `rgb(${Math.round(r + (255 - r) * factor)},${Math.round(g + (255 - g) * factor)},${Math.round(b + (255 - b) * factor)})`;
}

/** @type {Record<string, string>} Icono por tipo de terreno base */
const TILE_ICONS = {
    bosque:  bosqueIcon,
    cantera: canteraIcon,
    rio:     rioIcon,
    prado:   pradoIcon,
    mina:    minaIcon,
};

/**
 * Calcula el conjunto de coordenadas "coord_x,coord_y" adyacentes a tiles explorados.
 * Si no hay ningún tile explorado, devuelve null (sin restricción de adyacencia).
 * @param {object[]} tiles
 * @returns {Set<string>|null}
 */
function buildExplorableCoordSet(tiles, currentUserId) {
    if (!currentUserId) return new Set();

    const myExploredCoords = tiles
        .filter((t) => t.explored_by_player_id === currentUserId)
        .map((t) => `${t.coord_x},${t.coord_y}`);

    if (myExploredCoords.length === 0) return new Set();

    const myExploredSet = new Set(myExploredCoords);
    const allExploredSet = new Set(
        tiles.filter((t) => t.explored).map((t) => `${t.coord_x},${t.coord_y}`)
    );
    const explorable = new Set();

    for (const coord of myExploredSet) {
        const [x, y] = coord.split(',').map(Number);
        for (const neighbor of [`${x - 1},${y}`, `${x + 1},${y}`, `${x},${y - 1}`, `${x},${y + 1}`]) {
            if (!allExploredSet.has(neighbor)) explorable.add(neighbor);
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
    const [isHovered, setIsHovered] = useState(false);

    const isExplored    = tile.explored;
    const isOwnTile     = !tile.assigned_player || tile.assigned_player === currentUserId;
    const baseType      = tile.type?.base_type;
    const level         = tile.type?.level ?? 0;
    const isUpgradeable  = isExplored && isOwnTile && level < 5;
    const isExplorableOwn = !isExplored && isExplorable && isOwnTile;

    const isClickable     = isOwnTile && (isExplored || isExplorable);
    const backgroundColor = isExplored
        ? tileColorForLevel(TILE_BG_COLORS[baseType] ?? FOG_BG_COLOR, level || 1)
        : isExplorable ? EXPLORABLE_BG_COLOR : FOG_BG_COLOR;
    const tileIcon        = isExplored ? TILE_ICONS[baseType] : null;
    const borderStyle     = DEFAULT_BORDER;
    const tileName        = tile.type?.name ?? TYPE_LABELS[baseType] ?? baseType;

    const specificTestIdAttributes = {
        'data-x':     tile.coord_x,
        'data-y':     tile.coord_y,
        'data-owner': tile.assigned_player,
        ...(isExplored ? { 'data-base-type': baseType } : {}),
    };

    const tileLabel = isExplored
        ? `${tileName}, coordenadas ${tile.coord_x},${tile.coord_y}${isUpgradeable ? '. Mejorable.' : ''}`
        : isExplorable
            ? `Casilla desconocida explorable, coordenadas ${tile.coord_x},${tile.coord_y}`
            : `Casilla en niebla de guerra, coordenadas ${tile.coord_x},${tile.coord_y}`;

    return (
        <div
            data-testid="tile"
            className={`tile${isExplored ? '' : ' tile--fog'}`}
            style={{
                backgroundColor,
                width: '100%',
                aspectRatio: '1 / 1',
                border: borderStyle,
            }}
            onMouseEnter={(e) => { setIsHovered(true);  if (isExplored) onHoverEnter(tile, e); }}
            onMouseLeave={() =>  { setIsHovered(false); if (isExplored) onHoverLeave(); }}
        >
            <div
                data-testid={`tile-${tile.coord_x}-${tile.coord_y}`}
                {...specificTestIdAttributes}
                role={isClickable ? 'button' : 'img'}
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
                            top:             '2px',
                            right:           '2px',
                            fontSize:        '8px',
                            backgroundColor: '#fff',
                            color:           '#000',
                            borderRadius:    '50%',
                            width:           '13px',
                            height:          '13px',
                            display:         'flex',
                            alignItems:      'center',
                            justifyContent:  'center',
                            padding:         0,
                            minWidth:        'auto',
                            lineHeight:      1,
                            pointerEvents:   'none',
                        }}
                    />
                )}
                {isHovered && isUpgradeable && (
                    <div
                        aria-hidden="true"
                        style={{
                            position:        'absolute',
                            inset:           0,
                            backgroundColor: 'rgba(0,0,0,0.40)',
                            display:         'flex',
                            flexDirection:   'column',
                            alignItems:      'center',
                            justifyContent:  'center',
                            gap:             '1px',
                            pointerEvents:   'none',
                        }}
                    >
                        <span style={{ fontSize: '11px', color: '#fff', lineHeight: 1 }}>▲</span>
                        <span style={{ fontSize: '7px', color: '#fff', textTransform: 'uppercase', letterSpacing: '0.05em', lineHeight: 1 }}>Subir</span>
                    </div>
                )}
                {isHovered && isExplorableOwn && (
                    <div
                        aria-hidden="true"
                        style={{
                            position:        'absolute',
                            inset:           0,
                            backgroundColor: 'rgba(0,0,0,0.35)',
                            display:         'flex',
                            flexDirection:   'column',
                            alignItems:      'center',
                            justifyContent:  'center',
                            gap:             '1px',
                            pointerEvents:   'none',
                        }}
                    >
                        <span style={{ fontSize: '11px', color: '#fff', lineHeight: 1 }}>◉</span>
                        <span style={{ fontSize: '7px', color: '#fff', textTransform: 'uppercase', letterSpacing: '0.05em', lineHeight: 1 }}>Explorar</span>
                    </div>
                )}
            </div>
        </div>
    );
}

/**
 * Cuadrícula principal 15×15 del tablero de juego.
 */
function BoardGrid() {
    const { user: currentUser } = useAuth();
    const { currentGame } = useGames();
    const { tiles, isLoading, exploreTile, upgradeTile } = useBoard(currentGame?.id);
    const [hoveredTile, setHoveredTile] = useState(null);
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
    const explorableCoordSet = useMemo(() => buildExplorableCoordSet(tiles, currentUser?.id), [tiles, currentUser?.id]);

    /**
     * Devuelve true si el tile es explorable según la regla de adyacencia.
     * Si no hay tiles explorados aún (partida nueva), todos son explorables.
     * @param {object} tile
     * @returns {boolean}
     */
    function isTileExplorable(tile) {
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
