<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class UserController extends Controller
{
    public function revertImpersonate()
    {
        $adminId = session('impersonated_by');

        // Validate admin ID exists and is an admin
        $admin = User::findOrFail($adminId);
        if (!$admin->hasRole('admin')) {
            abort(403, 'Invalid admin user.');
        }

        Auth::logout();
        // Regenerate session after logout
        session()->invalidate();
        session()->regenerateToken();

        Auth::login($admin);

        session()->forget('impersonated_by');

        redirect()->route('welcome');
        return redirect()->route('welcome');
    }
}
