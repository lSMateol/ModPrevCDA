<?php

namespace App\Support;

/**
 * Clase que define el catálogo de preguntas secretas disponibles para
 * la recuperación de contraseña.
 *
 * Para agregar o modificar preguntas, editar únicamente esta clase.
 * La clave (ej: 'q1') se guarda en la BD; el texto se resuelve en tiempo de ejecución.
 */
final class SecretQuestions
{
    /**
     * Catálogo de preguntas disponibles.
     * Clave: identificador corto almacenado en BD.
     * Valor: texto completo de la pregunta mostrado al usuario.
     */
    public const QUESTIONS = [
        'q1' => '¿Cuál es el nombre de tu mascota?',
        'q2' => '¿En qué ciudad naciste?',
        'q3' => '¿Cuál es el apellido de soltera de tu madre?',
        'q4' => '¿Cuál fue el nombre de tu primera escuela?',
        'q5' => '¿Cuál es tu comida favorita?',
    ];

    /**
     * Retorna todas las preguntas como array asociativo [clave => texto].
     */
    public static function all(): array
    {
        return self::QUESTIONS;
    }

    /**
     * Retorna el texto de una pregunta dado su identificador.
     * Retorna null si la clave no existe.
     */
    public static function get(string $key): ?string
    {
        return self::QUESTIONS[$key] ?? null;
    }

    /**
     * Valida que una clave pertenezca al catálogo.
     */
    public static function isValid(string $key): bool
    {
        return array_key_exists($key, self::QUESTIONS);
    }

    /**
     * Retorna solo las claves del catálogo (útil para validaciones Rule::in).
     */
    public static function keys(): array
    {
        return array_keys(self::QUESTIONS);
    }
}
