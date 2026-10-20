<?php

namespace App\Enums;

enum UserRole: string
{
    case Admin = 'admin';
    case Owner = 'owner';
    case Seller = 'seller';

    public function label(): string
    {
        return match ($this) {
            self::Admin => 'Administrator',
            self::Owner => "Do'kon egasi",
            self::Seller => 'Sotuvchi',
        };
    }

    /**
     * Whether a URL belongs to this role's area ("/admin", "/owner", "/seller").
     * Used to drop a remembered "intended" URL that another role left in the session.
     */
    public function canVisit(string $url): bool
    {
        $path = '/'.trim((string) parse_url($url, PHP_URL_PATH), '/');
        $area = '/'.$this->value;

        return $path === $area || str_starts_with($path, $area.'/');
    }

    /**
     * Name of the route the user lands on after signing in.
     */
    public function homeRoute(): string
    {
        return match ($this) {
            self::Admin => 'admin.dashboard',
            self::Owner => 'owner.dashboard',
            self::Seller => 'seller.dashboard',
        };
    }
}
