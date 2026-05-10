// @ts-check
import { test, expect } from '@playwright/test';

/**
 * T34 — Tests E2E
 *
 * Flujo completo cubierto:
 *   registro → login → crear partida → ver tablero → explorar casilla → ver inventario
 *
 * Requisito: entorno de desarrollo con Docker Sail levantado (backend en :80,
 * frontend Vite en :5173).
 */

// ── Helpers ──────────────────────────────────────────────────────────────────

/** Genera credenciales únicas para no colisionar entre runs. */
function uniqueCredentials() {
    const ts = Date.now();
    return {
        name:     `Pionero ${ts}`,
        email:    `pionero${ts}@e2e.test`,
        password: 'password123',
        teamName: `Equipo ${ts}`,
    };
}

/**
 * Registro completo desde /register. Redirige a /dashboard si tiene éxito.
 * @param {import('@playwright/test').Page} page
 * @param {{ name: string, email: string, password: string }} creds
 */
async function registerUser(page, { name, email, password }) {
    await page.goto('/register');
    await page.fill('#name',     name);
    await page.fill('#email',    email);
    await page.fill('#password', password);
    await page.click('button[type="submit"]');
    await page.waitForURL('**/dashboard', { timeout: 15_000 });
}

// ── Tests ─────────────────────────────────────────────────────────────────────

