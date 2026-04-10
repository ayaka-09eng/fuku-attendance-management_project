<?php

namespace App\Providers;

use App\Actions\Fortify\CreateNewUser;
use Illuminate\Support\ServiceProvider;
use Laravel\Fortify\Fortify;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\ValidationException;
use Laravel\Fortify\Http\Requests\LoginRequest as FortifyLoginRequest;
use App\Actions\Fortify\RegisterResponse;
use Laravel\Fortify\Contracts\RegisterResponse as RegisterResponseContract;
use App\Actions\Fortify\LoginResponse;
use App\Actions\Fortify\LogoutResponse;
use Laravel\Fortify\Contracts\LoginResponse as LoginResponseContract;
use Laravel\Fortify\Contracts\LogoutResponse as LogoutResponseContract;
use App\Models\User;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Cache\RateLimiting\Limit;
use Illuminate\Http\Request;

class FortifyServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        $this->app->bind(
            \Laravel\Fortify\Http\Requests\LoginRequest::class,
            function () {
                if (request()->input('login_type') === 'admin') {
                    return new \App\Http\Requests\Admin\Auth\AdminLoginRequest;
                }
                return new \App\Http\Requests\Auth\UserLoginRequest;
            }
        );

        $this->app->singleton(LoginResponseContract::class, LoginResponse::class);

        $this->app->singleton(RegisterResponseContract::class, RegisterResponse::class);

        $this->app->singleton(LogoutResponseContract::class, LogoutResponse::class);
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        Fortify::createUsersUsing(CreateNewUser::class);

        Fortify::registerView(function () {
            return view('user.auth.register');
        });

        Fortify::authenticateUsing(function (FortifyLoginRequest $request) {
            $user = User::where('email', $request->email)->first();

            $isAdminLogin = $request->login_type === 'admin';

            if ($user && Hash::check($request->password, $user->password)) {

                if ($isAdminLogin && !$user->is_admin) {
                    throw ValidationException::withMessages([
                        'email' => '管理者としてログインできません',
                    ]);
                }

                if (!$isAdminLogin && $user->is_admin) {
                    throw ValidationException::withMessages([
                        'email' => '一般ユーザーとしてログインできません',
                    ]);
                }

                return $user;
            }

            throw ValidationException::withMessages([
                'email' => 'ログイン情報が登録されていません',
            ]);
        });

        Fortify::loginView(function () {
            if (request()->is('admin/login')) {
                return view('admin.auth.login');
            }

            return view('user.auth.login');
        });

        RateLimiter::for('login', function (Request $request) {
            $email = (string) $request->email;

            return Limit::perMinute(10)->by($email . $request->ip());
        });
    }
}
