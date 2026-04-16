<?php

namespace App\Http\Controllers;

use App\Http\Requests\UpdateStickyNoteRequest;
use App\Models\StickyNote;
use Illuminate\Http\JsonResponse;

class StickyNoteController extends Controller
{
    public function update(UpdateStickyNoteRequest $request): JsonResponse
    {
        /** @var StickyNote $stickyNote */
        $stickyNote = $request->user()->stickyNote()->updateOrCreate(
            [],
            [
                'content' => $request->filled('content')
                    ? $request->string('content')->toString()
                    : null,
                'position_x' => $request->integer('position_x'),
                'position_y' => $request->integer('position_y'),
                'is_collapsed' => $request->boolean('is_collapsed'),
            ],
        );

        return response()->json([
            'saved_at' => $stickyNote->updated_at?->toIso8601String(),
        ]);
    }
}
