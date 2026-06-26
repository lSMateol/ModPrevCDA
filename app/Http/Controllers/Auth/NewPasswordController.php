<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Auth\Events\PasswordReset;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;
use Illuminate\Validation\Rules;
use Illuminate\View\View;

/**
 * Controlador para establecer la nueva contraseña tras validar la pregunta secreta.
 *
 * Reemplaza el mecanismo anterior basado en token de email.
 * El email del usuario se obtiene de la sesión (guardado por PasswordResetLinkController
 * tras verificar exitosamente la respuesta secreta).
 */
class NewPasswordController extends Controller
{
    /**
     * Muestra el formulario para establecer la nueva contraseña.
     * Redirige al formulario de recuperación si no hay sesión de recuperación activa.
     */
    public function create(Request $request): View|RedirectResponse
    {
        // Verificar que existe una sesión de recuperación válida
        if (!$request->session()->has('password_recovery_email')) {
            return redirect()->route('password.request')
                ->withErrors(['email' => 'La sesión de recuperación ha expirado. Por favor, inicia el proceso nuevamente.']);
        }

        return view('auth.reset-password');
    }

    /**
     * Procesa el formulario de nueva contraseña.
     * Actualiza la contraseña del usuario y limpia la sesión de recuperación.
     */
    public function store(Request $request): RedirectResponse
    {
        // Verificar que existe una sesión de recuperación válida
        $email = $request->session()->get('password_recovery_email');

        if (!$email) {
            return redirect()->route('password.request')
                ->withErrors(['email' => 'La sesión de recuperación ha expirado. Por favor, inicia el proceso nuevamente.']);
        }

        $request->validate([
            'password' => ['required', 'confirmed', Rules\Password::defaults()],
        ], [
            'password.required'  => 'La nueva contraseña es obligatoria.',
            'password.confirmed' => 'Las contraseñas no coinciden.',
        ]);

        $user = User::where('email', $email)->first();

        if (!$user) {
            // Limpiar sesión y redirigir al inicio
            $request->session()->forget('password_recovery_email');
            return redirect()->route('password.request')
                ->withErrors(['email' => 'No se encontró el usuario asociado. Por favor, intente nuevamente.']);
        }

        // Actualizar contraseña (el cast 'hashed' del modelo la hashea automáticamente)
        $user->forceFill([
            'password'       => Hash::make($request->password),
            'remember_token' => Str::random(60),
        ])->save();

        event(new PasswordReset($user));

        // Limpiar sesión de recuperación
        $request->session()->forget('password_recovery_email');

        return redirect()->route('login')
            ->with('status', '¡Contraseña actualizada exitosamente! Ya puedes iniciar sesión con tu nueva contraseña.');
    }
}
