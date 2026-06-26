<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\View\View;

/**
 * Controlador de recuperación de contraseña por pregunta secreta.
 *
 * Reemplaza el mecanismo anterior basado en envío de correo electrónico.
 * El flujo es:
 *   1. Usuario ingresa su email + respuesta a la pregunta secreta.
 *   2. Se valida que el usuario exista y que la respuesta coincida (Hash::check).
 *   3. Si es correcto, se guarda el email en sesión y se redirige al formulario
 *      de nueva contraseña (/reset-password).
 *   4. Si es incorrecto, se muestra error genérico sin revelar qué campo falló.
 */
class PasswordResetLinkController extends Controller
{
    /**
     * Muestra el formulario de recuperación de contraseña por pregunta secreta.
     */
    public function create(): View
    {
        return view('auth.forgot-password');
    }

    /**
     * Busca la pregunta secreta asociada al email o username ingresado.
     * Retorna JSON con la pregunta o error correspondiente.
     */
    public function findQuestion(Request $request)
    {
        $request->validate([
            'email' => ['required', 'string'],
        ], [
            'email.required' => 'El usuario o correo es obligatorio.',
        ]);

        $user = User::where('email', $request->email)
                    ->orWhere('username', $request->email)
                    ->first();

        if (!$user) {
            return response()->json([
                'success' => false,
                'message' => 'Las credenciales ingresadas no coinciden con nuestros registros.'
            ], 404);
        }

        if (empty($user->secret_question) || empty($user->secret_answer)) {
            return response()->json([
                'success' => false,
                'message' => 'Este usuario no tiene una pregunta secreta registrada. Por favor comuníquese con el CDA Rastrillantas para crear la respuesta secreta.'
            ], 422);
        }

        $questionText = \App\Support\SecretQuestions::get($user->secret_question);

        if (!$questionText) {
            return response()->json([
                'success' => false,
                'message' => 'La pregunta configurada para este usuario no es válida.'
            ], 422);
        }

        return response()->json([
            'success' => true,
            'question' => $questionText,
        ]);
    }

    /**
     * Procesa la solicitud de recuperación de contraseña.
     * Valida identidad por email + respuesta secreta en lugar de enviar correo.
     */
    public function store(Request $request): RedirectResponse
    {
        $request->validate([
            'email'         => ['required', 'string'],
            'secret_answer' => ['required', 'string'],
        ], [
            'email.required'         => 'El usuario o correo es obligatorio.',
            'secret_answer.required' => 'La respuesta secreta es obligatoria.',
        ]);

        // Buscar usuario por email O por username
        $user = User::where('email', $request->email)
                    ->orWhere('username', $request->email)
                    ->first();

        // Caso 1: El usuario no existe — error genérico (no revelar qué falló)
        if (!$user) {
            return back()
                ->withInput($request->only('email'))
                ->withErrors(['email' => 'Las credenciales ingresadas no coinciden con nuestros registros.']);
        }

        // Caso 2: El usuario existe pero no tiene respuesta/pregunta secreta configurada
        if (empty($user->secret_question) || empty($user->secret_answer)) {
            return back()
                ->withInput($request->only('email'))
                ->withErrors([
                    'secret_answer' => 'Este usuario no tiene una pregunta secreta registrada. Por favor comuníquese con el CDA Rastrillantas para crear la respuesta secreta.',
                ]);
        }

        // Caso 3: La respuesta secreta no coincide — error genérico
        if (!Hash::check(strtolower(trim($request->secret_answer)), $user->secret_answer)) {
            return back()
                ->withInput($request->only('email'))
                ->withErrors(['email' => 'Las credenciales ingresadas no coinciden con nuestros registros.']);
        }

        // Validación exitosa: guardar email en sesión y redirigir al formulario de nueva contraseña
        $request->session()->put('password_recovery_email', $user->email);

        return redirect()->route('password.reset')
            ->with('status', 'Identidad verificada. Establece tu nueva contraseña.');
    }
}
