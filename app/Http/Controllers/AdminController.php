<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;
use App\Models\User;
use App\Models\Card;
use App\Models\Role;
use App\Models\Permission;
use App\Models\ActivityLog;
use Carbon\Carbon;

class AdminController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth');
        $this->middleware(function ($request, $next) {
            $user = Auth::user();
            if (!$user->role || $user->role->name !== 'admin') {
                abort(403, 'Unauthorized access');
            }
            return $next($request);
        });
    }

    /**
     * Show admin dashboard
     */
    public function dashboard()
    {
        $this->logActivity('viewed', null, null, null, 'Admin viewed dashboard');

        $stats = [
            'total_users' => User::count(),
            'total_cards' => Card::count(),
            'total_admins' => User::whereHas('role', function($q) {
                $q->where('name', 'admin');
            })->count(),
            'recent_activities' => ActivityLog::with('user')->latest()->take(10)->get(),
            'users_this_month' => User::whereMonth('created_at', now()->month)->count(),
            'cards_this_month' => Card::whereMonth('created_at', now()->month)->count(),
        ];

        return view('admin.dashboard', compact('stats'));
    }

    /**
     * Show users management
     */
    public function users()
    {
        $this->logActivity('viewed', null, null, null, 'Admin viewed users management');

        $users = User::with('role')->paginate(15);
        $roles = Role::all();
        
        return view('admin.users.index', compact('users', 'roles'));
    }

    /**
     * Show create user form
     */
    public function createUser()
    {
        $roles = Role::all();
        return view('admin.users.create', compact('roles'));
    }

    /**
     * Store new user
     */
    public function storeUser(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'name' => 'required|string|max:255',
            'email' => 'required|string|email|max:255|unique:users',
            'password' => 'required|string|min:8|confirmed',
            'role_id' => 'required|exists:roles,id',
            'is_active' => 'boolean',
        ]);

        if ($validator->fails()) {
            return back()->withErrors($validator)->withInput();
        }

        $user = User::create([
            'name' => $request->name,
            'email' => $request->email,
            'password' => Hash::make($request->password),
            'role_id' => $request->role_id,
            'is_active' => $request->has('is_active'),
        ]);

        $this->logActivity('created', $user, null, $user->toArray(), "Admin created user: {$user->name}");

        return redirect()->route('admin.users')->with('success', 'User created successfully!');
    }

    /**
     * Show edit user form
     */
    public function editUser(User $user)
    {
        $roles = Role::all();
        return view('admin.users.edit', compact('user', 'roles'));
    }

    /**
     * Update user
     */
    public function updateUser(Request $request, User $user)
    {
        $validator = Validator::make($request->all(), [
            'name' => 'required|string|max:255',
            'email' => 'required|string|email|max:255|unique:users,email,' . $user->id,
            'password' => 'nullable|string|min:8|confirmed',
            'role_id' => 'required|exists:roles,id',
            'is_active' => 'boolean',
        ]);

        if ($validator->fails()) {
            return back()->withErrors($validator)->withInput();
        }

        $oldData = $user->toArray();

        $updateData = [
            'name' => $request->name,
            'email' => $request->email,
            'role_id' => $request->role_id,
            'is_active' => $request->has('is_active'),
        ];

        if ($request->password) {
            $updateData['password'] = Hash::make($request->password);
        }

        $user->update($updateData);

        $this->logActivity('updated', $user, $oldData, $user->fresh()->toArray(), "Admin updated user: {$user->name}");

        return redirect()->route('admin.users')->with('success', 'User updated successfully!');
    }

    /**
     * Delete user
     */
    public function deleteUser(User $user)
    {
        if ($user->id === Auth::id()) {
            return back()->with('error', 'You cannot delete your own account!');
        }

        $userName = $user->name;
        $oldData = $user->toArray();
        $user->delete();

        $this->logActivity('deleted', null, $oldData, null, "Admin deleted user: {$userName}");

        return back()->with('success', 'User deleted successfully!');
    }

    /**
     * Toggle user status
     */
    public function toggleUserStatus(User $user)
    {
        $oldStatus = $user->is_active;
        $oldData = $user->toArray();
        
        $user->is_active = !$user->is_active;
        $user->save();

        $status = $user->is_active ? 'activated' : 'deactivated';
        $this->logActivity('updated', $user, $oldData, $user->fresh()->toArray(), "Admin {$status} user: {$user->name}");

        return back()->with('success', "User {$status} successfully!");
    }

    /**
     * Show cards management
     */
    public function cards()
    {
        $this->logActivity('viewed', null, null, null, 'Admin viewed cards management');

        $cards = Card::with('user')->paginate(15);
        return view('admin.cards.index', compact('cards'));
    }

    /**
     * Delete card
     */
    public function deleteCard(Card $card)
    {
        $cardName = $card->name;
        $oldData = $card->toArray();
        $card->delete();

        $this->logActivity('deleted', null, $oldData, null, "Admin deleted card: {$cardName}");

        return back()->with('success', 'Card deleted successfully!');
    }

    /**
     * Show permissions management
     */
    public function permissions()
    {
        $this->logActivity('viewed', null, null, null, 'Admin viewed permissions management');

        $roles = Role::with('permissions')->get();
        $permissions = Permission::all();
        
        return view('admin.permissions.index', compact('roles', 'permissions'));
    }

    /**
     * Update role permissions
     */
    public function updateRolePermissions(Request $request, Role $role)
    {
        $request->validate([
            'permissions' => 'array',
            'permissions.*' => 'exists:permissions,id',
        ]);

        $oldPermissions = $role->permissions->pluck('id')->toArray();
        $oldData = ['permissions' => $oldPermissions];
        
        $role->permissions()->sync($request->permissions ?? []);

        $newPermissions = $role->fresh()->permissions->pluck('id')->toArray();
        $newData = ['permissions' => $newPermissions];

        $this->logActivity('updated', $role, $oldData, $newData, "Admin updated permissions for role: {$role->name}");

        return back()->with('success', 'Role permissions updated successfully!');
    }

    /**
     * Show activity logs
     */
    public function logs(Request $request)
    {
        $this->logActivity('viewed', null, null, null, 'Admin viewed activity logs');

        $query = ActivityLog::with('user')->latest();

        // Filter by date range
        if ($request->from_date) {
            $query->whereDate('created_at', '>=', $request->from_date);
        }
        if ($request->to_date) {
            $query->whereDate('created_at', '<=', $request->to_date);
        }

        // Filter by action
        if ($request->action) {
            $query->where('action', $request->action);
        }

        // Filter by user
        if ($request->user_id) {
            $query->where('user_id', $request->user_id);
        }

        // Filter by IP address
        if ($request->ip_address) {
            $query->where('ip_address', 'like', '%' . $request->ip_address . '%');
        }

        $logs = $query->paginate(20);
        $users = User::select('id', 'name')->get();
        $actions = ActivityLog::distinct()->pluck('action');

        return view('admin.logs.index', compact('logs', 'users', 'actions'));
    }

    /**
     * Show detailed log
     */
    public function showLog(ActivityLog $log)
    {
        return view('admin.logs.show', compact('log'));
    }

    /**
     * Export logs to CSV
     */
    public function exportLogs(Request $request)
    {
        $query = ActivityLog::with('user')->latest();

        // Apply same filters as logs method
        if ($request->from_date) {
            $query->whereDate('created_at', '>=', $request->from_date);
        }
        if ($request->to_date) {
            $query->whereDate('created_at', '<=', $request->to_date);
        }
        if ($request->action) {
            $query->where('action', $request->action);
        }
        if ($request->user_id) {
            $query->where('user_id', $request->user_id);
        }
        if ($request->ip_address) {
            $query->where('ip_address', 'like', '%' . $request->ip_address . '%');
        }

        $logs = $query->get();

        $filename = 'activity_logs_' . now()->format('Y-m-d_H-i-s') . '.csv';
        
        $headers = [
            'Content-Type' => 'text/csv',
            'Content-Disposition' => 'attachment; filename="' . $filename . '"',
        ];

        $callback = function() use ($logs) {
            $file = fopen('php://output', 'w');
            
            // CSV headers
            fputcsv($file, [
                'ID', 'User', 'Action', 'Model Type', 'Model ID', 
                'Description', 'IP Address', 'MAC Address', 'User Agent', 'Created At'
            ]);

            // CSV data
            foreach ($logs as $log) {
                fputcsv($file, [
                    $log->id,
                    $log->user ? $log->user->name : 'N/A',
                    $log->action,
                    $log->model_type,
                    $log->model_id,
                    $log->description,
                    $log->ip_address,
                    $log->mac_address,
                    $log->user_agent,
                    $log->created_at->format('Y-m-d H:i:s'),
                ]);
            }

            fclose($file);
        };

        $this->logActivity('exported', null, null, null, 'Admin exported activity logs');

        return response()->stream($callback, 200, $headers);
    }
}
