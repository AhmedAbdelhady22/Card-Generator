<!-- Navigation Bar Component -->
<nav class= "navbar navbar-expand-lg bg-body-tertiary">
    <div class="container-fluid">
        <a class="navbar-brand" href="{{ route('home') }}">Card Generator</a>
        <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarSupportedContent" aria-controls="navbarSupportedContent" aria-expanded="false" aria-label="Toggle navigation">
            <span class="navbar-toggler-icon"></span>
        </button>
        <div class="collapse navbar-collapse" id="navbarSupportedContent">
            <ul class="navbar-nav me-auto mb-2 mb-lg-0">
                <li class="nav-item">
                    <a class="nav-link" href="{{ route('home') }}">Home</a>
                </li>
                @auth
                    <li class="nav-item">
                        <a class="nav-link" href="{{ route('dashboard') }}">
                            <i class="fas fa-tachometer-alt me-1"></i>Dashboard
                        </a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="{{ route('cards.index') }}">
                            <i class="fas fa-id-card me-1"></i>Cards
                        </a>
                    </li>
                    @if(Auth::user()->role && Auth::user()->role->name === 'admin')
                        <li class="nav-item">
                            <a class="nav-link text-danger" href="{{ route('admin.dashboard') }}">
                                <i class="fas fa-user-shield me-1"></i>Admin Panel
                            </a>
                        </li>
                    @endif
                @else
                    <li class="nav-item">
                        <a class="nav-link" href="{{ route('cards.index') }}">Cards</a>
                    </li>
                @endif
            </ul>
            <ul class="navbar-nav ms-auto mb-2 mb-lg-0">
                @auth
                    <li class="nav-item dropdown">
                        <a class="nav-link dropdown-toggle" href="#" id="navbarDropdown" role="button" data-bs-toggle="dropdown" aria-expanded="false">
                            {{ Auth::user()->name }}
                        </a>
                        <ul class="dropdown-menu" aria-labelledby="navbarDropdown">
                            <li><a class="dropdown-item" href="{{ route('dashboard') }}">
                                <i class="fas fa-tachometer-alt me-1"></i>Dashboard
                            </a></li>
                            @if(Auth::user()->role && Auth::user()->role->name === 'admin')
                                <li><hr class="dropdown-divider"></li>
                                <li class="dropdown-header">Admin Functions</li>
                                <li><a class="dropdown-item" href="{{ route('admin.dashboard') }}">
                                    <i class="fas fa-user-shield me-1"></i>Admin Dashboard
                                </a></li>
                                <li><a class="dropdown-item" href="{{ route('admin.users') }}">
                                    <i class="fas fa-users me-1"></i>Manage Users
                                </a></li>
                                <li><a class="dropdown-item" href="{{ route('admin.logs') }}">
                                    <i class="fas fa-clipboard-list me-1"></i>Activity Logs
                                </a></li>
                                <li><hr class="dropdown-divider"></li>
                            @endif
                            <li>
                                <a class="dropdown-item" href="{{ route('logout') }}" 
                                   onclick="event.preventDefault(); document.getElementById('logout-form').submit();">
                                   <i class="fas fa-sign-out-alt me-1"></i>Logout
                                </a>
                            </li>
                        </ul>
                    </li>
                @else
                    <li class="nav-item">
                        <a class="nav-link" href="{{ route('login') }}">Login</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="{{ route('register') }}">Register</a>
                    </li>
                @endauth
            </ul>
        </div>
    </div>
</nav>

<!-- Hidden Logout Form -->
@auth
<form id="logout-form" action="{{ route('logout') }}" method="POST" style="display: none;">
    @csrf
</form>
@endauth
