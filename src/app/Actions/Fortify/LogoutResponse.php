<?php

namespace App\Actions\Fortify;

use Laravel\Fortify\Contracts\LogoutResponse as LogoutResponseContract;

class LogoutResponse implements LogoutResponseContract
{
    public function toResponse($request)
    {
        if ($request->query('role') === 'admin') {
            return redirect()->route('admin.login');
        }
        return redirect()->route('login');
    }
}