<?php

use Filament\Actions\Concerns\InteractsWithActions;
use Filament\Actions\Contracts\HasActions;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Concerns\InteractsWithForms;
use Filament\Forms\Contracts\HasForms;
use Filament\Schemas\Schema;
use Illuminate\Support\MessageBag;
use Livewire\Component as LivewireComponent;
use Livewire\Livewire;
use YousefAman\ModalRepeater\Column;
use YousefAman\ModalRepeater\ModalRepeater;

/*
 * Ensures the relationship-aware changes do not regress plain (array-state)
 * repeaters that are not bound to an Eloquent relationship.
 */

class PlainItemsForm extends LivewireComponent implements HasActions, HasForms
{
    use InteractsWithActions;
    use InteractsWithForms;

    public ?array $data = [];

    public function mount(): void
    {
        $this->form->fill([
            'items' => [
                ['name' => 'Widget', 'price' => 10],
            ],
        ]);
    }

    public function getErrorBag()
    {
        return new MessageBag;
    }

    public function form(Schema $schema): Schema
    {
        return $schema
            ->statePath('data')
            ->components([
                ModalRepeater::make('items')
                    ->tableColumns([
                        Column::make('name')->label('Name'),
                        Column::make('price')->money('USD'),
                    ])
                    ->schema([
                        TextInput::make('name')->required(),
                        TextInput::make('price')->numeric()->required(),
                    ]),
            ]);
    }

    public function render(): string
    {
        return <<<'BLADE'
            <div>{{ $this->form }}</div>
        BLADE;
    }
}

function bootPlainRepeater(): ModalRepeater
{
    $form = Livewire::test(PlainItemsForm::class)
        ->instance()
        ->getSchema('form');

    return collect($form->getFlatComponents())
        ->first(fn ($component) => $component instanceof ModalRepeater);
}

it('still resolves plain array column values for display', function () {
    $repeater = bootPlainRepeater();

    $itemKey = array_key_first($repeater->getRawState());

    $values = $repeater->getItemDisplayValues($itemKey);

    expect($values['name'])->toBe('Widget')
        ->and($values['price'])->toBe('10.00 USD');
});

it('opens the edit modal for a plain array item', function () {
    Livewire::test(PlainItemsForm::class)
        ->mountFormComponentAction('items', 'edit', ['item' => '0'])
        ->assertFormComponentActionMounted('items', 'edit')
        ->assertHasNoFormComponentActionErrors();
});
