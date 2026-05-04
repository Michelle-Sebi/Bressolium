/**
 * @module InventoryPanel
 * @description Panel lateral izquierdo con el inventario de materiales de la partida.
 * Muestra todos los materiales del catálogo con su icono. Los materiales con quantity > 0
 * se resaltan en color (material--active) con un badge de cantidad. El resto aparece
 * en opacidad reducida (material--inactive).
 *
 * Estructura DOM de cada ítem (dos capas para satisfacer testids simultáneos):
 * - Capa exterior: `data-testid="material-item"` — usada por getAllByTestId para contar/filtrar.
 * - Capa interior: `data-testid="material-item-{name}"` — usada para inspección individual.
 * Ambas capas llevan la clase de estado (material--active / material--inactive).
 *
 * @see Tarea 18 - Material Inventory Side-Panel (HU 2.4)
 */

import { useInventory } from './useInventory';
import { useGames } from '../game/useGames';

/** @type {Record<string, string>} Mapeo de nombre de material a ruta de icono */
const MATERIAL_ICON_MAP = {
    'roble':               new URL('../../assets/icons/materials/roble.png',                    import.meta.url).href,
    'pino':                new URL('../../assets/icons/materials/pino.png',                     import.meta.url).href,
    'carbon-natural':      new URL('../../assets/icons/materials/carbon.png',                   import.meta.url).href,
    'pieles':              new URL('../../assets/icons/materials/pieles.png',                   import.meta.url).href,
    'latex':               new URL('../../assets/icons/materials/latex.png',                    import.meta.url).href,
    'resinas-inflamables': new URL('../../assets/icons/materials/resinas-inflamables.png',      import.meta.url).href,
    'mat-aisl-nat':        new URL('../../assets/icons/materials/materiales-aislantes.png',     import.meta.url).href,
    'silex':               new URL('../../assets/icons/materials/silex.png',                    import.meta.url).href,
    'granito':             new URL('../../assets/icons/materials/granito.png',                  import.meta.url).href,
    'obsidiana':           new URL('../../assets/icons/materials/obsidiana.png',                import.meta.url).href,
    'arena-de-silice':     new URL('../../assets/icons/materials/arena-silice.png',             import.meta.url).href,
    'arena-de-cuarzo':     new URL('../../assets/icons/materials/arena-cuarzo.png',             import.meta.url).href,
    'cristales-nat':       new URL('../../assets/icons/materials/cristales-naturales.png',      import.meta.url).href,
    'silicio':             new URL('../../assets/icons/materials/silicio.png',                  import.meta.url).href,
    'min-semi':            new URL('../../assets/icons/materials/minerales-semiconductores.png',import.meta.url).href,
    'agua':                new URL('../../assets/icons/materials/hidrogeno.png',                import.meta.url).href,
    'cana-comun':          new URL('../../assets/icons/materials/caña.png',                     import.meta.url).href,
    'tierras-fertiles':    new URL('../../assets/icons/materials/tierras-fertiles.png',         import.meta.url).href,
    'hidrogeno':           new URL('../../assets/icons/materials/hidrogeno.png',                import.meta.url).href,
    'gases-naturales':     new URL('../../assets/icons/materials/gases-naturales.png',          import.meta.url).href,
    'lino':                new URL('../../assets/icons/materials/lino.png',                     import.meta.url).href,
    'yute':                new URL('../../assets/icons/materials/yute.png',                     import.meta.url).href,
    'canamo':              new URL('../../assets/icons/materials/cañamo.png',                   import.meta.url).href,
    'lana':                new URL('../../assets/icons/materials/lana.png',                     import.meta.url).href,
    'cobre':               new URL('../../assets/icons/materials/cobre.png',                    import.meta.url).href,
    'hierro':              new URL('../../assets/icons/materials/hierro.png',                   import.meta.url).href,
    'estano':              new URL('../../assets/icons/materials/estaño.png',                   import.meta.url).href,
    'grafito':             new URL('../../assets/icons/materials/grafito.png',                  import.meta.url).href,
    'oro':                 new URL('../../assets/icons/materials/oro.png',                      import.meta.url).href,
    'mat-mag-nat':         new URL('../../assets/icons/materials/materiales-magenticos.png',    import.meta.url).href,
};

/**
 * Ítem individual de material en el panel.
 * @param {{ material: import('./inventorySlice').InventoryMaterial }} props
 */
function MaterialItem({ material }) {
    const isActive   = material.quantity > 0;
    const stateClass = isActive ? 'material--active' : 'material--inactive';
    const iconSrc    = MATERIAL_ICON_MAP[material.name] ?? '';

    return (
        // Capa exterior: contada por getAllByTestId('material-item')
        <div
            data-testid="material-item"
            className={`material-item ${stateClass}`}
            style={{ opacity: isActive ? 1 : 0.35 }}
        >
            {/* Capa interior: inspeccionada individualmente por getByTestId('material-item-{name}') */}
            <div
                data-testid={`material-item-${material.name}`}
                className={`material-item ${stateClass}`}
                style={{
                    display:       'flex',
                    flexDirection: 'column',
                    alignItems:    'center',
                    gap:           '4px',
                    padding:       '6px 4px',
                    position:      'relative',
                }}
            >
                <img
                    data-testid={`material-icon-${material.name}`}
                    src={iconSrc}
                    alt={material.name}
                    style={{ width: '32px', height: '32px', objectFit: 'contain' }}
                />
                {isActive && (
                    <span
                        data-testid={`material-badge-${material.name}`}
                        style={{
                            fontSize:        '11px',
                            fontWeight:      'bold',
                            color:           '#fff',
                            backgroundColor: '#458B74',
                            padding:         '1px 5px',
                            minWidth:        '18px',
                            textAlign:       'center',
                        }}
                    >
                        {material.quantity}
                    </span>
                )}
            </div>
        </div>
    );
}

