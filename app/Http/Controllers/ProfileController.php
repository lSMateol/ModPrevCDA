<?php

namespace App\Http\Controllers;

use App\Http\Requests\ProfileUpdateRequest;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Redirect;
use Illuminate\View\View;
use App\Models\Diag;
use App\Models\Vehiculo;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;

class ProfileController extends Controller
{
    /**
     * Display the user's profile form.
     */
    public function edit(Request $request): View
    {
        return view('profile.edit', [
            'user' => $request->user(),
        ]);
    }

    /**
     * Update the user's profile information.
     */
    public function update(ProfileUpdateRequest $request): RedirectResponse
    {
        $request->user()->fill($request->validated());

        if ($request->user()->isDirty('email')) {
            $request->user()->email_verified_at = null;
        }

        $request->user()->save();

        return Redirect::route('profile.edit')->with('status', 'profile-updated');
    }

    /**
     * Delete the user's account.
     */
    public function destroy(Request $request): RedirectResponse
    {
        $request->validateWithBag('userDeletion', [
            'password' => ['required', 'current_password'],
        ]);

        $user = $request->user();

        Auth::logout();

        $user->delete();

        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return Redirect::to('/');
    }
    /**
     * Display the personal dashboard (Mi Perfil) for Admin/Digitador.
     */
    public function dashboard(Request $request): View
    {
        $hoy = Carbon::today();
        $user = $request->user();

        // 1. Rendimiento Diario
        $rendimiento = [
            'pendientes' => Diag::whereDate('fecdia', $hoy)->whereNull('aprobado')->count(),
            'aprobados'  => Diag::whereDate('fecdia', $hoy)->where('aprobado', 1)->count(),
            'rechazados' => Diag::whereDate('fecdia', $hoy)->where('aprobado', 0)->count(),
        ];

        // 2. Tendencia de Diagnóstico (Últimos 7 días por defecto)
        $tendencia = Diag::select(DB::raw('DATE(fecdia) as fecha'), DB::raw('count(*) as total'))
            ->where('fecdia', '>=', Carbon::now()->subDays(30))
            ->groupBy('fecha')
            ->orderBy('fecha', 'ASC')
            ->get();

        // 3. Alertas Críticas (SOAT y Tecno)
        // Optimizamos: Solo traer vehículos con vencimientos próximos (< 15 días) o ya vencidos
        $limiteAlertas = Carbon::now()->addDays(15);
        $alertasVehiculos = Vehiculo::with(['empresa', 'marca'])
            ->where(function($q) use ($hoy, $limiteAlertas) {
                $q->whereBetween('fecvens', [$hoy->copy()->subYears(5), $limiteAlertas])
                  ->orWhereBetween('fecvent', [$hoy->copy()->subYears(5), $limiteAlertas]);
            })
            ->get();

        $alertas = $alertasVehiculos->map(function($v) use ($hoy) {
            $docs = [];
            if ($v->fecvens) {
                $fec = Carbon::parse($v->fecvens);
                if ($fec->lte($hoy->copy()->addDays(15))) {
                    $docs[] = [
                        'tipo' => 'SOAT',
                        'fecha' => $fec,
                        'dias' => $hoy->diffInDays($fec, false),
                        'estado' => $fec->lt($hoy) ? 'vencido' : 'por_vencer'
                    ];
                }
            }
            if ($v->fecvent) {
                $fec = Carbon::parse($v->fecvent);
                if ($fec->lte($hoy->copy()->addDays(15))) {
                    $docs[] = [
                        'tipo' => 'Tecnomecánica',
                        'fecha' => $fec,
                        'dias' => $hoy->diffInDays($fec, false),
                        'estado' => $fec->lt($hoy) ? 'vencido' : 'por_vencer'
                    ];
                }
            }
            return empty($docs) ? null : ['vehiculo' => $v, 'docs' => $docs];
        })->filter()->values();

        // 4. Actividad Reciente
        $actividad = Diag::with(['vehiculo.empresa', 'persona'])
            ->latest('iddia')
            ->take(10)
            ->get();

        return view('profile.dashboard', compact('rendimiento', 'tendencia', 'alertas', 'actividad'));
    }
}
