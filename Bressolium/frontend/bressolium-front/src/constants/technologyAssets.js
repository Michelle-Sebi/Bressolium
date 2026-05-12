import { PALETTE } from './palette';

export const TECHNOLOGY_COLORS = {
    'agricultura':                 PALETTE.verde1,
    'biotecnologia':               PALETTE.azul3,
    'ceramica-y-alfareria':        PALETTE.rojo2,
    'computacion':                 PALETTE.gris2,
    'comunicaciones-inalambricas': PALETTE.amarillo1,
    'conservacion-alimentos':      PALETTE.gris1,
    'control-fuego':               PALETTE.rojo3,
    'edicion-genetica':            PALETTE.rojo1,
    'electricidad':                PALETTE.amarillo4,
    'energias-renovables':         PALETTE.verde2,
    'escritura':                   PALETTE.marron2,
    'fermentacion':                PALETTE.marron3,
    'fotografia':                  PALETTE.marron1,
    'ganaderia':                   PALETTE.verde2,
    'gps':                         PALETTE.teal3,
    'herramientas-piedra':         PALETTE.teal4,
    'inteligencia-artificial':     PALETTE.amarillo2,
    'internet':                    PALETTE.azul2,
    'metalurgia-y-aleaciones':     PALETTE.amarillo2,
    'nanotecnologia':              PALETTE.marron1,
    'quimica':                     PALETTE.rojo1,
    'robotica':                    PALETTE.amarillo1,
    'sistemas-autonomos':          PALETTE.azul3,
    'tecnologia-espacial':         PALETTE.amarillo3,
    'tejido':                      PALETTE.rojo2,
    'terraformacion':              PALETTE.azul4,
};

export const TECHNOLOGY_ICON_MAP = {
    'agricultura':                 new URL('../assets/icons/technologies/agricultura.png',                 import.meta.url).href,
    'biotecnologia':               new URL('../assets/icons/technologies/biotecnologia.png',               import.meta.url).href,
    'ceramica-y-alfareria':        new URL('../assets/icons/technologies/ceramica y alfareria.png',        import.meta.url).href,
    'computacion':                 new URL('../assets/icons/technologies/computacion.png',                 import.meta.url).href,
    'comunicaciones-inalambricas': new URL('../assets/icons/technologies/comunicaciones-inalambricas.png', import.meta.url).href,
    'conservacion-alimentos':      new URL('../assets/icons/technologies/conservacion-alimentos.png',      import.meta.url).href,
    'control-fuego':               new URL('../assets/icons/technologies/control-fuego.png',               import.meta.url).href,
    'edicion-genetica':            new URL('../assets/icons/technologies/edicion-genetica.png',            import.meta.url).href,
    'electricidad':                new URL('../assets/icons/technologies/electricidad.png',                import.meta.url).href,
    'energias-renovables':         new URL('../assets/icons/technologies/energias-renovables.png',         import.meta.url).href,
    'escritura':                   new URL('../assets/icons/technologies/escritura.png',                   import.meta.url).href,
    'fermentacion':                new URL('../assets/icons/technologies/fermentacion.png',                import.meta.url).href,
    'fotografia':                  new URL('../assets/icons/technologies/fotografia.png',                  import.meta.url).href,
    'ganaderia':                   new URL('../assets/icons/technologies/ganaderia.png',                   import.meta.url).href,
    'gps':                         new URL('../assets/icons/technologies/gps.png',                         import.meta.url).href,
    'herramientas-piedra':         new URL('../assets/icons/technologies/herramientas-piedra.png',         import.meta.url).href,
    'inteligencia-artificial':     new URL('../assets/icons/technologies/inteligencia artificial.png',     import.meta.url).href,
    'internet':                    new URL('../assets/icons/technologies/internet.png',                    import.meta.url).href,
    'metalurgia-y-aleaciones':     new URL('../assets/icons/technologies/metalurgia y aleaciones.png',     import.meta.url).href,
    'nanotecnologia':              new URL('../assets/icons/technologies/nanotecnologia.png',              import.meta.url).href,
    'quimica':                     new URL('../assets/icons/technologies/quimica.png',                     import.meta.url).href,
    'robotica':                    new URL('../assets/icons/technologies/robotica.png',                    import.meta.url).href,
    'sistemas-autonomos':          new URL('../assets/icons/technologies/sistemas-autonomos.png',          import.meta.url).href,
    'tecnologia-espacial':         new URL('../assets/icons/technologies/tecnologia-espacial.png',         import.meta.url).href,
    'tejido':                      new URL('../assets/icons/technologies/tejido.png',                      import.meta.url).href,
    'terraformacion':              new URL('../assets/icons/technologies/terraformacion.png',              import.meta.url).href,
};

export function technologyNameToKey(name) {
    return name
        .toLowerCase()
        .normalize('NFD').replace(/[̀-ͯ]/g, '')
        .replace(/\bdel?\b\s*/g, '')
        .replace(/\s+/g, '-')
        .replace(/-+/g, '-');
}
