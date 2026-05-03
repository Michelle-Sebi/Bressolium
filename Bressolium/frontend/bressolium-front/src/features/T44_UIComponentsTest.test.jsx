// ==========================================
// TEST FOR: TASK 44 — Contexts + UI Components Reutilizables
// Validates: ThemeContext, ToastContext, Button, Input, Modal, Toast, Badge, IconTile
// ==========================================

import React from 'react';
import { render, screen, fireEvent, act } from '@testing-library/react';
import { describe, it, expect } from 'vitest';
import fs from 'node:fs';
import path from 'node:path';
import { fileURLToPath } from 'node:url';

const __dirname = path.dirname(fileURLToPath(import.meta.url));
const SRC = path.resolve(__dirname, '..');

async function dynamicImport(relativePath) {
    try {
        return await import(/* @vite-ignore */ relativePath);
    } catch {
        throw new Error(`Módulo no encontrado: ${relativePath} — ¿T44 está implementada?`);
    }
}

// ─── 1. Ficheros existentes ──────────────────────────────────────────────────

describe('T44 — ficheros existentes', () => {
    it('existe src/contexts/ThemeContext.jsx', () => {
        expect(fs.existsSync(path.join(SRC, 'contexts', 'ThemeContext.jsx'))).toBe(true);
    });

    it('existe src/contexts/ToastContext.jsx', () => {
        expect(fs.existsSync(path.join(SRC, 'contexts', 'ToastContext.jsx'))).toBe(true);
    });

    it('existe src/components/ui/Button.jsx', () => {
        expect(fs.existsSync(path.join(SRC, 'components', 'ui', 'Button.jsx'))).toBe(true);
    });

    it('existe src/components/ui/Input.jsx', () => {
        expect(fs.existsSync(path.join(SRC, 'components', 'ui', 'Input.jsx'))).toBe(true);
    });

    it('existe src/components/ui/Modal.jsx', () => {
        expect(fs.existsSync(path.join(SRC, 'components', 'ui', 'Modal.jsx'))).toBe(true);
    });

    it('existe src/components/ui/Toast.jsx', () => {
        expect(fs.existsSync(path.join(SRC, 'components', 'ui', 'Toast.jsx'))).toBe(true);
    });

    it('existe src/components/ui/Badge.jsx', () => {
        expect(fs.existsSync(path.join(SRC, 'components', 'ui', 'Badge.jsx'))).toBe(true);
    });

    it('existe src/components/ui/IconTile.jsx', () => {
        expect(fs.existsSync(path.join(SRC, 'components', 'ui', 'IconTile.jsx'))).toBe(true);
    });
});

// ─── 2. ThemeContext ─────────────────────────────────────────────────────────

describe('ThemeContext', () => {
    it('exporta ThemeProvider como componente', async () => {
        const mod = await dynamicImport('../contexts/ThemeContext');
        const ThemeProvider = mod.ThemeProvider ?? mod.default;
        expect(typeof ThemeProvider).toBe('function');
    });

    it('exporta useTheme como hook (función)', async () => {
        const mod = await dynamicImport('../contexts/ThemeContext');
        expect(typeof mod.useTheme).toBe('function');
    });

    it('useTheme dentro de ThemeProvider devuelve un objeto con colores', async () => {
        const { ThemeProvider, useTheme } = await dynamicImport('../contexts/ThemeContext');

        function Consumer() {
            const theme = useTheme();
            return <div data-testid="theme">{JSON.stringify(theme)}</div>;
        }

        render(<ThemeProvider><Consumer /></ThemeProvider>);
        const content = screen.getByTestId('theme').textContent;
        expect(content).toBeTruthy();
        expect(content).not.toBe('{}');
    });

    it('el tema incluye los colores principales de la guía de estilos', async () => {
        const { ThemeProvider, useTheme } = await dynamicImport('../contexts/ThemeContext');

        function Consumer() {
            const theme = useTheme();
            return <div data-testid="theme">{JSON.stringify(theme)}</div>;
        }

        render(<ThemeProvider><Consumer /></ThemeProvider>);
        const content = screen.getByTestId('theme').textContent;
        // Paleta brutalista: verde primario, marrón secundario
        expect(content).toMatch(/458B74|8B7355/i);
    });
});

// ─── 3. ToastContext ─────────────────────────────────────────────────────────

