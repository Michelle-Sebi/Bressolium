📘 Introducción y Propósito de esta Guía
Este documento sirve como guía para construir una aplicación full-stack profesional. Lo que aquí se presenta es un primer punto de partida para proporcionar una aproximación de lo que se espera implementar.

Una Guía Viva para un Proyecto Real
Este documento se considera una guía fundacional. Se definen los pilares y la estructura del proyecto. Sin embargo, es importante entender que se trata de un documento "vivo" y se podrán realizar pequeñas modificaciones o aclaraciones hasta el inicio formal del proyecto, siempre con el objetivo de alinearse perfectamente con los requisitos finales del módulo. Cualquier ajuste se comunicará de manera oficial.

Libertad Temática con una Base Estándar
La temática del proyecto será de libre elección. Se busca trabajar en algo que motive y permita demostrar la creatividad de cada uno.

No obstante, independientemente del tema que se elija, la arquitectura, los patrones y las prácticas profesionales descritas en esta guía son de obligado cumplimiento.

¿Qué se Espera del Proyecto?
Más allá de entregar una aplicación funcional, se espera demostrar una comprensión profunda de los conceptos aquí expuestos. El objetivo es lograr:

Justificar las decisiones técnicas basándose en los principios de esta guía.
Escribir código limpio, desacoplado y testeable.
Trabajar siguiendo un flujo profesional, desde el control de versiones hasta las pruebas y la documentación.
Documentar el proyecto adecuadamente, explicando la arquitectura y las decisiones clave.
Se recomienda estudiar esta guía con atención y usarla como referencia principal a lo largo de todo el desarrollo.

🗺️ PARTE I: ARQUITECTURA BACKEND
Esta arquitectura se estructura en componentes lógicos y desacoplados. Cada componente tiene una única responsabilidad, comunicándose con otros componentes a través de contratos bien definidos.

🏗️ Estructura de Carpetas del Backend
/app
/Http:
/Controllers: Controladores que coordinan el flujo de peticiones.
/Middleware: Filtros globales y de ruta.
/Requests: Clases de validación y autorización (Form Requests).
/Resources: Transformadores de modelos a JSON (API Resources).
/Policies: Clases de autorización para acciones sobre recursos.
/Services: Clases que contienen la lógica de negocio.
/Repositories: Interfaces e implementaciones para acceso a datos.
/Contracts: Interfaces de los repositorios.
/Eloquent: Implementaciones concretas con Eloquent.
/DTOs: Objetos de transferencia de datos entre capas.
/Events: Eventos del sistema.
/Listeners: Clases que responden a eventos.
/Exceptions: Excepciones personalizadas del dominio.
/Models: Modelos de base de datos.
/Providers: Proveedores de servicios para inyección de dependencias y registro en el contenedor IoC.
/config: Archivos de configuración de la aplicación.
/database:
/migrations: Migraciones de base de datos.
/seeders: Datos de prueba.
/routes: Definición de rutas de la API.
/tests: Tests unitarios y de integración.
/Unit: Tests unitarios de servicios y repositorios.
/Feature: Tests de integración de controladores.
🧩 Componentes del Backend
Router: Se encarga de conectar las URLs con los controladores correspondientes. Se mapean las rutas y los métodos HTTP. Se debe implementar versionado de APIs y protección mediante rate limiting.

Middleware: Son filtros que se ejecutan antes de llegar al controlador. Existen dos tipos:

Middleware Global: Se aplica a un grupo de rutas para tareas comunes como forzar respuestas JSON o registrar peticiones.
Middleware de Ruta: Se aplica a rutas específicas para verificar autenticación, roles o permisos.
Form Requests: Clases que se ejecutan antes del controlador y tienen dos funciones:

Autorización: Se verifica si el usuario tiene permiso para realizar la acción.
Validación: Se comprueba que los datos de entrada cumplen las reglas definidas.
Policies: Clases dedicadas a la autorización de acciones sobre recursos específicos. Se definen reglas de acceso que determinan si un usuario puede realizar operaciones como ver, crear, actualizar o eliminar un recurso. Se utilizan junto con los Form Requests para una autorización más granular.

DTOs (Data Transfer Objects): Objetos simples que transportan datos entre capas. Se utilizan para pasar información del controlador al servicio de forma organizada y estructurada.

Controladores: Se encargan de coordinar el flujo de la petición. Sus responsabilidades son:

