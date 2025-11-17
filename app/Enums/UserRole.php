<?php

namespace App\Enums;

enum UserRole: string
{
    case Pasien = 'pasien';
    case Kader = 'kader';
    case Puskesmas = 'puskesmas';
    case Kelurahan = 'kelurahan';
    case Pemda = 'pemda';

    public function label(): string
    {
        return match ($this) {
            self::Pasien => 'Pasien',
            self::Kader => 'Kader TBC',
            self::Puskesmas => 'Puskesmas',
            self::Kelurahan => 'Kelurahan',
            self::Pemda => 'Pemda',
        };
    }

    /**
     * Useful when building dropdowns.
     *
     * @param  array<int, self>  $except
     * @return array<int, array{value: string, label: string}>
     */
    public static function options(array $except = []): array
    {
        $cases = array_filter(
            self::cases(),
            fn (self $role) => ! in_array($role, $except, true),
        );

        return array_map(
            fn (self $role) => [
                'value' => $role->value,
                'label' => $role->label(),
            ],
            $cases,
        );
    }
}
