/**
 * Componente reutilizable para mostrar un ítem del inventario (material, invento, tecnología).
 * Layout: [icono] [nombre + subtítulo] [cantidad]
 */

const GROUP_COLORS = {
    bosque:  '#458B74',
    cantera: '#696969',
    prado:   '#8FBC8F',
    rio:     '#4682B4',
    mina:    '#DAA520',
};

const GROUP_LABELS = {
    bosque:  'Bosque',
    cantera: 'Cantera',
    prado:   'Prado',
    rio:     'Río',
    mina:    'Mina',
};

/**
 * @param {{
 *   iconSrc: string,
 *   iconBgColor?: string,
 *   name: string,
 *   subtitle?: string | import('react').ReactNode,
 *   quantity?: number,
 *   isActive?: boolean,
 * }} props
 */
function ItemCard({ iconSrc, iconBgColor = '#a0a0a0', name, subtitle, quantity, isActive = true }) {
    return (
        <div
            style={{
                display:         'flex',
                alignItems:      'center',
                gap:             '10px',
                padding:         '8px 10px',
                backgroundColor: '#fff',
                borderBottom:    '1px solid #e8e8e8',
                opacity:         isActive ? 1 : 0.4,
            }}
        >
            {/* Icono */}
            <div
                style={{
                    width:           '40px',
                    height:          '40px',
                    flexShrink:      0,
                    backgroundColor: iconBgColor,
                    display:         'flex',
                    alignItems:      'center',
                    justifyContent:  'center',
                }}
            >
                {iconSrc && (
                    <img
                        src={iconSrc}
                        alt=""
                        aria-hidden="true"
                        style={{ width: '28px', height: '28px', objectFit: 'contain' }}
                    />
                )}
            </div>

            {/* Texto */}
            <div style={{ flex: 1, minWidth: 0 }}>
                <div style={{ fontWeight: 'bold', fontSize: '12px', color: 'rgba(0,0,0,0.85)', lineHeight: 1.3 }}>
                    {name.replace(/-/g, ' ')}
                </div>
                {subtitle && (
                    <div style={{ fontSize: '10px', color: 'rgba(0,0,0,0.45)', marginTop: '2px', lineHeight: 1.3 }}>
                        {subtitle}
                    </div>
                )}
            </div>

            {/* Cantidad */}
            {quantity !== undefined && (
                <div
                    aria-label={`Cantidad: ${quantity}`}
                    style={{
                        fontWeight:  'bold',
                        fontSize:    '14px',
                        color:       isActive ? 'rgba(0,0,0,0.8)' : 'rgba(0,0,0,0.4)',
                        flexShrink:  0,
                        minWidth:    '24px',
                        textAlign:   'right',
                    }}
                >
                    {quantity}
                </div>
            )}
        </div>
    );
}

export { GROUP_COLORS, GROUP_LABELS };
export default ItemCard;
