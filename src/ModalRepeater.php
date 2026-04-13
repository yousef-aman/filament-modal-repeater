<?php

namespace YousefAman\ModalRepeater;

use Closure;
use Filament\Actions\Action;
use Filament\Forms\Components\Repeater;
use Filament\Schemas\Components\Grid;
use Filament\Support\Enums\Size;
use Filament\Support\Enums\Width;
use Filament\Support\Icons\Heroicon;

class ModalRepeater extends Repeater
{
    /** @var array<Column> */
    protected array $displayColumns = [];

    protected array|Closure|null $modalSchema = null;

    protected int|Closure $modalColumns = 2;

    protected Width|string|Closure $modalWidth = '2xl';

    protected ?Closure $modifyEditActionUsing = null;

    protected string|Closure|null $emptyLabel = null;

    protected function setUp(): void
    {
        parent::setUp();

        $this->defaultItems(0);

        $this->registerActions([
            fn (self $component): Action => $component->getEditAction(),
        ]);
    }

    public function emptyLabel(string|Closure $label): static
    {
        $this->emptyLabel = $label;

        return $this;
    }

    public function getEmptyLabel(): string
    {
        return $this->evaluate($this->emptyLabel)
            ?? __('filament-tables::table.empty.heading', ['model' => $this->getLabel()]);
    }

    public function schema(array|Closure|null $schema): static
    {
        $this->modalSchema = $schema;

        return parent::schema($schema);
    }

    public function tableColumns(array $columns): static
    {
        $this->displayColumns = $columns;

        return $this;
    }

    public function getDisplayColumns(): array
    {
        return $this->displayColumns;
    }

    public function modalColumns(int|Closure $columns): static
    {
        $this->modalColumns = $columns;

        return $this;
    }

    public function getModalColumns(): int
    {
        return $this->evaluate($this->modalColumns);
    }

    public function modalWidth(Width|string|Closure $width): static
    {
        $this->modalWidth = $width;

        return $this;
    }

    public function getModalWidth(): Width|string
    {
        return $this->evaluate($this->modalWidth);
    }

    public function getModalSchema(): array
    {
        return $this->evaluate($this->modalSchema) ?? [];
    }

    public function getDefaultView(): string
    {
        return 'modal-repeater::modal-repeater';
    }

    public function getAddAction(): Action
    {
        $action = Action::make($this->getAddActionName())
            ->label(fn (self $component) => $component->getAddActionLabel())
            ->color('gray')
            ->schema(fn (self $component) => [
                Grid::make($component->getModalColumns())
                    ->schema($component->getModalSchema()),
            ])
            ->modalHeading(fn (self $component) => $component->getAddActionLabel())
            ->modalWidth(fn (self $component) => $component->getModalWidth())
            ->action(function (array $data, self $component): void {
                $newUuid = $component->generateUuid();

                $items = $component->getRawState();

                if ($newUuid) {
                    $items[$newUuid] = [];
                } else {
                    $items[] = [];
                }

                $component->rawState($items);

                $itemKey = $newUuid ?? array_key_last($items);

                $component->getChildSchema($itemKey)->fill();

                $items = $component->getRawState();
                $items[$itemKey] = array_merge($items[$itemKey] ?? [], $data);
                $component->rawState($items);

                $component->callAfterStateUpdated();

                $component->shouldPartiallyRenderAfterActionsCalled() ? $component->partiallyRender() : null;
            })
            ->button()
            ->size(Size::Small)
            ->visible(fn (self $component): bool => $component->isAddable());

        if ($this->modifyAddActionUsing) {
            $action = $this->evaluate($this->modifyAddActionUsing, [
                'action' => $action,
            ]) ?? $action;
        }

        return $action;
    }

    public function getEditAction(): Action
    {
        $action = Action::make('edit')
            ->label(__('filament-actions::edit.single.label'))
            ->schema(fn (self $component) => [
                Grid::make($component->getModalColumns())
                    ->schema($component->getModalSchema()),
            ])
            ->modalHeading(__('filament-actions::edit.single.label'))
            ->modalWidth(fn (self $component) => $component->getModalWidth())
            ->fillForm(function (array $arguments, self $component): array {
                $items = $component->getRawState();

                return $items[$arguments['item']] ?? [];
            })
            ->action(function (array $data, array $arguments, self $component): void {
                $items = $component->getRawState();
                $items[$arguments['item']] = array_merge($items[$arguments['item']] ?? [], $data);

                $component->rawState($items);

                $component->callAfterStateUpdated();

                $component->shouldPartiallyRenderAfterActionsCalled() ? $component->partiallyRender() : null;
            })
            ->iconButton()
            ->icon(Heroicon::PencilSquare)
            ->color('gray')
            ->size(Size::Small);

        if ($this->modifyEditActionUsing) {
            $action = $this->evaluate($this->modifyEditActionUsing, [
                'action' => $action,
            ]) ?? $action;
        }

        return $action;
    }

    public function editAction(?Closure $callback): static
    {
        $this->modifyEditActionUsing = $callback;

        return $this;
    }

    public function getItemDisplayValues(string $itemKey): array
    {
        $items = $this->getRawState();
        $itemState = $items[$itemKey] ?? [];
        $values = [];

        foreach ($this->getDisplayColumns() as $column) {
            $rawValue = $itemState[$column->getName()] ?? null;
            $values[$column->getName()] = $column->resolveValue($rawValue);
        }

        return $values;
    }
}
