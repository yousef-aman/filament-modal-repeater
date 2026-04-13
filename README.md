# Filament Modal Repeater

[![Latest Version on Packagist](https://img.shields.io/packagist/v/yousefaman/filament-modal-repeater.svg?style=flat-square)](https://packagist.org/packages/yousefaman/filament-modal-repeater)
[![Total Downloads](https://img.shields.io/packagist/dt/yousefaman/filament-modal-repeater.svg?style=flat-square)](https://packagist.org/packages/yousefaman/filament-modal-repeater)

A Filament v5 form component that displays repeater items in a compact table with modal-based editing. Perfect for forms with many fields where inline editing becomes cluttered.

## Demo

![Demo](art/demo.jpeg)

## Requirements

- PHP 8.2+
- Filament v5

## Installation

You can install the package via composer:

```bash
composer require yousefaman/filament-modal-repeater
```

## Setup

Register the plugin in your panel provider (optional but recommended):

```php
use YousefAman\ModalRepeater\ModalRepeaterPlugin;

public function panel(Panel $panel): Panel
{
    return $panel
        ->plugin(ModalRepeaterPlugin::make());
}
```

## Usage

```php
use YousefAman\ModalRepeater\ModalRepeater;
use YousefAman\ModalRepeater\Column;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Toggle;

ModalRepeater::make('items')
    ->tableColumns([
        Column::make('name')->label('Name'),
        Column::make('price')->money('USD'),
        Column::make('active')->boolean(),
    ])
    ->schema([
        TextInput::make('name')->required(),
        TextInput::make('price')->numeric()->required(),
        Toggle::make('active'),
    ])
```

Items are listed in a table. Clicking a row or the edit button opens a modal with the full form schema. Adding a new item also opens the same modal.

## Column Types

Columns define how each field is displayed in the summary table.

```php
// Custom label
Column::make('name')->label('Product Name'),

// Boolean — renders a checkmark/cross icon
Column::make('active')->boolean(),

// Badge — renders a colored badge using the value
Column::make('status')->badge('success'),

// Money — formats the value as a currency amount
Column::make('price')->money('USD'),

// Custom formatter
Column::make('created_at')->formatUsing(fn ($value) => $value->diffForHumans()),

// Fixed column width
Column::make('name')->width('200px'),
```

## Modal Configuration

Control the modal layout independently from the table:

```php
ModalRepeater::make('items')
    ->modalColumns(3)     // Number of columns in the modal form grid
    ->modalWidth('4xl')   // Tailwind modal max-width (e.g. 'sm', 'lg', '4xl', '7xl')
```

## Relationships

Use `relationship()` to bind the repeater to an Eloquent relationship. Filament handles loading, saving, and deleting related records automatically.

```php
ModalRepeater::make('addresses')
    ->relationship('addresses')
    ->tableColumns([
        Column::make('street')->label('Street'),
        Column::make('city')->label('City'),
        Column::make('postcode')->label('Postcode'),
    ])
    ->schema([
        TextInput::make('street')->required(),
        TextInput::make('city')->required(),
        TextInput::make('postcode'),
    ])
```

## Customizing Actions

The component inherits Filament's standard repeater action API.

```php
ModalRepeater::make('items')
    // Change the label on the "Add" button
    ->addActionLabel('Add Item')

    // Customize the edit action
    ->editAction(
        fn (Action $action) => $action->label('Edit')->icon('heroicon-o-pencil')
    )

    // Append additional per-row actions
    ->extraItemActions([
        Action::make('duplicate')->action(fn ($arguments) => ...),
    ])

    // Text shown when the list is empty
    ->emptyLabel('No items added yet.')

    // Allow rows to be reordered via drag-and-drop
    ->reorderable()

    // Allow rows to be cloned
    ->cloneable()

    // Prevent deletion
    ->deletable(false)
```

## Changelog

Please see [CHANGELOG](CHANGELOG.md) for more information on what has changed recently.

## Contributing

Please see [CONTRIBUTING](CONTRIBUTING.md) for details.

## Security Vulnerabilities

If you discover a security vulnerability, please send an e-mail to Yousef Aman. All security vulnerabilities will be promptly addressed.

## Credits

- [Yousef Aman](https://github.com/yousef-aman)
- [All Contributors](../../contributors)

## License

The MIT License (MIT). Please see [License File](LICENSE.md) for more information.
