function Modal({ isOpen, onClose, title, children }) {
    if (!isOpen) return null;

    return (
        <div
            style={{
                position:        'fixed',
                inset:           0,
                backgroundColor: 'rgba(139,115,85,0.9)',
                display:         'flex',
                alignItems:      'center',
                justifyContent:  'center',
                padding:         '1rem',
                zIndex:          50,
            }}
        >
            <div style={{ backgroundColor: '#fff', width: '100%', maxWidth: '32rem', padding: '2.5rem', position: 'relative' }}>
                <button
                    onClick={onClose}
                    style={{
                        position:   'absolute',
                        top:        '1rem',
                        right:      '1rem',
                        fontSize:   '1.875rem',
                        fontWeight: 900,
                        color:      '#8B7355',
                        background: 'none',
                        border:     'none',
                        cursor:     'pointer',
                        lineHeight: 1,
                    }}
                    aria-label="Cerrar"
                >
                    ×
                </button>
                {title && (
                    <h3 style={{ fontSize: '1.875rem', fontWeight: 900, color: '#8B7355', marginBottom: '2rem' }}>
                        {title}
                    </h3>
                )}
                {children}
            </div>
        </div>
    );
}

export default Modal;
