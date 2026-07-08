<?php

namespace App\Http\Controllers;

use App\Models\FinanceCommitmentCategory;
use App\Models\FinancePageAccess;
use App\Models\FinancePeriod;
use App\Models\FinancePeriodCommitment;
use App\Models\FinanceRecord;
use App\Models\User;
use Carbon\CarbonImmutable;
use Illuminate\Database\Eloquent\Collection as EloquentCollection;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\View\View;

class FinanceController extends Controller
{
    public function index(Request $request): View
    {
        $user = $request->user();
        abort_unless($user?->canAccessFinancePage(), 403);

        $accessTableReady = Schema::hasTable('finance_page_accesses');
        $users = $accessTableReady
            ? User::query()->orderBy('name')->orderBy('email')->get()
            : collect();

        $financePeriods = $user->financePeriods()
            ->with(['commitments.category', 'records'])
            ->orderByDesc('period_year')
            ->orderByDesc('period_month')
            ->get();

        $commitmentCategories = $user->financeCommitmentCategories()->get();

        return view('finance.index', [
            'canManageAccess' => $user?->isAdmin() ?? false,
            'accessTableReady' => $accessTableReady,
            'users' => $users,
            'alwaysAllowedUserIds' => $users
                ->filter(fn (User $user) => $user->isAdmin())
                ->pluck('id')
                ->all(),
            'selectedUserIds' => $accessTableReady
                ? FinancePageAccess::query()->pluck('user_id')->all()
                : [],
            'chartPresets' => $this->buildChartPresets($financePeriods, $commitmentCategories),
            'groupedRecords' => $this->buildGroupedRecords($financePeriods, $commitmentCategories),
            'monthStatuses' => $this->buildMonthStatuses($financePeriods, $commitmentCategories),
            'todayDate' => now()->format('Y-m-d'),
        ]);
    }

    public function updateAccess(Request $request): RedirectResponse
    {
        abort_unless($request->user()?->isAdmin(), 403);

        if (! Schema::hasTable('finance_page_accesses')) {
            return redirect()
                ->route('finance.index')
                ->with('status', 'finance-access-unavailable');
        }

        $validated = $request->validate([
            'user_ids' => ['nullable', 'array'],
            'user_ids.*' => ['integer', 'exists:users,id'],
        ]);

        $selectedUserIds = collect($validated['user_ids'] ?? [])
            ->map(fn (mixed $id) => (int) $id)
            ->unique()
            ->values();

        FinancePageAccess::query()
            ->whereNotIn('user_id', $selectedUserIds->all())
            ->delete();

        $selectedUserIds->each(function (int $userId): void {
            FinancePageAccess::query()->firstOrCreate([
                'user_id' => $userId,
            ]);
        });

        return redirect()
            ->route('finance.index')
            ->with('status', 'finance-access-updated');
    }

