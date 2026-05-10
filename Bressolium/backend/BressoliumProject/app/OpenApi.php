<?php

namespace App;

/**
 * @OA\Info(
 *     version="1.0.0",
 *     title="Bressolium API",
 *     description="API REST del videojuego de estrategia colaborativa Bressolium. Todos los endpoints devuelven JSON con el formato unificado {success, data, error}.",
 *     @OA\Contact(name="Equipo Bressolium")
 * )
 *
 * @OA\Server(
 *     url="http://localhost/api/v1",
 *     description="Servidor de desarrollo local"
 * )
 *
 * @OA\SecurityScheme(
 *     securityScheme="sanctum",
 *     type="http",
 *     scheme="bearer",
 *     bearerFormat="Sanctum-Token",
 *     description="Token de Laravel Sanctum obtenido al hacer login. Se envía en la cabecera Authorization: Bearer {token}."
 * )
 *
 * @OA\Tag(name="Auth", description="Registro, login y logout de usuarios")
 * @OA\Tag(name="Game", description="Creación, unión y consulta de partidas")
 * @OA\Tag(name="Board", description="Consulta del tablero de juego")
 * @OA\Tag(name="Tile", description="Acciones sobre casillas (explorar, mejorar)")
 * @OA\Tag(name="Sync", description="Estado completo del juego para el frontend")
 * @OA\Tag(name="Vote", description="Votación de tecnologías e inventos")
 * @OA\Tag(name="Round", description="Cierre de jornada")
 * @OA\Tag(name="Stats", description="Métricas del sistema y del juego")
 *
 * @OA\Schema(
 *     schema="ApiResponse",
 *     type="object",
 *     description="Formato unificado de respuesta de la API",
 *     @OA\Property(property="success", type="boolean", example=true),
 *     @OA\Property(property="data", type="object", nullable=true),
 *     @OA\Property(property="error", type="string", nullable=true, example=null)
 * )
 *
 * @OA\Schema(
 *     schema="ApiError",
 *     type="object",
 *     description="Respuesta de error estándar",
 *     @OA\Property(property="success", type="boolean", example=false),
 *     @OA\Property(property="data", type="object", nullable=true, example=null),
 *     @OA\Property(property="error", type="string", example="Mensaje de error")
 * )
 */
class OpenApi
{
}
