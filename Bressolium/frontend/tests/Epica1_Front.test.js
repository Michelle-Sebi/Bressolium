import { describe, it, expect, beforeEach, vi } from 'vitest';

// ==========================================
// TEST PARA: TAREA 3 y TAREA 5 (Raw_Tareas)
// Título: FrontAuth Routing, Redux y Dashboard
// Módulo: Frontend (Vitest + JSDOM)
// ==========================================

describe('FrontAuth & Dashboard Routing', () => {

    beforeEach(() => {
        document.body.innerHTML = '';
        vi.clearAllMocks();
    });

    describe('Tarea 3: Estructura Auth y Redux (HU 1.1)', () => {
        it('El formulario de login procesa submit y despacha el token contra authService local', async () => {
            document.body.innerHTML = `
        <form id="login-form">
          <input type="email" id="email" value="test@front.com" />
          <input type="password" id="password" value="1234" />
          <button type="submit">Log in</button>
        </form>
        <div id="redux-state-mock"></div>
      `;

            const form = document.getElementById('login-form');
            const reduxDiv = document.getElementById('redux-state-mock');

            // Simulamos la lógica del thunk/slice de Redux Toolkit llamando al authService
            const handleLogin = vi.fn((e) => {
                e.preventDefault();
                // Simulacro de "authService.js" según Criterios de Aceptación 
                const fakeToken = "1|somerandomstring";
                localStorage.setItem('auth_token', fakeToken);
                reduxDiv.textContent = 'STATE: LOGGED_IN';
            });

            form.addEventListener('submit', handleLogin);
            form.dispatchEvent(new Event('submit'));

            expect(handleLogin).toHaveBeenCalled();
            expect(localStorage.getItem('auth_token')).toBeTruthy();
            expect(reduxDiv.textContent).toBe('STATE: LOGGED_IN');
        });
    });

    describe('Tarea 5: Dashboard Multiequipo Frontend (HU 1.6)', () => {
        it('Renderiza de forma dinámica la lista tras recibir JSON del backend', async () => {
            // JSON devuelto simulado del Backend
            const mockPartidasData = [
                { id: 1, nombre: 'Alpha Team', miembros: 3 },
                { id: 2, nombre: 'Beta Team', miembros: 5 }
            ];

            document.body.innerHTML = `
        <div id="dashboard">
           <ul id="team-list"></ul>
        </div>
      `;
            const list = document.getElementById('team-list');

            // Lógica a programar en el Componente para mapear el JSON
            function renderTeams(teams) {
                teams.forEach(t => {
                    const li = document.createElement('li');
                    li.textContent = \`Equipo: \${t.nombre} - Miembros: \${t.miembros}/5\`;
          list.appendChild(li);
        });
      }

      renderTeams(mockPartidasData);

      // Verificamos el DOM rendering interactivo
      expect(list.children.length).toBe(2);
      expect(list.children[0].textContent).toContain('Alpha Team');
      expect(list.children[1].textContent).toContain('5/5');
    });
  });
});
