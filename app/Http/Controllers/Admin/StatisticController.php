<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\ReorderStatisticsRequest;
use App\Http\Requests\Admin\StoreStatisticRequest;
use App\Http\Requests\Admin\UpdateStatisticRequest;
use App\Models\Statistic;
use App\Services\StatisticService;
use Illuminate\Http\RedirectResponse;

class StatisticController extends Controller
{
    public function __construct(
        private readonly StatisticService $statisticService,
    ) {}

    public function store(StoreStatisticRequest $request): RedirectResponse
    {
        $this->statisticService->create($request->validated());

        return redirect()
            ->route('admin.about.edit', ['tab' => 'statistics'])
            ->with('success', 'Statistic created.');
    }

    public function update(UpdateStatisticRequest $request, Statistic $statistic): RedirectResponse
    {
        $this->statisticService->update($statistic, $request->validated());

        return redirect()
            ->route('admin.about.edit', ['tab' => 'statistics'])
            ->with('success', 'Statistic updated.');
    }

    public function destroy(Statistic $statistic): RedirectResponse
    {
        $this->statisticService->delete($statistic);

        return redirect()
            ->route('admin.about.edit', ['tab' => 'statistics'])
            ->with('success', 'Statistic deleted.');
    }

    public function reorder(ReorderStatisticsRequest $request): RedirectResponse
    {
        $this->statisticService->reorder($request->validated('ids'));

        return redirect()
            ->route('admin.about.edit', ['tab' => 'statistics'])
            ->with('success', 'Statistics order saved.');
    }
}
