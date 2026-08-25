<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Middleware\SetLocale;
use App\Http\Requests\Admin\UpdateAdminLocaleRequest;
use Illuminate\Http\RedirectResponse;

class LocaleController extends Controller
{
    /**
     * Persist the admin panel UI language for this session.
     */
    public function update(UpdateAdminLocaleRequest $request): RedirectResponse
    {
        $request->session()->put(
            SetLocale::ADMIN_SESSION_KEY,
            $request->validated('locale'),
        );

        return back();
    }
}
