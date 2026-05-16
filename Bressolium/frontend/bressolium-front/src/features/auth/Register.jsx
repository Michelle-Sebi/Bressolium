import React, { useState } from 'react';
import { Link, useNavigate } from 'react-router-dom';
import { useAuth } from './useAuth';

function Register() {
  const { register } = useAuth();
  const navigate = useNavigate();

  const [name, setName] = useState('');
  const [email, setEmail] = useState('');
  const [password, setPassword] = useState('');
  const [loading, setLoading] = useState(false);
  const [error, setError] = useState(null);

  const handleSubmit = async (e) => {
    e.preventDefault();
    setLoading(true);
    setError(null);
    try {
      await register(name, email, password);
      navigate('/dashboard');
    } catch (err) {
      setError(err.message || 'Error en el registro');
    } finally {
      setLoading(false);
    }
  };

  return (
    <div className="flex min-h-screen bg-bgray">
      <div className="w-full flex">

        {/* Formulario (Bloque Blanco en el lado Izquierdo para contrastar con Login) */}
        <div className="w-full lg:w-1/2 bg-white flex flex-col justify-center px-8 sm:px-16 xl:px-32">
          <div className="w-full max-w-md mx-auto py-12">
            <h2 className="text-4xl font-extrabold text-btext mb-2">
              Únete a Bressolium
            </h2>
            <p className="mb-10 text-lg" style={{ color: '#5f5f5f' }}>
              Crea tu cuenta e inicia la misión.
            </p>
            
            <form className="space-y-6" onSubmit={handleSubmit} aria-label="Formulario de Registro">
              <div className="form-group">
                <label htmlFor="name" className="block text-sm font-bold text-btext mb-2 uppercase tracking-wide">
                  Nombre de pionero
                </label>
                <input
                  id="name"
                  name="name"
                  type="text"
                  required
                  className="input-field"
                  placeholder="Tu alias"
                  value={name}
                  onChange={(e) => setName(e.target.value)}
                />
              </div>

              <div className="form-group">
                <label htmlFor="email" className="block text-sm font-bold text-btext mb-2 uppercase tracking-wide">
                  Email
                </label>
                <input
                  id="email"
                  name="email"
                  type="email"
                  autoComplete="email"
                  required
                  className="input-field"
                  placeholder="tu@email.com"
                  value={email}
                  onChange={(e) => setEmail(e.target.value)}
                />
              </div>
              
              <div className="form-group">
                <label htmlFor="password" className="block text-sm font-bold text-btext mb-2 uppercase tracking-wide">
                  Password
                </label>
                <input
                  id="password"
                  name="password"
                  type="password"
                  autoComplete="new-password"
                  required
                  className="input-field"
                  placeholder="••••••••"
                  value={password}
                  onChange={(e) => setPassword(e.target.value)}
                />
              </div>

              {error && (
                <div className="bg-bred p-4 flex mt-6">
                  <p className="text-white font-bold" role="alert">{error}</p>
                </div>
              )}

              <div className="pt-6">
                <button
                  type="submit"
                  disabled={loading}
                  className="btn-primary"
                >
                  {loading ? 'CREANDO...' : 'REGISTRARSE'}
                </button>
              </div>
            </form>

            <div className="mt-12 text-center">
              <p style={{ color: '#5f5f5f' }}>
                ¿Ya tienes cuenta?{' '}
                <Link to="/login" className="font-bold text-btext hover:text-bgreen transition-colors">
                  Accede aquí
                </Link>
              </p>
            </div>
          </div>
        </div>

        {/* Panel Decorativo Derecho */}
        <div className="hidden lg:flex lg:w-1/2 flex-col justify-center items-center py-16 px-8 relative overflow-hidden">
          <div className="relative z-10 w-full max-w-lg">
             <h3 className="text-5xl font-black text-bdark mb-8 text-center leading-tight">
                LA SUPERVIVENCIA<br />
                ES TAREA<br />
                DE TODOS
             </h3>
          </div>
          {/* Geometría abstracta usando marrón como acento */}
          <div className="absolute top-0 right-0 w-96 h-96 bg-btext rounded-bl-full transform translate-x-1/3 -translate-y-1/3 opacity-20"></div>
          <div className="absolute bottom-0 left-0 w-80 h-80 bg-bgreen rounded-tr-full transform -translate-x-1/4 translate-y-1/4 opacity-10"></div>
        </div>

      </div>
    </div>
  );
}

export default Register;
