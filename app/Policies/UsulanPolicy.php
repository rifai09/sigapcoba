<?php

namespace App\Policies;

use App\Models\User;
use App\Models\Usulan;

class UsulanPolicy
{
    /**
     * Kepala Unit boleh approve jika status sedang menunggu_kepala_unit.
     */
    public function approveAsKepalaUnit(User $user, Usulan $usulan): bool
    {
        return in_array($user->role, ['kepala_unit','admin'], true)
            && $usulan->status === 'menunggu_kepala_unit';
    }

    /**
     * Kepala Unit boleh reject jika masih di antrian awal (atau sebelum final).
     */
    public function rejectAsKepalaUnit(User $user, Usulan $usulan): bool
    {
        return in_array($user->role, ['kepala_unit','admin'], true)
            && in_array($usulan->status, ['menunggu_kepala_unit','menunggu_katimker','menunggu_kabid'], true);
    }

    /**
     * Katimker boleh approve jika status menunggu_katimker.
     */
    public function approveAsKatimker(User $user, Usulan $usulan): bool
    {
        return in_array($user->role, ['katimker','admin'], true)
            && $usulan->status === 'menunggu_katimker';
    }

    /**
     * Katimker boleh reject jika masih di katimker atau (edge) sebelum kabid proses.
     */
    public function rejectAsKatimker(User $user, Usulan $usulan): bool
    {
        return in_array($user->role, ['katimker','admin'], true)
            && in_array($usulan->status, ['menunggu_katimker','menunggu_kabid'], true);
    }

    /**
     * Kabid boleh approve final jika status menunggu_kabid.
     */
    public function approveAsKabid(User $user, Usulan $usulan): bool
    {
        return in_array($user->role, ['kabid','admin'], true)
            && $usulan->status === 'menunggu_kabid';
    }

    /**
     * Kabid boleh reject final jika status menunggu_kabid.
     */
    public function rejectAsKabid(User $user, Usulan $usulan): bool
    {
        return in_array($user->role, ['kabid','admin'], true)
            && $usulan->status === 'menunggu_kabid';
    }
}
