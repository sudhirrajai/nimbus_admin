<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rule;
use Inertia\Inertia;

class AdminUserController extends Controller
{
    public function index()
    {
        $users = User::withCount(['licenses', 'hostingAccounts', 'invoices'])
            ->orderBy('created_at', 'desc')
            ->get();

        return Inertia::render('Admin/Users', [
            'users' => $users
        ]);
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'string', 'lowercase', 'email', 'max:255', 'unique:users,email'],
            'password' => ['required', 'string', 'min:8'],
            'is_admin' => ['nullable', 'boolean'],
            'is_active' => ['nullable', 'boolean'],
            'phone' => ['nullable', 'string', 'max:50'],
            'company_name' => ['nullable', 'string', 'max:150'],
            'address' => ['nullable', 'string', 'max:500'],
            'city' => ['nullable', 'string', 'max:100'],
            'state' => ['nullable', 'string', 'max:100'],
            'postal_code' => ['nullable', 'string', 'max:30'],
            'country' => ['nullable', 'string', 'max:100'],
            'notes' => ['nullable', 'string', 'max:1000'],
        ]);

        User::create([
            'name' => $validated['name'],
            'email' => $validated['email'],
            'password' => Hash::make($validated['password']),
            'is_admin' => $request->boolean('is_admin'),
            'is_active' => $request->boolean('is_active', true),
            'phone' => $validated['phone'] ?? null,
            'company_name' => $validated['company_name'] ?? null,
            'address' => $validated['address'] ?? null,
            'city' => $validated['city'] ?? null,
            'state' => $validated['state'] ?? null,
            'postal_code' => $validated['postal_code'] ?? null,
            'country' => $validated['country'] ?? 'India',
            'notes' => $validated['notes'] ?? null,
            'email_verified_at' => now(),
        ]);

        return back()->with('success', "User '{$validated['name']}' created successfully.");
    }

    public function update(Request $request, User $user)
    {
        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'string', 'lowercase', 'email', 'max:255', Rule::unique('users', 'email')->ignore($user->id)],
            'password' => ['nullable', 'string', 'min:8'],
            'is_admin' => ['nullable', 'boolean'],
            'is_active' => ['nullable', 'boolean'],
            'phone' => ['nullable', 'string', 'max:50'],
            'company_name' => ['nullable', 'string', 'max:150'],
            'address' => ['nullable', 'string', 'max:500'],
            'city' => ['nullable', 'string', 'max:100'],
            'state' => ['nullable', 'string', 'max:100'],
            'postal_code' => ['nullable', 'string', 'max:30'],
            'country' => ['nullable', 'string', 'max:100'],
            'notes' => ['nullable', 'string', 'max:1000'],
        ]);

        // Guard against removing own admin or active status
        if ($user->id === Auth::id()) {
            $validated['is_admin'] = true;
            $validated['is_active'] = true;
        }

        $data = [
            'name' => $validated['name'],
            'email' => $validated['email'],
            'is_admin' => $request->boolean('is_admin'),
            'is_active' => $request->boolean('is_active'),
            'phone' => $validated['phone'] ?? null,
            'company_name' => $validated['company_name'] ?? null,
            'address' => $validated['address'] ?? null,
            'city' => $validated['city'] ?? null,
            'state' => $validated['state'] ?? null,
            'postal_code' => $validated['postal_code'] ?? null,
            'country' => $validated['country'] ?? 'India',
            'notes' => $validated['notes'] ?? null,
        ];

        if (!empty($validated['password'])) {
            $data['password'] = Hash::make($validated['password']);
        }

        $user->update($data);

        return back()->with('success', "User '{$user->name}' updated successfully.");
    }

    public function toggleAdmin(User $user)
    {
        if ($user->id === Auth::id()) {
            return back()->withErrors(['error' => 'You cannot toggle your own admin status.']);
        }

        $user->update([
            'is_admin' => !$user->is_admin
        ]);

        return back()->with('success', "Admin status for {$user->name} toggled successfully.");
    }

    public function toggleActive(User $user)
    {
        if ($user->id === Auth::id()) {
            return back()->withErrors(['error' => 'You cannot deactivate your own administrative account.']);
        }

        $user->update([
            'is_active' => !$user->is_active
        ]);

        $statusText = $user->is_active ? 'activated' : 'deactivated';
        return back()->with('success', "User {$user->name} has been {$statusText}.");
    }

    public function destroy(User $user)
    {
        if ($user->id === Auth::id()) {
            return back()->withErrors(['error' => 'You cannot delete yourself.']);
        }

        // Delete user licenses first to prevent orphaned records
        $user->licenses()->delete();
        $user->delete();

        return back()->with('success', 'User and associated data deleted successfully.');
    }
}
