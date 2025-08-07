<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Facades\Log;

class Role extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'description',
    ];

    /**
     * Get the users that belong to this role
     */
    public function users(): HasMany
    {
        return $this->hasMany(User::class);
    }

    /**
     * Get the permissions for this role
     */
    public function permissions(): BelongsToMany
    {
        return $this->belongsToMany(Permission::class, 'role_permissions');
    }

    /**
     * Check if role has a specific permission
     */
    public function hasPermission(string $permission): bool
    {
        return $this->permissions()->where('name', $permission)->exists();
    }

    /**
     * Assign a permission to this role
     */
    public function assignPermission(Permission $permission): void
    {
        $this->permissions()->syncWithoutDetaching([$permission->id]);
    }

    /**
     * Remove a permission from this role
     */
    public function removePermission(Permission $permission): void
    {
        $this->permissions()->detach($permission->id);
    }

    // PERMISSION MANAGEMENT METHODS
    public function updatePermissions(array $permissionIds): bool
    {
        try {
            $this->permissions()->sync($permissionIds);
            return true;
        } catch (\Exception $e) {
            Log::error('Error updating role permissions: ' . $e->getMessage());
            return false;
        }
    }

    public function getPermissionIds(): array
    {
        return $this->permissions->pluck('id')->toArray();
    }

    public static function getAllWithPermissions()
    {
        return self::with('permissions')->get();
    }

    // ROLE MANAGEMENT METHODS
    public static function getOrCreateUserRole(): self
    {
        return self::firstOrCreate(
            ['name' => 'user'],
            [
                'name' => 'user',
                'description' => 'Regular user role'
            ]
        );
    }

    public static function getAdminRole(): ?self
    {
        return self::where('name', 'admin')->first();
    }

    public static function getAllRoles()
    {
        return self::all();
    }
}
