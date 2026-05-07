#!/usr/bin/env bash
# Run with: GITHUB_TOKEN=ghp_xxx bash update_github_dods.sh
# Requires: curl, jq (apt install jq)

REPO="Michelle-Sebi/Bressolium"
TOKEN="${GITHUB_TOKEN:?Set GITHUB_TOKEN env var}"

patch_issue() {
  local number="$1"
  local body="$2"
  curl -s -X PATCH \
    -H "Authorization: Bearer $TOKEN" \
    -H "Content-Type: application/json" \
    "https://api.github.com/repos/$REPO/issues/$number" \
    --data-binary "$body" | jq -r '"#\(.number) → \(.title)"'
}

append_dod() {
  local number="$1"
  local dod_text="$2"
  # Fetch current body
  current=$(curl -s -H "Authorization: Bearer $TOKEN" \
    "https://api.github.com/repos/$REPO/issues/$number" | jq -r '.body // ""')
  new_body="${current}

**Definition of Done (DoD):**
${dod_text}"
  patch_issue "$number" "$(jq -n --arg b "$new_body" '{"body":$b}')"
}

echo "Updating GitHub issues with missing DoDs..."

# T10 (#23)
append_dod 23 "El endpoint \`GET /api/v1/game/sync\` devuelve inventario de recursos, inventos construidos con cantidades, progreso tecnológico y datos de la ronda activa. El frontend realiza polling cada ~30s actualizando el estado RTK. La arquitectura completa (Form Request, DTO, Service, Repository, API Resource) está implementada."

# T12 (#25)
append_dod 25 "El panel muestra dos zonas separadas: tecnologías e inventos votables. Los ítems con recursos suficientes están activos; los que no cumplen requisitos están en gris con indicación de qué falta. Las llamadas API pasan por el cliente HTTP centralizado."

# T13 (#26)
append_dod 26 "El job resuelve el ganador de votos (tecnología e invento), aplica costes, incrementa la cantidad del invento construido, suma la producción de casillas explotadas y crea una nueva \`Round\`. El campo \`actions_spent\` se resetea a 0 para todos los jugadores. Los tests cubren todos los escenarios."

# T15 (#28)
append_dod 28 "Al construir el invento con \`is_final=true\` en \`CloseRoundService\`, el campo \`game.status\` cambia a \`FINISHED\`, se dispara el evento \`GameFinished\` y no se crea una nueva ronda."

# T16 (#29)
append_dod 29 "Si un jugador no realiza ninguna acción durante una jornada completa, el flag \`is_afk\` en \`game_user\` se activa automáticamente al cerrar la ronda. La resolución del turno ignora a los jugadores con \`is_afk=true\` al calcular el quórum de votos."

# T19 (#39)
append_dod 39 "El modal muestra investigaciones completadas, disponibles (recursos y prerrequisitos suficientes) y bloqueadas con indicación de qué falta y en qué cantidad. Se abre al hacer clic sobre la casilla central \`pueblo\`. Los tests de \`Epica2_Front.test.jsx\` cubren las tres categorías de visualización."

# T25 (#60)
append_dod 60 "Todos los repositorios existentes tienen su interfaz en \`/Repositories/Contracts\`. Las implementaciones están en \`/Repositories/Eloquent\`. El \`RepositoryServiceProvider\` registra todos los bindings. Los servicios inyectan la interfaz, no la clase concreta. Los tests existentes siguen en verde."

# T26 (#61)
append_dod 61 "Cada endpoint tiene su Form Request con validación declarativa. Las Policies controlan la autorización sobre recursos. Todos los controladores están en \`/Http/Controllers/Api/\`. Los tests existentes siguen en verde."

# T27 (#62)
append_dod 62 "Cada flujo principal tiene su DTO. Todos los controladores usan API Resources para transformar modelos antes de devolverlos. Los modelos Eloquent no se devuelven directamente al cliente. Los tests existentes siguen en verde."

# T28 (#63)
append_dod 63 "Las excepciones de dominio están en \`/Exceptions\` y el handler global las convierte en JSON con el código HTTP correcto. Los servicios lanzan excepciones en lugar de devolver arrays con status. Los tests existentes siguen en verde."

# T29 (#64)
append_dod 64 "Los tests en \`/tests/Unit\` cubren GameService, ActionService y los repositorios principales mockeando todas las dependencias. Todos los tests unitarios pasan sin tocar la base de datos."

# T30 (#65)
append_dod 65 "El cliente Axios en \`/src/lib/\` añade automáticamente el token en cada petición y redirige a login en 401. Todas las llamadas API existentes pasan por este cliente. Las definiciones de API están organizadas en \`/features/[nombre]/api/\`."

# T31 (#66)
append_dod 66 "Los hooks \`useAuth\`, \`useGames\`, \`useBoard\` y \`useInventory\` encapsulan la lógica de interacción con la API. Los componentes son presentacionales puros que reciben datos y callbacks desde el hook. Los tests existentes siguen en verde."

# T32 (#67)
append_dod 67 "Los tests unitarios cubren \`useAuth\`, \`useBoard\` y \`useInventory\`. Los tests de componentes cubren Login, Register, Dashboard y BoardGrid. El cliente HTTP está mockeado en todos los tests y ningún test depende de red. Todos los tests pasan."

# T33 (#68)
append_dod 68 "El workflow de GitHub Actions se ejecuta en cada PR y push a \`main\`. Instala dependencias, ejecuta linters, tests de backend y frontend, y construye el frontend. El pipeline falla si algún test falla o el build no compila."

