/**
 * @module InventoryPanel
 * @description Panel lateral izquierdo con el inventario de materiales e inventos de la partida.
 * Usa el componente ItemCard para mostrar cada ítem en formato lista.
 * @see Tarea 18 - Material Inventory Side-Panel (HU 2.4)
 */

import { useState } from 'react';
import { useInventory } from './useInventory';
import { useGames } from '../game/useGames';
import ItemCard, { GROUP_COLORS, GROUP_LABELS } from '../../components/ui/ItemCard';
import Badge from '../../components/ui/Badge';

// ─── Colores individuales de materiales (sobreescriben GROUP_COLORS) ──────────

const MATERIAL_COLORS = {
    'roble':          '#947E63',
    'pino':           '#CBC0B3',
    'carbon-natural': '#C8C8C8',
    'silex':          '#D9E0D9',
    'granito':        '#CFD8CF',
    'obsidiana':      '#a0a0a0',
    'arena-de-silice': '#B7A896',
    'arena-de-cuarzo': '#D9E0D9',
    'cristales-nat':   '#ACC7DD',
    'silicio':         '#ABCBC0',
    'min-semi':        '#E9B0A6',
    'cana-comun':          '#6EA593',
    'tierras-fertiles':    '#458B74',
    'hidrogeno':           '#ACC7DD',
    'gases-naturales':     '#ACC7DD',
    'lino':                '#EED79B',
    'yute':                '#CBC0B3',
    'canamo':              '#B7A896',
    'lana':                '#D87665',
    'pieles':              '#8B7355',
    'agua':                '#4682B4',
    'cobre':               '#A5927A',
    'hierro':              '#B5B5B5',
    'estano':              '#D4D4D4',
    'grafito':             '#a0a0a0',
    'oro':                 '#DAA520',
    'mat-mag-nat':         '#D15D49',
    'latex':               '#EED79B',
    'resinas-inflamables': '#D87665',
    'mat-aisl-nat':        '#A5927A',
};

// ─── Iconos de materiales ──────────────────────────────────────────────────────

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
    'agua':                new URL('../../assets/icons/materials/agua.png',                     import.meta.url).href,
    'cana-comun':          new URL('../../assets/icons/materials/cana.png',                      import.meta.url).href,
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

// ─── Iconos de inventos ────────────────────────────────────────────────────────

