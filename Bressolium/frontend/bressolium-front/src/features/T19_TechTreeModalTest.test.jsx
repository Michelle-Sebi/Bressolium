// ==========================================
// TEST FOR: TASK 19 — [Feat] Technology Tree & Progress Archive
// Validates: TechTreeModal component, useTechTree hook,
//            categorización completed/available/blocked,
//            integración con RTK Query (bressoliumApi.getSync),
//            renderizado de secciones y datos faltantes en bloqueados
// HU: 4.1
// ==========================================

import React from 'react';
import { render, screen, fireEvent, act, waitFor } from '@testing-library/react';
import { configureStore } from '@reduxjs/toolkit';
import { Provider } from 'react-redux';
import { renderHook } from '@testing-library/react';
import { describe, it, expect } from 'vitest';
import fs from 'node:fs';
import path from 'node:path';
import { fileURLToPath } from 'node:url';

import authReducer      from './auth/authSlice';
import gameReducer      from './game/gameSlice';
import boardReducer     from './board/boardSlice';
import inventoryReducer from './inventory/inventorySlice';
import { bressoliumApi } from '../services/bressoliumApi';

const __dirname = path.dirname(fileURLToPath(import.meta.url));
const SRC = path.resolve(__dirname, '..');

// ─── Store factory ────────────────────────────────────────────────────────────

function makeStore() {
    return configureStore({
        reducer: {
            auth:      authReducer,
            game:      gameReducer,
            board:     boardReducer,
            inventory: inventoryReducer,
            [bressoliumApi.reducerPath]: bressoliumApi.reducer,
        },
        middleware: (g) => g().concat(bressoliumApi.middleware),
    });
}

function wrapper(store) {
    return ({ children }) => React.createElement(Provider, { store }, children);
}

// ─── Mock sync con árbol tecnológico ─────────────────────────────────────────
//
// El endpoint getSync devuelve en progress.technologies cada tecnología con:
//   is_active          — true si el equipo ya la tiene investigada
//   prerequisites_met  — true si todos sus prerequisitos están activos
//   missing            — lista de {name, type} de lo que falta para desbloquearla
//
// El hook useTechTree categoriza en:
//   completed  → is_active: true
//   available  → is_active: false && prerequisites_met: true
//   blocked    → is_active: false && prerequisites_met: false

const mockTechTreeSync = {
    current_round:  { number: 2, start_date: '2026-05-03' },
    user_actions:   { actions_spent: 0 },
    inventory:      [],
    progress: {
        technologies: [
            {
                id:                'tech-fuego',
                name:              'Control del Fuego',
                is_active:         true,
                prerequisites_met: true,
                missing:           [],
            },
            {
                id:                'tech-ceramica',
                name:              'Cerámica y Alfarería',
                is_active:         false,
                prerequisites_met: true,
                missing:           [],
            },
            {
                id:                'tech-ganaderia',
                name:              'Ganadería',
                is_active:         false,
                prerequisites_met: true,
                missing:           [],
            },
            {
                id:                'tech-escritura',
                name:              'Escritura',
                is_active:         false,
                prerequisites_met: false,
                missing:           [{ name: 'Cerámica y Alfarería', type: 'technology' }],
            },
            {
                id:                'tech-metalurgia',
                name:              'Metalurgia y Aleaciones',
                is_active:         false,
                prerequisites_met: false,
                missing:           [
                    { name: 'Control del Fuego', type: 'technology' },
                    { name: 'Ganadería',          type: 'technology' },
                ],
            },
        ],
        inventions: [],
    },
};

// ─── 1. Ficheros existentes ──────────────────────────────────────────────────

describe('T19 — ficheros existentes', () => {
    it('existe src/features/techtree/TechTreeModal.jsx', () => {
        expect(
            fs.existsSync(path.join(SRC, 'features', 'techtree', 'TechTreeModal.jsx'))
        ).toBe(true);
    });

    it('existe src/features/techtree/useTechTree.js', () => {
        expect(
            fs.existsSync(path.join(SRC, 'features', 'techtree', 'useTechTree.js'))
        ).toBe(true);
    });
});

