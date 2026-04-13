@php
    use Filament\Actions\Action;
    use Filament\Support\Enums\Alignment;
    use Illuminate\View\ComponentAttributeBag;

    $fieldWrapperView = $getFieldWrapperView();

    $items = $getItems();
    $rawState = $getRawState() ?? [];

    $addAction = $getAction($getAddActionName());
    $addActionAlignment = $getAddActionAlignment();
    $editAction = $getAction('edit');
    $cloneAction = $getAction($getCloneActionName());
    $deleteAction = $getAction($getDeleteActionName());
    $moveDownAction = $getAction($getMoveDownActionName());
    $moveUpAction = $getAction($getMoveUpActionName());
    $reorderAction = $getAction($getReorderActionName());
    $extraItemActions = $getExtraItemActions();

    $isAddable = $isAddable();
    $isCloneable = $isCloneable();
    $isDeletable = $isDeletable();
    $isReorderableWithButtons = $isReorderableWithButtons();
    $isReorderableWithDragAndDrop = $isReorderableWithDragAndDrop();

    $displayColumns = $getDisplayColumns();

    $key = $getKey();
    $statePath = $getStatePath();
@endphp

<x-dynamic-component :component="$fieldWrapperView" :field="$field">
    <div
        {{ $attributes
                ->merge($getExtraAttributes(), escape: false)
                ->class(['fi-fo-modal-repeater']) }}
    >
        @if (count($items))
            <table>
                <thead>
                    <tr>
                        @if ((count($items) > 1) && ($isReorderableWithButtons || $isReorderableWithDragAndDrop))
                            <th class="fi-fo-modal-repeater-empty-header-cell"></th>
                        @endif

                        @foreach ($displayColumns as $column)
                            <th
                                @style([
                                    ('width: ' . $column->getWidth()) => $column->getWidth(),
                                ])
                            >
                                {{ $column->getLabel() }}
                            </th>
                        @endforeach

                        @if ($editAction->isVisible() || count($extraItemActions) || $isCloneable || $isDeletable)
                            <th class="fi-fo-modal-repeater-empty-header-cell"></th>
                        @endif
                    </tr>
                </thead>

                <tbody
                    @if ($isReorderableWithDragAndDrop)
                        x-sortable
                        {{ (new ComponentAttributeBag)
                                ->merge([
                                    'data-sortable-animation-duration' => $getReorderAnimationDuration(),
                                    'x-on:end.stop' => '$wire.mountAction(\'reorder\', { items: $event.target.sortable.toArray() }, { schemaComponent: \'' . $key . '\' })',
                                ], escape: false) }}
                    @endif
                >
                    @foreach ($items as $itemKey => $item)
                        @php
                            $visibleExtraItemActions = array_filter(
                                $extraItemActions,
                                fn (Action $action): bool => $action(['item' => $itemKey])->isVisible(),
                            );
                            $itemCloneAction = $cloneAction(['item' => $itemKey]);
                            $cloneActionIsVisible = $isCloneable && $itemCloneAction->isVisible();
                            $itemDeleteAction = $deleteAction(['item' => $itemKey]);
                            $deleteActionIsVisible = $isDeletable && $itemDeleteAction->isVisible();
                            $itemMoveDownAction = $moveDownAction(['item' => $itemKey])->disabled($loop->last);
                            $moveDownActionIsVisible = $isReorderableWithButtons && $itemMoveDownAction->isVisible();
                            $itemMoveUpAction = $moveUpAction(['item' => $itemKey])->disabled($loop->first);
                            $moveUpActionIsVisible = $isReorderableWithButtons && $itemMoveUpAction->isVisible();
                            $reorderActionIsVisible = $isReorderableWithDragAndDrop && $reorderAction->isVisible();
                            $displayValues = $getItemDisplayValues($itemKey);
                        @endphp

                        <tr
                            wire:key="{{ $item->getLivewireKey() }}.item"
                            @if ($isReorderableWithDragAndDrop)
                                x-sortable-item="{{ $itemKey }}"
                            @endif
                        >
                            @if ((count($items) > 1) && ($isReorderableWithButtons || $isReorderableWithDragAndDrop))
                                <td>
                                    @if ($reorderActionIsVisible || $moveUpActionIsVisible || $moveDownActionIsVisible)
                                        <div class="fi-fo-modal-repeater-actions">
                                            @if ($reorderActionIsVisible)
                                                <div x-on:click.stop>
                                                    {{ $reorderAction->extraAttributes(['x-sortable-handle' => true], merge: true) }}
                                                </div>
                                            @endif

                                            @if ($moveUpActionIsVisible || $moveDownActionIsVisible)
                                                <div x-on:click.stop>
                                                    {{ $itemMoveUpAction }}
                                                </div>

                                                <div x-on:click.stop>
                                                    {{ $itemMoveDownAction }}
                                                </div>
                                            @endif
                                        </div>
                                    @endif
                                </td>
                            @endif

                            @foreach ($displayColumns as $column)
                                <td>
                                    @if ($column->isBoolean())
                                        @if ($displayValues[$column->getName()] ?? false)
                                            <x-filament::icon
                                                icon="heroicon-o-check-circle"
                                                class="fi-fo-modal-repeater-boolean-icon fi-fo-modal-repeater-boolean-icon--true"
                                            />
                                        @else
                                            <x-filament::icon
                                                icon="heroicon-o-x-circle"
                                                class="fi-fo-modal-repeater-boolean-icon fi-fo-modal-repeater-boolean-icon--false"
                                            />
                                        @endif
                                    @elseif ($column->isBadge())
                                        <x-filament::badge
                                            :color="$column->getBadgeColor()"
                                        >
                                            {{ $displayValues[$column->getName()] ?? '-' }}
                                        </x-filament::badge>
                                    @else
                                        {{ $displayValues[$column->getName()] ?? '-' }}
                                    @endif
                                </td>
                            @endforeach

                            @if ($editAction->isVisible() || count($visibleExtraItemActions) || $cloneActionIsVisible || $deleteActionIsVisible)
                                <td>
                                    <div class="fi-fo-modal-repeater-actions">
                                        @if ($editAction->isVisible())
                                            <div x-on:click.stop>
                                                {{ $editAction(['item' => $itemKey]) }}
                                            </div>
                                        @endif

                                        @foreach ($visibleExtraItemActions as $extraItemAction)
                                            <div x-on:click.stop>
                                                {{ $extraItemAction(['item' => $itemKey]) }}
                                            </div>
                                        @endforeach

                                        @if ($cloneActionIsVisible)
                                            <div x-on:click.stop>
                                                {{ $itemCloneAction }}
                                            </div>
                                        @endif

                                        @if ($deleteActionIsVisible)
                                            <div x-on:click.stop>
                                                {{ $itemDeleteAction }}
                                            </div>
                                        @endif
                                    </div>
                                </td>
                            @endif
                        </tr>
                    @endforeach
                </tbody>
            </table>
        @else
            <div class="fi-fo-modal-repeater-empty">
                <p class="fi-fo-modal-repeater-empty-text">
                    {{ $getEmptyLabel() }}
                </p>
            </div>
        @endif

        @if ($isAddable && $addAction->isVisible())
            <div
                @class([
                    'fi-fo-modal-repeater-add',
                    ($addActionAlignment instanceof Alignment) ? ('fi-align-' . $addActionAlignment->value) : $addActionAlignment,
                ])
            >
                {{ $addAction }}
            </div>
        @endif
    </div>
</x-dynamic-component>
