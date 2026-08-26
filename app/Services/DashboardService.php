<?php

namespace App\Services;

use App\Models\Lead;
use App\Models\Project;
use App\Repositories\Contracts\BlogRepositoryInterface;
use App\Repositories\Contracts\LeadRepositoryInterface;
use App\Repositories\Contracts\ProjectRepositoryInterface;
use App\Repositories\Contracts\ServiceRepositoryInterface;
use Carbon\CarbonPeriod;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Auth;

class DashboardService
{
    private const TRAFFIC_RANGES = ['7d', '30d', 'year'];

    public function __construct(
        private readonly LeadRepositoryInterface $leadRepository,
        private readonly ProjectRepositoryInterface $projectRepository,
        private readonly ServiceRepositoryInterface $serviceRepository,
        private readonly BlogRepositoryInterface $blogRepository,
    ) {}

    /**
     * @return array{
     *     greeting: array{admin_name: string, date_label: string},
     *     stats: array<string, array{value: int, change_percent: float|null, trend: string}>,
     *     traffic: array{range: string, labels: list<string>, values: list<int>},
     *     recent_leads: list<array<string, mixed>>,
     *     recent_projects: list<array<string, mixed>>,
     *     pending_leads_count: int
     * }
     */
    public function forAdmin(?string $trafficRange = '30d'): array
    {
        $range = $this->normalizeTrafficRange($trafficRange);
        $locale = app()->getLocale();

        return [
            'greeting' => $this->greeting(),
            'stats' => $this->stats(),
            'traffic' => $this->traffic($range),
            'recent_leads' => $this->recentLeads($locale),
            'recent_projects' => $this->recentProjects($locale),
            'pending_leads_count' => $this->leadRepository->countPending(),
        ];
    }

    /**
     * @return array{admin_name: string, date_label: string}
     */
    private function greeting(): array
    {
        $user = Auth::user();

        return [
            'admin_name' => $user?->name ?? 'Admin',
            'date_label' => now()->translatedFormat('l, F j, Y'),
        ];
    }

    /**
     * @return array<string, array{value: int, change_percent: float|null, trend: string}>
     */
    private function stats(): array
    {
        $now = now();
        $last30From = $now->copy()->subDays(30)->startOfDay();
        $last30To = $now->copy()->endOfDay();
        $prev30From = $now->copy()->subDays(60)->startOfDay();
        $prev30To = $now->copy()->subDays(30)->subSecond();

        $weekFrom = $now->copy()->startOfWeek();
        $weekTo = $now->copy()->endOfDay();
        $prevWeekFrom = $now->copy()->subWeek()->startOfWeek();
        $prevWeekTo = $now->copy()->subWeek()->endOfWeek();

        $projectsCreated = $this->projectRepository->countCreatedBetween($last30From, $last30To);
        $projectsCreatedPrev = $this->projectRepository->countCreatedBetween($prev30From, $prev30To);
        $servicesCreated = $this->serviceRepository->countCreatedBetween($last30From, $last30To);
        $servicesCreatedPrev = $this->serviceRepository->countCreatedBetween($prev30From, $prev30To);
        $leadsThisWeek = $this->leadRepository->countCreatedBetween($weekFrom, $weekTo);
        $leadsPrevWeek = $this->leadRepository->countCreatedBetween($prevWeekFrom, $prevWeekTo);

        $pageViews = $this->projectRepository->sumViews() + $this->blogRepository->sumViews();

        return [
            'projects' => $this->statCard(
                $this->projectRepository->count(),
                $projectsCreated,
                $projectsCreatedPrev,
            ),
            'services' => $this->statCard(
                $this->serviceRepository->count(),
                $servicesCreated,
                $servicesCreatedPrev,
            ),
            'leads_this_week' => $this->statCard(
                $leadsThisWeek,
                $leadsThisWeek,
                $leadsPrevWeek,
            ),
            'page_views' => [
                'value' => $pageViews,
                'change_percent' => null,
                'trend' => 'flat',
            ],
        ];
    }

