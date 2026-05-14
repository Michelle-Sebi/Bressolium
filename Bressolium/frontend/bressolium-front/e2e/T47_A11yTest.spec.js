// @ts-check
import { test, expect } from '@playwright/test';
import AxeBuilder from '@axe-core/playwright';

/**
 * T47 — Tests de Accesibilidad (a11y)
 *
 * Audita las páginas públicas y flujos principales con axe-core (WCAG AA).
 * Requisito: entorno de desarrollo levantado (backend en :80, frontend Vite en :5174).
 *
 * Ejecutar:
 *   npx playwright test e2e/T47_A11yTest.spec.js
 */

// ── Helpers ──────────────────────────────────────────────────────────────────

function uniqueCredentials() {
    const ts = Date.now();
    return {
        name:     `A11y ${ts}`,
        email:    `a11y${ts}@e2e.test`,
        password: 'password123',
        teamName: `Equipo A11y ${ts}`,
    };
}

async function registerAndLogin(page, creds) {
    await page.goto('/register');
    await page.fill('#name',     creds.name);
    await page.fill('#email',    creds.email);
    await page.fill('#password', creds.password);
    await page.click('button[type="submit"]');
    await page.waitForURL('**/dashboard', { timeout: 15_000 });
}

// ── Tests ─────────────────────────────────────────────────────────────────────

test.describe('T47 — Accesibilidad (axe-core WCAG AA)', () => {

    // ── Páginas públicas ──────────────────────────────────────────────────────

    test('login: sin violaciones axe WCAG AA', async ({ page }) => {
        await page.goto('/login');
        await page.waitForLoadState('networkidle');

        const results = await new AxeBuilder({ page })
            .withTags(['wcag2a', 'wcag2aa'])
            .analyze();

        expect(results.violations).toEqual([]);
    });

    test('register: sin violaciones axe WCAG AA', async ({ page }) => {
        await page.goto('/register');
        await page.waitForLoadState('networkidle');

        const results = await new AxeBuilder({ page })
            .withTags(['wcag2a', 'wcag2aa'])
            .analyze();

        expect(results.violations).toEqual([]);
    });

    // ── Dashboard ─────────────────────────────────────────────────────────────

    test('dashboard: sin violaciones axe WCAG AA', async ({ page }) => {
        const creds = uniqueCredentials();
        await registerAndLogin(page, creds);

        const results = await new AxeBuilder({ page })
            .withTags(['wcag2a', 'wcag2aa'])
            .analyze();

        expect(results.violations).toEqual([]);
    });

    test('dashboard: modal "Fundar equipo" sin violaciones axe WCAG AA', async ({ page }) => {
        const creds = uniqueCredentials();
        await registerAndLogin(page, creds);

        await page.click('button:has-text("CREAR EQUIPO NUEVO")');
        await page.waitForSelector('[role="dialog"]', { timeout: 5_000 });

        const results = await new AxeBuilder({ page })
            .withTags(['wcag2a', 'wcag2aa'])
            .analyze();

        expect(results.violations).toEqual([]);
    });

    // ── Keyboard navigation ───────────────────────────────────────────────────

    test('dashboard: las game cards son activables por teclado', async ({ page }) => {
        const creds = uniqueCredentials();
        await registerAndLogin(page, creds);

        // Crear una partida para que aparezca en "Mis expediciones"
        await page.click('button:has-text("CREAR EQUIPO NUEVO")');
        await page.waitForSelector('[role="dialog"]');
        await page.fill('#teamName', creds.teamName);
        await page.click('button[type="submit"]');
        await page.waitForTimeout(1_000);

        // Comprobar que la card tiene role="button" y tabIndex
        const card = page.locator('[role="button"]').filter({ hasText: creds.teamName.toUpperCase() }).first();
        await expect(card).toHaveAttribute('tabindex', '0');
    });

    test('dashboard: modal "Fundar equipo" se cierra con Escape', async ({ page }) => {
        const creds = uniqueCredentials();
        await registerAndLogin(page, creds);

        await page.click('button:has-text("CREAR EQUIPO NUEVO")');
        await page.waitForSelector('[role="dialog"]');
        await page.keyboard.press('Escape');
        await expect(page.locator('[role="dialog"]')).toHaveCount(0);
    });

    // ── BoardGrid (requiere estar en una partida) ─────────────────────────────

    test('tablero: tiles explorables tienen role="button" y son activables por teclado', async ({ page }) => {
        const creds = uniqueCredentials();
        await registerAndLogin(page, creds);

        // Crear partida y navegar al tablero
        await page.click('button:has-text("CREAR EQUIPO NUEVO")');
        await page.waitForSelector('[role="dialog"]');
        await page.fill('#teamName', creds.teamName);
        await page.click('button[type="submit"]');
        await page.waitForTimeout(1_000);

        const card = page.locator('[role="button"]').filter({ hasText: creds.teamName.toUpperCase() }).first();
        await card.click();
        await page.waitForURL('**/board', { timeout: 10_000 });
        await page.waitForSelector('[data-testid="board-grid"]', { timeout: 10_000 });

        // Al menos un tile explorable debe tener role="button"
        const explorableTiles = page.locator('[data-testid^="tile-"][role="button"]');
        await expect(explorableTiles.first()).toBeVisible();
        await expect(explorableTiles.first()).toHaveAttribute('tabindex', '0');
    });

    test('tablero: sin violaciones axe WCAG AA', async ({ page }) => {
        const creds = uniqueCredentials();
        await registerAndLogin(page, creds);

        await page.click('button:has-text("CREAR EQUIPO NUEVO")');
        await page.waitForSelector('[role="dialog"]');
        await page.fill('#teamName', creds.teamName);
        await page.click('button[type="submit"]');
        await page.waitForTimeout(1_000);

        const card = page.locator('[role="button"]').filter({ hasText: creds.teamName.toUpperCase() }).first();
        await card.click();
        await page.waitForURL('**/board', { timeout: 10_000 });
        await page.waitForSelector('[data-testid="board-grid"]', { timeout: 10_000 });

        const results = await new AxeBuilder({ page })
            .withTags(['wcag2a', 'wcag2aa'])
            .analyze();

        expect(results.violations).toEqual([]);
    });

});
