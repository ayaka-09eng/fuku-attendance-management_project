<?php

namespace App\Actions\Fortify;

use Laravel\Fortify\Contracts\LoginResponse as LoginResponseContract;

class LoginResponse implements LoginResponseContract
{
    public function toResponse($request)
    {
        $user = $request->user();

        if ($user->is_admin === 1) {
            return redirect()->route('admin.attendance.index');
        }

        return redirect()->route('user.attendance.create');
    }
}