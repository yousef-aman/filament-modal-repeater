<?php

namespace YousefAman\ModalRepeater;

use Filament\Contracts\Plugin;
use Filament\Panel;

class ModalRepeaterPlugin implements Plugin
{
    public static function make(): static
    {
        return app(static::class);
    }

    public function getId(): string
    {
        return 'modal-repeater';
    }

    public function register(Panel $panel): void
    {
        //
    }

    public function boot(Panel $panel): void
    {
        //
    }
}
