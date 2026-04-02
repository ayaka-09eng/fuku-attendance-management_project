<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\User;

class UserController extends Controller
{
    public function index() {
        $staffs = User::where('is_admin', 0)
            ->orderBy('id', 'asc')
            ->get();

        return view('admin.staff.index', compact('staffs'));
    }
}
