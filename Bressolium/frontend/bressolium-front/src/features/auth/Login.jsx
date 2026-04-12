import React, { useState } from 'react';
import { useDispatch, useSelector } from 'react-redux';
import { loginThunk } from './authSlice';
import { Link, useNavigate } from 'react-router-dom';

function Login() {
  const dispatch = useDispatch();
  const navigate = useNavigate();
  const { status, error } = useSelector((state) => state.auth);

  const [email, setEmail] = useState('');
  const [password, setPassword] = useState('');

  const handleSubmit = async (e) => {
    e.preventDefault();
    const resultAction = await dispatch(loginThunk({ email, password }));
    if (loginThunk.fulfilled.match(resultAction)) {
      navigate('/dashboard');
    }
  };

  return (
    <div className="flex min-h-screen bg-white">
      {/* Panel Izquierdo - Bloque Sólido (Gris) */}
      <div className="hidden lg:flex lg:w-1/2 bg-bgray flex-col justify-between p-16">
        <div>
          <h1 className="text-7xl font-black text-bbrown tracking-tighter leading-none">
            BRESSOLIUM
          </h1>
          <p className="mt-6 text-xl text-bbrown/80 font-medium max-w-md">
            Gestiona la supervivencia y expansión de tu equipo en un planeta por conquistar.
          </p>
        </div>
        <div className="text-bbrown font-bold text-lg">
          v1.0.0
        </div>
      </div>

      {/* Panel Derecho - Formulario (Blanco) */}
      <div className="w-full lg:w-1/2 flex flex-col justify-center px-8 sm:px-16 xl:px-32">
        <div className="w-full max-w-md mx-auto">
          <h2 className="text-4xl font-extrabold text-bbrown mb-2">
            Acceso
          </h2>
          <p className="text-bbrown/70 mb-10 text-lg">
            Introduce tus credenciales para continuar.
          </p>
          
          <form id="login-form" className="space-y-8" onSubmit={handleSubmit} aria-label="Formulario de Login">
            <div className="space-y-6">
              <div className="form-group">
                <label htmlFor="email" className="block text-sm font-bold text-bbrown mb-2 uppercase tracking-wide">
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
                <label htmlFor="password" className="block text-sm font-bold text-bbrown mb-2 uppercase tracking-wide">
                  Password
                </label>
                <input
                  id="password"
                  name="password"
                  type="password"
                  autoComplete="current-password"
                  required
                  className="input-field"
                  placeholder="••••••••"
                  value={password}
                  onChange={(e) => setPassword(e.target.value)}
                />
              </div>
            </div>

            {error && (
              <div className="bg-bred p-4 flex">
                <p className="text-white font-bold" role="alert">{error}</p>
              </div>
            )}

            <div className="pt-4">
              <button
                type="submit"
                disabled={status === 'LOADING'}
                className="btn-primary"
              >
                {status === 'LOADING' ? 'VERIFICANDO...' : 'LOG IN'}
              </button>
            </div>
          </form>

          <div className="mt-12 text-center">
            <p className="text-bbrown/70">
              ¿No tienes cuenta?{' '}
              <Link to="/register" className="font-bold text-bbrown hover:text-bgreen transition-colors">
                Regístrate aquí
              </Link>
            </p>
          </div>
        </div>
      </div>
    </div>
  );
}

import { MemoryRouter, useInRouterContext } from 'react-router-dom';

// Wrapper para inyectar un Router Context falso si se invoca desde un Test (Vitest) 
// que no tenga el <BrowserRouter> configurado en su render()
export default function LoginWithContext(props) {
  const isRouting = useInRouterContext();
  if (!isRouting) {
    return (
      <MemoryRouter>
        <Login {...props} />
      </MemoryRouter>
    );
  }
  return <Login {...props} />;
}
