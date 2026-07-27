# Changelog

All notable changes to `filament-modal-repeater` will be documented in this file.

## v1.1.2 - 2026-07-27

- The released archive now ships only what the package needs at runtime, so
  installing it downloads about 52 KB instead of 2.1 MB. No behaviour changed.
- Internal: the test suite now runs against both Laravel 12 and 13, and pull
  requests are checked by CI across PHP 8.3/8.4.

## v1.1.1 - 2026-06-01

- Fixed dot-notation columns on relationship repeaters. A column such as
  `competency.name` now resolves against the related Eloquent record, so it
  traverses the relation instead of reading the raw array state.
- Fixed the add and edit modals for relationship repeaters. The modal grid is
  now bound to the related model, so a nested field that uses its own
  relationship (for example a `Select`) resolves against that model rather than
  the parent form's model. Pivot relationships previously threw a
  `LogicException` here.

## v1.1.0 - 2026-04-29

- Added an install command: `php artisan modal-repeater:install`.
- Widened Filament support to `^4.0 || ^5.0`; it was Filament 5 only.

## v1.0.0 - 2026-04-13

- Initial release
- Table display with configurable columns (label, boolean, badge, money, custom formatter)
- Modal-based editing and creation
- Configurable modal width and column layout
- Relationship support
- Reorderable, cloneable, and deletable rows
- Customizable actions (add, edit, extra item actions)
- Empty state label
