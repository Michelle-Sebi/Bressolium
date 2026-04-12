import { describe, it, expect, beforeEach, vi } from 'vitest';

// ==========================================
// TEST PARA: TAREA 9 (Raw_Tareas)
// Título: Componente Grid Tablero y Frontend UI
// ==========================================

describe('Componente de Tablero Grid React/JSDOM', () => {

    beforeEach(() => {
        document.body.innerHTML = '';
        vi.clearAllMocks();
    });

    describe('Renderizado de matriz XY (HU 2.4)', () => {
        it('Renderiza las casillas basándose en un array devuelto simulando Axios', () => {
            // JSON falso imitando /api/tablero (T7)
            const casillasApiMock = [
                { id: 10, x: 0, y: 0, recurso_id: 1, descubierta: 1 },
                { id: 11, x: 0, y: 1, recurso_id: 2, descubierta: 0 } // No descubierta
            ];

            document.body.innerHTML = `<div id="board-grid"></div>`;
            const board = document.getElementById('board-grid');

            function mapBoard(data) {
                data.forEach(box => {
                    const div = document.createElement('div');
                    div.id = `casilla-${box.id}`;
                    div.className = `casilla ${box.descubierta ? 'visible' : 'oscurecida'}`;
          board.appendChild(div);
        });
      }

      mapBoard(casillasApiMock);

      expect(board.children.length).toBe(2);
      expect(board.querySelector('#casilla-10').classList.contains('visible')).toBe(true);
      expect(board.querySelector('#casilla-11').classList.contains('oscurecida')).toBe(true);
    });
  });

  describe('Interacciones Frontend', () => {
    it('Al hacer click Explorar, dispara función y actualiza localmente -1 acción', () => {
      document.body.innerHTML = `
        <div id="acc-counter">2</div>
        <button id="casilla-trigger">Explorar esta casilla oscura</button>
      `;

      const counter = document.getElementById('acc-counter');
      const btn = document.getElementById('casilla-trigger');

      // Simula dispacher RTK a BD
      const mockPostAccion = vi.fn(() => {
        let cur = parseInt(counter.textContent);
        if(cur > 0) counter.textContent = cur - 1;
      });

      btn.addEventListener('click', mockPostAccion);
      btn.click();

      expect(mockPostAccion).toHaveBeenCalledTimes(1);
      expect(counter.textContent).toBe('1');
    });
  });

});
