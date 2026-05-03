function Input({ value, onChange, placeholder, type = 'text', name, id, required = false, className = '' }) {
    return (
        <input
            id={id}
            name={name}
            type={type}
            value={value}
            onChange={onChange}
            placeholder={placeholder}
            required={required}
            className={`block w-full px-4 py-4 bg-gray-50 text-bbrown border-0 border-l-4 border-bgray
                focus:ring-0 focus:border-bgreen focus:bg-white transition-all outline-none ${className}`}
        />
    );
}

export default Input;
