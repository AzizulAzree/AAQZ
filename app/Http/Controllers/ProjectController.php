<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreWorkspaceNodeRequest;
use App\Http\Requests\StoreWorkspaceRequest;
use App\Models\RecentShortcut;
use App\Models\Workspace;
use App\Models\WorkspaceNode;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Collection;
use Illuminate\View\View;

class ProjectController extends Controller
{
    public function index(): View
    {
        $user = request()->user();

        $workspaces = $user->workspaces()
            ->with(['nodes' => fn ($query) => $query->orderBy('sort_order')->orderBy('name')])
            ->get()
            ->map(fn (Workspace $workspace) => [
                'id' => $workspace->id,
                'name' => $workspace->name,
                'folders' => $this->buildTree($workspace->nodes),
                'folder_count' => $workspace->nodes->where('type', 'folder')->count(),
                'shortcut_count' => $workspace->nodes->where('type', 'shortcut')->count(),
            ]);

        $recentShortcuts = $user->recentShortcuts()
            ->with('workspaceNode.workspace')
            ->take(6)
            ->get()
            ->filter(fn (RecentShortcut $recent) => $recent->workspaceNode?->isShortcut())
            ->map(fn (RecentShortcut $recent) => [
                'name' => $recent->workspaceNode->name,
                'context' => $recent->workspaceNode->workspace?->name ?? __('Workspace'),
                'open_url' => route('project.shortcuts.open', $recent->workspaceNode),
            ]);

        return view('project.index', [
            'workspaces' => $workspaces,
            'recentShortcuts' => $recentShortcuts,
        ]);
    }

    public function storeWorkspace(StoreWorkspaceRequest $request): RedirectResponse
    {
        $request->user()->workspaces()->create([
            'name' => $request->string('name')->toString(),
            'sort_order' => (int) $request->user()->workspaces()->max('sort_order') + 1,
        ]);

        return redirect()
            ->route('project.index')
            ->with('status', 'workspace-created');
    }

    public function storeNode(StoreWorkspaceNodeRequest $request): RedirectResponse
    {
        $workspace = $request->user()->workspaces()->findOrFail($request->integer('workspace_id'));
        $parentId = $request->filled('parent_id') ? $request->integer('parent_id') : null;

        WorkspaceNode::create([
            'workspace_id' => $workspace->id,
            'parent_id' => $parentId,
            'type' => $request->string('type')->toString(),
            'name' => $request->string('name')->toString(),
            'url' => $request->string('type')->toString() === 'shortcut'
                ? $request->string('url')->toString()
                : null,
            'description' => $request->filled('description')
                ? $request->string('description')->toString()
                : null,
            'sort_order' => $this->nextSortOrder($workspace->id, $parentId),
        ]);

        return redirect()
            ->route('project.index')
            ->with('status', $request->string('type')->toString() === 'folder' ? 'folder-created' : 'shortcut-created');
    }

    public function openShortcut(WorkspaceNode $workspaceNode): RedirectResponse
    {
        abort_unless($workspaceNode->isShortcut(), 404);
        abort_unless($workspaceNode->workspace->user_id === request()->user()?->id, 403);

        RecentShortcut::updateOrCreate(
            [
                'user_id' => request()->user()->id,
                'workspace_node_id' => $workspaceNode->id,
            ],
            [
                'opened_at' => now(),
            ],
        );

        return redirect()->away($workspaceNode->url);
    }

    private function buildTree(Collection $nodes, ?int $parentId = null): array
    {
        return $nodes
            ->filter(fn (WorkspaceNode $node) => $node->parent_id === $parentId)
            ->sortBy(['sort_order', 'name'])
            ->map(function (WorkspaceNode $node) use ($nodes): array {
                if ($node->isShortcut()) {
                    return [
                        'id' => $node->id,
                        'type' => 'shortcut',
                        'name' => $node->name,
                        'description' => $node->description,
                        'open_url' => route('project.shortcuts.open', $node),
                    ];
                }

                return [
                    'id' => $node->id,
                    'type' => 'folder',
                    'name' => $node->name,
                    'children' => $this->buildTree($nodes, $node->id),
                ];
            })
            ->values()
            ->all();
    }

    private function nextSortOrder(int $workspaceId, ?int $parentId): int
    {
        return (int) WorkspaceNode::query()
            ->where('workspace_id', $workspaceId)
            ->where('parent_id', $parentId)
            ->max('sort_order') + 1;
    }
}
