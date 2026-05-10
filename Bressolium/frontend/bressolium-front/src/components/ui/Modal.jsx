import { useEffect, useRef } from 'react';

function Modal({ isOpen, onClose, title, children }) {
    const dialogRef = useRef(null);

    useEffect(() => {
        if (!isOpen) return;
        const dialog = dialogRef.current;
        if (!dialog) return;

        const previousFocus = document.activeElement;
        const focusable = Array.from(dialog.querySelectorAll(
            'button, [href], input, select, textarea, [tabindex]:not([tabindex="-1"])'
        ));
        focusable[0]?.focus();

        function handleKeyDown(e) {
            if (e.key === 'Escape') { onClose(); return; }
            if (e.key !== 'Tab' || focusable.length === 0) return;
            const first = focusable[0];
            const last  = focusable[focusable.length - 1];
            if (e.shiftKey) {
                if (document.activeElement === first) { e.preventDefault(); last.focus(); }
            } else {
                if (document.activeElement === last) { e.preventDefault(); first.focus(); }
            }
        }

        dialog.addEventListener('keydown', handleKeyDown);
        return () => {
            dialog.removeEventListener('keydown', handleKeyDown);
            previousFocus?.focus();
        };
    }, [isOpen, onClose]);

    if (!isOpen) return null;

    return (
        <div
            ref={dialogRef}
            role="dialog"
            aria-modal="true"
            aria-labelledby={title ? 'modal-title' : undefined}
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
                        color:      '#a0a0a0',
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
                    <h3 id="modal-title" style={{ fontSize: '1.875rem', fontWeight: 900, color: '#a0a0a0', marginBottom: '2rem' }}>
                        {title}
                    </h3>
                )}
                {children}
            </div>
        </div>
    );
}

export default Modal;
