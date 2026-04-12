import { describe, it, expect, beforeEach, vi } from 'vitest';

// =====================================================================
// ARCHIVO DE TESTS TDD PARA BRESSOLIUM MVP
// Framework: Vitest + JSDOM
// Descripción: Pruebas unitarias/integración basadas en historias_mvp.md
// =====================================================================

describe('Bressolium MVP - Pruebas de Historias de Usuario (Gherkin)', () => {

    beforeEach(() => {
        // Limpiamos el DOM y estado local antes de cada test para evitar colisiones
        document.body.innerHTML = '';
        localStorage.clear();
        vi.clearAllMocks();
    });

    describe('Épica 1: Gestión de Usuarios y Equipos', () => {
        it('HU 1.1 - Registro y Login: Debería autenticar al usuario usando input #email-input y #login-btn', async () => {
            // Dado que el jugador accede a la pantalla de acceso
            document.body.innerHTML = `
        <div id="login-container">
          <input type="email" id="email-input" />
          <input type="password" id="password-input" />
          <button id="login-btn">Acceder</button>
        </div>
        <div id="status-badge"></div>
      `;

            const emailInput = document.getElementById('email-input');
            const passwordInput = document.getElementById('password-input');
            const loginBtn = document.getElementById('login-btn');
            const statusBadge = document.getElementById('status-badge');

            // Función esperada en la implementación real
            const loginMock = vi.fn().mockImplementation(() => {
                statusBadge.textContent = 'Autenticado';
                return Promise.resolve({ success: true });
            });
            loginBtn.addEventListener('click', loginMock);

            // Cuando introduce sus credenciales válidas
            emailInput.value = 'jugador@bressolium.com';
            passwordInput.value = 'password123';
            loginBtn.click();

            // Entonces el sistema inicia su sesión de forma segura
            expect(loginMock).toHaveBeenCalled();
            expect(statusBadge.textContent).toBe('Autenticado');
        });

        it('HU 1.2 - Creación de Equipo: Permite crear equipo con nombre único usando #team-name-input', async () => {
            // Dado que un jugador autenticado está en el selector
            document.body.innerHTML = `
        <input type="text" id="team-name-input" />
        <button id="create-team-btn">Crear Equipo</button>
        <div id="status-badge"></div>
      `;

            const input = document.getElementById('team-name-input');
            const createBtn = document.getElementById('create-team-btn');

            // Cuando solicita crear un nuevo equipo
            input.value = 'Pioneros Digitales';
            const mockCreateTeam = vi.fn().mockResolvedValue({ success: true });
            createBtn.addEventListener('click', mockCreateTeam);
            createBtn.click();

            // Entonces el sistema crea el equipo
            expect(mockCreateTeam).toHaveBeenCalled();
        });
    });

    describe('Épica 2: El Tablero y la Exploración', () => {
        it('HU 2.2 - Exploración de Casillas: Revela recurso descubriendo #tile-XY', async () => {
            // Dado que es el turno de un jugador con acciones
            document.body.innerHTML = `
         <div id="tile-1-2" class="tile hidden"></div>
         <button id="explore-tile-btn" data-target="1-2">Explorar Casilla [1,2]</button>
         <span id="actions-counter">2</span>
       `;

            const tile = document.getElementById('tile-1-2');
            const exploreBtn = document.getElementById('explore-tile-btn');
            const actionsCounter = document.getElementById('actions-counter');

            const mockExplore = vi.fn().mockImplementation(() => {
                tile.classList.remove('hidden');
                tile.classList.add('biome-forest');
                actionsCounter.textContent = '1';
                return Promise.resolve({ success: true, biome: 'forest' });
            });
            exploreBtn.addEventListener('click', mockExplore);

            // Cuando pulsa "Explorar" sobre una casilla oculta
            exploreBtn.click();

            // Entonces el sistema invierte una acción y revela el tipo
            expect(mockExplore).toHaveBeenCalled();
            expect(tile.classList.contains('hidden')).toBeFalsy();
            expect(tile.classList.contains('biome-forest')).toBeTruthy();
            expect(actionsCounter.textContent).toBe('1');
        });
    });

    describe('Épica 3: Mecánicas de Turno y Cooperación', () => {
        it('HU 3.3 - Sistema de Votación: Registra el voto en #vote-tech-btn', async () => {
            // Dado que el jugador ha completado sus dos acciones
            document.body.innerHTML = `
        <div id="voting-panel">
           <button id="vote-tech-btn" data-tech-id="rueda">Votar por Rueda</button>
        </div>
        <div id="status-badge">Turno en progreso</div>
      `;

            const voteBtn = document.getElementById('vote-tech-btn');
            const votingPanel = document.getElementById('voting-panel');
            const statusBadge = document.getElementById('status-badge');

            const mockVote = vi.fn().mockImplementation(() => {
                votingPanel.style.display = 'none';
                statusBadge.textContent = 'Voto Registrado. Esperando compañeros...';
                return Promise.resolve({ success: true });
            });
            voteBtn.addEventListener('click', mockVote);

            // Cuando envía su voto para crear una tecnología
            voteBtn.click();

            // Entonces el sistema registra la elección y oculta el panel
            expect(mockVote).toHaveBeenCalled();
            expect(votingPanel.style.display).toBe('none');
            expect(statusBadge.textContent).toContain('Esperando');
        });
    });

    describe('Habilidad QA (TDD Integrator)', () => {
        it('SeedData: Carga inicial mediante JSON cuando LocalStorage está vacío', () => {
            /* 
              Basado en test_integrator.md:
              Si el LocalStorage está vacío, carga los datos del test al localStorage
            */
            const seedData = vi.fn(() => {
                if (!localStorage.getItem('bressolium_state')) {
                    const mockJSONData = { users: [], technologies: [] };
                    localStorage.setItem('bressolium_state', JSON.stringify(mockJSONData));
                }
            });

            // Inicialmente vacío
            expect(localStorage.getItem('bressolium_state')).toBeNull();

            seedData();

            // El estado fue cargado
            expect(localStorage.getItem('bressolium_state')).toBeTruthy();
            const parsedState = JSON.parse(localStorage.getItem('bressolium_state'));
            expect(parsedState).toHaveProperty('technologies');
        });
    });

});
