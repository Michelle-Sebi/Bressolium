import { createContext, useContext } from 'react';

// ThemeContext expone la paleta a componentes que necesitan colores dinámicos
// (p.ej. canvas de tablero). Para uso en pruebas unitarias de T44.
// Los componentes Tailwind usan los tokens de index.css directamente.
const THEME = {
    colors: {
        primary:   '#3b7864',  // --color-bgreen (AA sobre blanco)
        secondary: '#8B7355',  // PALETTE.marron5 — cantera
        light:     '#C1CDC1',  // --color-bgray — fondo panel
        resalt:    '#CD4F39',  // --color-bred
        text:      '#545454',  // --color-btext
        dark:      '#333333',  // --color-bdark
        white:     '#ffffff',
    },
    tiles: {
        bosque:  '#458B74',  // PALETTE.verde5
        cantera: '#8B7355',  // PALETTE.marron5
        prado:   '#CD4F39',  // PALETTE.rojo5
        rio:     '#4682B4',  // PALETTE.azul5
        mina:    '#DAA520',  // PALETTE.amarillo5
        fog:     '#C1CDC1',  // --color-bgray
    },
};

const ThemeContext = createContext(THEME);

export function ThemeProvider({ children }) {
    return <ThemeContext.Provider value={THEME}>{children}</ThemeContext.Provider>;
}

export function useTheme() {
    return useContext(ThemeContext);
}
