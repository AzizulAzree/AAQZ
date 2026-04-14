<?php

namespace App\Http\Controllers;

use App\Support\DatabaseInspector\DatabaseInspector;
use Illuminate\Http\Request;
use Illuminate\View\View;

class DatabaseInspectorController extends Controller
{
    public function index(DatabaseInspector $inspector): View
    {
        return view('admin.database.index', [
            'databaseOverview' => $inspector->overview(),
        ]);
    }

    public function show(string $table, Request $request, DatabaseInspector $inspector): View
    {
        return view('admin.database.show', [
            'table' => $inspector->table($table, max(1, (int) $request->integer('page', 1))),
        ]);
    }
}