// ─── 2. useTechTree — estructura del retorno ──────────────────────────────────

describe('useTechTree — estructura', () => {
    async function importHook() {
        const mod = await import(
            /* @vite-ignore */
            path.join(SRC, 'features', 'techtree', 'useTechTree.js')
        );
        return mod.useTechTree ?? mod.default;
    }

    it('exporta una función useTechTree', async () => {
        const useTechTree = await importHook();
        expect(typeof useTechTree).toBe('function');
    });

    it('devuelve { completed, available, blocked, isLoading } con arrays vacíos por defecto', async () => {
        const useTechTree = await importHook();
        const store = makeStore();
        const { result } = renderHook(() => useTechTree('game-1'), { wrapper: wrapper(store) });

        expect(Array.isArray(result.current.completed)).toBe(true);
        expect(Array.isArray(result.current.available)).toBe(true);
        expect(Array.isArray(result.current.blocked)).toBe(true);
        expect(typeof result.current.isLoading).toBe('boolean');
    });

    it('devuelve arrays vacíos antes de que haya datos en caché', async () => {
        const useTechTree = await importHook();
        const store = makeStore();
        const { result } = renderHook(() => useTechTree('game-1'), { wrapper: wrapper(store) });

        expect(result.current.completed).toHaveLength(0);
        expect(result.current.available).toHaveLength(0);
        expect(result.current.blocked).toHaveLength(0);
    });
});

// ─── 3. useTechTree — categorización de tecnologías ─────────────────────────

describe('useTechTree — categorización', () => {
    async function importHook() {
        const mod = await import(
            /* @vite-ignore */
            path.join(SRC, 'features', 'techtree', 'useTechTree.js')
        );
        return mod.useTechTree ?? mod.default;
    }

    async function hookWithData() {
        const useTechTree = await importHook();
        const store = makeStore();

        await act(async () => {
            await store.dispatch(
                bressoliumApi.util.upsertQueryData('getSync', 'game-1', mockTechTreeSync)
            );
        });

        const { result } = renderHook(() => useTechTree('game-1'), { wrapper: wrapper(store) });
        await waitFor(() => expect(result.current.completed.length).toBeGreaterThan(0));
        return result.current;
    }

    it('las tecnologías con is_active=true van a completed', async () => {
        const { completed } = await hookWithData();
        expect(completed.some(t => t.name === 'Control del Fuego')).toBe(true);
    });

    it('las tecnologías con is_active=false y prerequisites_met=true van a available', async () => {
        const { available } = await hookWithData();
        expect(available.some(t => t.name === 'Cerámica y Alfarería')).toBe(true);
        expect(available.some(t => t.name === 'Ganadería')).toBe(true);
    });

    it('las tecnologías con prerequisites_met=false van a blocked', async () => {
        const { blocked } = await hookWithData();
        expect(blocked.some(t => t.name === 'Escritura')).toBe(true);
        expect(blocked.some(t => t.name === 'Metalurgia y Aleaciones')).toBe(true);
    });

    it('las tecnologías completadas NO aparecen en available ni en blocked', async () => {
        const { available, blocked } = await hookWithData();
        expect(available.some(t => t.name === 'Control del Fuego')).toBe(false);
        expect(blocked.some(t => t.name === 'Control del Fuego')).toBe(false);
    });

    it('los elementos bloqueados exponen el array missing con lo que falta', async () => {
        const { blocked } = await hookWithData();
        const escritura = blocked.find(t => t.name === 'Escritura');
        expect(escritura).toBeDefined();
        expect(Array.isArray(escritura.missing)).toBe(true);
        expect(escritura.missing.length).toBeGreaterThan(0);
    });

    it('missing incluye el nombre del prerrequisito que falta', async () => {
        const { blocked } = await hookWithData();
        const escritura = blocked.find(t => t.name === 'Escritura');
        expect(escritura.missing.some(m => m.name === 'Cerámica y Alfarería')).toBe(true);
    });

    it('una tecnología con varios prerequisitos en missing los lista todos', async () => {
        const { blocked } = await hookWithData();
        const metalurgia = blocked.find(t => t.name === 'Metalurgia y Aleaciones');
        expect(metalurgia.missing.length).toBeGreaterThanOrEqual(2);
    });
});

