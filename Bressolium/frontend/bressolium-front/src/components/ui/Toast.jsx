const TYPE_COLORS = {
    success: '#458B74',
    error:   '#CD4F39',
    info:    '#8B7355',
};

function Toast({ message, type = 'info', onDismiss }) {
    const bg = TYPE_COLORS[type] ?? TYPE_COLORS.info;

    return (
        <div
            style={{
                display:         'flex',
                alignItems:      'center',
                justifyContent:  'space-between',
                gap:             '1rem',
                padding:         '0.75rem 1rem',
                backgroundColor: bg,
                color:           '#fff',
                fontWeight:      'bold',
                fontSize:        '0.875rem',
            }}
            role="alert"
        >
            <span>{message}</span>
            <button
                onClick={onDismiss}
                style={{ background: 'none', border: 'none', color: '#fff', cursor: 'pointer', fontWeight: 900, fontSize: '1.25rem', lineHeight: 1 }}
                aria-label="Cerrar notificación"
            >
                ×
            </button>
        </div>
    );
}

export default Toast;