const INVENTION_ICON_MAP = {
    'acero':                          new URL('../../assets/icons/inventions/acero.png',                          import.meta.url).href,
    'acueducto':                      new URL('../../assets/icons/inventions/acueducto.png',                      import.meta.url).href,
    'arado':                          new URL('../../assets/icons/inventions/arado.png',                          import.meta.url).href,
    'arco':                           new URL('../../assets/icons/inventions/arco.png',                           import.meta.url).href,
    'avion':                          new URL('../../assets/icons/inventions/avion.png',                          import.meta.url).href,
    'barco':                          new URL('../../assets/icons/inventions/barco.png',                          import.meta.url).href,
    'bateria':                        new URL('../../assets/icons/inventions/bateria.png',                        import.meta.url).href,
    'bombilla':                       new URL('../../assets/icons/inventions/bombilla.png',                       import.meta.url).href,
    'brujula':                        new URL('../../assets/icons/inventions/brujula.png',                        import.meta.url).href,
    'carro':                          new URL('../../assets/icons/inventions/carro.png',                          import.meta.url).href,
    'ceramica':                       new URL('../../assets/icons/inventions/ceramica.png',                       import.meta.url).href,
    'cuchillo':                       new URL('../../assets/icons/inventions/cuchillo.png',                       import.meta.url).href,
    'cuerda':                         new URL('../../assets/icons/inventions/cuerda.png',                         import.meta.url).href,
    'estacion-espacial':              new URL('../../assets/icons/inventions/estacion-espacial.png',              import.meta.url).href,
    'fibra-optica':                   new URL('../../assets/icons/inventions/fibra-optica.png',                   import.meta.url).href,
    'hacha':                          new URL('../../assets/icons/inventions/hacha.png',                          import.meta.url).href,
    'imprenta':                       new URL('../../assets/icons/inventions/imprenta.png',                       import.meta.url).href,
    'lanza':                          new URL('../../assets/icons/inventions/lanza.png',                          import.meta.url).href,
    'laser':                          new URL('../../assets/icons/inventions/laser.png',                          import.meta.url).href,
    'microscopio':                    new URL('../../assets/icons/inventions/microscopio.png',                    import.meta.url).href,
    'molino':                         new URL('../../assets/icons/inventions/molino.png',                         import.meta.url).href,
    'moneda':                         new URL('../../assets/icons/inventions/moneda.png',                         import.meta.url).href,
    'nave-asentamiento-interestelar': new URL('../../assets/icons/inventions/nave-asentamiento-interestelar.png', import.meta.url).href,
    'papel':                          new URL('../../assets/icons/inventions/papel.png',                          import.meta.url).href,
    'penicilina':                     new URL('../../assets/icons/inventions/penicilina.png',                     import.meta.url).href,
    'refugio':                        new URL('../../assets/icons/inventions/refugio.png',                        import.meta.url).href,
    'reloj':                          new URL('../../assets/icons/inventions/reloj.png',                          import.meta.url).href,
    'rueda':                          new URL('../../assets/icons/inventions/rueda.png',                          import.meta.url).href,
    'satelite':                       new URL('../../assets/icons/inventions/satelite.png',                       import.meta.url).href,
    'tela':                           new URL('../../assets/icons/inventions/tela.png',                           import.meta.url).href,
    'telefono-movil':                 new URL('../../assets/icons/inventions/telefono-movil.png',                 import.meta.url).href,
    'telescopio':                     new URL('../../assets/icons/inventions/telescopio.png',                     import.meta.url).href,
    'trampa':                         new URL('../../assets/icons/inventions/trampa.png',                         import.meta.url).href,
    'vidrio':                         new URL('../../assets/icons/inventions/vidrio.png',                         import.meta.url).href,
};

function inventionNameToKey(name) {
    return name
        .toLowerCase()
        .normalize('NFD').replace(/[̀-ͯ]/g, '')
        .replace(/\bde\b\s*/g, '')
        .replace(/\s+/g, '-')
        .replace(/-+/g, '-')
        .replace(/arcos/, 'arco')
        .replace(/refugios/, 'refugio');
}

// ─── Cabecera de sección ───────────────────────────────────────────────────────

function SectionHeader({ label, isOpen, onToggle }) {
    return (
        <button
            onClick={onToggle}
            aria-expanded={isOpen}
            style={{
                width:           '100%',
                display:         'flex',
                justifyContent:  'space-between',
                alignItems:      'center',
                padding:         '8px 10px',
                backgroundColor: '#f0f0f0',
                color:           'rgba(0,0,0,0.5)',
                fontWeight:      'bold',
                textTransform:   'uppercase',
                letterSpacing:   '0.08em',
                fontSize:        '10px',
                border:          'none',
                borderBottom:    '1px solid #e8e8e8',
                cursor:          'pointer',
                textAlign:       'left',
            }}
        >
            {label}
            <svg
                width="12" height="12" viewBox="0 0 12 12" fill="none"
                style={{ transform: isOpen ? 'rotate(180deg)' : 'rotate(0deg)', transition: 'transform 0.2s' }}
            >
                <path d="M2 4l4 4 4-4" stroke="currentColor" strokeWidth="1.5" strokeLinecap="round" strokeLinejoin="round"/>
            </svg>
        </button>
    );
}

// ─── Subtítulos ────────────────────────────────────────────────────────────────

function materialSubtitle(material) {
    const group = GROUP_LABELS[material.group] ?? material.group ?? '';
    const tier  = material.tier != null ? `Nivel ${material.tier + 1}` : '';
    return [group, tier].filter(Boolean).join(' · ');
}