// ─── 4. TechTreeModal — renderizado de secciones ─────────────────────────────

describe('TechTreeModal — secciones', () => {
    async function importModal() {
        const mod = await import(
            /* @vite-ignore */
            path.join(SRC, 'features', 'techtree', 'TechTreeModal.jsx')
        );
        return mod.default ?? mod.TechTreeModal;
    }

    function renderModal(TechTreeModal, props = {}) {
        const store = makeStore();
        const defaults = {
            isOpen:    true,
            onClose:   () => {},
            completed: [],
            available: [],
            blocked:   [],
        };
        render(
            React.createElement(Provider, { store },
                React.createElement(TechTreeModal, { ...defaults, ...props })
            )
        );
    }

    it('no renderiza nada cuando isOpen=false', async () => {
        const TechTreeModal = await importModal();
        const store = makeStore();
        const { container } = render(
            React.createElement(Provider, { store },
                React.createElement(TechTreeModal, {
                    isOpen: false, onClose: () => {},
                    completed: [], available: [], blocked: [],
                })
            )
        );
        expect(container.firstChild).toBeNull();
    });

    it('renderiza el modal cuando isOpen=true', async () => {
        const TechTreeModal = await importModal();
        renderModal(TechTreeModal);
        expect(screen.getByRole('dialog') ?? document.body.firstChild).toBeTruthy();
    });

    it('tiene una sección o encabezado para tecnologías completadas', async () => {
        const TechTreeModal = await importModal();
        renderModal(TechTreeModal);
        expect(
            screen.getByText(/completad/i) ||
            screen.getByText(/investigad/i) ||
            screen.getByText(/activ/i)
        ).toBeTruthy();
    });

    it('tiene una sección o encabezado para tecnologías disponibles', async () => {
        const TechTreeModal = await importModal();
        renderModal(TechTreeModal);
        expect(
            screen.getByText(/disponib/i) ||
            screen.getByText(/investigab/i)
        ).toBeTruthy();
    });

    it('tiene una sección o encabezado para tecnologías bloqueadas', async () => {
        const TechTreeModal = await importModal();
        renderModal(TechTreeModal);
        expect(
            screen.getByText(/bloquead/i) ||
            screen.getByText(/pendiente/i)
        ).toBeTruthy();
    });

    it('muestra el nombre de una tecnología completada en su sección', async () => {
        const TechTreeModal = await importModal();
        renderModal(TechTreeModal, {
            completed: [{ id: 'tech-fuego', name: 'Control del Fuego', missing: [] }],
        });
        expect(screen.getByText('Control del Fuego')).toBeTruthy();
    });

    it('muestra el nombre de una tecnología disponible en su sección', async () => {
        const TechTreeModal = await importModal();
        renderModal(TechTreeModal, {
            available: [{ id: 'tech-ceramica', name: 'Cerámica y Alfarería', missing: [] }],
        });
        expect(screen.getByText('Cerámica y Alfarería')).toBeTruthy();
    });

    it('muestra el nombre de una tecnología bloqueada en su sección', async () => {
        const TechTreeModal = await importModal();
        renderModal(TechTreeModal, {
            blocked: [{
                id:      'tech-escritura',
                name:    'Escritura',
                missing: [{ name: 'Cerámica y Alfarería', type: 'technology' }],
            }],
        });
        expect(screen.getByText('Escritura')).toBeTruthy();
    });

    it('los bloqueados muestran qué falta para desbloquearlos', async () => {
        const TechTreeModal = await importModal();
        renderModal(TechTreeModal, {
            blocked: [{
                id:      'tech-escritura',
                name:    'Escritura',
                missing: [{ name: 'Cerámica y Alfarería', type: 'technology' }],
            }],
        });
        expect(screen.getByText(/Cerámica y Alfarería/i)).toBeTruthy();
    });

    it('llama onClose al hacer click en el botón de cerrar', async () => {
        const TechTreeModal = await importModal();
        const store = makeStore();
        let closed = false;
        render(
            React.createElement(Provider, { store },
                React.createElement(TechTreeModal, {
                    isOpen:    true,
                    onClose:   () => { closed = true; },
                    completed: [],
                    available: [],
                    blocked:   [],
                })
            )
        );
        const closeBtn = screen.getByLabelText('Cerrar') ?? screen.getByRole('button');
        fireEvent.click(closeBtn);
        expect(closed).toBe(true);
    });
});