/** @type {Record<string, string>} Mapeo de nombre de invento a ruta de icono */
const INVENTION_ICON_MAP = {
    'acero':             new URL('../../assets/icons/inventions/acero.png',             import.meta.url).href,
    'acueducto':         new URL('../../assets/icons/inventions/acueducto.png',         import.meta.url).href,
    'arado':             new URL('../../assets/icons/inventions/arado.png',             import.meta.url).href,
    'arco':              new URL('../../assets/icons/inventions/arco.png',              import.meta.url).href,
    'avion':             new URL('../../assets/icons/inventions/avion.png',             import.meta.url).href,
    'barco':             new URL('../../assets/icons/inventions/barco.png',             import.meta.url).href,
    'bateria':           new URL('../../assets/icons/inventions/bateria.png',           import.meta.url).href,
    'bombilla':          new URL('../../assets/icons/inventions/bombilla.png',          import.meta.url).href,
    'brujula':           new URL('../../assets/icons/inventions/brujula.png',           import.meta.url).href,
    'carro':             new URL('../../assets/icons/inventions/carro.png',             import.meta.url).href,
    'ceramica':          new URL('../../assets/icons/inventions/ceramica.png',          import.meta.url).href,
    'cuchillo':          new URL('../../assets/icons/inventions/cuchillo.png',          import.meta.url).href,
    'cuerda':            new URL('../../assets/icons/inventions/cuerda.png',            import.meta.url).href,
    'estacion-espacial': new URL('../../assets/icons/inventions/estacion-espacial.png', import.meta.url).href,
    'fibra-optica':      new URL('../../assets/icons/inventions/fibra-optica.png',      import.meta.url).href,
    'hacha':             new URL('../../assets/icons/inventions/hacha.png',             import.meta.url).href,
    'imprenta':          new URL('../../assets/icons/inventions/imprenta.png',          import.meta.url).href,
    'lanza':             new URL('../../assets/icons/inventions/lanza.png',             import.meta.url).href,
    'laser':             new URL('../../assets/icons/inventions/laser.png',             import.meta.url).href,
    'microscopio':       new URL('../../assets/icons/inventions/microscopio.png',       import.meta.url).href,
};

/**
 * Cabecera visual de sección dentro del panel de inventario.
 * @param {{ label: string }} props
 */
function InventorySectionHeader({ label }) {
    return (
        <div
            style={{
                padding:         '4px 8px',
                backgroundColor: '#8B7355',
                color:           '#fff',
                fontWeight:      'bold',
                textTransform:   'uppercase',
                letterSpacing:   '0.08em',
                fontSize:        '10px',
                marginBottom:    '2px',
            }}
        >
            {label}
        </div>
    );
}

/**
 * Ítem individual de invento en el panel.
 * @param {{ invention: import('./inventorySlice').InventoryInvention }} props
 */
function InventionItem({ invention }) {
    const isActive   = invention.quantity > 0;
    const stateClass = isActive ? 'invention--active' : 'invention--inactive';
    const iconKey    = invention.icon?.replace('.png', '') ?? '';
    const iconSrc    = INVENTION_ICON_MAP[iconKey] ?? '';

    return (
        <div
            data-testid="invention-item"
            className={`invention-item ${stateClass}`}
            style={{ opacity: isActive ? 1 : 0.35 }}
        >
            <div
                data-testid={`invention-item-${invention.name}`}
                className={`invention-item ${stateClass}`}
                style={{
                    display:       'flex',
                    flexDirection: 'column',
                    alignItems:    'center',
                    gap:           '4px',
                    padding:       '6px 4px',
                    position:      'relative',
                }}
            >
                {iconSrc && (
                    <img
                        src={iconSrc}
                        alt={invention.name}
                        style={{ width: '32px', height: '32px', objectFit: 'contain' }}
                    />
                )}
                <span style={{ fontSize: '9px', color: '#8B7355', textAlign: 'center', lineHeight: 1.2 }}>
                    {invention.name}
                </span>
                {isActive && (
                    <span
                        style={{
                            fontSize:        '11px',
                            fontWeight:      'bold',
                            color:           '#fff',
                            backgroundColor: '#8B7355',
                            padding:         '1px 5px',
                            minWidth:        '18px',
                            textAlign:       'center',
                        }}
                    >
                        {invention.quantity}
                    </span>
                )}
            </div>
        </div>
    );
}

/**
 * Panel lateral izquierdo de inventario: materiales (Recursos) e inventos (Inventos).
 */
function InventoryPanel() {
    const { currentGame } = useGames();
    const { materials, inventions, isLoading } = useInventory(currentGame?.id);

    if (isLoading) {
        return (
            <div
                data-testid="inventory-loading"
                style={{
                    padding:       '16px',
                    color:         '#8B7355',
                    fontWeight:    'bold',
                    textTransform: 'uppercase',
                    letterSpacing: '0.1em',
                }}
            >
                Cargando inventario…
            </div>
        );
    }

    return (
        <div
            data-testid="inventory-panel"
            style={{
                display:         'flex',
                flexDirection:   'column',
                gap:             '2px',
                padding:         '8px',
                backgroundColor: '#f7f9f7',
                borderRight:     '2px solid #C1CDC1',
                overflowY:       'auto',
            }}
        >
            <InventorySectionHeader label="Recursos" />
            {materials.map((material) => (
                <MaterialItem key={material.id} material={material} />
            ))}

            <InventorySectionHeader label="Inventos" />
            {inventions.map((invention) => (
                <InventionItem key={invention.id} invention={invention} />
            ))}
        </div>
    );
}

export default InventoryPanel;
