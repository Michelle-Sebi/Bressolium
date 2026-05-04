/**
 * @module Dashboard
 * @description Vista principal del Lobby y gestor de equipos.
 * Implementa un diseño Brutalista / Minimalista Plano dividido al 50%.
 * @see Tarea 5 - Game Lobby & Team Manager UI
 */

import React, { useState, useEffect } from 'react';
import { useDispatch, useSelector } from 'react-redux';
import { useNavigate } from 'react-router-dom';
import { 
  fetchGamesThunk, 
  fetchMyGamesThunk, 
  createGameThunk, 
  joinRandomThunk,
  setCurrentGame
} from '../game/gameSlice';

const Dashboard = () => {
  const dispatch = useDispatch();
  const navigate = useNavigate();
  const { availableGames = [], myGames = [], status, error } = useSelector((state) => state.game || {});
  
  const [searchTerm, setSearchTerm] = useState('');
  const [isModalOpen, setIsModalOpen] = useState(false);
  const [newTeamName, setNewTeamName] = useState('');

  useEffect(() => {
    dispatch(fetchGamesThunk());
    dispatch(fetchMyGamesThunk());
  }, [dispatch]);

  const filteredGames = availableGames.filter(game => 
    game.name.toLowerCase().includes(searchTerm.toLowerCase())
  );

  const handleCreateTeam = async (e) => {
    e.preventDefault();
    const result = await dispatch(createGameThunk({ name: newTeamName }));
    if (!result.error) {
      setIsModalOpen(false);
      setNewTeamName('');
    }
  };

  const handleJoinRandom = () => {
    dispatch(joinRandomThunk());
  };

  const handleGoToGame = (game) => {
    dispatch(setCurrentGame(game));
    navigate(`/board`);
  };

  return (
    <div className="flex-1 flex flex-col lg:flex-row font-sans overflow-hidden">
      
      {/* SECCIÓN IZQUIERDA: LOBBY (UNIRSE) */}
      <div className="w-full lg:w-1/2 bg-bgray p-8 lg:p-16 flex flex-col overflow-y-auto">
        <h1 className="text-4xl lg:text-5xl font-black text-bbrown mb-12 tracking-tighter">
          UNIRSE A LA <br/> TERRAFORMACIÓN
        </h1>

        <div className="space-y-8 max-w-md">
          {/* Unirse Aleatorio */}
          <button 
            onClick={handleJoinRandom}
            className="btn-primary"
          >
            ASIGNACIÓN ALEATORIA
          </button>

          {/* Crear Nuevo */}
          <button 
            onClick={() => setIsModalOpen(true)}
            className="btn-primary bg-bbrown hover:bg-[#6e5b44]"
          >
            CREAR EQUIPO NUEVO
          </button>

          {/* Buscador y Lista */}
          <div className="mt-12">
            <input 
              id="search"
              name="search"
              type="text" 
              placeholder="BUSCAR EQUIPO..."
              className="input-field mb-4"
              value={searchTerm}
              onChange={(e) => setSearchTerm(e.target.value)}
            />
            
            <div className="bg-white border-l-8 border-bbrown min-h-50">
              {filteredGames.length > 0 ? (
                <ul className="divide-y divide-gray-100">
                  {filteredGames.map(game => (
                    <li key={game.id} className="p-4 flex justify-between items-center hover:bg-gray-50 transition-colors">
                      <div>
                        <span className="font-bold text-bbrown block">{game.name.toUpperCase()}</span>
                        <span className="text-xs text-gray-400">{game.users_count || 0}/5 MIEMBROS</span>
                      </div>
                      <button className="text-sm font-black text-bgreen hover:underline">
                        UNIRSE
                      </button>
                    </li>
                  ))}
                </ul>
              ) : (
                <div className="p-8 text-center text-gray-400 italic">
                  No hay exploraciones disponibles con ese nombre...
                </div>
              )}
            </div>
          </div>
        </div>
      </div>

      {/* SECCIÓN DERECHA: MIS PARTIDAS */}
      <div className="w-full lg:w-1/2 bg-white p-8 lg:p-16 flex flex-col overflow-y-auto">
        <h2 className="text-3xl lg:text-4xl font-black text-bbrown mb-12 tracking-tighter border-b-8 border-bgray pb-4 inline-block">
          MIS EXPEDICIONES <br/> ACTIVAS
        </h2>

        <div className="grid grid-cols-1 gap-4 overflow-y-auto">
          {myGames.length > 0 ? (
            myGames.map(game => (
              <div 
                key={game.id} 
                onClick={() => handleGoToGame(game)}
                className="group cursor-pointer bg-bgray p-6 hover:bg-bbrown transition-all"
              >
                <div className="flex justify-between items-center">
                  <div>
                    <span className="block text-2xl font-black text-bbrown group-hover:text-white transition-colors">
                      {game.name.toUpperCase()}
                    </span>
                    <span className="text-xs font-bold text-bgreen group-hover:text-bgray uppercase">
                      ESTADO: {game.status}
                    </span>
                  </div>
                  <div className="text-bbrown group-hover:text-white">
                    <svg className="w-8 h-8" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                      <path strokeLinecap="round" strokeLinejoin="round" strokeWidth="3" d="M14 5l7 7m0 0l-7 7m7-7H3" />
                    </svg>
                  </div>
                </div>
              </div>
            ))
          ) : (
            <div className="py-24 text-center border-4 border-dashed border-gray-100 text-gray-300 font-bold">
              SIN EXPEDICIONES EN CURSO
            </div>
          )}
        </div>
      </div>

      {/* MODAL CREACIÓN (Simulado con overlay plano) */}
      {isModalOpen && (
        <div className="fixed inset-0 bg-bbrown/90 flex items-center justify-center p-4 z-50">
          <div className="bg-white w-full max-w-lg p-10 relative">
            <button 
              onClick={() => setIsModalOpen(false)}
              className="absolute top-4 right-4 text-3xl font-black text-bbrown hover:text-bred"
            >
              ×
            </button>
            
            <h3 className="text-3xl font-black text-bbrown mb-8">FUNDAR EQUIPO</h3>
            
            <form onSubmit={handleCreateTeam} className="space-y-6">
              <div>
                <label htmlFor="teamName" className="block text-xs font-bold text-gray-400 mb-2 uppercase">NOMBRE DEL EQUIPO</label>
                <input 
                  id="teamName"
                  name="teamName"
                  type="text" 
                  className="input-field" 
                  required
                  value={newTeamName}
                  onChange={(e) => setNewTeamName(e.target.value)}
                />
              </div>
              
              <button type="submit" className="btn-primary mt-8">
                FUNDAR CIVILIZACIÓN
              </button>
            </form>
          </div>
        </div>
      )}

      {error && (
        <div className="fixed bottom-0 left-0 right-0 bg-bred text-white p-4 text-center font-bold z-50">
          ERROR: {error}
        </div>
      )}
    </div>
  );
};

export default Dashboard;
