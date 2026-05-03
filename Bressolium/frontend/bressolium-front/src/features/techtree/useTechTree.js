import { bressoliumApi } from '../../services/bressoliumApi';

/**
 * @typedef {{ id: string, name: string, is_active: boolean, prerequisites_met: boolean, missing: MissingItem[] }} TechItem
 * @typedef {{ name: string, type: 'technology'|'invention' }} MissingItem
 */

/**
 * Hook que categoriza las tecnologías del equipo en tres grupos:
 *   - completed  → is_active=true
 *   - available  → is_active=false y todos los prerrequisitos cumplidos
 *   - blocked    → is_active=false con prerrequisitos pendientes (expone `missing`)
 *
 * @param {string} gameId
 * @returns {{ completed: TechItem[], available: TechItem[], blocked: TechItem[], isLoading: boolean }}
 */
export function useTechTree(gameId) {
    const { data, isLoading } = bressoliumApi.useGetSyncQuery(gameId, { skip: !gameId });

    const technologies = data?.progress?.technologies ?? [];

    const completed = technologies.filter(t =>  t.is_active);
    const available = technologies.filter(t => !t.is_active &&  t.prerequisites_met);
    const blocked   = technologies.filter(t => !t.is_active && !t.prerequisites_met);

    return { completed, available, blocked, isLoading: Boolean(isLoading) };
}
