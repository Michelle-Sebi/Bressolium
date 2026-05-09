import React from 'react';

/**
 * Captura errores de renderizado en el árbol de componentes hijos y muestra
 * un fallback en lugar de dejar la pantalla en blanco.
 */
class ErrorBoundary extends React.Component {
    constructor(props) {
        super(props);
        this.state = { hasError: false };
    }

    static getDerivedStateFromError() {
        return { hasError: true };
    }

    componentDidCatch(error, info) {
        console.error('[ErrorBoundary]', error, info);
    }

    render() {
        if (this.state.hasError) {
            return (
                <div
                    data-testid="error-boundary-fallback"
                    role="alert"
                    style={{
                        padding:         '2rem',
                        backgroundColor: '#f7f9f7',
                        border:          '2px solid #CD4F39',
                        color:           '#8B7355',
                        fontWeight:      'bold',
                        textTransform:   'uppercase',
                        letterSpacing:   '0.08em',
                    }}
                >
                    Algo salió mal. Por favor, recarga la página.
                </div>
            );
        }

        return this.props.children;
    }
}

export default ErrorBoundary;
