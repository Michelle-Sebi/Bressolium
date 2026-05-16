import React, { useState } from 'react';
import { useDispatch, useSelector } from 'react-redux';
import { useNavigate, Link } from 'react-router-dom';
import { logout } from '../../features/auth/authSlice';
import { setCurrentGame } from '../../features/game/gameSlice';

/**
 * @component TopBar
 * @description Barra superior persistente con gestión de sesión y cambio rápido de partida.
 * Sigue la estética Brutalista: bloques sólidos, bordes marcados (#a0a0a0) y colores planos.
 */
const TopBar = () => {
  const dispatch = useDispatch();
  const navigate = useNavigate();
  const [isSwitcherOpen, setIsSwitcherOpen] = useState(false);

  const { user } = useSelector((state) => state.auth);
  const { currentGame, myGames = [] } = useSelector((state) => state.game);

  const handleLogout = () => {
    dispatch(logout());
    navigate('/login');
  };

  const handleSwitchGame = (game) => {
    dispatch(setCurrentGame(game));
    setIsSwitcherOpen(false);
    navigate(`/board`);
  };

  return (
    <nav className="h-16 bg-white flex items-center justify-between px-6 z-40 relative">
      {/* Lado Izquierdo: Branding y Partida Actual */}
      <div className="flex items-center space-x-6">
        <Link to="/dashboard" className="text-2xl font-black text-bgreen tracking-tighter hover:text-bgreen transition-colors">
          BRESSOLIUM
        </Link>
        
        <div className="hidden md:flex items-center pl-6 h-10">
          <div className="relative">
            <button 
              onClick={() => setIsSwitcherOpen(!isSwitcherOpen)}
              className="flex items-center space-x-2 px-3 py-1 bg-bgreen hover:bg-[#2d5c50] transition-colors"
              aria-label="Cambiar partida"
            >
              <span className="text-xs font-bold text-white uppercase">
                {currentGame ? `EXPEDICIÓN: ${currentGame.name.toUpperCase()}` : 'SELECCIONAR PARTIDA'}
              </span>
              <svg
                className={`w-4 h-4 text-white transition-transform ${isSwitcherOpen ? 'rotate-180' : ''}`} 
                fill="none" 
                stroke="currentColor" 
                viewBox="0 0 24 24"
              >
                <path strokeLinecap="round" strokeLinejoin="round" strokeWidth="3" d="M19 9l-7 7-7-7" />
              </svg>
            </button>

            {/* Quick Switcher Dropdown */}
            {isSwitcherOpen && (
              <div className="absolute top-full left-0 mt-1 w-64 bg-white border-4 border-bbrown shadow-none z-50">
                <div className="p-2 border-b-4 border-bgray bg-bgray">
                  <span className="text-[10px] font-black text-white uppercase">Tus expediciones activas</span>
                </div>
                <div className="max-h-60 overflow-y-auto">
                  {myGames.length > 0 ? (
                    myGames.map((game) => (
                      <button
                        key={game.id}
                        onClick={() => handleSwitchGame(game)}
                        className={`w-full text-left p-3 hover:bg-bgray transition-colors flex items-center justify-between ${currentGame?.id === game.id ? 'bg-bgreen/10' : ''}`}
                      >
                        <span className="font-bold text-sm text-bbrown">{game.name.toUpperCase()}</span>
                        {currentGame?.id === game.id && (
                          <div className="w-2 h-2 bg-bgreen"></div>
                        )}
                      </button>
                    ))
                  ) : (
                    <div className="p-4 text-center text-xs text-gray-400 italic">No tienes partidas activas</div>
                  )}
                </div>
                <div className="p-2 bg-white border-t-4 border-bgray">
                  <Link 
                    to="/dashboard" 
                    onClick={() => setIsSwitcherOpen(false)}
                    className="block w-full text-center py-2 text-xs font-black text-bbrown hover:text-bgreen transition-colors"
                  >
                    + GESTIONAR EQUIPOS
                  </Link>
                </div>
              </div>
            )}
          </div>
        </div>
      </div>

      {/* Lado Derecho: Usuario y Logout */}
      <div className="flex items-center space-x-4">
        <div className="hidden sm:flex flex-col items-end leading-none">
          <span className="text-[10px] font-bold uppercase text-bdark">Pionero</span>
          <span className="text-sm font-black truncate max-w-30 text-bdark">
            {user?.name?.toUpperCase() || 'INVITADO'}
          </span>
        </div>
        
        <div className="w-10 h-10 bg-bgray rounded-full flex items-center justify-center">
           <span className="font-black text-white text-lg">
             {user?.name?.[0]?.toUpperCase() || 'P'}
           </span>
        </div>

        <button 
          onClick={handleLogout}
          className="ml-2 p-2 text-bdark hover:text-bred transition-colors"
          title="Salir"
          aria-label="Salir"
        >
          <svg className="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path strokeLinecap="round" strokeLinejoin="round" strokeWidth="2.5" d="M17 16l4-4m0 0l-4-4m4 4H7m6 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h4a3 3 0 013 3v1" />
          </svg>
        </button>
      </div>
    </nav>
  );
};

export default TopBar;
