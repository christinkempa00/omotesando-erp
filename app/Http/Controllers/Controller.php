<?php

namespace App\Http\Controllers;

use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

abstract class Controller
{
    /**
     * Beberapa controller GA dipakai bersama oleh 2 route group (ga.* dan
     * outlet.*, lihat routes/web.php) — data yang dikirim sama persis,
     * cuma template Blade yang beda (Outlet dapat versi lebih sederhana,
     * tier-aware). Nama route saat ini yang menentukan prefix view mana
     * yang dirender, bukan role — supaya tidak perlu query tambahan.
     */
    protected function viewFor(string $name, array $data = []): View
    {
        $prefix = str_starts_with((string) request()->route()?->getName(), 'outlet.') ? 'outlet' : 'ga';

        return view("{$prefix}.{$name}", $data);
    }

    /**
     * Sama seperti viewFor() tapi utk nama route (redirect ke halaman lain
     * dalam portal yang sama, mis. ga.requests.index vs outlet.requests.index).
     */
    protected function routeFor(string $name): string
    {
        $prefix = str_starts_with((string) request()->route()?->getName(), 'outlet.') ? 'outlet' : 'ga';

        return "{$prefix}.{$name}";
    }

    /**
     * Ingat filter halaman daftar di session (per nama route), supaya saat
     * user buka detail lalu kembali ke daftar lewat URL tanpa query (link
     * "Kembali", menu sidebar, redirect setelah simpan/hapus) daftar tetap
     * dalam kondisi filter terakhir. ?reset=1 menghapus filter tersimpan.
     *
     * Return RedirectResponse kalau perlu redirect (reset / pulihkan
     * filter), null kalau halaman boleh lanjut dirender.
     */
    protected function rememberFilters(Request $request, string $routeName, array $keys): ?RedirectResponse
    {
        $sessionKey = 'filters.'.$routeName;

        if ($request->boolean('reset')) {
            $request->session()->forget($sessionKey);

            return redirect()->route($routeName);
        }

        if ($request->hasAny($keys)) {
            $request->session()->put($sessionKey, array_filter(
                $request->only($keys),
                fn ($v) => $v !== null && $v !== ''
            ));

            return null;
        }

        if ($saved = $request->session()->get($sessionKey)) {
            // Pertahankan flash message (mis. "Data dihapus") melewati redirect ini.
            $request->session()->reflash();

            return redirect()->route($routeName, $saved);
        }

        return null;
    }
}