Recibir la petición validada.
Crear el DTO con los datos.
Llamar al servicio correspondiente.
Devolver la respuesta formateada.
Servicios: Contienen toda la lógica de negocio de la aplicación. Se implementan las reglas y procesos que definen cómo funciona el sistema. Se coordinan múltiples operaciones y se gestionan transacciones de base de datos.

Repositorios: Se encargan de acceder a los datos. Se componen de:

Interfaz: Define qué operaciones se pueden realizar con los datos.
Implementación: Contiene el código que realmente accede a la base de datos u otra fuente de datos.
Se utiliza inyección de dependencias para poder cambiar la fuente de datos sin modificar el resto del código.
Modelos: Representan las entidades de la base de datos. Se definen las propiedades, relaciones entre tablas y comportamientos básicos de cada entidad. Los modelos son utilizados por los repositorios para interactuar con la base de datos.

Cache Service: Servicio dedicado a gestionar la caché. Se centraliza la lógica para guardar, recuperar e invalidar datos temporales y mejorar el rendimiento de la aplicación.

API Resources: Se transforman los modelos de base de datos en respuestas JSON. Se define qué datos se muestran al cliente y cómo se formatean.

Response Builder: Clase auxiliar que estandariza el formato de todas las respuestas de la API, garantizando consistencia en éxitos, errores y paginación.

Eventos y Listeners: Sistema para desacoplar acciones:

Eventos: Se lanzan cuando ocurre algo importante en el sistema.
Listeners: Escuchan eventos y ejecutan acciones en respuesta. Pueden ejecutarse de forma síncrona o en segundo plano.
Manejo de Excepciones:

Excepciones Personalizadas: Clases específicas que representan errores del dominio.
Handler Global: Intercepta todas las excepciones y las convierte en respuestas HTTP apropiadas.
IoC Container (Contenedor de Inversión de Control): Sistema que gestiona automáticamente la creación y resolución de dependencias. Permite inyectar las dependencias necesarias en los componentes sin tener que crearlas manualmente, facilitando el desacoplamiento y la testabilidad del código.

Service Providers: Clases que se ejecutan al arrancar la aplicación y actúan como archivos de configuración del IoC Container. Se encargan de "enseñar" al contenedor cómo construir y registrar los servicios, definiendo qué implementación concreta debe usar cuando se solicita una interfaz. Por ejemplo, se configura qué implementación de un Repositorio debe usar el contenedor cuando un Servicio lo solicite.

🌊 Flujo Arquitectónico del Backend (Diagrama Visual)
Para ilustrar el ciclo de vida de una petición, se usa este diagrama conceptual:

🧩 Arquitectura de Soporte

📢 Sistema de Eventos

🖼️ Respuesta

🧠 Lógica y Datos

🛡️ Filtros de Entrada

▶ Cliente

1. Petición

2. Dirige a

3. Pasa a

4. Pasa a

5. Validada y Autorizada

6. Crea

7. Pasa a

8. Usa

9. Opera con

10. Accede a

Alternativa

11. Devuelve datos

12. Devuelve datos

13. Devuelve

14. Transforma con

15. Formatea con

16. Genera

17. Envía

Dispara

Escuchado por

Registra en

Inyecta

Inyecta

Inyecta

Define

Cliente HTTP

Router

Middleware Global

Middleware de Ruta

Form Request + Policy

Controlador

DTO

Servicio

Repositorio

Modelo

Base de Datos

Cache

API Resource

Response Builder

Respuesta HTTP

Eventos

Listeners

IoC Container

Contratos/Interfaces

Service Providers

🎨 PARTE II: ARQUITECTURA FRONTEND
Esta arquitectura se basa en un enfoque modular, tipado y centrado en la gestión eficiente del estado, especialmente el que proviene del servidor.

