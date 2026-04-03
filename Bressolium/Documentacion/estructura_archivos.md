/Bressolium-Project (Raíz del Repositorio Git)
│
├── .git/                  <-- Único historial para ambos proyectos
├── README.md              <-- Documentación principal (y el raw_tareas.md)
│
├── /backend/              <-- SERVIDOR Y BASE DE DATOS (Laravel)
│   ├── app/               <-- Modelos (User, Partida, Casilla, etc.) y Controladores de la API
│   ├── database/          <-- Migraciones y Seeders de vuestra BD PostgreSQL
│   ├── routes/
│   │   └── api.php        <-- Todas vuestras rutas REST (/api/v1/login, /api/v1/tablero)
│   ├── public/            <-- Punto de entrada del servidor de Render/Railway
│   └── .env               <-- Variables de entorno para BD y JWT
│
└── /frontend/             <-- CLIENTE (React generado con Vite)
    ├── package.json       
    ├── vite.config.js     
    ├── src/
    │   ├── components/    <-- Componentes reutilizables (Botón, Carta de Casilla)
    │   ├── pages/         <-- Vistas enteras (Login, Tablero, Inventario)
    │   ├── services/      <-- Donde irá Axios/Fetch para llamar al "/backend"
    │   ├── styles/        <-- Tailwind o CSS global
    │   └── App.jsx        
    └── .env               <-- VITE_API_URL=http://localhost:8000 (para dev)

    
¿Por qué esta es la estructura más sencilla para desplegar?
Evita dolores de cabeza en el despliegue automático: Cuando queráis desplegar el frontend en páginas como Vercel o Netlify, simplemente le diréis "El proyecto está en la carpeta /frontend". Vercel ignorará Laravel por completo e instalará solo React.
Lo mismo para la API: Si usáis Railway, Render o Heroku para Laravel, haréis que el servidor apunte solo a la carpeta /backend/public. El servidor PHP no se ensuciará con el Javascript.
División del Trabajo: Si Bárbara está con una tarea de Front (feat/HU02-login-interfaz) solo tocará cosas de /frontend, y si Michelle o Bárbara hace el comportamiento del login (feat/HU03-login-api), trabajará en /backend. Cuando hagáis merge, ambas mitades convivirán en GitHub juntas, lo que facilita descargar y arrancar el MVP de una vez.
Flujo en local: Para desarrollar simplemente tendréis dos terminales abiertas:

Terminal 1 (en /backend): php artisan serve (Corriendo en el puerto 8000)
Terminal 2 (en /frontend): npm run dev (Corriendo en el puerto 5173)