    public function storeRecord(Request $request): JsonResponse
    {
        $user = $request->user();
        abort_unless($user?->canAccessFinancePage(), 403);

        $validated = $request->validate([
            'type' => ['required', 'in:income,spending'],
            'date' => ['required', 'date'],
            'value' => ['required', 'numeric', 'min:0.01'],
            'category' => ['nullable', 'string', 'max:255'],
        ]);

        DB::transaction(function () use ($user, $validated): void {
            $recordDate = CarbonImmutable::parse($validated['date']);
            $period = $this->resolvePeriodForDate($user, $recordDate);
            $amount = round((float) $validated['value'], 2);

            if ($validated['type'] === 'income') {
                $period->forceFill([
                    'salary_received_on' => $recordDate->toDateString(),
                    'salary_amount' => $amount,
                ])->save();

                FinanceRecord::query()->create([
                    'user_id' => $user->id,
                    'finance_period_id' => $period->id,
                    'record_type' => 'salary',
                    'recorded_on' => $recordDate->toDateString(),
                    'amount' => $amount,
                    'title' => 'Monthly salary update',
                    'notes' => $this->formatPeriodLabel($period),
                ]);

                return;
            }

            $categoryName = trim((string) ($validated['category'] ?? ''));
            abort_if($categoryName === '', 422, 'Category is required.');

            $category = FinanceCommitmentCategory::query()->firstOrCreate(
                [
                    'user_id' => $user->id,
                    'name' => $categoryName,
                ],
                [
                    'default_amount' => $amount,
                    'icon' => strtoupper(substr($categoryName, 0, 1)),
                    'sort_order' => ((int) ($user->financeCommitmentCategories()->max('sort_order') ?? 0)) + 10,
                ],
            );
            $category->forceFill([
                'default_amount' => $amount,
                'is_active' => true,
            ])->save();

            FinancePeriodCommitment::query()->updateOrCreate(
                [
                    'finance_period_id' => $period->id,
                    'finance_commitment_category_id' => $category->id,
                    'name_snapshot' => $categoryName,
                ],
                [
                    'amount' => $amount,
                    'status' => 'paid',
                    'paid_on' => $recordDate->toDateString(),
                    'notes' => 'Paid for this month',
                ],
            );

            FinanceRecord::query()->create([
                'user_id' => $user->id,
                'finance_period_id' => $period->id,
                'finance_commitment_category_id' => $category->id,
                'record_type' => 'commitment',
                'recorded_on' => $recordDate->toDateString(),
                'amount' => $amount,
                'title' => sprintf('%s commitment', $categoryName),
                'notes' => 'Paid for this month',
            ]);
        });

        return response()->json(['saved' => true]);
    }

    public function updateCarryBalance(Request $request): JsonResponse
    {
        $user = $request->user();
        abort_unless($user?->canAccessFinancePage(), 403);

        $validated = $request->validate([
            'date' => ['required', 'date'],
            'value' => ['required', 'numeric', 'min:0'],
        ]);

        DB::transaction(function () use ($user, $validated): void {
            $recordDate = CarbonImmutable::parse($validated['date']);
            $period = $this->resolvePeriodForDate($user, $recordDate);
            $amount = round((float) $validated['value'], 2);

            $period->forceFill([
                'carry_balance_before_salary' => $amount,
            ])->save();

            FinanceRecord::query()->create([
                'user_id' => $user->id,
                'finance_period_id' => $period->id,
                'record_type' => 'carry_balance',
                'recorded_on' => $recordDate->toDateString(),
                'amount' => $amount,
                'title' => 'Carry-over before salary',
                'notes' => sprintf('Balance before salary for %s', $this->formatPeriodLabel($period)),
            ]);
        });

        return response()->json(['saved' => true]);
    }

    private function buildChartPresets(EloquentCollection $financePeriods, EloquentCollection $commitmentCategories): array
    {
        $periodsAsc = $financePeriods
            ->sortBy(fn (FinancePeriod $period) => sprintf('%04d-%02d', $period->period_year, $period->period_month))
            ->values();

        return [
            'weekly' => $this->makeChartPreset($periodsAsc->take(-3), $commitmentCategories, 'Recent cycles'),
            'monthly' => $this->makeChartPreset($periodsAsc->take(-6), $commitmentCategories, 'Monthly'),
            'custom' => $this->makeChartPreset($periodsAsc->take(-12), $commitmentCategories, 'Year view'),
        ];
    }

    private function makeChartPreset(Collection $periods, EloquentCollection $commitmentCategories, string $label): array
    {
        if ($periods->isEmpty()) {
            return [
                'label' => $label,
                'range' => 'No records yet',
                'carry' => [0],
                'starting_balance' => [0],
                'income' => [0],
                'spending' => [0],
                'ending_balance' => [0],
                'labels' => ['No data'],
            ];
        }

        $first = $periods->first();
        $last = $periods->last();
        $snapshots = $this->buildPeriodSnapshots($periods);

        return [
            'label' => $label,
            'range' => $this->formatPeriodRange($first, $last),
            'carry' => $snapshots->pluck('carry')->all(),
            'starting_balance' => $snapshots->pluck('starting_balance')->all(),
            'income' => $snapshots->pluck('salary')->all(),
            'spending' => $snapshots->pluck('paid_spending')->all(),
            'ending_balance' => $snapshots->pluck('ending_balance')->all(),
            'labels' => $periods
                ->map(fn (FinancePeriod $period) => CarbonImmutable::create($period->period_year, $period->period_month, 1)->format('M y'))
                ->all(),
        ];
    }