// ─── 5. Múltiples caminos del árbol ──────────────────────────────────────────
//
// La tecnología final (nave-asentamiento) puede alcanzarse por distintas ramas.
// El modal debe ser capaz de mostrar tecnologías de diferentes familias
// (control del fuego → metalurgia, cerámica → escritura, ganadería → …)
// sin que unas oculten a otras.

describe('TechTreeModal — múltiples caminos', () => {
    async function importModal() {
        const mod = await import(
            /* @vite-ignore */
            path.join(SRC, 'features', 'techtree', 'TechTreeModal.jsx')
        );
        return mod.default ?? mod.TechTreeModal;
    }

    it('muestra simultáneamente tecnologías de distintas ramas', async () => {
        const TechTreeModal = await importModal();
        const store = makeStore();
        render(
            React.createElement(Provider, { store },
                React.createElement(TechTreeModal, {
                    isOpen:    true,
                    onClose:   () => {},
                    completed: [],
                    available: [
                        { id: 'tech-ceramica',  name: 'Cerámica y Alfarería', missing: [] },
                        { id: 'tech-ganaderia', name: 'Ganadería',            missing: [] },
                    ],
                    blocked: [],
                })
            )
        );
        expect(screen.getByText('Cerámica y Alfarería')).toBeTruthy();
        expect(screen.getByText('Ganadería')).toBeTruthy();
    });

    it('un elemento bloqueado con varios requisitos los muestra todos', async () => {
        const TechTreeModal = await importModal();
        const store = makeStore();
        render(
            React.createElement(Provider, { store },
                React.createElement(TechTreeModal, {
                    isOpen:    true,
                    onClose:   () => {},
                    completed: [],
                    available: [],
                    blocked: [{
                        id:      'tech-metalurgia',
                        name:    'Metalurgia y Aleaciones',
                        missing: [
                            { name: 'Control del Fuego', type: 'technology' },
                            { name: 'Ganadería',          type: 'technology' },
                        ],
                    }],
                })
            )
        );
        expect(screen.getByText(/Control del Fuego/i)).toBeTruthy();
        expect(screen.getByText(/Ganadería/i)).toBeTruthy();
    });
});

// ─── T53 — Fix: BoardGrid debe conectar useTechTree al TechTreeModal ─────────
//
// Bug raíz: BoardGrid renderiza <TechTreeModal isOpen={...} onClose={...} />
// sin pasar completed/available/blocked, por lo que el modal siempre aparece vacío.
// Los tests de este bloque fallarán hasta que BoardGrid importe useTechTree
// y pase las tres listas como props al modal.


// ─── T53 — DoD: tecnologías disponibles tienen botón de voto activo ──────────
//
// Las tecnologías en la sección "Disponibles" deben mostrar un botón de votar
// habilitado. Las bloqueadas no deben tenerlo.

