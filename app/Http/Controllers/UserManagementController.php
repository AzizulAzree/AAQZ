<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreUserRequest;
use App\Support\DatabaseInspector\DatabaseInspector;
use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\View\View;

class UserManagementController extends Controller
{
    public function index(DatabaseInspector $inspector): View
    {
        return view('users.index', [
            'users' => User::query()->orderBy('name')->orderBy('email')->get(),
            'databaseOverview' => $inspector->overview(),
        ]);
    }

    public function store(StoreUserRequest $request): RedirectResponse
    {
        User::create($request->validated());

        return redirect()
            ->route('users.index')
            ->with('status', 'user-created');
    }
}
