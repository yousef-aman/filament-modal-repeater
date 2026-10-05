<?php

use Filament\Actions\Concerns\InteractsWithActions;
use Filament\Actions\Contracts\HasActions;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Concerns\InteractsWithForms;
use Filament\Forms\Contracts\HasForms;
use Filament\Schemas\Components\Component;
use Filament\Schemas\Schema;
use Illuminate\Support\MessageBag;
use Livewire\Component as LivewireComponent;
use Livewire\Livewire;
use YousefAman\ModalRepeater\Column;
use YousefAman\ModalRepeater\ModalRepeater;

/*
 * Reproduces https://github.com/yousef-aman/filament-modal-repeater/issues/4
 *
 * Filament 4.14 and 5.8 widened Component::schema() to also accept a Schema
 * object. ModalRepeater must keep a compatible signature so the class loads at
 * all, and a Schema object passed to it must still drive the add and edit
 * modals, with the modal fields bound to the modal's own data.
 */

class SchemaObjectItemsForm extends LivewireComponent implements HasActions, HasForms
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
                    ->schema(Schema::make()->components([
                        TextInput::make('name')->required(),
                        TextInput::make('price')->numeric()->required(),
                    ])),
            ]);
    }

    public function render(): string
    {
        return <<<'BLADE'
            <div>{{ $this->form }}</div>
        BLADE;
    }
}

function filamentAcceptsSchemaObjects(): bool
{
    $type = (new ReflectionMethod(Component::class, 'schema'))->getParameters()[0]->getType();

    return str_contains((string) $type, Schema::class);
}

it('adds an item through the modal when the schema is a Schema object', function () {
    $component = Livewire::test(SchemaObjectItemsForm::class)
        ->mountFormComponentAction('items', 'add')
        ->assertFormComponentActionDataSet(['name' => null, 'price' => null])
        ->setFormComponentActionData(['name' => 'Gadget', 'price' => 25])
        ->callMountedFormComponentAction()
        ->assertHasNoFormComponentActionErrors();

    $items = array_values($component->get('data.items'));

    expect($items)->toHaveCount(2)
        ->and($items[1])->toMatchArray(['name' => 'Gadget', 'price' => 25]);
})->skip(fn () => ! filamentAcceptsSchemaObjects(), 'This Filament version does not accept Schema objects in schema().');

it('fills and saves the edit modal when the schema is a Schema object', function () {
    $component = Livewire::test(SchemaObjectItemsForm::class);

    $itemKey = array_key_first($component->get('data.items'));

    $component
        ->mountFormComponentAction('items', 'edit', ['item' => $itemKey])
        ->assertFormComponentActionDataSet(['name' => 'Widget', 'price' => 10])
        ->setFormComponentActionData(['name' => 'Widget Pro'])
        ->callMountedFormComponentAction()
        ->assertHasNoFormComponentActionErrors();

    expect($component->get("data.items.{$itemKey}"))
        ->toMatchArray(['name' => 'Widget Pro', 'price' => 10]);
})->skip(fn () => ! filamentAcceptsSchemaObjects(), 'This Filament version does not accept Schema objects in schema().');
