/**
 * @module BoardGrid
 * @description Cuadrícula 15×15 del tablero de juego.
 * Muestra el mapa con niebla de guerra, colores por tipo de terreno e iconos de casilla.
 * Solo permite explorar casillas adyacentes (4 ortogonales) a casillas ya exploradas.
 * @see Tarea 9 - Board Grid Component y Frontend UI (HU 2.1, 2.2, 2.6)
 */

import { useState, useMemo } from 'react';
import { useBoard } from './useBoard';
import { useAuth } from '../auth/useAuth';
import { useGames } from '../game/useGames';
import TechTreeModal from '../techtree/TechTreeModal';

import bosqueIcon  from '../../assets/icons/tiles/bosque.png';
import canteraIcon from '../../assets/icons/tiles/cantera.png';
import rioIcon     from '../../assets/icons/tiles/rio.png';
import pradoIcon   from '../../assets/icons/tiles/prado.png';
import minaIcon    from '../../assets/icons/tiles/mina.png';
import puebloIcon  from '../../assets/icons/tiles/pueblo.png';

/** @type {Record<string, string>} Color de fondo por tipo de terreno */
const TILE_BG_COLORS = {
    bosque:  '#458B74',
    cantera: '#696969',
    prado:   '#8FBC8F',
    rio:     '#4682B4',
    mina:    '#DAA520',
    pueblo:  '#C1CDC1',
};

const FOG_BG_COLOR       = '#8B7355';
const EXPLORABLE_BORDER  = '2px solid rgba(255,255,255,0.6)';
const DEFAULT_BORDER     = '1px solid rgba(0,0,0,0.15)';

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
function Tile({ tile, currentUserId, isExplorable, onTileClick }) {
    const isExplored = tile.explored;
    const isOwnTile  = !tile.assigned_player || tile.assigned_player === currentUserId;
    const baseType   = tile.type?.base_type;
    const level      = tile.type?.level ?? 0;

    const isClickable     = isOwnTile && (isExplored || isExplorable);
    const backgroundColor = isExplored ? (TILE_BG_COLORS[baseType] ?? FOG_BG_COLOR) : FOG_BG_COLOR;
    const tileIcon        = isExplored ? TILE_ICONS[baseType] : null;
    const borderStyle     = (!isExplored && isExplorable) ? EXPLORABLE_BORDER : DEFAULT_BORDER;

    const specificTestIdAttributes = {
        'data-x':     tile.coord_x,
        'data-y':     tile.coord_y,
        'data-owner': tile.assigned_player,
        ...(isExplored ? { 'data-base-type': baseType } : {}),
    };

    const isPueblo = baseType === 'pueblo';

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
        >
            <div
                data-testid={`tile-${tile.coord_x}-${tile.coord_y}`}
                {...specificTestIdAttributes}
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
            >
                {tileIcon && (
                    <img
                        src={tileIcon}
                        alt={baseType}
                        style={{ width: '60%', height: '60%', objectFit: 'contain' }}
                    />
                )}
                {isExplored && level > 0 && (
                    <span style={{
                        position:        'absolute',
                        bottom:          '1px',
                        right:           '2px',
                        fontSize:        '8px',
                        fontWeight:      'bold',
                        color:           '#fff',
                        textShadow:      '0 0 2px rgba(0,0,0,0.8)',
                        lineHeight:      1,
                        pointerEvents:   'none',
                    }}>
                        {level}
                    </span>
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
    const [isTechTreeOpen, setIsTechTreeOpen] = useState(false);

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
                <span className="font-bold uppercase tracking-widest" style={{ color: '#8B7355' }}>
                    Cargando tablero…
                </span>
            </div>
        );
    }

    return (
        <>
        <TechTreeModal isOpen={isTechTreeOpen} onClose={() => setIsTechTreeOpen(false)} />
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
                />
            ))}
        </div>
        </>
    );
}

export default BoardGrid;
