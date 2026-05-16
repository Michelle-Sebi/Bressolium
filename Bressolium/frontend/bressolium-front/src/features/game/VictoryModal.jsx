import { useRef, useEffect, useCallback } from 'react';
import { useNavigate } from 'react-router-dom';

const overlay = {
    position:        'fixed',
    inset:           0,
    backgroundColor: 'rgba(0,0,0,0.75)',
    display:         'flex',
    alignItems:      'center',
    justifyContent:  'center',
    zIndex:          9999,
};

const modal = {
    backgroundColor: '#f7f9f7',
    border:          '4px solid #458B74',
    padding:         '48px 56px',
    maxWidth:        '540px',
    width:           '90%',
    textAlign:       'center',
    boxShadow:       '8px 8px 0 #458B74',
};

const title = {
    fontFamily:    'monospace',
    fontSize:      '2.2rem',
    fontWeight:    'bold',
    textTransform: 'uppercase',
    letterSpacing: '0.12em',
    color:         '#458B74',
    margin:        '0 0 12px',
};

const subtitle = {
    fontFamily:    'monospace',
    fontSize:      '1rem',
    textTransform: 'uppercase',
    letterSpacing: '0.08em',
    color:         'rgba(0,0,0,0.7)',
    margin:        '0 0 40px',
};

const badge = {
    display:         'inline-block',
    backgroundColor: '#458B74',
    color:           '#f7f9f7',
    fontFamily:      'monospace',
    fontWeight:      'bold',
    fontSize:        '0.85rem',
    textTransform:   'uppercase',
    letterSpacing:   '0.1em',
    padding:         '6px 16px',
    marginBottom:    '40px',
};

const btn = {
    display:       'block',
    width:         '100%',
    padding:       '14px',
    backgroundColor: '#458B74',
    color:         '#f7f9f7',
    border:        'none',
    fontFamily:    'monospace',
    fontWeight:    'bold',
    fontSize:      '1rem',
    textTransform: 'uppercase',
    letterSpacing: '0.1em',
    cursor:        'pointer',
};

export default function VictoryModal({ teamName }) {
    const navigate  = useNavigate();
    const modalRef  = useRef(null);
    const handleClose = useCallback(() => navigate('/dashboard'), [navigate]);

    useEffect(() => {
        const dialog = modalRef.current;
        if (!dialog) return;

        const previousFocus = document.activeElement;
        const focusable = Array.from(dialog.querySelectorAll(
            'button, [href], input, select, textarea, [tabindex]:not([tabindex="-1"])'
        ));
        focusable[0]?.focus();

        function handleKeyDown(e) {
            if (e.key === 'Escape') { handleClose(); return; }
            if (e.key !== 'Tab' || focusable.length === 0) return;
            const first = focusable[0];
            const last  = focusable[focusable.length - 1];
            if (e.shiftKey) {
                if (document.activeElement === first) { e.preventDefault(); last.focus(); }
            } else {
                if (document.activeElement === last)  { e.preventDefault(); first.focus(); }
            }
        }

        dialog.addEventListener('keydown', handleKeyDown);
        return () => {
            dialog.removeEventListener('keydown', handleKeyDown);
            previousFocus?.focus();
        };
    }, [handleClose]);

    return (
        <div style={overlay} role="dialog" aria-modal="true" aria-labelledby="victory-title">
            <div ref={modalRef} style={modal}>
                <p style={title} id="victory-title">¡Victoria!</p>
                <p style={subtitle}>La nave de asentamiento ha partido</p>
                {teamName && <span style={badge}>{teamName}</span>}
                <p style={{
                    fontFamily:    'monospace',
                    fontSize:      '0.9rem',
                    color:         'rgba(0,0,0,0.6)',
                    textTransform: 'uppercase',
                    letterSpacing: '0.06em',
                    marginBottom:  '40px',
                }}>
                    Han completado la civilización y conquistado las estrellas.
                </p>
                <button style={btn} onClick={() => navigate('/dashboard')}>
                    Volver al panel
                </button>
            </div>
        </div>
    );
}