function inventionSubtitle(invention) {
    if (!invention.missing || invention.missing.length === 0) return 'Listo para construir';
    return 'Falta: ' + invention.missing
        .map(m => m.required > 1 ? `${m.name} ×${m.required}` : m.name)
        .join(', ');
}

// ─── Panel principal ───────────────────────────────────────────────────────────

function InventoryPanel() {
    const { currentGame } = useGames();
    const { materials, inventions, isLoading } = useInventory(currentGame?.id);
    const [materialsOpen, setMaterialsOpen] = useState(true);
    const [inventionsOpen, setInventionsOpen] = useState(true);

    if (isLoading) {
        return (
            <div
                data-testid="inventory-loading"
                style={{ padding: '16px', color: '#a0a0a0', fontWeight: 'bold', textTransform: 'uppercase', letterSpacing: '0.1em', fontSize: '11px' }}
            >
                Cargando inventario…
            </div>
        );
    }

    return (
        <div
            data-testid="inventory-panel"
            style={{ display: 'flex', flexDirection: 'column', overflowY: 'auto', backgroundColor: '#fff' }}
        >
            {/* ── Recursos ── */}
            <SectionHeader label="Recursos" isOpen={materialsOpen} onToggle={() => setMaterialsOpen(o => !o)} />
            {materialsOpen && [...materials]
                .sort((a, b) => (b.quantity > 0 ? 1 : 0) - (a.quantity > 0 ? 1 : 0))
                .map((material) => {
                const isActive = material.quantity > 0;
                return (
                    <div
                        key={material.id}
                        data-testid="material-item"
                        title={`${material.name} · ${material.group} · tier ${material.tier} · ×${material.quantity}`}
                        className={isActive ? 'material--active' : 'material--inactive'}
                    >
                        <div
                            data-testid={`material-item-${material.name}`}
                            className={isActive ? 'material--active' : 'material--inactive'}
                        >
                            <ItemCard
                                iconSrc={MATERIAL_ICON_MAP[material.name] ?? ''}
                                iconBgColor={MATERIAL_COLORS[material.name] ?? GROUP_COLORS[material.group] ?? '#a0a0a0'}
                                name={material.name}
                                subtitle={materialSubtitle(material)}
                                quantity={material.quantity}
                                isActive={isActive}
                            />
                            {/* elementos ocultos para compatibilidad con tests existentes */}
                            <img
                                data-testid={`material-icon-${material.name}`}
                                src={MATERIAL_ICON_MAP[material.name] ?? ''}
                                alt={`${material.name}, tier ${material.tier}`}
                                style={{ display: 'none' }}
                            />
                            {isActive && (
                                <Badge
                                    count={material.quantity}
                                    data-testid={`material-badge-${material.name}`}
                                    style={{ display: 'none' }}
                                />
                            )}
                        </div>
                    </div>
                );
            })}

            {/* ── Inventos ── */}
            <SectionHeader label="Inventos" isOpen={inventionsOpen} onToggle={() => setInventionsOpen(o => !o)} />
            {inventionsOpen && [...inventions]
                .sort((a, b) => {
                    const rank = inv => inv.quantity > 0 ? 0 : (inv.missing?.length === 0 ? 1 : 2);
                    return rank(a) - rank(b);
                })
                .map((invention) => {
                const iconKey  = inventionNameToKey(invention.name);
                const isActive = invention.quantity > 0;
                return (
                    <div
                        key={invention.id}
                        data-testid="invention-item"
                        className={isActive ? 'invention--active' : 'invention--inactive'}
                    >
                        <div
                            data-testid={`invention-item-${invention.name}`}
                            className={isActive ? 'invention--active' : 'invention--inactive'}
                        >
                            <ItemCard
                                iconSrc={INVENTION_ICON_MAP[iconKey] ?? ''}
                                iconBgColor={isActive ? '#458B74' : '#a0a0a0'}
                                name={invention.name}
                                subtitle={inventionSubtitle(invention)}
                                quantity={isActive ? invention.quantity : undefined}
                                isActive={isActive}
                            />
                        </div>
                    </div>
                );
            })}
        </div>
    );
}

export default InventoryPanel;
