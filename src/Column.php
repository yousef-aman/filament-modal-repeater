<?php

namespace YousefAman\ModalRepeater;

use Closure;

class Column
{
    protected string|Closure|null $label = null;

    protected ?Closure $formatUsing = null;

    protected ?string $width = null;

    protected bool $isBoolean = false;

    protected bool $isBadge = false;

    protected ?string $badgeColor = null;

    public function __construct(
        protected string $name,
    ) {}

    public static function make(string $name): static
    {
        return new static($name);
    }

    public function label(string|Closure $label): static
    {
        $this->label = $label;

        return $this;
    }

    public function formatUsing(Closure $callback): static
    {
        $this->formatUsing = $callback;

        return $this;
    }

    public function width(string $width): static
    {
        $this->width = $width;

        return $this;
    }

    public function boolean(): static
    {
        $this->isBoolean = true;

        return $this;
    }

    public function badge(?string $color = null): static
    {
        $this->isBadge = true;
        $this->badgeColor = $color;

        return $this;
    }

    public function money(string $currency): static
    {
        $this->formatUsing = fn ($value) => $value !== null
            ? number_format((float) $value, 2).' '.$currency
            : '-';

        return $this;
    }

    public function getName(): string
    {
        return $this->name;
    }

    public function getLabel(): string
    {
        $label = $this->label;

        if ($label instanceof Closure) {
            return ($label)();
        }

        return $label ?? $this->name;
    }

    public function getWidth(): ?string
    {
        return $this->width;
    }

    public function isBoolean(): bool
    {
        return $this->isBoolean;
    }

    public function isBadge(): bool
    {
        return $this->isBadge;
    }

    public function getBadgeColor(): ?string
    {
        return $this->badgeColor;
    }

    public function resolveValue(mixed $value): mixed
    {
        if ($this->formatUsing) {
            return ($this->formatUsing)($value);
        }

        return $value;
    }
}