🏗️ Estructura de Carpetas del Frontend
/src
/api: Configuración del cliente HTTP y servicios de datos.
/assets: Recursos estáticos como imágenes, fuentes e iconos.
/components: Componentes de interfaz reutilizables.
/ui: Componentes básicos como botones, inputs o modales.
/layout: Componentes estructurales como cabecera, barra lateral o diseño principal.
/features: Carpeta principal de la arquitectura. Se organizan módulos independientes por cada funcionalidad de la aplicación.
/[nombre-feature]:
/api: Llamadas a la API específicas de esta funcionalidad.
/components: Componentes específicos de esta funcionalidad.
/hooks: Hooks personalizados con la lógica de negocio.
/slices: Estado global relacionado con esta funcionalidad (opcional).
/types: Definiciones de tipos de TypeScript.
/__tests__: Tests de esta funcionalidad.
/hooks: Hooks personalizados globales y reutilizables.
/pages: Componentes que representan las vistas principales de la aplicación. Se implementa carga diferida para optimizar rendimiento.
/routes: Configuración centralizada del enrutador. Se incluyen rutas protegidas para autenticación.
/store: Configuración del gestor de estado global cuando sea necesario.
/contexts: Proveedores de contexto para estados compartidos como tema o notificaciones.
/lib: Configuración de librerías externas como cliente HTTP o validación.
/types: Tipos y definiciones de TypeScript globales para el proyecto.
/utils: Funciones de ayuda, constantes y validadores genéricos.
/styles: Estilos globales y configuración de temas.
/locales: Recursos para internacionalización.
App.jsx: Componente raíz de la aplicación.
main.jsx: Punto de entrada de la aplicación.
🧩 Componentes del Frontend
Cliente HTTP: Se configura un cliente HTTP centralizado con interceptores para gestionar automáticamente los tokens de autenticación y manejar errores comunes de forma global.

Servicios de API: Colecciones de funciones que realizan las llamadas a los endpoints del backend. Son la única capa que interactúa directamente con el cliente HTTP.

Hooks de Lógica: Contienen la lógica de interacción con la API. Se utiliza una librería de gestión de estado de servidor para manejar la obtención de datos, el cacheo y las actualizaciones.

Estado Global: Se gestiona el estado que debe ser accesible desde múltiples partes de la aplicación. Se recomienda usar Context para datos que cambian poco y Store para datos que cambian frecuentemente.

Contexts: Se utilizan para compartir estados que no cambian con frecuencia pero necesitan ser accesibles desde múltiples componentes.

Componentes de UI: Componentes básicos y reutilizables que solo se encargan de mostrar la interfaz. Reciben datos mediante props y no contienen lógica de negocio.

Componentes de Feature: Componentes que combinan varios componentes de UI para crear funcionalidades específicas dentro de un módulo.

Páginas: Componentes que representan vistas completas de la aplicación. Se encargan de obtener los datos mediante hooks y pasarlos a los componentes de presentación.

Router: Sistema centralizado que define las rutas de la aplicación. Se incluyen rutas protegidas que verifican la autenticación antes de permitir el acceso.

🌊 Flujo de Datos en Frontend (Diagrama Visual)
Para paralelizar con el backend, se muestra un flujo típico:

Hit

Miss

Usuario

Router

Página

Hook de Lógica

Cache?

Servicio API

Cliente HTTP

Interceptor

Backend API

Estado Global/Context

Componentes Feature

Componentes UI

🛠️ PARTE III: PRÁCTICAS PROFESIONALES TRANSVERSALES
Estos elementos garantizan la calidad y profesionalidad de todo el proyecto. Se aplican de forma transversal a todas las capas.

Testing Automatizado:

Backend: Se crean tests unitarios para servicios y repositorios, y tests de integración para los controladores.
Frontend: Se implementan tests unitarios para componentes y hooks, y tests de integración para las páginas.
End-to-End: Se ejecutan pruebas que simulan el flujo completo del usuario a través del navegador.
CI/CD (Integración y Despliegue Continuo): Se configuran procesos automatizados que ejecutan tareas ante cada cambio en el código:

Instalación de dependencias.
Ejecución de linters y formateadores de código.
Ejecución de tests.
Construcción de los artefactos de producción.
Despliegue automático a los entornos correspondientes.
Gestión de la Configuración y Secretos: Se utilizan variables de entorno para manejar información sensible y parámetros que cambian entre entornos, evitando exponer datos críticos en el código fuente.

Documentación y Convenciones:

Commits Semánticos: Se adopta un estándar para los mensajes de commit que mejora la legibilidad del historial.
Documentación de Código: Se documentan clases y métodos complejos siguiendo estándares establecidos.
Convenciones de Nomenclatura: Se establecen reglas consistentes para nombrar archivos, clases, funciones y variables.
Deployment y Entornos: Se implementan estrategias para producción utilizando contenedores para backend y servicios de hosting para frontend. Se asegura HTTPS y configuración CORS adecuada.

Monitoreo y Métricas: Se utilizan herramientas para rastrear errores y optimizar el rendimiento de la aplicación.

Seguridad y Optimizaciones: Se implementan prácticas de accesibilidad, internacionalización y optimización de rendimiento mediante caching y carga diferida.

