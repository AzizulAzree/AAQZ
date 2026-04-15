<?php

namespace Database\Seeders;

use App\Models\CalendarEntry;
use App\Models\User;
use Carbon\CarbonImmutable;
use Illuminate\Database\Seeder;

class CalendarEntrySeeder extends Seeder
{
    public function run(): void
    {
        CalendarEntry::query()->delete();

        $today = CarbonImmutable::today();
        $defaultOwnerId = User::query()->orderBy('id')->value('id');

        $entries = [
            [
                'entry_date' => $today->subMonthsNoOverflow(2)->setDay(4),
                'title' => 'Quarter planning notes finalized',
                'details' => 'Locked the main priorities and budget assumptions for the next release cycle.',
            ],
            [
                'entry_date' => $today->subMonthsNoOverflow(2)->setDay(18),
                'title' => 'Vendor contract review',
                'details' => 'Reviewed renewal terms and flagged one pricing change for follow-up.',
            ],
            [
                'entry_date' => $today->subMonthNoOverflow()->setDay(7),
                'title' => 'Feature scope check-in',
                'details' => 'Compared committed work against the roadmap and moved two ideas to backlog.',
            ],
            [
                'entry_date' => $today->subMonthNoOverflow()->setDay(7),
                'title' => 'Staging smoke test',
                'details' => 'Manual smoke test across login, dashboard, and admin pages before release.',
            ],
            [
                'entry_date' => $today->subMonthNoOverflow()->setDay(21),
                'title' => 'Analytics review',
                'details' => null,
            ],
            [
                'entry_date' => $today->startOfMonth()->setDay(2),
                'title' => 'Monthly goals drafted',
                'details' => 'Outlined the most important deliverables and the minimum launch checklist.',
            ],
            [
                'entry_date' => $today->startOfMonth()->setDay(5),
                'title' => 'Database cleanup pass',
                'details' => 'Removed stale test rows and checked migration state after local changes.',
            ],
            [
                'entry_date' => $today->startOfMonth()->setDay(5),
                'title' => 'Calendar UX review',
                'details' => 'Checked month navigation, weekday alignment, and entry density on mobile width.',
            ],
            [
                'entry_date' => $today->startOfMonth()->setDay(9),
                'title' => 'Content publishing batch',
                'details' => null,
            ],
            [
                'entry_date' => $today->startOfMonth()->setDay(12),
                'title' => 'Billing follow-ups sent',
                'details' => 'Sent reminders for two unpaid invoices and noted expected response dates.',
            ],
            [
                'entry_date' => $today->startOfMonth()->setDay(12),
                'title' => 'Support queue triage',
                'details' => 'Sorted bug reports by severity and linked the highest-impact items to release notes.',
            ],
            [
                'entry_date' => $today->startOfMonth()->setDay(16),
                'title' => 'Production deployment',
                'details' => 'Released the latest auth and dashboard changes during the low-traffic window.',
            ],
            [
                'entry_date' => $today->startOfMonth()->setDay(18),
                'title' => 'Customer interview notes',
                'details' => 'Summarized the three biggest friction points from the latest user call.',
            ],
            [
                'entry_date' => $today->startOfMonth()->setDay(18),
                'title' => 'Metrics snapshot',
                'details' => null,
            ],
            [
                'entry_date' => $today->startOfMonth()->setDay(22),
                'title' => 'Refinement session',
                'details' => 'Split a large feature into smaller tickets and clarified data-model follow-up work.',
            ],
            [
                'entry_date' => $today->startOfMonth()->setDay(24),
                'title' => 'Homepage copy polish',
                'details' => 'Shortened the value proposition and aligned wording with the current product scope.',
            ],
            [
                'entry_date' => $today->startOfMonth()->setDay(27),
                'title' => 'Weekly review',
                'details' => 'Captured wins, blockers, and the top two decisions to make next week.',
            ],
            [
                'entry_date' => $today->addMonthNoOverflow()->startOfMonth()->setDay(3),
                'title' => 'Roadmap preview',
                'details' => 'Prepared the next-month backlog candidate list and grouped it by impact.',
            ],
            [
                'entry_date' => $today->addMonthNoOverflow()->startOfMonth()->setDay(11),
                'title' => 'Design critique',
                'details' => null,
            ],
            [
                'entry_date' => $today->addMonthNoOverflow()->startOfMonth()->setDay(11),
                'title' => 'Accessibility pass',
                'details' => 'Review scheduled for focus states, contrast, and keyboard navigation.',
            ],
            [
                'entry_date' => $today->addMonthsNoOverflow(2)->startOfMonth()->setDay(6),
                'title' => 'Forecast planning',
                'details' => 'Drafted a rough effort forecast for upcoming maintenance and product work.',
            ],
        ];

        foreach ($entries as $entry) {
            $followUpEnabled = in_array($entry['title'], ['Production deployment', 'Metrics snapshot', 'Homepage copy polish'], true);

            CalendarEntry::factory()
                ->onDate($entry['entry_date'])
                ->create([
                    'title' => $entry['title'],
                    'details' => $entry['details'],
                    'follow_up_enabled' => $followUpEnabled,
                    'follow_up_days' => $followUpEnabled ? 2 : null,
                    'source_type' => $defaultOwnerId ? 'self' : null,
                    'source_id' => $defaultOwnerId,
                ]);
        }

        CalendarEntry::factory()->count(6)->create([
            'entry_date' => $today->startOfMonth()->setDay(14)->toDateString(),
            'title' => 'Debug calendar density check',
            'details' => 'Intentional cluster of entries on one day to test stacked dashboard summaries.',
            'follow_up_enabled' => false,
            'follow_up_days' => null,
            'source_type' => $defaultOwnerId ? 'self' : null,
            'source_id' => $defaultOwnerId,
        ]);
    }
}
