<?php

namespace YousefAman\ModalRepeater;

use Filament\Support\Assets\Css;
use Filament\Support\Facades\FilamentAsset;
use Spatie\LaravelPackageTools\Package;
use Spatie\LaravelPackageTools\PackageServiceProvider;

class ModalRepeaterServiceProvider extends PackageServiceProvider
{
    public function configurePackage(Package $package): void
    {
        $package
            ->name('modal-repeater')
            ->hasViews();
    }

    public function packageBooted(): void
    {
        FilamentAsset::register([
            Css::make('modal-repeater', __DIR__ . '/../dist/css/modal-repeater.css'),
        ], 'yousefaman/filament-modal-repeater');
    }
}
