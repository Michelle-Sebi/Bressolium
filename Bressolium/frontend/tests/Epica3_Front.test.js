import { describe, it, expect, beforeEach, vi } from 'vitest';

// ==========================================
// TEST PARA: TAREA 12 (Raw_Tareas)
// Título: UI Votación interactiva
// ==========================================

describe('Front: Modal de Votaciones (HU 2.5 y HU 3.3)', () => {

    beforeEach(() => {
        document.body.innerHTML = '';
        vi.clearAllMocks();
    });

    it('Genera el render de Candados si falta un Prerrequisito', () => {
        // Array API falso de tecnologías
        const mockRecetas = [
            { id: 1, nombre: 'Rueda', unlocked: true, requires: null },
            { id: 2, nombre: 'Carro', unlocked: false, requires: 1 }, // Puede votarse porque tiene la 1
            { id: 3, nombre: 'Motor', unlocked: false, requires: 99 }  // Bloqueada por prerequisito 99
        ];

        document.body.innerHTML = \`<div id="modal-content"></div>\`;
    const modal = document.getElementById('modal-content');

    function renderModal(recetasArr) {
      recetasArr.forEach(receta => {
        const btn = document.createElement('button');
        btn.id = \`tech-\${receta.id}\`;
        
        // Lógica pseudo-Redux de validación (El requisito 99 no está desbloqueado)
        let isLocked = false;
        if(receta.requires) {
           const finder = recetasArr.find(r => r.id === receta.requires);
           if(!finder || !finder.unlocked) isLocked = true;
        }

        if (isLocked) {
          btn.disabled = true;
          btn.textContent = '🔒 ' + receta.nombre;
        } else {
          btn.textContent = receta.nombre;
        }

        modal.appendChild(btn);
      });
    }

    renderModal(mockRecetas);

    // Carro puede votarse
    expect(document.getElementById('tech-2').disabled).toBe(false);
    
    // Motor está bloqueado
    expect(document.getElementById('tech-3').disabled).toBe(true);
    expect(document.getElementById('tech-3').textContent).toContain('🔒');
  });

});