Herramientas de Construcción (Build Tools): Se utilizan herramientas como Vite o Webpack para procesar el código fuente del frontend. Estas herramientas se encargan de:

Transpilar código moderno (JSX, TypeScript) a JavaScript compatible con navegadores.
Procesar y optimizar archivos CSS y assets.
Empaquetar y minificar el código para producción.
Generar los archivos estáticos finales (HTML, JS, CSS) que se envían al navegador.
Proporcionar un servidor de desarrollo con recarga en caliente para agilizar el desarrollo.
🔄 PARTE IV: Visión Unificada y Síntesis Arquitectónica
Se destacan las similitudes entre el frontend y el backend para facilitar la comprensión del sistema completo.

Entrada y Seguridad:

Backend: Router + Middleware
Frontend: Router + Interceptores
Ambos controlan el acceso y direccionan las peticiones.
Negocio y Lógica:

Backend: Servicios + Repositorios
Frontend: Hooks + Servicios API
Ambos contienen la lógica de negocio y gestionan el acceso a los datos.
Presentación y Respuesta:

Backend: Controladores + API Resources
Frontend: Páginas + Componentes UI
Ambos se encargan de coordinar y formatear la información.
Datos y Estado:

Backend: Capa de Acceso a Datos + Caché
Frontend: Estado Global + Contexts + Caching
Ambos gestionan el almacenamiento y la recuperación de información.
Asincronía y Desacoplamiento:

Backend: Eventos + Queues
Frontend: Mutations Optimistas
Ambos permiten ejecutar acciones sin bloquear el flujo principal.
Inyección y Flexibilidad:

Backend: Inyección de Dependencias
Frontend: Contexts + Providers
Ambos permiten inyectar funcionalidades de forma organizada.
Se comprende que estas similitudes facilitan el desarrollo y mantenimiento del proyecto, permitiendo aplicar conceptos equivalentes en ambas partes de la aplicación.

🔗 PARTE V: Integración con Otros Módulos
Este proyecto forma parte de un módulo transversal que se relaciona con otros módulos del ciclo formativo. Se pueden integrar y aprovechar contenidos desarrollados en otros módulos como recursos externos al proyecto.

📦 Recursos Externos Permitidos
Los contenidos creados en otros módulos no tienen que seguir necesariamente la estructura arquitectónica definida en esta guía. Se consideran recursos externos y se pueden integrar de las siguientes formas:

Desarrollo de Interfaces Web (DIW):

Se pueden utilizar hojas de estilo CSS, sistemas de diseño o componentes visuales creados en este módulo.
Estos recursos se integran como librerías o assets externos en la carpeta /assets o /styles del frontend.
No se requiere que sigan la estructura de componentes de React o el framework utilizado.
Desarrollo Web en Entorno Cliente (DWEC):

Se pueden aprovechar componentes, utilidades o librerías JavaScript desarrolladas en este módulo.
Estos recursos se integran como módulos externos o librerías en la carpeta /lib o /utils.
Se pueden adaptar para seguir los patrones de hooks o componentes si es necesario, pero no es obligatorio.
Desarrollo Web en Entorno Servidor (DWES):

Se pueden utilizar APIs REST o servicios web desarrollados en este módulo.
Estos servicios se consumen como servicios externos mediante los servicios de API.
La API externa no tiene que seguir la arquitectura de capas del backend si ya está implementada previamente.
Despliegue de Aplicaciones Web (DAW):

Se pueden aprovechar configuraciones de Docker, docker-compose, scripts de despliegue o configuraciones de servidores.
Estos recursos se integran en la raíz del proyecto o en carpetas de configuración específicas.
Facilitan el proceso de despliegue y configuración de entornos sin necesidad de recrearlos desde cero.
🎯 Consideraciones de Integración
Se deben tener en cuenta las siguientes consideraciones al integrar recursos externos:

Documentación: Se debe documentar claramente qué recursos externos se utilizan, de qué módulo provienen y cómo se integran en el proyecto.

Adaptación: Aunque no sea obligatorio seguir la arquitectura propuesta, se recomienda adaptar los recursos externos para que se integren de forma coherente con el resto del proyecto.

Independencia: El proyecto debe poder funcionar correctamente con los recursos externos integrados, asegurando que las dependencias estén bien gestionadas.

Esta integración transversal permite aprovechar el trabajo realizado en otros módulos, fomentando la reutilización de código y la visión integral del desarrollo web profesional.