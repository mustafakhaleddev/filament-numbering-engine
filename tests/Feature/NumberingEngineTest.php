<?php

use App\Models\Company;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Event;
use Wezlo\FilamentNumberingEngine\Concerns\HasNumbering;
use Wezlo\FilamentNumberingEngine\Enums\ResetFrequency;
use Wezlo\FilamentNumberingEngine\Events\NumberGenerated;
use Wezlo\FilamentNumberingEngine\Models\NumberingCounter;
use Wezlo\FilamentNumberingEngine\Models\NumberingSequence;
use Wezlo\FilamentNumberingEngine\Services\NumberingEngine;

uses(RefreshDatabase::class);

function createTestSequence(array $overrides = []): NumberingSequence
{
    return NumberingSequence::create(array_merge([
        'name' => 'Test Sequence',
        'model_type' => 'test_model',
        'attribute' => 'number',
        'pattern' => 'TST-{year}-{sequence:4}',
        'reset_frequency' => ResetFrequency::Yearly,
        'fiscal_year_start_month' => 1,
        'is_gap_free' => false,
        'is_active' => true,
        'initial_value' => 1,
    ], $overrides));
}

it('generates a number with the correct pattern', function () {
    Event::fake();
    $sequence = createTestSequence();
    $engine = app(NumberingEngine::class);

    $model = new class extends Model
    {
        use HasNumbering;

        protected $table = 'users';

        protected $guarded = [];

        public function getMorphClass(): string
        {
            return 'test_model';
        }
    };

    $number = $engine->generate($model, $sequence);
    expect($number)->toBe('TST-'.now()->format('Y').'-0001');

    Event::assertDispatched(NumberGenerated::class, function (NumberGenerated $event) use ($number) {
        return $event->generatedNumber === $number && $event->attribute === 'number';
    });
});

it('increments the counter on each generation', function () {
    $sequence = createTestSequence();
    $engine = app(NumberingEngine::class);

    $model = new class extends Model
    {
        protected $table = 'users';

        protected $guarded = [];

        public function getMorphClass(): string
        {
            return 'test_model';
        }
    };

    $first = $engine->generate($model, $sequence);
    $second = $engine->generate($model, $sequence);
    $third = $engine->generate($model, $sequence);

    $year = now()->format('Y');
    expect($first)->toBe("TST-{$year}-0001");
    expect($second)->toBe("TST-{$year}-0002");
    expect($third)->toBe("TST-{$year}-0003");
});

it('previews the next number without consuming it', function () {
    $sequence = createTestSequence();
    $engine = app(NumberingEngine::class);

    $model = new class extends Model
    {
        protected $table = 'users';

        protected $guarded = [];

        public function getMorphClass(): string
        {
            return 'test_model';
        }
    };

    $preview = $engine->preview($model, $sequence);
    $year = now()->format('Y');
    expect($preview)->toBe("TST-{$year}-0001");

    // Calling preview again should return the same number (not consumed)
    $preview2 = $engine->preview($model, $sequence);
    expect($preview2)->toBe("TST-{$year}-0001");

    // Now generate — should still be 0001
    $actual = $engine->generate($model, $sequence);
    expect($actual)->toBe("TST-{$year}-0001");
});

it('generates gap-free numbers', function () {
    $sequence = createTestSequence(['is_gap_free' => true]);
    $engine = app(NumberingEngine::class);

    $model = new class extends Model
    {
        protected $table = 'users';

        protected $guarded = [];

        public function getMorphClass(): string
        {
            return 'test_model';
        }
    };

    $scopeKey = $engine->buildScopeKey($sequence, $model);
    $first = $engine->generateGapFree($model, $sequence, $scopeKey);
    $second = $engine->generateGapFree($model, $sequence, $scopeKey);

    $year = now()->format('Y');
    expect($first)->toBe("TST-{$year}-0001");
    expect($second)->toBe("TST-{$year}-0002");

    // Verify counter state in database
    $counter = NumberingCounter::where('numbering_sequence_id', $sequence->id)->first();
    expect($counter->current_value)->toBe(2);
});

it('isolates counters per tenant', function () {
    $company1 = Company::factory()->create();
    $company2 = Company::factory()->create();

    $sequence1 = createTestSequence(['company_id' => $company1->id]);
    $sequence2 = createTestSequence([
        'company_id' => $company2->id,
        'name' => 'Test Sequence 2',
    ]);

    $engine = app(NumberingEngine::class);

    $model1 = new class extends Model
    {
        protected $table = 'users';

        protected $guarded = [];

        public int|string $company_id = 0;

        public function getMorphClass(): string
        {
            return 'test_model';
        }
    };
    $model1->company_id = $company1->id;

    $model2 = new class extends Model
    {
        protected $table = 'users';

        protected $guarded = [];

        public int|string $company_id = 0;

        public function getMorphClass(): string
        {
            return 'test_model';
        }
    };
    $model2->company_id = $company2->id;

    $number1a = $engine->generate($model1, $sequence1);
    $number1b = $engine->generate($model1, $sequence1);
    $number2a = $engine->generate($model2, $sequence2);

    $year = now()->format('Y');
    expect($number1a)->toBe("TST-{$year}-0001");
    expect($number1b)->toBe("TST-{$year}-0002");
    expect($number2a)->toBe("TST-{$year}-0001"); // Independent counter
});

it('uses initial_value for the first counter', function () {
    $sequence = createTestSequence(['initial_value' => 100]);
    $engine = app(NumberingEngine::class);

    $model = new class extends Model
    {
        protected $table = 'users';

        protected $guarded = [];

        public function getMorphClass(): string
        {
            return 'test_model';
        }
    };

    $number = $engine->generate($model, $sequence);
    $year = now()->format('Y');
    expect($number)->toBe("TST-{$year}-0100");
});

it('builds correct scope keys for different reset frequencies', function () {
    $company = Company::factory()->create();
    $engine = app(NumberingEngine::class);

    $model = new class extends Model
    {
        protected $table = 'users';

        protected $guarded = [];

        public int|string $company_id = 0;

        public function getMorphClass(): string
        {
            return 'test_model';
        }
    };
    $model->company_id = $company->id;

    $yearly = createTestSequence(['reset_frequency' => ResetFrequency::Yearly, 'company_id' => $company->id]);
    $monthly = createTestSequence([
        'reset_frequency' => ResetFrequency::Monthly,
        'company_id' => $company->id,
        'name' => 'Monthly Seq',
        'attribute' => 'monthly_number',
    ]);
    $never = createTestSequence([
        'reset_frequency' => ResetFrequency::Never,
        'company_id' => $company->id,
        'name' => 'Never Seq',
        'attribute' => 'never_number',
    ]);

    $yearlyKey = $engine->buildScopeKey($yearly, $model);
    $monthlyKey = $engine->buildScopeKey($monthly, $model);
    $neverKey = $engine->buildScopeKey($never, $model);

    expect($yearlyKey)->toBe("company_{$company->id}:".now()->format('Y'));
    expect($monthlyKey)->toBe("company_{$company->id}:".now()->format('Y-m'));
    expect($neverKey)->toBe("company_{$company->id}:all");
});
