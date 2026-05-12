import { PALETTE } from './palette';

export const INVENTION_COLORS = {
    'acero':                          PALETTE.gris3,
    'acueducto':                      PALETTE.gris2,
    'arado':                          PALETTE.marron5,
    'arco':                           PALETTE.marron2,
    'avion':                          PALETTE.azul1,
    'barco':                          PALETTE.azul5,
    'bateria':                        PALETTE.gris1,
    'bombilla':                       PALETTE.amarillo3,
    'brujula':                        PALETTE.verde3,
    'carro':                          PALETTE.marron4,
    'ceramica':                       PALETTE.rojo2,
    'cuchillo':                       PALETTE.gris2,
    'cuerda':                         PALETTE.marron2,
    'estacion-espacial':              PALETTE.gris5,
    'fibra-optica':                   PALETTE.azul1,
    'hacha':                          PALETTE.marron3,
    'imprenta':                       PALETTE.marron1,
    'lanza':                          PALETTE.gris4,
    'laser':                          PALETTE.rojo4,
    'microscopio':                    PALETTE.azul1,
    'molino':                         PALETTE.verde3,
    'moneda':                         PALETTE.amarillo4,
    'nave-asentamiento-interestelar': PALETTE.gris2,
    'papel':                          PALETTE.marron1,
    'penicilina':                     PALETTE.rojo5,
    'refugio':                        PALETTE.marron3,
    'reloj':                          PALETTE.rojo1,
    'rueda':                          PALETTE.marron5,
    'satelite':                       PALETTE.amarillo5,
    'tela':                           PALETTE.azul2,
    'telefono-movil':                 PALETTE.azul1,
    'telescopio':                     PALETTE.azul5,
    'trampa':                         PALETTE.marron5,
    'vidrio':                         PALETTE.azul1,
};

export const INVENTION_ICON_MAP = {
    'acero':                          new URL('../assets/icons/inventions/acero.png',                          import.meta.url).href,
    'acueducto':                      new URL('../assets/icons/inventions/acueducto.png',                      import.meta.url).href,
    'arado':                          new URL('../assets/icons/inventions/arado.png',                          import.meta.url).href,
    'arco':                           new URL('../assets/icons/inventions/arco.png',                           import.meta.url).href,
    'avion':                          new URL('../assets/icons/inventions/avion.png',                          import.meta.url).href,
    'barco':                          new URL('../assets/icons/inventions/barco.png',                          import.meta.url).href,
    'bateria':                        new URL('../assets/icons/inventions/bateria.png',                        import.meta.url).href,
    'bombilla':                       new URL('../assets/icons/inventions/bombilla.png',                       import.meta.url).href,
    'brujula':                        new URL('../assets/icons/inventions/brujula.png',                        import.meta.url).href,
    'carro':                          new URL('../assets/icons/inventions/carro.png',                          import.meta.url).href,
    'ceramica':                       new URL('../assets/icons/inventions/ceramica.png',                       import.meta.url).href,
    'cuchillo':                       new URL('../assets/icons/inventions/cuchillo.png',                       import.meta.url).href,
    'cuerda':                         new URL('../assets/icons/inventions/cuerda.png',                         import.meta.url).href,
    'estacion-espacial':              new URL('../assets/icons/inventions/estacion-espacial.png',              import.meta.url).href,
    'fibra-optica':                   new URL('../assets/icons/inventions/fibra-optica.png',                   import.meta.url).href,
    'hacha':                          new URL('../assets/icons/inventions/hacha.png',                          import.meta.url).href,
    'imprenta':                       new URL('../assets/icons/inventions/imprenta.png',                       import.meta.url).href,
    'lanza':                          new URL('../assets/icons/inventions/lanza.png',                          import.meta.url).href,
    'laser':                          new URL('../assets/icons/inventions/laser.png',                          import.meta.url).href,
    'microscopio':                    new URL('../assets/icons/inventions/microscopio.png',                    import.meta.url).href,
    'molino':                         new URL('../assets/icons/inventions/molino.png',                         import.meta.url).href,
    'moneda':                         new URL('../assets/icons/inventions/moneda.png',                         import.meta.url).href,
    'nave-asentamiento-interestelar': new URL('../assets/icons/inventions/nave-asentamiento-interestelar.png', import.meta.url).href,
    'papel':                          new URL('../assets/icons/inventions/papel.png',                          import.meta.url).href,
    'penicilina':                     new URL('../assets/icons/inventions/penicilina.png',                     import.meta.url).href,
    'refugio':                        new URL('../assets/icons/inventions/refugio.png',                        import.meta.url).href,
    'reloj':                          new URL('../assets/icons/inventions/reloj.png',                          import.meta.url).href,
    'rueda':                          new URL('../assets/icons/inventions/rueda.png',                          import.meta.url).href,
    'satelite':                       new URL('../assets/icons/inventions/satelite.png',                       import.meta.url).href,
    'tela':                           new URL('../assets/icons/inventions/tela.png',                           import.meta.url).href,
    'telefono-movil':                 new URL('../assets/icons/inventions/telefono-movil.png',                 import.meta.url).href,
    'telescopio':                     new URL('../assets/icons/inventions/telescopio.png',                     import.meta.url).href,
    'trampa':                         new URL('../assets/icons/inventions/trampa.png',                         import.meta.url).href,
    'vidrio':                         new URL('../assets/icons/inventions/vidrio.png',                         import.meta.url).href,
};

export function inventionNameToKey(name) {
    return name
        .toLowerCase()
        .normalize('NFD').replace(/[̀-ͯ]/g, '')
        .replace(/\bde\b\s*/g, '')
        .replace(/\s+/g, '-')
        .replace(/-+/g, '-')
        .replace(/arcos/, 'arco')
        .replace(/refugios/, 'refugio');
}
