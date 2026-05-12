import { PALETTE } from './palette';

export const MATERIAL_COLORS = {
    // Bosque
    'roble':               PALETTE.marron4,
    'pino':                PALETTE.marron1,
    'carbon-natural':      PALETTE.gris2,
    'pieles':              PALETTE.marron5,
    'latex':               PALETTE.amarillo1,
    'resinas-inflamables': PALETTE.rojo3,
    'mat-aisl-nat':        PALETTE.marron3,

    // Cantera
    'silex':               PALETTE.teal2,
    'granito':             PALETTE.teal3,
    'obsidiana':           PALETTE.gris1,
    'arena-de-silice':     PALETTE.marron2,
    'arena-de-cuarzo':     PALETTE.teal2,
    'cristales-nat':       PALETTE.azul1,
    'silicio':             PALETTE.verde1,
    'min-semi':            PALETTE.rojo1,

    // Río
    'agua':                PALETTE.azul5,
    'cana-comun':          PALETTE.verde3,
    'tierras-fertiles':    PALETTE.verde5,
    'hidrogeno':           PALETTE.azul1,
    'gases-naturales':     PALETTE.azul1,

    // Prado
    'lino':                PALETTE.amarillo1,
    'yute':                PALETTE.marron1,
    'canamo':              PALETTE.marron2,
    'lana':                PALETTE.rojo3,

    // Mina
    'cobre':               PALETTE.marron3,
    'hierro':              PALETTE.gris3,
    'estano':              PALETTE.gris1,
    'grafito':             PALETTE.gris1,
    'oro':                 PALETTE.amarillo5,
    'mat-mag-nat':         PALETTE.rojo4,
};

export const MATERIAL_ICON_MAP = {
    'roble':               new URL('../assets/icons/materials/roble.png',                     import.meta.url).href,
    'pino':                new URL('../assets/icons/materials/pino.png',                      import.meta.url).href,
    'carbon-natural':      new URL('../assets/icons/materials/carbon.png',                    import.meta.url).href,
    'pieles':              new URL('../assets/icons/materials/pieles.png',                    import.meta.url).href,
    'latex':               new URL('../assets/icons/materials/latex.png',                     import.meta.url).href,
    'resinas-inflamables': new URL('../assets/icons/materials/resinas-inflamables.png',       import.meta.url).href,
    'mat-aisl-nat':        new URL('../assets/icons/materials/materiales-aislantes.png',      import.meta.url).href,
    'silex':               new URL('../assets/icons/materials/silex.png',                     import.meta.url).href,
    'granito':             new URL('../assets/icons/materials/granito.png',                   import.meta.url).href,
    'obsidiana':           new URL('../assets/icons/materials/obsidiana.png',                 import.meta.url).href,
    'arena-de-silice':     new URL('../assets/icons/materials/arena-silice.png',              import.meta.url).href,
    'arena-de-cuarzo':     new URL('../assets/icons/materials/arena-cuarzo.png',              import.meta.url).href,
    'cristales-nat':       new URL('../assets/icons/materials/cristales-naturales.png',       import.meta.url).href,
    'silicio':             new URL('../assets/icons/materials/silicio.png',                   import.meta.url).href,
    'min-semi':            new URL('../assets/icons/materials/minerales-semiconductores.png', import.meta.url).href,
    'agua':                new URL('../assets/icons/materials/agua.png',                      import.meta.url).href,
    'cana-comun':          new URL('../assets/icons/materials/cana.png',                      import.meta.url).href,
    'tierras-fertiles':    new URL('../assets/icons/materials/tierras-fertiles.png',          import.meta.url).href,
    'hidrogeno':           new URL('../assets/icons/materials/hidrogeno.png',                 import.meta.url).href,
    'gases-naturales':     new URL('../assets/icons/materials/gases-naturales.png',           import.meta.url).href,
    'lino':                new URL('../assets/icons/materials/lino.png',                      import.meta.url).href,
    'yute':                new URL('../assets/icons/materials/yute.png',                      import.meta.url).href,
    'canamo':              new URL('../assets/icons/materials/cañamo.png',                    import.meta.url).href,
    'lana':                new URL('../assets/icons/materials/lana.png',                      import.meta.url).href,
    'cobre':               new URL('../assets/icons/materials/cobre.png',                     import.meta.url).href,
    'hierro':              new URL('../assets/icons/materials/hierro.png',                    import.meta.url).href,
    'estano':              new URL('../assets/icons/materials/estaño.png',                    import.meta.url).href,
    'grafito':             new URL('../assets/icons/materials/grafito.png',                   import.meta.url).href,
    'oro':                 new URL('../assets/icons/materials/oro.png',                       import.meta.url).href,
    'mat-mag-nat':         new URL('../assets/icons/materials/materiales-magenticos.png',     import.meta.url).href,
};
