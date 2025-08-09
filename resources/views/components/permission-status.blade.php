@props(['user', 'permission'])

@php
    $hasFromRole = $user->role && $user->role->hasPermission($permission);
    $hasGranted = $user->userPermissions->contains('name', $permission);
    $hasDenied = $user->deniedPermissions->contains('name', $permission);
    
    if ($hasDenied) {
        $status = 'denied';
        $class = 'bg-danger';
        $icon = 'fa-times';
        $text = 'Denied';
    } elseif ($hasGranted) {
        $status = 'granted';
        $class = 'bg-success';
        $icon = 'fa-check';
        $text = 'Granted';
    } elseif ($hasFromRole) {
        $status = 'role';
        $class = 'bg-info';
        $icon = 'fa-shield-alt';
        $text = 'From Role';
    } else {
        $status = 'none';
        $class = 'bg-secondary';
        $icon = 'fa-minus';
        $text = 'Not Granted';
    }
@endphp

<span class="badge {{ $class }}" title="{{ $text }}">
    <i class="fas {{ $icon }}"></i> {{ $text }}
</span>