    private function sumPaidCommitments(FinancePeriod $period): float
    {
        return (float) $period->commitments
            ->where('status', 'paid')
            ->sum('amount');
    }

    private function buildGroupedRecords(EloquentCollection $financePeriods, EloquentCollection $commitmentCategories): array
    {
        $salaryEntries = $financePeriods->map(function (FinancePeriod $period): array {
            return [
                'date' => optional($period->salary_received_on)?->format('j M Y') ?? $this->formatPeriodLabel($period),
                'title' => 'Monthly salary update',
                'note' => $this->formatPeriodLabel($period),
                'amount' => (float) $period->salary_amount,
            ];
        })->values();

        $carryEntries = $financePeriods->map(function (FinancePeriod $period): array {
            return [
                'date' => optional($period->salary_received_on)?->format('j M Y') ?? $this->formatPeriodLabel($period),
                'title' => 'Carry-over before salary',
                'note' => sprintf('Balance before salary for %s', $this->formatPeriodLabel($period)),
                'amount' => (float) $period->carry_balance_before_salary,
            ];
        })->values();

        $groups = collect([
            [
                'category' => 'Salary',
                'icon' => 'Y',
                'accent' => '#27C498',
                'type' => 'income',
                'entries' => $salaryEntries->all(),
            ],
            [
                'category' => 'End Month Balance',
                'icon' => 'B',
                'accent' => '#9B8AFB',
                'type' => 'balance',
                'entries' => $carryEntries->all(),
            ],
        ]);

        $categoryGroups = $commitmentCategories->map(function (FinanceCommitmentCategory $category) use ($financePeriods): array {
            $entries = $financePeriods
                ->flatMap(function (FinancePeriod $period) use ($category) {
                    return $this->buildPeriodCommitments($period, collect([$category]))
                        ->map(function (FinancePeriodCommitment $commitment) use ($period): array {
                            return [
                                'date' => $this->formatPeriodLabel($period),
                                'title' => sprintf('%s commitment', $commitment->name_snapshot),
                                'note' => $commitment->status === 'paid' ? 'Paid for this month' : 'Still waiting this month',
                                'amount' => -1 * (float) $commitment->amount,
                            ];
                        });
                })
                ->values();

            return [
                'category' => $category->name,
                'icon' => $category->icon ?: strtoupper(substr($category->name, 0, 1)),
                'accent' => $category->color ?: '#7C8DB5',
                'type' => 'spending',
                'entries' => $entries->all(),
            ];
        });

        return $groups
            ->concat($categoryGroups)
            ->map(function (array $group): array {
                $entries = collect($group['entries']);

                return $group + [
                    'count' => $entries->count(),
                    'total' => (float) $entries->sum('amount'),
                ];
            })
            ->values()
            ->all();
    }

    private function buildMonthStatuses(EloquentCollection $financePeriods, EloquentCollection $commitmentCategories): array
    {
        $snapshots = $this->buildPeriodSnapshots(
            $financePeriods
                ->sortBy(fn (FinancePeriod $period) => sprintf('%04d-%02d', $period->period_year, $period->period_month))
                ->values()
        )->keyBy('id');

        return $financePeriods
            ->map(function (FinancePeriod $period) use ($commitmentCategories, $snapshots): array {
                $snapshot = $snapshots->get($period->id);

                return [
                    'id' => sprintf('%04d-%02d', $period->period_year, $period->period_month),
                    'label' => $this->formatPeriodLabel($period),
                    'salary_date' => optional($period->salary_received_on)?->format('j M Y') ?? '',
                    'salary' => (float) $period->salary_amount,
                    'carry_balance' => (float) ($snapshot['carry'] ?? $period->carry_balance_before_salary),
                    'ending_balance' => (float) ($snapshot['ending_balance'] ?? 0),
                    'commitments' => $this->buildPeriodCommitments($period, $commitmentCategories)
                        ->sortBy([
                            fn (FinancePeriodCommitment $commitment) => $commitment->status !== 'paid',
                            fn (FinancePeriodCommitment $commitment) => $commitment->category?->sort_order ?? PHP_INT_MAX,
                            fn (FinancePeriodCommitment $commitment) => $commitment->name_snapshot,
                        ])
                        ->values()
                        ->map(function (FinancePeriodCommitment $commitment): array {
                            return [
                                'category' => $commitment->name_snapshot,
                                'amount' => (float) $commitment->amount,
                                'status' => $commitment->status,
                                'paid_on' => optional($commitment->paid_on)?->format('j M Y'),
                            ];
                        })
                        ->all(),
                ];
            })
            ->values()
            ->all();
    }

