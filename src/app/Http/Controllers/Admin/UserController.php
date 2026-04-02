<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\Request;

class UserController extends Controller
{
    public function index() {
        $staffs = User::where('is_admin', 0)
            ->orderBy('id', 'asc')
            ->get();

        return view('admin.staff.index', compact('staffs'));
    }
}
