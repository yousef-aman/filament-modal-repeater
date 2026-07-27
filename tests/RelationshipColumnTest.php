<?php

use Filament\Actions\Concerns\InteractsWithActions;
use Filament\Actions\Contracts\HasActions;
use Filament\Forms\Components\Select;
use Filament\Forms\Concerns\InteractsWithForms;
use Filament\Forms\Contracts\HasForms;
use Filament\Schemas\Schema;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Facades\Schema as DbSchema;
use Illuminate\Support\MessageBag;
use Livewire\Component as LivewireComponent;
use Livewire\Livewire;
use YousefAman\ModalRepeater\Column;
use YousefAman\ModalRepeater\ModalRepeater;

/*
 * Reproduces https://github.com/yousef-aman/filament-modal-repeater/issues/1
 *
 * A ModalRepeater bound to a relationship whose related model has its own
 * relationship (e.g. CompetencyJobProfile->competency). A dot-notation column
 * `competency.name` should display the related value, and clicking edit should
 * not throw a LogicException by resolving `competency` against the parent model.
 */

class Competency extends Model
{
    protected $guarded = [];

    public $timestamps = false;
}

class JobProfile extends Model
{
    protected $guarded = [];

    public $timestamps = false;

    public function competencyJobProfiles(): HasMany
    {
        return $this->hasMany(CompetencyJobProfile::class);
    }
}

class CompetencyJobProfile extends Model
{
    protected $guarded = [];

    public $timestamps = false;

    public function competency(): BelongsTo
    {
        return $this->belongsTo(Competency::class);
    }
}

class JobProfileForm extends LivewireComponent implements HasActions, HasForms
{
    use InteractsWithActions;
    use InteractsWithForms;

    public JobProfile $record;

    public ?array $data = [];

    public function mount(JobProfile $record): void
    {
        $this->record = $record;

        $this->form->fill();

        // Mirror how Filament hydrates a relationship repeater's state from the
        // existing records (keyed by "record-{id}"), so the table actually
        // renders rows in this minimal test harness.
        $this->data['competencyJobProfiles'] = $record->competencyJobProfiles
            ->mapWithKeys(fn (CompetencyJobProfile $item) => ["record-{$item->getKey()}" => $item->attributesToArray()])
            ->all();
    }

    // Livewire v4's data store is not persisted in headless tests, so the
    // default error bag resolves to null during render. Return a real one.
    public function getErrorBag()
    {
        return new MessageBag;
    }

    public function form(Schema $schema): Schema
    {
        return $schema
            ->statePath('data')
            ->model($this->record)
            ->components([
                ModalRepeater::make('competencyJobProfiles')
                    ->relationship()
                    ->tableColumns([
                        Column::make('competency.name')->label('Competency'),
                    ])
                    ->schema([
                        Select::make('competency_id')
                            ->relationship('competency', 'name')
                            ->required(),
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

beforeEach(function () {
    foreach (['competencies', 'job_profiles', 'competency_job_profiles'] as $table) {
        DbSchema::dropIfExists($table);
    }

    DbSchema::create('competencies', function ($table) {
        $table->id();
        $table->string('name');
    });

    DbSchema::create('job_profiles', function ($table) {
        $table->id();
        $table->string('title');
    });

    DbSchema::create('competency_job_profiles', function ($table) {
        $table->id();
        $table->foreignId('job_profile_id');
        $table->foreignId('competency_id');
    });

    $this->php = Competency::create(['name' => 'PHP']);
    $this->laravel = Competency::create(['name' => 'Laravel']);

    $this->jobProfile = JobProfile::create(['title' => 'Backend Developer']);
    $this->jobProfile->competencyJobProfiles()->create(['competency_id' => $this->php->id]);
    $this->jobProfile->competencyJobProfiles()->create(['competency_id' => $this->laravel->id]);
});

function bootRelationshipRepeater(JobProfile $record): ModalRepeater
{
    $form = Livewire::test(JobProfileForm::class, ['record' => $record])
        ->instance()
        ->getSchema('form');

    $repeater = collect($form->getFlatComponents())
        ->first(fn ($component) => $component instanceof ModalRepeater);

    $repeater->fillFromRelationship();

    return $repeater;
}

it('resolves dot-notation relationship column values for display', function () {
    $repeater = bootRelationshipRepeater($this->jobProfile);

    expect($repeater->getItemDisplayValues('record-1')['competency.name'])->toBe('PHP')
        ->and($repeater->getItemDisplayValues('record-2')['competency.name'])->toBe('Laravel');
});

it('renders the dot-notation relationship value in the table', function () {
    // End-to-end: the rendered repeater table HTML shows the related value.
    Livewire::test(JobProfileForm::class, ['record' => $this->jobProfile])
        ->assertSee('PHP')
        ->assertSee('Laravel');
});

it('opens the edit modal for a pivot relationship item without a LogicException', function () {
    // Mounting builds the modal schema, where the relationship Select resolves
    // its relationship — the exact point that threw before the fix.
    Livewire::test(JobProfileForm::class, ['record' => $this->jobProfile])
        ->mountFormComponentAction('competencyJobProfiles', 'edit', ['item' => 'record-1'])
        ->assertFormComponentActionMounted('competencyJobProfiles', 'edit')
        ->assertHasNoFormComponentActionErrors();
});

it('opens the add modal for a relationship without a LogicException', function () {
    Livewire::test(JobProfileForm::class, ['record' => $this->jobProfile])
        ->mountFormComponentAction('competencyJobProfiles', 'add')
        ->assertFormComponentActionMounted('competencyJobProfiles', 'add')
        ->assertHasNoFormComponentActionErrors();
});