describe('T53 — TechTreeModal: disponibles tienen botón de voto activo', () => {
    async function importModal() {
        const mod = await import(
            /* @vite-ignore */
            path.join(SRC, 'features', 'techtree', 'TechTreeModal.jsx')
        );
        return mod.default ?? mod.TechTreeModal;
    }

    it('cada tecnología disponible muestra un botón de votar', async () => {
        const TechTreeModal = await importModal();
        const store = makeStore();
        render(
            React.createElement(Provider, { store },
                React.createElement(TechTreeModal, {
                    isOpen:    true,
                    onClose:   () => {},
                    completed: [],
                    available: [{ id: 'tech-ceramica', name: 'Cerámica y Alfarería', missing: [] }],
                    blocked:   [],
                })
            )
        );
        expect(screen.getByRole('button', { name: /votar/i })).toBeTruthy();
    });

    it('el botón de votar de una tecnología disponible está habilitado', async () => {
        const TechTreeModal = await importModal();
        const store = makeStore();
        render(
            React.createElement(Provider, { store },
                React.createElement(TechTreeModal, {
                    isOpen:    true,
                    onClose:   () => {},
                    completed: [],
                    available: [{ id: 'tech-ceramica', name: 'Cerámica y Alfarería', missing: [] }],
                    blocked:   [],
                })
            )
        );
        expect(screen.getByRole('button', { name: /votar/i })).not.toBeDisabled();
    });

    it('las tecnologías bloqueadas no tienen botón de votar', async () => {
        const TechTreeModal = await importModal();
        const store = makeStore();
        render(
            React.createElement(Provider, { store },
                React.createElement(TechTreeModal, {
                    isOpen:    true,
                    onClose:   () => {},
                    completed: [],
                    available: [],
                    blocked: [{
                        id:      'tech-escritura',
                        name:    'Escritura',
                        missing: [{ name: 'Cerámica y Alfarería', type: 'technology' }],
                    }],
                })
            )
        );
        expect(screen.queryAllByRole('button', { name: /votar/i })).toHaveLength(0);
    });
});

// ─── T53 — DoD: bloqueadas muestran la cantidad requerida ────────────────────
//
// Cuando un prerrequisito faltante tiene quantity > 1, el modal debe indicar
// cuántas unidades se necesitan (ej. "×2" junto al nombre del prerrequisito).

describe('T53 — TechTreeModal: bloqueadas muestran cantidad requerida', () => {
    async function importModal() {
        const mod = await import(
            /* @vite-ignore */
            path.join(SRC, 'features', 'techtree', 'TechTreeModal.jsx')
        );
        return mod.default ?? mod.TechTreeModal;
    }

    it('muestra la cantidad cuando un prerrequisito faltante tiene quantity > 1', async () => {
        const TechTreeModal = await importModal();
        const store = makeStore();
        render(
            React.createElement(Provider, { store },
                React.createElement(TechTreeModal, {
                    isOpen:    true,
                    onClose:   () => {},
                    completed: [],
                    available: [],
                    blocked: [{
                        id:      'tech-metalurgia',
                        name:    'Metalurgia y Aleaciones',
                        missing: [{ name: 'Control del Fuego', type: 'technology', quantity: 3 }],
                    }],
                })
            )
        );
        expect(screen.getByText(/×3|x3/i)).toBeTruthy();
    });

    it('no muestra multiplicador cuando quantity es 1', async () => {
        const TechTreeModal = await importModal();
        const store = makeStore();
        render(
            React.createElement(Provider, { store },
                React.createElement(TechTreeModal, {
                    isOpen:    true,
                    onClose:   () => {},
                    completed: [],
                    available: [],
                    blocked: [{
                        id:      'tech-escritura',
                        name:    'Escritura',
                        missing: [{ name: 'Cerámica y Alfarería', type: 'technology', quantity: 1 }],
                    }],
                })
            )
        );
        expect(screen.queryByText(/×1|x1/i)).toBeNull();
    });
});
