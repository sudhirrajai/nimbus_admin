<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Inertia\Inertia;

class AdminUserController extends Controller
{
    public function index()
    {
        $users = User::withCount('licenses')->orderBy('created_at', 'desc')->get();

        return Inertia::render('Admin/Users', [
            'users' => $users
        ]);
    }

    public function toggleAdmin(User $user)
    {
        if ($user->id === Auth::id()) {
            return back()->withErrors(['error' => 'You cannot toggle your own admin status.']);
        }

        $user->update([
            'is_admin' => !$user->is_admin
        ]);

        return back()->with('success', 'User admin status updated successfully.');
    }

    public function destroy(User $user)
    {
        if ($user->id === Auth::id()) {
            return back()->withErrors(['error' => 'You cannot delete yourself.']);
        }

        // Delete user licenses first or let cascade handle it. Let's delete manually to be safe.
        $user->licenses()->delete();
        $user->delete();

        return back()->with('success', 'User and their licenses deleted successfully.');
    }
}