describe('ToastContext', () => {
    it('exporta ToastProvider como componente', async () => {
        const mod = await dynamicImport('../contexts/ToastContext');
        const ToastProvider = mod.ToastProvider ?? mod.default;
        expect(typeof ToastProvider).toBe('function');
    });

    it('exporta useToast como hook (función)', async () => {
        const mod = await dynamicImport('../contexts/ToastContext');
        expect(typeof mod.useToast).toBe('function');
    });

    it('useToast expone show como función', async () => {
        const { ToastProvider, useToast } = await dynamicImport('../contexts/ToastContext');

        function Consumer() {
            const { show } = useToast();
            return <div data-testid="show-type">{typeof show}</div>;
        }

        render(<ToastProvider><Consumer /></ToastProvider>);
        expect(screen.getByTestId('show-type').textContent).toBe('function');
    });

    it('useToast expone dismiss como función', async () => {
        const { ToastProvider, useToast } = await dynamicImport('../contexts/ToastContext');

        function Consumer() {
            const { dismiss } = useToast();
            return <div data-testid="dismiss-type">{typeof dismiss}</div>;
        }

        render(<ToastProvider><Consumer /></ToastProvider>);
        expect(screen.getByTestId('dismiss-type').textContent).toBe('function');
    });

    it('show() añade un toast a la lista', async () => {
        const { ToastProvider, useToast } = await dynamicImport('../contexts/ToastContext');

        function Consumer() {
            const { toasts, show } = useToast();
            return (
                <>
                    <button onClick={() => show('Hola mundo', 'success')}>add</button>
                    <div data-testid="count">{toasts.length}</div>
                </>
            );
        }

        render(<ToastProvider><Consumer /></ToastProvider>);
        expect(screen.getByTestId('count').textContent).toBe('0');

        act(() => { fireEvent.click(screen.getByText('add')); });
        expect(screen.getByTestId('count').textContent).toBe('1');
    });

    it('dismiss() elimina un toast por id', async () => {
        const { ToastProvider, useToast } = await dynamicImport('../contexts/ToastContext');

        function Consumer() {
            const { toasts, show, dismiss } = useToast();
            return (
                <>
                    <button onClick={() => show('msg', 'info')}>add</button>
                    {toasts.map(t => (
                        <button key={t.id} onClick={() => dismiss(t.id)} data-testid={`dismiss-${t.id}`}>
                            dismiss
                        </button>
                    ))}
                    <div data-testid="count">{toasts.length}</div>
                </>
            );
        }

        render(<ToastProvider><Consumer /></ToastProvider>);
        act(() => { fireEvent.click(screen.getByText('add')); });
        expect(screen.getByTestId('count').textContent).toBe('1');

        const dismissBtn = screen.getByText('dismiss');
        act(() => { fireEvent.click(dismissBtn); });
        expect(screen.getByTestId('count').textContent).toBe('0');
    });
});

// ─── 4. Button ───────────────────────────────────────────────────────────────

describe('Button — componente brutalista', () => {
    it('renderiza un <button> con el texto pasado como children', async () => {
        const { default: Button } = await dynamicImport('../components/ui/Button');
        render(<Button>EXPLORAR</Button>);
        expect(screen.getByRole('button', { name: 'EXPLORAR' })).toBeInTheDocument();
    });

    it('llama a onClick al hacer click', async () => {
        const { default: Button } = await dynamicImport('../components/ui/Button');
        let clicked = false;
        render(<Button onClick={() => { clicked = true; }}>click</Button>);
        fireEvent.click(screen.getByRole('button'));
        expect(clicked).toBe(true);
    });

    it('queda deshabilitado cuando disabled=true', async () => {
        const { default: Button } = await dynamicImport('../components/ui/Button');
        render(<Button disabled>no click</Button>);
        expect(screen.getByRole('button')).toBeDisabled();
    });

    it('acepta variant="primary" sin errores', async () => {
        const { default: Button } = await dynamicImport('../components/ui/Button');
        expect(() => render(<Button variant="primary">ok</Button>)).not.toThrow();
    });

    it('acepta variant="danger" sin errores', async () => {
        const { default: Button } = await dynamicImport('../components/ui/Button');
        expect(() => render(<Button variant="danger">ok</Button>)).not.toThrow();
    });

    it('acepta variant="secondary" sin errores', async () => {
        const { default: Button } = await dynamicImport('../components/ui/Button');
        expect(() => render(<Button variant="secondary">ok</Button>)).not.toThrow();
    });
});

// ─── 5. Input ────────────────────────────────────────────────────────────────