    /**
     * @return array{value: int, change_percent: float|null, trend: string}
     */
    private function statCard(int $value, int $currentWindow, int $previousWindow): array
    {
        [$changePercent, $trend] = $this->compareWindows($currentWindow, $previousWindow);

        return [
            'value' => $value,
            'change_percent' => $changePercent,
            'trend' => $trend,
        ];
    }

    /**
     * @return array{0: float|null, 1: string}
     */
    private function compareWindows(int $current, int $previous): array
    {
        if ($previous === 0) {
            if ($current === 0) {
                return [null, 'flat'];
            }

            return [100.0, 'up'];
        }

        $percent = round((($current - $previous) / $previous) * 100, 1);

        if ($percent > 0) {
            return [abs($percent), 'up'];
        }

        if ($percent < 0) {
            return [abs($percent), 'down'];
        }

        return [0.0, 'flat'];
    }

    /**
     * @return array{range: string, labels: list<string>, values: list<int>}
     */
    private function traffic(string $range): array
    {
        if ($range === 'year') {
            $labels = [];
            $values = [];
            $cursor = now()->subMonths(11)->startOfMonth();

            for ($i = 0; $i < 12; $i++) {
                $labels[] = $cursor->format('M Y');
                $values[] = 0;
                $cursor->addMonth();
            }

            return [
                'range' => $range,
                'labels' => $labels,
                'values' => $values,
            ];
        }

        $days = $range === '7d' ? 6 : 29;
        $period = CarbonPeriod::create(
            now()->subDays($days)->startOfDay(),
            now()->endOfDay(),
        );

        $labels = [];
        $values = [];

        foreach ($period as $date) {
            /** @var Carbon $date */
            $labels[] = $date->format('M j');
            $values[] = 0;
        }

        return [
            'range' => $range,
            'labels' => $labels,
            'values' => $values,
        ];
    }

    /**
     * @return list<array<string, mixed>>
     */
    private function recentLeads(string $locale): array
    {
        return $this->leadRepository->latest(5)->map(function (Lead $lead) use ($locale) {
            return [
                'id' => $lead->id,
                'full_name' => $lead->full_name,
                'interest_label' => $this->leadInterestLabel($lead, $locale),
                'status' => $lead->status,
                'created_at' => $lead->created_at?->toIso8601String(),
            ];
        })->values()->all();
    }

    private function leadInterestLabel(Lead $lead, string $locale): string
    {
        if ($lead->relationLoaded('service') && $lead->service) {
            $translation = $lead->service->translations->first(
                fn ($row) => $row->language?->code === $locale
            ) ?? $lead->service->translations->first();

            if (filled($translation?->title)) {
                return (string) $translation->title;
            }
        }

        if (filled($lead->interest_other)) {
            return (string) $lead->interest_other;
        }

        return 'General';
    }

    /**
     * @return list<array<string, mixed>>
     */
    private function recentProjects(string $locale): array
    {
        return $this->projectRepository->latestWithRelations(3)->map(function (Project $project) use ($locale) {
            $translation = $project->translations->first(
                fn ($row) => $row->language?->code === $locale
            ) ?? $project->translations->first();

            $categoryTranslation = null;
            if ($project->category) {
                $categoryTranslation = $project->category->translations->first(
                    fn ($row) => $row->language?->code === $locale
                ) ?? $project->category->translations->first();
            }

            return [
                'id' => $project->id,
                'name' => $translation?->name ?? 'Untitled project',
                'category_name' => $categoryTranslation?->name,
                'thumbnail_url' => $project->getFirstMediaUrl('thumbnail') ?: null,
                'updated_at' => $project->updated_at?->toIso8601String(),
            ];
        })->values()->all();
    }

    private function normalizeTrafficRange(?string $range): string
    {
        $range = $range ?: '30d';

        return in_array($range, self::TRAFFIC_RANGES, true) ? $range : '30d';
    }
}
