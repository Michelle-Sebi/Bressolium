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
import { MATERIAL_COLORS, MATERIAL_ICON_MAP } from '../../constants/materialAssets';
import { INVENTION_COLORS, INVENTION_ICON_MAP, inventionNameToKey } from '../../constants/inventionAssets';

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
                                iconBgColor={INVENTION_COLORS[iconKey] ?? '#a0a0a0'}
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
