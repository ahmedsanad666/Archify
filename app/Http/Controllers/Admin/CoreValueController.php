<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\ReorderCoreValuesRequest;
use App\Http\Requests\Admin\StoreCoreValueRequest;
use App\Http\Requests\Admin\UpdateCoreValueRequest;
use App\Models\CoreValue;
use App\Services\CoreValueService;
use Illuminate\Http\RedirectResponse;

class CoreValueController extends Controller
{
    public function __construct(
        private readonly CoreValueService $coreValueService,
    ) {}

    public function store(StoreCoreValueRequest $request): RedirectResponse
    {
        $this->coreValueService->create($request->validated());

        return redirect()
            ->route('admin.about.edit', ['tab' => 'core_values'])
            ->with('success', 'Core value created.');
    }

    public function update(UpdateCoreValueRequest $request, CoreValue $coreValue): RedirectResponse
    {
        $this->coreValueService->update($coreValue, $request->validated());

        return redirect()
            ->route('admin.about.edit', ['tab' => 'core_values'])
            ->with('success', 'Core value updated.');
    }

    public function destroy(CoreValue $coreValue): RedirectResponse
    {
        $this->coreValueService->delete($coreValue);

        return redirect()
            ->route('admin.about.edit', ['tab' => 'core_values'])
            ->with('success', 'Core value deleted.');
    }

    public function reorder(ReorderCoreValuesRequest $request): RedirectResponse
    {
        $this->coreValueService->reorder($request->validated('ids'));

        return redirect()
            ->route('admin.about.edit', ['tab' => 'core_values'])
            ->with('success', 'Core values order saved.');
    }
}
