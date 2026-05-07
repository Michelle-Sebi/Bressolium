const VARIANT_CLASSES = {
    primary:   'bg-bgreen hover:bg-[#3b7864] text-white',
    danger:    'bg-bred hover:bg-[#b84633] text-white',
    secondary: 'bg-bbrown hover:bg-[#6e5b44] text-white',
};

function Button({ children, variant = 'primary', disabled = false, onClick, type = 'button', className = '', style }) {
    return (
        <button
            type={type}
            disabled={disabled}
            onClick={onClick}
            style={style}
            className={`w-full flex justify-center py-4 px-6 text-base font-bold transition-colors
                disabled:opacity-50 disabled:cursor-not-allowed
                ${VARIANT_CLASSES[variant] ?? VARIANT_CLASSES.primary}
                ${className}`}
        >
            {children}
        </button>
    );
}

export default Button;
