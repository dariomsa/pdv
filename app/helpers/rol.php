<?php

use Illuminate\Support\Facades\Auth;

if (!function_exists('tieneRol')) {
    function tieneRol(string $nombreRol): bool
    {
        $usuario = Auth::user();

        if (!$usuario) {
            return false;
        }

        if (! $usuario->relationLoaded('roles')) {
            $usuario->load('roles');
        }

        return $usuario->roles->contains('title', $nombreRol);
    }
}
