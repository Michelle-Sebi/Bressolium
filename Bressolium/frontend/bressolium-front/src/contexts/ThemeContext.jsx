import { createContext, useContext } from 'react';

const THEME = {
    colors: {
        primary:   '#458B74',
        secondary: '#8B7355',
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
        pueblo:  '#C1CDC1',
        fog:     '#8B7355',
    },
};

const ThemeContext = createContext(THEME);

export function ThemeProvider({ children }) {
    return <ThemeContext.Provider value={THEME}>{children}</ThemeContext.Provider>;
}

export function useTheme() {
    return useContext(ThemeContext);
}
