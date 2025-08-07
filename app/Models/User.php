<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Sanctum\HasApiTokens;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Log;
use Carbon\Carbon;
use Laravel\Sanctum\PersonalAccessToken;


// Add these model imports
use App\Models\Role;
use App\Models\Card;
use App\Models\ActivityLog;

class User extends Authenticatable
{
    /** @use HasFactory<\Database\Factories\UserFactory> */
    use HasFactory, Notifiable, HasApiTokens;

    /**
     * The attributes that are mass assignable.
     *
     * @var list<string>
     */
    protected $fillable = [
        'name',
        'email',
        'password',
        'role_id',
        'is_active',
        'last_login_at',
        'last_login_ip',
    ];

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var list<string>
     */
    protected $hidden = [
        'password',
        'remember_token',
    ];

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
            'is_active' => 'boolean',
            'last_login_at' => 'datetime',
        ];
    }

    // Relationships
    /**
     * Get the role that the user belongs to
     */
    public function role(): BelongsTo
    {
        return $this->belongsTo(Role::class);
    }

    /**
     * Get the cards for the user
     */
    public function cards(): HasMany
    {
        return $this->hasMany(Card::class);
    }

    /**
     * Get the activity logs for the user
     */
    public function activityLogs(): HasMany
    {
        return $this->hasMany(ActivityLog::class);
    }

    // Business logic

    /**
     * Check if user has a specific permission
     */
    public function hasPermission(string $permission): bool
    {
        return $this->role && $this->role->hasPermission($permission);
    }

    /**
     * Check if user is admin
     */
    public function isAdmin(): bool
    {
        return $this->role && $this->role->name === 'admin';
    }

    /**
     * Check if user is active
     */
    public function isActive(): bool
    {
        return $this->is_active;
    }

    /**
     * Determine if the user can manage the given card
     */
    public function canManageCard(Card $card): bool
    {
        return $this->id === $card->user_id || $this->isAdmin();
    }

    /**
     * Update the last login information for the user
     */
    public function updateLastLogin(string $ip): void
    {
        $this->update([
            'last_login_at' => now(),
            'last_login_ip' => $ip,
        ]);
    }

    /**
     * Get status attribute
     */
    public function getStatusAttribute(): string
    {
        return $this->is_active ? 'active' : 'inactive';
    }

    /**
     * Get formatted last login
     */
    public function getFormattedLastLoginAttribute(): ?string
    {
        return $this->last_login_at?->diffForHumans();
    }

    /**
     * Validate current password
     */
    public function validatePassword(string $password): bool
    {
        return Hash::check($password, $this->password);
    }

    /**
     * Change user password
     */
    public function changePassword(string $newPassword): void
    {
        $this->update([
            'password' => Hash::make($newPassword),
        ]);

        // Revoke other tokens if using Sanctum
        if (method_exists($this, 'tokens')) {
            $this->tokens()
                ->where('id', '!=', $this->currentAccessToken()?->id)
                ->delete();
        }
    }

    /**
     * Get dashboard statistics
     */
    public function getDashboardStats(): array
    {
        $cards = $this->cards;
        $now = Carbon::now();
        
        return [
            'total_cards' => $cards->count(),
            'active_cards' => $cards->where('is_active', true)->count(),
            'inactive_cards' => $cards->where('is_active', false)->count(),
            'cards_this_month' => $cards->where('created_at', '>=', $now->startOfMonth())->count(),
            'cards_this_week' => $cards->where('created_at', '>=', $now->startOfWeek())->count(),
        ];
    }

    /**
     * Get card creation trend
     */
    public function getCardTrend(int $months = 6): array
    {
        $cards = $this->cards;
        $trend = [];
        
        for ($i = $months - 1; $i >= 0; $i--) {
            $month = Carbon::now()->subMonths($i);
            $count = $cards->whereBetween('created_at', [
                $month->copy()->startOfMonth(),
                $month->copy()->endOfMonth()
            ])->count();
            
            $trend[] = [
                'month' => $month->format('M Y'),
                'count' => $count,
                'short_month' => $month->format('M'),
            ];
        }
        
        return $trend;
    }

    /**
     * Delete user account and related data
     */
    public function deleteAccount(): bool
    {
        try {
            // Delete related data
            $this->cards()->delete();
            $this->activityLogs()->delete();
            
            // Delete tokens if using Sanctum
            if (method_exists($this, 'tokens')) {
                $this->tokens()->delete();
            }
            
            return $this->delete();
        } catch (\Exception $e) {
            Log::error('Error deleting user account: ' . $e->getMessage());
            return false;
        }
    }

    // ADMIN STATISTICS METHODS
    public static function getAdminStats(): array
    {
        return [
            'total_users' => self::count(),
            'total_cards' => Card::count(),
            'total_admins' => self::admins()->count(),
            'recent_activities' => ActivityLog::with('user')->latest()->take(10)->get(),
            'users_this_month' => self::whereMonth('created_at', now()->month)->count(),
            'cards_this_month' => Card::whereMonth('created_at', now()->month)->count(),
        ];
    }

    // USER MANAGEMENT METHODS
    public static function createAdminUser(array $data): self
    {
        return self::create([
            'name' => $data['name'],
            'email' => $data['email'],
            'password' => Hash::make($data['password']),
            'role_id' => $data['role_id'],
            'is_active' => $data['is_active'] ?? true,
        ]);
    }

    public function updateByAdmin(array $data): bool
    {
        $updateData = [
            'name' => $data['name'],
            'email' => $data['email'],
            'role_id' => $data['role_id'],
            'is_active' => $data['is_active'] ?? false,
        ];

        if (!empty($data['password'])) {
            $updateData['password'] = Hash::make($data['password']);
        }

        return $this->update($updateData);
    }

    public function canBeDeletedByAdmin(int $adminId): bool
    {
        return $this->id !== $adminId;
    }

    public function toggleStatus(): string
    {
        $this->is_active = !$this->is_active;
        $this->save();
        
        return $this->is_active ? 'activated' : 'deactivated';
    }

    public function deleteByAdmin(): bool
    {
        try {
            // Delete related data
            $this->cards()->delete();
            $this->activityLogs()->delete();
            
            // Delete tokens if using Sanctum
            if (method_exists($this, 'tokens')) {
                $this->tokens()->delete();
            }
            
            return $this->delete();
        } catch (\Exception $e) {
            Log::error('Error deleting user by admin: ' . $e->getMessage());
            return false;
        }
    }

    // AUTHENTICATION METHODS
    public static function attemptLogin(string $email, string $password, string $ip): array
    {
        $user = self::where('email', $email)->first();

        if (!$user) {
            return [
                'success' => false,
                'message' => 'User not found',
                'code' => 401,
                'user' => null
            ];
        }

        if (!Hash::check($password, $user->password)) {
            return [
                'success' => false,
                'message' => 'Wrong password',
                'code' => 401,
                'user' => $user,
                'reason' => 'wrong_password'
            ];
        }

        if (!$user->is_active) {
            return [
                'success' => false,
                'message' => 'Account is inactive',
                'code' => 403,
                'user' => $user,
                'reason' => 'account_inactive'
            ];
        }

        // Update login details
        $user->update([
            'last_login_at' => now(),
            'last_login_ip' => $ip
        ]);

        return [
            'success' => true,
            'message' => 'Login successful',
            'code' => 200,
            'user' => $user
        ];
    }

    public function createAuthToken(string $tokenName = 'auth_token'): string
    {
        return $this->createToken($tokenName)->plainTextToken;
    }

    public function getAuthData(): array
    {
        return [
            'user' => $this->load(['role.permissions']),
            'token' => $this->createAuthToken(),
            'token_type' => 'Bearer'
        ];
    }

    // REGISTRATION METHODS
    public static function registerUser(array $data, string $ip): array
    {
        try {
            // Get or create user role
            $userRole = Role::getOrCreateUserRole();

            $user = self::create([
                'name' => $data['name'],
                'email' => $data['email'],
                'password' => Hash::make($data['password']),
                'role_id' => $userRole->id,
                'email_verified_at' => now(),
                'is_active' => true,
            ]);

            return [
                'success' => true,
                'message' => 'Registration successful',
                'code' => 201,
                'user' => $user
            ];

        } catch (\Exception $e) {
            Log::error('User registration failed: ' . $e->getMessage());
            
            return [
                'success' => false,
                'message' => 'Registration failed',
                'code' => 500,
                'user' => null
            ];
        }
    }

    public function logoutUser(): bool 
    {
        try {
            if ($this->hasActiveApiToken()) {
                $currentToken = $this->currentAccessToken();

                if ($currentToken instanceof PersonalAccessToken) {
                    $currentToken->delete();
                    Log::info('API token deleted', ['user_id' => $this->id]);
                } else {
                    Log::warning('Current token is not deletable', [
                        'user_id' => $this->id,
                        'type' => $currentToken ? get_class($currentToken) : null,
                    ]);
                }
            } else {
                Log::info('Web session logout - no API token to handle', ['user_id' => $this->id]);
            }

            return true;
        } catch (\Exception $e) {
            Log::error('Logout failed: ' . $e->getMessage(), ['user_id' => $this->id]);
            return false;
        }
    }

    /**
     * Check if user has an active API token context
     */
    private function hasActiveApiToken(): bool
    {
        try {
            return method_exists($this, 'currentAccessToken') && 
                   $this->currentAccessToken() !== null;
        } catch (\Exception $e) {
            return false;
        }
    }

    // Scopes

    /**
     * Scope for active users
     */
    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    /**
     * Scope for admin users
     */
    public function scopeAdmins($query)
    {
        return $query->whereHas('role', function($q) {
            $q->where('name', 'admin');
        });
    }

    /**
     * Scope to include card count
     */
    public function scopeWithCardCount($query)
    {
        return $query->withCount('cards');
    }

    public function scopeThisMonth($query)
    {
        return $query->whereMonth('created_at', now()->month);
    }

    public function scopeWithRoleAndCards($query)
    {
        return $query->with(['role', 'cards']);
    }
}
