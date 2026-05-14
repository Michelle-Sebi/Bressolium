import { createContext, useContext } from 'react';

const THEME = {
    colors: {
        primary:   '#458B74',
        secondary: '#a0a0a0',
        light:     '#C1CDC1',
        resalt:    '#CD4F39',
        white:     '#ffffff',
    },
    tiles: {
        bosque:  '#458B74',
        cantera: '#696969',
        prado:   '#8FBC8F',
        rio:     '#4682B4',
        mina:    '#DAA520',
        fog:     '#a0a0a0',
    },
};

const ThemeContext = createContext(THEME);

export function ThemeProvider({ children }) {
    return <ThemeContext.Provider value={THEME}>{children}</ThemeContext.Provider>;
}

export function useTheme() {
    return useContext(ThemeContext);
}