test.describe('T34 — E2E: Flujo completo de partida', () => {

    // ── 1. Registro ──────────────────────────────────────────────────────────

    test('registro crea cuenta y redirige al dashboard', async ({ page }) => {
        const { name, email, password } = uniqueCredentials();

        await page.goto('/register');

        // El formulario de registro es accesible
        await expect(page.locator('form[aria-label="Formulario de Registro"]')).toBeVisible();

        await page.fill('#name',     name);
        await page.fill('#email',    email);
        await page.fill('#password', password);
        await page.click('button[type="submit"]');

        // Redirige al dashboard tras el registro exitoso
        await page.waitForURL('**/dashboard', { timeout: 15_000 });
        await expect(page).toHaveURL(/\/dashboard/);
    });

    // ── 2. Login ─────────────────────────────────────────────────────────────

    test('login con credenciales correctas redirige al dashboard', async ({ page }) => {
        const creds = uniqueCredentials();
        await registerUser(page, creds);

        // Logout para poder hacer login manualmente
        await page.goto('/login');

        await expect(page.locator('form[aria-label="Formulario de Login"]')).toBeVisible();
        await page.fill('#email',    creds.email);
        await page.fill('#password', creds.password);
        await page.click('button[type="submit"]');

        await page.waitForURL('**/dashboard', { timeout: 15_000 });
        await expect(page).toHaveURL(/\/dashboard/);
    });

    test('login con credenciales incorrectas muestra mensaje de error', async ({ page }) => {
        await page.goto('/login');
        await page.fill('#email',    'noexiste@e2e.test');
        await page.fill('#password', 'wrongpassword');
        await page.click('button[type="submit"]');

        // Error visible y no redirige
        await expect(page.locator('[role="alert"]')).toBeVisible({ timeout: 10_000 });
        await expect(page).toHaveURL(/\/login/);
    });

    // ── 3. Rutas protegidas ──────────────────────────────────────────────────

    test('acceder al dashboard sin sesión redirige a /login', async ({ page }) => {
        // Sin cookies de sesión, nueva página
        await page.goto('/dashboard');
        await expect(page).toHaveURL(/\/login/, { timeout: 5_000 });
    });

    test('acceder al tablero sin sesión redirige a /login', async ({ page }) => {
        await page.goto('/board');
        await expect(page).toHaveURL(/\/login/, { timeout: 5_000 });
    });

    // ── 4. Crear partida ─────────────────────────────────────────────────────

    test('crear partida nueva aparece en "Mis expediciones"', async ({ page }) => {
        const creds = uniqueCredentials();
        await registerUser(page, creds);

        // Abre modal de creación
        await page.click('text=CREAR EQUIPO NUEVO');
        await expect(page.locator('#teamName')).toBeVisible();

        await page.fill('#teamName', creds.teamName);
        await page.click('text=FUNDAR CIVILIZACIÓN');

        // La partida recién creada aparece en el panel derecho
        await expect(page.locator('text=' + creds.teamName.toUpperCase())).toBeVisible({ timeout: 10_000 });
    });

    // ── 5. Ver tablero ────────────────────────────────────────────────────────

    test('entrar a la partida muestra el tablero 15×15', async ({ page }) => {
        const creds = uniqueCredentials();
        await registerUser(page, creds);

        // Crear partida
        await page.click('text=CREAR EQUIPO NUEVO');
        await page.fill('#teamName', creds.teamName);
        await page.click('text=FUNDAR CIVILIZACIÓN');

        // Click en la expedición para abrir el tablero
        await page.click('text=' + creds.teamName.toUpperCase());
        await page.waitForURL('**/board', { timeout: 15_000 });

        // El tablero carga correctamente (225 tiles = 15×15)
        const boardGrid = page.locator('[data-testid="board-grid"]');
        await expect(boardGrid).toBeVisible({ timeout: 20_000 });

        const tiles = page.locator('[data-testid="tile"]');
        await expect(tiles).toHaveCount(225, { timeout: 20_000 });
    });

    // ── 6. Explorar casilla ───────────────────────────────────────────────────

    test('explorar una casilla la revela en el tablero', async ({ page }) => {
        const creds = uniqueCredentials();
        await registerUser(page, creds);

        await page.click('text=CREAR EQUIPO NUEVO');
        await page.fill('#teamName', creds.teamName);
        await page.click('text=FUNDAR CIVILIZACIÓN');
        await page.click('text=' + creds.teamName.toUpperCase());
        await page.waitForURL('**/board', { timeout: 15_000 });

        await page.waitForSelector('[data-testid="board-grid"]', { timeout: 20_000 });

        // El juego inicia con tiles pre-explorados (pueblo + base del jugador).
        // Solo los tiles adyacentes a territorio explorado tienen cursor:pointer.
        // Buscamos el primer tile en niebla que sea explorable.
        const fogBefore = await page.locator('[data-testid="tile"].tile--fog').count();
        expect(fogBefore).toBeGreaterThan(0);

        // El inner div de un tile explorable adyacente tiene cursor:pointer
        const explorableInner = page.locator('.tile--fog > div[style*="cursor: pointer"]').first();
        await expect(explorableInner).toBeVisible({ timeout: 10_000 });
        await explorableInner.click();

        // Tras la exploración, hay un tile menos en niebla
        await expect(page.locator('[data-testid="tile"].tile--fog')).toHaveCount(fogBefore - 1, { timeout: 15_000 });
    });

    // ── 7. Inventario actualizado ─────────────────────────────────────────────

    test('el panel de inventario es visible y muestra los materiales del juego', async ({ page }) => {
        const creds = uniqueCredentials();
        await registerUser(page, creds);

        await page.click('text=CREAR EQUIPO NUEVO');
        await page.fill('#teamName', creds.teamName);
        await page.click('text=FUNDAR CIVILIZACIÓN');
        await page.click('text=' + creds.teamName.toUpperCase());
        await page.waitForURL('**/board', { timeout: 15_000 });

        // Esperar que el sync inicial cargue el inventario
        await page.waitForSelector('[data-testid="board-grid"]', { timeout: 20_000 });

        // El panel de inventario está en la pantalla
        await expect(page.locator('text=Inventario')).toBeVisible();

        // Los items de materiales se renderizan (el catálogo tiene 44 recursos)
        const materialItems = page.locator('[data-testid="material-item"]');
        await expect(materialItems.first()).toBeVisible({ timeout: 15_000 });
        const count = await materialItems.count();
        expect(count).toBeGreaterThan(0);
    });

    // ── 8. Flujo E2E completo integrado ──────────────────────────────────────

    test('flujo completo: registro → login → crear partida → tablero → explorar → inventario', async ({ page }) => {
        const creds = uniqueCredentials();

        // Registro
        await page.goto('/register');
        await page.fill('#name',     creds.name);
        await page.fill('#email',    creds.email);
        await page.fill('#password', creds.password);
        await page.click('button[type="submit"]');
        await page.waitForURL('**/dashboard', { timeout: 15_000 });

        // Dashboard visible
        await expect(page.locator('text=UNIRSE A LA')).toBeVisible();

        // Crear partida
        await page.click('text=CREAR EQUIPO NUEVO');
        await page.fill('#teamName', creds.teamName);
        await page.click('text=FUNDAR CIVILIZACIÓN');
        await expect(page.locator('text=' + creds.teamName.toUpperCase())).toBeVisible({ timeout: 10_000 });

        // Entrar al tablero
        await page.click('text=' + creds.teamName.toUpperCase());
        await page.waitForURL('**/board', { timeout: 15_000 });

        // Tablero cargado con 225 tiles
        await page.waitForSelector('[data-testid="board-grid"]', { timeout: 20_000 });
        await expect(page.locator('[data-testid="tile"]')).toHaveCount(225, { timeout: 20_000 });

        // Explorar el primer tile adyacente a territorio explorado (cursor:pointer)
        const fogBefore = await page.locator('[data-testid="tile"].tile--fog').count();
        const explorableInner = page.locator('.tile--fog > div[style*="cursor: pointer"]').first();
        await expect(explorableInner).toBeVisible({ timeout: 10_000 });
        await explorableInner.click();
        await expect(page.locator('[data-testid="tile"].tile--fog')).toHaveCount(fogBefore - 1, { timeout: 15_000 });

        // Inventario visible con materiales cargados
        await expect(page.locator('text=Inventario')).toBeVisible();
        const items = page.locator('[data-testid="material-item"]');
        await expect(items.first()).toBeVisible({ timeout: 15_000 });
        expect(await items.count()).toBeGreaterThan(0);
    });

});