# T34 (#69)
append_dod 69 "Los tests de Playwright simulan el flujo completo: registro → login → crear partida → ver tablero → explorar casilla → ver inventario actualizado. Los tests pasan contra el entorno de desarrollo con Docker levantado."

# T35 (#70)
append_dod 70 "El documento de arquitectura existe en \`/Documentacion/\` y explica el patrón Controller→Service→Repository, el uso de Contracts e IoC Container, la estructura de features en el frontend, la gestión de estado con Redux y RTK Query, y las convenciones de nomenclatura."

# T36 (#71)
append_dod 71 "Las rutas API tienen throttle a 60 peticiones/minuto por usuario autenticado. Todas las rutas tienen el prefijo \`/api/v1/\`. El frontend y los tests apuntan a la nueva URL base."

# T37 (#72)
append_dod 72 "El \`CacheService\` centraliza guardar, recuperar e invalidar datos temporales. El estado del tablero y el sync están cacheados por \`game_id\`. La caché se invalida automáticamente al ejecutar una acción de explorar o upgrade. Los tests existentes siguen en verde."

# T38 (#73)
append_dod 73 "\`php artisan db:seed\` puebla el catálogo con los 44 recursos, 31 tecnologías y 34 inventos actualizados según \`casillas.md\` y \`evolucion-tecnologias-e-inventos.md\`, incluyendo las cantidades correctas en todos los prerrequisitos."

# T39 (#77)
append_dod 77 "Los eventos \`TileExplored\`, \`TileUpgraded\`, \`RoundClosed\`, \`MaterialsProduced\`, \`GameFinished\`, \`VoteCast\` e \`InventionBuilt\` se emiten desde los servicios correspondientes. Los listeners costosos implementan \`ShouldQueue\`. Los tests verifican que los eventos se disparan con \`Event::assertDispatched()\`."

# T40 (#78)
append_dod 78 "\`ResponseBuilder::success()\`, \`error()\` y \`paginated()\` son utilizados por todos los controladores. Los tests existentes siguen respondiendo con el formato \`{success, data, error}\`."

# T41 (#79)
append_dod 79 "Todas las peticiones API reciben \`Content-Type: application/json\`. Cada petición queda registrada en log estructurado con método, ruta, usuario, tiempo de respuesta y status. El middleware está registrado globalmente en \`bootstrap/app.php\`."

# T42 (#80)
append_dod 80 "Las llamadas API de board, inventory, sync y votes usan \`createApi\` con caché automática y revalidación. Los slices de Redux quedan reservados para estado de UI. El store incluye una justificación documentada de que RTK Query cumple el requisito de 'librería de gestión de estado de servidor'."

# T43 (#81)
append_dod 81 "Las vistas \`Login\`, \`Register\`, \`Dashboard\` y \`GameBoard\` son Pages independientes en \`/src/pages/\` con carga diferida mediante \`React.lazy()\`. La configuración del router está centralizada en \`/src/routes/\` con un HOC \`ProtectedRoute\`. El build del frontend no falla."

# T44 (#82)
append_dod 82 "\`ThemeContext\` y \`ToastContext\` están disponibles en la app. Los primitivos \`Button\`, \`Input\`, \`Modal\`, \`Toast\`, \`Badge\` e \`IconTile\` existen en \`/src/components/ui/\` siguiendo la guía de estilos brutalista. Los componentes de T12, T19, T50 y T51 los utilizan."

# T45 (#83)
append_dod 83 "El backend despliega correctamente en contenedor Docker con php-fpm + nginx. El frontend sirve como estáticos. HTTPS está activo con certbot o equivalente. CORS en Laravel restringe el acceso al dominio del frontend. Las variables de entorno de producción están separadas en \`.env.production\`."

# T46 (#84)
append_dod 84 "Sentry (o equivalente) captura errores en backend y frontend. El endpoint \`/api/v1/health\` devuelve uptime, estado de la BD, peticiones/min, errores/min y latencia p95. Existe un dashboard básico accesible al equipo."

# T47 (#85)
append_dod 85 "La app supera la auditoría de axe-core con contraste mínimo AA. Los iconos de materiales y casillas tienen \`alt\` o \`aria-label\`. La navegación por teclado funciona en \`BoardGrid\` y en los modales. Los tests de Playwright incluyen aserciones de accesibilidad."

# T48 (#86)
append_dod 86 "\`php artisan migrate\` añade la columna \`quantity\` a \`invention_prerequisites\` y \`technology_prerequisites\`. La tabla \`game_inventions\` existe con columnas \`game_id\`, \`invention_id\` y \`quantity\`. Los modelos Eloquent reflejan las nuevas relaciones. Los tests existentes siguen en verde."

# T49 (#87)
append_dod 87 "El diagrama ER HTML refleja V6 incluyendo la columna \`quantity\` en prerrequisitos y la tabla \`game_inventions\`. Los documentos \`casillas.md\` y \`evolucion-tecnologias-e-inventos.md\` incluyen las cantidades requeridas. La referencia en \`global_rules.md\` apunta a la versión V6."

# T50 (#88)
append_dod 88 "El \`InventoryPanel\` muestra dos zonas diferenciadas: 'Recursos' e 'Inventos'. Los inventos con \`quantity > 0\` aparecen activos y los demás inactivos. Los tests de \`Epica2_Front.test.jsx\` cubren ambas zonas."

# T51 (#89)
append_dod 89 "La casilla \`(7, 7)\` del tablero es siempre \`base_type=pueblo\`. Las acciones de explorar y mejorar sobre esa casilla lanzan una excepción 422. En el frontend, hacer clic sobre la casilla pueblo abre el modal del árbol tecnológico. Los tests de backend y frontend cubren todos los casos."

echo "Done."
