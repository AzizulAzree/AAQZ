<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreWorkspaceNodeRequest;
use App\Http\Requests\StoreWorkspaceRequest;
use App\Models\RecentShortcut;
use App\Models\Workspace;
use App\Models\WorkspaceNode;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
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
                'manage_url' => route('project.workspaces.update', $workspace),
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
        $workspace = $request->user()->workspaces()->create([
            'name' => $request->string('name')->toString(),
            'sort_order' => (int) $request->user()->workspaces()->max('sort_order') + 1,
        ]);

        return redirect()
            ->route('project.index', ['workspace' => $workspace->id])
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
            ->route('project.index', array_filter(['workspace' => $workspace->id, 'folder' => $parentId]))
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

    public function updateWorkspace(Request $request, Workspace $workspace): JsonResponse
    {
        abort_unless($workspace->user_id === $request->user()->id, 403);
        $workspace->update($request->validate(['name' => ['required', 'string', 'max:255']]));

        return response()->json(['redirect' => route('project.index', ['workspace' => $workspace->id])]);
    }

    public function destroyWorkspace(Request $request, Workspace $workspace): JsonResponse
    {
        abort_unless($workspace->user_id === $request->user()->id, 403);
        $request->validate(['confirmed' => ['required', 'accepted']]);
        $workspace->delete();

        return response()->json(['redirect' => route('project.index')]);
    }

    public function updateNode(Request $request, WorkspaceNode $workspaceNode): JsonResponse
    {
        abort_unless($workspaceNode->workspace->user_id === $request->user()->id, 403);
        $rules = ['name' => ['required', 'string', 'max:255']];
        if ($workspaceNode->isShortcut()) {
            $rules['url'] = ['required', 'url:http,https', 'max:255'];
            $rules['description'] = ['nullable', 'string'];
        }
        $workspaceNode->update($request->validate($rules));

        return response()->json(['redirect' => route('project.index', array_filter([
            'workspace' => $workspaceNode->workspace_id, 'folder' => $workspaceNode->parent_id,
        ]))]);
    }

    public function destroyNode(Request $request, WorkspaceNode $workspaceNode): JsonResponse
    {
        abort_unless($workspaceNode->workspace->user_id === $request->user()->id, 403);
        $request->validate(['confirmed' => ['required', 'accepted']]);
        $destination = array_filter(['workspace' => $workspaceNode->workspace_id, 'folder' => $workspaceNode->parent_id]);
        $workspaceNode->delete();

        return response()->json(['redirect' => route('project.index', $destination)]);
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
                        'url' => $node->url,
                        'manage_url' => route('project.nodes.update', $node),
                        'description' => $node->description,
                        'open_url' => route('project.shortcuts.open', $node),
                    ];
                }

                return [
                    'id' => $node->id,
                    'type' => 'folder',
                    'name' => $node->name,
                    'manage_url' => route('project.nodes.update', $node),
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
