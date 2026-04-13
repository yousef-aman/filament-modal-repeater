<?php

use YousefAman\ModalRepeater\Column;
use YousefAman\ModalRepeater\ModalRepeater;

it('can create a modal repeater instance', function () {
    $repeater = ModalRepeater::make('items');

    expect($repeater)->toBeInstanceOf(ModalRepeater::class);
    expect($repeater->getName())->toBe('items');
});

it('can define table columns', function () {
    $repeater = ModalRepeater::make('items')
        ->tableColumns([
            Column::make('name')->label('Name'),
            Column::make('price')->money('SAR'),
        ]);

    $columns = $repeater->getDisplayColumns();

    expect($columns)->toHaveCount(2);
    expect($columns[0]->getName())->toBe('name');
    expect($columns[0]->getLabel())->toBe('Name');
    expect($columns[1]->getName())->toBe('price');
});

it('can set modal width and columns', function () {
    $repeater = ModalRepeater::make('items')
        ->modalColumns(3)
        ->modalWidth('4xl');

    expect($repeater->getModalColumns())->toBe(3);
    expect($repeater->getModalWidth())->toBe('4xl');
});

it('has default modal settings', function () {
    $repeater = ModalRepeater::make('items');

    expect($repeater->getModalColumns())->toBe(2);
    expect($repeater->getModalWidth())->toBe('2xl');
});

it('resolves column values with formatUsing', function () {
    $column = Column::make('status')
        ->formatUsing(fn ($value) => strtoupper($value));

    expect($column->resolveValue('active'))->toBe('ACTIVE');
});

it('resolves column values with money format', function () {
    $column = Column::make('price')->money('SAR');

    expect($column->resolveValue(100))->toBe('100.00 SAR');
    expect($column->resolveValue(null))->toBe('-');
});

it('identifies boolean columns', function () {
    $column = Column::make('active')->boolean();

    expect($column->isBoolean())->toBeTrue();
    expect($column->isBadge())->toBeFalse();
});

it('identifies badge columns', function () {
    $column = Column::make('status')->badge('success');

    expect($column->isBadge())->toBeTrue();
    expect($column->getBadgeColor())->toBe('success');
    expect($column->isBoolean())->toBeFalse();
});

it('returns the correct view', function () {
    $repeater = ModalRepeater::make('items');

    expect($repeater->getDefaultView())->toBe('modal-repeater::modal-repeater');
});

it('can set empty label', function () {
    $repeater = ModalRepeater::make('items')
        ->emptyLabel('No items yet');

    expect($repeater->getEmptyLabel())->toBe('No items yet');
});

it('can set column width', function () {
    $column = Column::make('name')->width('200px');

    expect($column->getWidth())->toBe('200px');
});

it('uses name as default label', function () {
    $column = Column::make('first_name');

    expect($column->getLabel())->toBe('first_name');
});
