<?php

/**
 * Script para actualizar el modelo User después de instalar tymon/jwt-auth
 * 
 * Uso: php update_jwt_model.php
 */

$userModelPath = __DIR__ . '/app/Models/User.php';

if (!file_exists($userModelPath)) {
    echo "Error: No se encontró el archivo User.php\n";
    exit(1);
}

$content = file_get_contents($userModelPath);

// Verificar si JWT está instalado
if (!interface_exists('Tymon\JWTAuth\Contracts\JWTSubject')) {
    echo "Error: El paquete tymon/jwt-auth no está instalado.\n";
    echo "Ejecuta: composer require tymon/jwt-auth\n";
    exit(1);
}

// Verificar si ya está implementando la interfaz
if (strpos($content, 'implements') !== false && strpos($content, 'JWTSubject') !== false) {
    echo "El modelo User ya implementa JWTSubject.\n";
    exit(0);
}

// Agregar el use statement si no existe
if (strpos($content, 'use Tymon\\JWTAuth\\Contracts\\JWTSubject;') === false) {
    $content = str_replace(
        'use Laravel\Sanctum\HasApiTokens;',
        "use Laravel\Sanctum\HasApiTokens;\nuse Tymon\JWTAuth\Contracts\JWTSubject;",
        $content
    );
}

// Actualizar la declaración de la clase
$content = preg_replace(
    '/class User extends Authenticatable/',
    'class User extends Authenticatable implements JWTSubject',
    $content
);

// Remover comentarios sobre descomentar
$content = preg_replace(
    '/\s*\*\s*NOTA:.*?Después de instalar.*?\n/',
    '',
    $content
);

file_put_contents($userModelPath, $content);

echo "✓ Modelo User actualizado correctamente para usar JWT.\n";

