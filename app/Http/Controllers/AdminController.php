<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Validator;
use App\Models\User;
use App\Models\Card;
use App\Models\Role;
use App\Models\Permission;
use App\Models\ActivityLog;

class AdminController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth');
        $this->middleware(function ($request, $next) {
            $user = User::find(Auth::user()->id);
            if (!$user || !$user->isAdmin()) {
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

        $stats = User::getAdminStats();

        return view('admin.dashboard', compact('stats'));
    }

    /**
     * Show users management
     */
    public function users()
    {
        $this->logActivity('viewed', null, null, null, 'Admin viewed users management');

        $users = User::withRoleAndCards()->paginate(15);
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

        $user = User::createAdminUser([
            'name' => $request->name,
            'email' => $request->email,
            'password' => $request->password,
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

        $user->updateByAdmin([
            'name' => $request->name,
            'email' => $request->email,
            'password' => $request->password,
            'role_id' => $request->role_id,
            'is_active' => $request->has('is_active'),
        ]);

        $this->logActivity('updated', $user, $oldData, $user->fresh()->toArray(), "Admin updated user: {$user->name}");

        return redirect()->route('admin.users')->with('success', 'User updated successfully!');
    }

    /**
     * Delete user
     */
    public function deleteUser(User $user)
    {
        if (!$user->canBeDeletedByAdmin(Auth::id())) {
            return back()->with('error', 'You cannot delete your own account!');
        }

        $userName = $user->name;
        $oldData = $user->toArray();
        
        $success = $user->deleteByAdmin();

        if (!$success) {
            return back()->with('error', 'Failed to delete user!');
        }

        $this->logActivity('deleted', null, $oldData, null, "Admin deleted user: {$userName}");

        return back()->with('success', 'User deleted successfully!');
    }

    /**
     * Toggle user status
     */
    public function toggleUserStatus(User $user)
    {
        $oldData = $user->toArray();
        
        $status = $user->toggleStatus();

        $this->logActivity('updated', $user, $oldData, $user->fresh()->toArray(), "Admin {$status} user: {$user->name}");

        return back()->with('success', "User {$status} successfully!");
    }

    /**
     * Show cards management
     */
    public function cards()
    {
        $this->logActivity('viewed', null, null, null, 'Admin viewed cards management');

        $cards = Card::getAdminCardsList();
        
        return view('admin.cards.index', compact('cards'));
    }

    /**
     * Delete card
     */
    public function deleteCard(Card $card)
    {
        $cardName = $card->name;
        $oldData = $card->toArray();
        
        $success = $card->deleteByAdmin();

        if (!$success) {
            return back()->with('error', 'Failed to delete card!');
        }

        $this->logActivity('deleted', null, $oldData, null, "Admin deleted card: {$cardName}");

        return back()->with('success', 'Card deleted successfully!');
    }

    /**
     * Show permissions management
     */
    public function permissions()
    {
        $this->logActivity('viewed', null, null, null, 'Admin viewed permissions management');

        $roles = Role::getAllWithPermissions();
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

        $oldPermissions = $role->getPermissionIds();
        $oldData = ['permissions' => $oldPermissions];
        
        $success = $role->updatePermissions($request->permissions ?? []);

        if (!$success) {
            return back()->with('error', 'Failed to update permissions!');
        }

        $newPermissions = $role->fresh()->getPermissionIds();
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

        $logs = ActivityLog::getFilteredLogs($request->all());
        $users = User::select('id', 'name')->get();
        $actions = ActivityLog::getDistinctActions();

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
        $logs = ActivityLog::getExportData($request->all());

        $filename = 'activity_logs_' . now()->format('Y-m-d_H-i-s') . '.csv';
        
        $headers = [
            'Content-Type' => 'text/csv',
            'Content-Disposition' => 'attachment; filename="' . $filename . '"',
        ];

        $callback = function() use ($logs) {
            $file = fopen('php://output', 'w');
            
            fputcsv($file, [
                'ID', 'User', 'Action', 'Model Type', 'Model ID', 
                'Description', 'IP Address', 'MAC Address', 'User Agent', 'Created At'
            ]);

            foreach ($logs as $log) {
                fputcsv($file, $log->toCsvArray());
            }

            fclose($file);
        };

        $this->logActivity('exported', null, null, null, 'Admin exported activity logs');

        return response()->stream($callback, 200, $headers);
    }
}
