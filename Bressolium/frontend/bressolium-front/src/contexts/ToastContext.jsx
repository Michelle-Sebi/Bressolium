import { createContext, useContext, useState, useCallback, useEffect } from 'react';
import Toast from '../components/ui/Toast';

const ToastContext = createContext(null);

let nextId = 1;

const AUTO_DISMISS_MS = 4000;

function ToastStack({ toasts, dismiss }) {
    return (
        <div style={{
            position:      'fixed',
            bottom:        '1.5rem',
            right:         '1.5rem',
            zIndex:        9999,
            display:       'flex',
            flexDirection: 'column',
            gap:           '0.5rem',
            minWidth:      '260px',
            maxWidth:      '360px',
        }}>
            {toasts.map((t) => (
                <Toast key={t.id} message={t.message} type={t.type} onDismiss={() => dismiss(t.id)} />
            ))}
        </div>
    );
}

function AutoDismiss({ id, dismiss }) {
    useEffect(() => {
        const timer = setTimeout(() => dismiss(id), AUTO_DISMISS_MS);
        return () => clearTimeout(timer);
    }, [id, dismiss]);
    return null;
}

export function ToastProvider({ children }) {
    const [toasts, setToasts] = useState([]);

    const show = useCallback((message, type = 'info') => {
        const id = nextId++;
        setToasts((prev) => [...prev, { id, message, type }]);
        return id;
    }, []);

    const dismiss = useCallback((id) => {
        setToasts((prev) => prev.filter((t) => t.id !== id));
    }, []);

    return (
        <ToastContext.Provider value={{ toasts, show, dismiss }}>
            {children}
            {toasts.map((t) => <AutoDismiss key={t.id} id={t.id} dismiss={dismiss} />)}
            <ToastStack toasts={toasts} dismiss={dismiss} />
        </ToastContext.Provider>
    );
}

const noopToast = { toasts: [], show: () => {}, dismiss: () => {} };

export function useToast() {
    return useContext(ToastContext) ?? noopToast;
}
