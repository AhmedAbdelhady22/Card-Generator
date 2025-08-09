<?php

namespace App\Providers;

use App\Models\User;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Blade;
use Illuminate\Support\ServiceProvider;

class BladeServiceProvider extends ServiceProvider
{
    /**
     * Register services.
     */
    public function register(): void
    {
        //
    }

    /**
     * Bootstrap services.
     */
    public function boot(): void
    {
        // Custom Blade directive for checking permissions
        Blade::directive('permission', function ($permission) {
            return "<?php if(auth()->check() && auth()->user()->hasPermissionCached({$permission})): ?>";
        });

        Blade::directive('endpermission', function () {
            return '<?php endif; ?>';
        });

        // Custom Blade directive for checking any of multiple permissions
        Blade::directive('anypermission', function ($permissions) {
            return "<?php if(auth()->check() && auth()->user()->hasAnyPermissionCached({$permissions})): ?>";
        });

        Blade::directive('endanypermission', function () {
            return '<?php endif; ?>';
        });
    }
}
