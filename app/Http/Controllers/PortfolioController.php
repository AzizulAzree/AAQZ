<?php

namespace App\Http\Controllers;

use Illuminate\Http\RedirectResponse;
use Illuminate\View\View;

class PortfolioController extends Controller
{
    public function redirect(): RedirectResponse
    {
        return redirect()->route('portfolio.show');
    }

    public function legacyRedirect(string $slug): RedirectResponse
    {
        abort_unless($slug === 'azizul-azree', 404);

        return redirect()->route('portfolio.show');
    }

    public function show(): View
    {
        return view('portfolio.show', [
            'slug' => 'azizulazree',
        ]);
    }
}