    private function buildPeriodSnapshots(Collection $periods): Collection
    {
        $previousEndingBalance = null;

        return $periods->map(function (FinancePeriod $period) use (&$previousEndingBalance): array {
            $hasExplicitCarryRecord = $period->records->contains(
                fn (FinanceRecord $record) => $record->record_type === 'carry_balance'
            );

            $rawCarry = (float) $period->carry_balance_before_salary;
            $effectiveCarry = $hasExplicitCarryRecord
                ? $rawCarry
                : (($rawCarry !== 0.0 || $previousEndingBalance === null) ? $rawCarry : $previousEndingBalance);

            $salary = (float) $period->salary_amount;
            $paidSpending = $this->sumPaidCommitments($period);
            $startingBalance = $effectiveCarry + $salary;
            $endingBalance = $startingBalance - $paidSpending;

            $previousEndingBalance = $endingBalance;

            return [
                'id' => $period->id,
                'carry' => $effectiveCarry,
                'salary' => $salary,
                'paid_spending' => $paidSpending,
                'starting_balance' => $startingBalance,
                'ending_balance' => $endingBalance,
            ];
        });
    }

    private function buildPeriodCommitments(FinancePeriod $period, Collection $commitmentCategories): Collection
    {
        $actualCommitments = $period->commitments
            ->keyBy(fn (FinancePeriodCommitment $commitment) => $commitment->finance_commitment_category_id ?: $commitment->name_snapshot);

        return $commitmentCategories
            ->where('is_active', true)
            ->map(function (FinanceCommitmentCategory $category) use ($actualCommitments, $period): FinancePeriodCommitment {
                $existing = $actualCommitments->get($category->id) ?? $actualCommitments->get($category->name);

                if ($existing) {
                    return $existing;
                }

                $synthetic = new FinancePeriodCommitment([
                    'finance_period_id' => $period->id,
                    'finance_commitment_category_id' => $category->id,
                    'name_snapshot' => $category->name,
                    'amount' => (float) ($category->default_amount ?? 0),
                    'status' => 'unpaid',
                    'paid_on' => null,
                    'notes' => 'Still waiting this month',
                ]);
                $synthetic->setRelation('category', $category);

                return $synthetic;
            })
            ->filter(fn (FinancePeriodCommitment $commitment) => (float) $commitment->amount > 0)
            ->values();
    }

    private function formatPeriodLabel(FinancePeriod $period): string
    {
        return CarbonImmutable::create($period->period_year, $period->period_month, 1)->format('F Y');
    }

    private function formatPeriodRange(FinancePeriod $first, FinancePeriod $last): string
    {
        return sprintf('%s - %s', $this->formatPeriodLabel($first), $this->formatPeriodLabel($last));
    }

    private function resolvePeriodForDate(User $user, CarbonImmutable $date): FinancePeriod
    {
        return FinancePeriod::query()->firstOrCreate(
            [
                'user_id' => $user->id,
                'period_year' => (int) $date->format('Y'),
                'period_month' => (int) $date->format('n'),
            ],
            [
                'salary_received_on' => $date->toDateString(),
                'salary_amount' => 0,
                'carry_balance_before_salary' => 0,
            ],
        );
    }
}