describe('Input — componente brutalista', () => {
    it('renderiza un <input>', async () => {
        const { default: Input } = await dynamicImport('../components/ui/Input');
        render(<Input />);
        expect(screen.getByRole('textbox')).toBeInTheDocument();
    });

    it('muestra el placeholder recibido', async () => {
        const { default: Input } = await dynamicImport('../components/ui/Input');
        render(<Input placeholder="BUSCAR EQUIPO..." />);
        expect(screen.getByPlaceholderText('BUSCAR EQUIPO...')).toBeInTheDocument();
    });

    it('llama a onChange al escribir', async () => {
        const { default: Input } = await dynamicImport('../components/ui/Input');
        let value = '';
        render(<Input value={value} onChange={(e) => { value = e.target.value; }} />);
        fireEvent.change(screen.getByRole('textbox'), { target: { value: 'test' } });
        expect(value).toBe('test');
    });
});

// ─── 6. Modal ────────────────────────────────────────────────────────────────

describe('Modal — componente brutalista', () => {
    it('no renderiza nada cuando isOpen=false', async () => {
        const { default: Modal } = await dynamicImport('../components/ui/Modal');
        render(<Modal isOpen={false} onClose={() => {}} title="Test"><p>contenido</p></Modal>);
        expect(screen.queryByText('contenido')).not.toBeInTheDocument();
    });

    it('renderiza el contenido cuando isOpen=true', async () => {
        const { default: Modal } = await dynamicImport('../components/ui/Modal');
        render(<Modal isOpen={true} onClose={() => {}} title="Mi Modal"><p>contenido visible</p></Modal>);
        expect(screen.getByText('contenido visible')).toBeInTheDocument();
    });

    it('muestra el título cuando isOpen=true', async () => {
        const { default: Modal } = await dynamicImport('../components/ui/Modal');
        render(<Modal isOpen={true} onClose={() => {}} title="FUNDAR EQUIPO"><p>x</p></Modal>);
        expect(screen.getByText('FUNDAR EQUIPO')).toBeInTheDocument();
    });

    it('llama a onClose al pulsar el botón de cerrar', async () => {
        const { default: Modal } = await dynamicImport('../components/ui/Modal');
        let closed = false;
        render(<Modal isOpen={true} onClose={() => { closed = true; }} title="Test"><p>x</p></Modal>);
        fireEvent.click(screen.getByRole('button'));
        expect(closed).toBe(true);
    });
});

// ─── 7. Toast ────────────────────────────────────────────────────────────────

describe('Toast — componente brutalista', () => {
    it('renderiza el mensaje recibido', async () => {
        const { default: Toast } = await dynamicImport('../components/ui/Toast');
        render(<Toast message="Casilla explorada" type="success" onDismiss={() => {}} />);
        expect(screen.getByText('Casilla explorada')).toBeInTheDocument();
    });

    it('llama a onDismiss al pulsar el botón de cierre', async () => {
        const { default: Toast } = await dynamicImport('../components/ui/Toast');
        let dismissed = false;
        render(<Toast message="msg" type="info" onDismiss={() => { dismissed = true; }} />);
        fireEvent.click(screen.getByRole('button'));
        expect(dismissed).toBe(true);
    });

    it('acepta type="success", "error" e "info" sin errores', async () => {
        const { default: Toast } = await dynamicImport('../components/ui/Toast');
        expect(() => render(<Toast message="ok" type="success" onDismiss={() => {}} />)).not.toThrow();
        expect(() => render(<Toast message="ok" type="error"   onDismiss={() => {}} />)).not.toThrow();
        expect(() => render(<Toast message="ok" type="info"    onDismiss={() => {}} />)).not.toThrow();
    });
});

// ─── 8. Badge ────────────────────────────────────────────────────────────────

describe('Badge — componente brutalista', () => {
    it('muestra el número recibido como count', async () => {
        const { default: Badge } = await dynamicImport('../components/ui/Badge');
        render(<Badge count={7} />);
        expect(screen.getByText('7')).toBeInTheDocument();
    });

    it('no renderiza nada cuando count es 0', async () => {
        const { default: Badge } = await dynamicImport('../components/ui/Badge');
        const { container } = render(<Badge count={0} />);
        expect(container.firstChild).toBeNull();
    });
});

// ─── 9. IconTile ─────────────────────────────────────────────────────────────

describe('IconTile — componente brutalista', () => {
    it('renderiza una imagen <img>', async () => {
        const { default: IconTile } = await dynamicImport('../components/ui/IconTile');
        render(<IconTile src="/bosque.png" alt="bosque" />);
        expect(screen.getByRole('img')).toBeInTheDocument();
    });

    it('aplica el src y alt recibidos', async () => {
        const { default: IconTile } = await dynamicImport('../components/ui/IconTile');
        render(<IconTile src="/cantera.png" alt="cantera" />);
        const img = screen.getByRole('img');
        expect(img).toHaveAttribute('src', '/cantera.png');
        expect(img).toHaveAttribute('alt', 'cantera');
    });
});
