<x-app-layout>
    <x-slot name="header">
        <div class="flex items-center justify-between gap-4">
            <div>
                <h2 class="text-xl font-semibold leading-tight text-gray-800">
                    {{ __('Project') }}
                </h2>
                <p class="mt-1 text-sm text-gray-500">
                    {{ __('Keep your workspaces, folders, and quick links in one place.') }}
                </p>
            </div>
            <button type="button" class="project-primary-action" onclick="window.dispatchEvent(new CustomEvent('project-open-workspace-modal'))">
                {{ __('+ Add workspace') }}
            </button>
        </div>
    </x-slot>

    @php
        $folderCounter = 0;

        $renderTree = function (array $nodes, int $workspaceId, int $depth = 0) use (&$renderTree, &$folderCounter) {
            foreach ($nodes as $node) {
                if (($node['type'] ?? 'folder') === 'shortcut') {
                    echo '<li class="project-tree-item" style="--project-depth: '.$depth.'">';
                    echo '<a href="'.e($node['open_url']).'" class="project-shortcut">';
                    echo '<span class="project-shortcut-icon" aria-hidden="true">&#8599;</span>';
                    echo '<span class="project-shortcut-copy">';
                    echo '<span class="project-shortcut-title">'.e($node['name']).'</span>';
                    if (! empty($node['description'])) {
                        echo '<span class="project-shortcut-description">'.e($node['description']).'</span>';
                    }
                    echo '</span>';
                    echo '</a>';
                    echo '</li>';
                    continue;
                }

                $folderCounter++;
                $folderId = 'project-folder-'.$folderCounter;
                $children = $node['children'] ?? [];

                echo '<li class="project-tree-item" style="--project-depth: '.$depth.'">';
                echo '<section x-data="{ open: false, actionsOpen: false }" class="project-folder">';
                echo '<div class="project-folder-head">';
                echo '<div class="project-folder-left">';
                echo '<button type="button" class="project-folder-toggle" x-on:click="open = ! open" x-bind:aria-expanded="open.toString()" aria-controls="'.$folderId.'">';
                echo '<span class="project-folder-icon" aria-hidden="true" x-bind:class="open ? \'project-folder-icon-open\' : \'\'">&#8250;</span>';
                echo '<span class="project-folder-title-wrap">';
                echo '<span class="project-folder-title">'.e($node['name']).'</span>';
                echo '<span class="project-folder-meta">'.count($children).' '.str(count($children) === 1 ? 'item' : 'items')->toString().'</span>';
                echo '</span>';
                echo '</button>';
                echo '</div>';

                echo '<div class="project-folder-actions" x-on:keydown.escape.window="actionsOpen = false">';
                echo '<button type="button" class="project-folder-add" x-on:click="actionsOpen = ! actionsOpen" x-bind:aria-expanded="actionsOpen.toString()">';
                echo '<span aria-hidden="true">+</span>';
                echo '<span class="sr-only">'.e(__('Add inside folder')).'</span>';
                echo '</button>';
                echo '</div>';
                echo '</div>';

                echo '<div id="'.$folderId.'" x-show="open || actionsOpen" x-transition.opacity.duration.150ms class="project-folder-body">';
                echo '<div class="project-folder-inline-menu" x-show="actionsOpen" x-transition.opacity.duration.150ms x-cloak>';
                echo '<button type="button" class="project-folder-inline-action" x-on:click="actionsOpen = false; window.dispatchEvent(new CustomEvent(\'project-open-node-modal\', { detail: { type: \'folder\', workspaceId: '.$workspaceId.', parentId: '.$node['id'].', parentName: '.json_encode($node['name'], JSON_HEX_TAG | JSON_HEX_APOS | JSON_HEX_AMP | JSON_HEX_QUOT).' } }))">'.e(__('New folder')).'</button>';
                echo '<button type="button" class="project-folder-inline-action" x-on:click="actionsOpen = false; window.dispatchEvent(new CustomEvent(\'project-open-node-modal\', { detail: { type: \'shortcut\', workspaceId: '.$workspaceId.', parentId: '.$node['id'].', parentName: '.json_encode($node['name'], JSON_HEX_TAG | JSON_HEX_APOS | JSON_HEX_AMP | JSON_HEX_QUOT).' } }))">'.e(__('New shortcut')).'</button>';
                echo '</div>';
                if (! empty($children)) {
                    echo '<ul class="project-tree-list">';
                    $renderTree($children, $workspaceId, $depth + 1);
                    echo '</ul>';
                } else {
                    echo '<div class="project-folder-empty">'.e(__('No items yet. Add a folder or shortcut here.')).'</div>';
                }
                echo '</div>';

                echo '</section>';
                echo '</li>';
            }
        };
    @endphp

    <div
        x-data="{
            workspaceModalOpen: false,
            nodeModalOpen: false,
            nodeType: 'folder',
            nodeWorkspaceId: '',
            nodeParentId: '',
            nodeParentName: '',
            openWorkspaceModal() {
                this.workspaceModalOpen = true;
            },
            openNodeModal(type, workspaceId, parentId = '', parentName = '') {
                this.nodeType = type;
                this.nodeWorkspaceId = workspaceId;
                this.nodeParentId = parentId ?? '';
                this.nodeParentName = parentName ?? '';
                this.nodeModalOpen = true;
            },
        }"
        x-init="
            @if ($errors->has('name') && old('workspace_form') === '1')
                workspaceModalOpen = true;
            @endif
            @if ($errors->has('workspace_id') || $errors->has('parent_id') || $errors->has('type') || $errors->has('url') || $errors->has('description'))
                nodeModalOpen = true;
                nodeType = @js(old('type', 'folder'));
                nodeWorkspaceId = @js(old('workspace_id', ''));
                nodeParentId = @js(old('parent_id', ''));
                nodeParentName = @js(old('parent_name', ''));
            @endif
        "
        x-on:project-open-workspace-modal.window="openWorkspaceModal()"
        x-on:project-open-node-modal.window="openNodeModal($event.detail.type, $event.detail.workspaceId, $event.detail.parentId, $event.detail.parentName)"
        class="py-12"
    >
        <div class="mx-auto max-w-6xl space-y-6 sm:px-6 lg:px-8">
            <div class="project-page-layout">
                <div class="space-y-6">
                    <section class="bg-white shadow-sm sm:rounded-2xl">
                        <div class="project-shell">
                            <div class="project-shell-copy">
                                <p class="project-shell-kicker">{{ __('Workspaces') }}</p>
                                <h3 class="project-shell-title">{{ __('Everything stays organized without getting in your way.') }}</h3>
                                <p class="project-shell-description">
                                    {{ __('Create a workspace, group links inside folders, and keep quick access close to the work that matters.') }}
                                </p>
                            </div>

                            <div class="project-shell-actions">
                                <div class="project-ghost-action">
                                    {{ trans_choice('{1} :count workspace|[2,*] :count workspaces', count($workspaces), ['count' => count($workspaces)]) }}
                                </div>
                            </div>
                        </div>

                        <div class="border-t border-slate-200/80 bg-slate-50/70 px-5 py-5 sm:px-6">
                            @if (session('status') === 'workspace-created')
                                <p class="mb-4 rounded-xl border border-green-200 bg-green-50 px-4 py-3 text-sm text-green-700">
                                    {{ __('Workspace created.') }}
                                </p>
                            @elseif (session('status') === 'folder-created')
                                <p class="mb-4 rounded-xl border border-green-200 bg-green-50 px-4 py-3 text-sm text-green-700">
                                    {{ __('Folder created.') }}
                                </p>
                            @elseif (session('status') === 'shortcut-created')
                                <p class="mb-4 rounded-xl border border-green-200 bg-green-50 px-4 py-3 text-sm text-green-700">
                                    {{ __('Shortcut created.') }}
                                </p>
                            @endif

                            @if (count($workspaces) === 0)
                                <div class="project-empty-state">
                                    <h3 class="project-empty-title">{{ __('No workspaces yet') }}</h3>
                                    <p class="project-empty-copy">{{ __('Start by creating your first workspace, then add folders and shortcuts inside it.') }}</p>
                                    <button type="button" class="project-primary-action" x-on:click="openWorkspaceModal()">
                                        {{ __('Create workspace') }}
                                    </button>
                                </div>
                            @else
                                <div class="project-workspace-grid">
                                    @foreach ($workspaces as $workspace)
                                        <section class="project-tree-board">
                                            <div class="project-tree-toolbar">
                                                <div>
                                                    <p class="project-tree-label">{{ __('Workspace') }}</p>
                                                    <p class="project-tree-name">{{ $workspace['name'] }}</p>
                                                </div>
                                                <div class="project-tree-toolbar-actions">
                                                    <p class="project-tree-hint">
                                                        {{ trans_choice('{1} :count folder|[2,*] :count folders', $workspace['folder_count'], ['count' => $workspace['folder_count']]) }}
                                                    </p>
                                                    <button
                                                        type="button"
                                                        class="project-circle-action"
                                                        x-on:click="openNodeModal('folder', {{ $workspace['id'] }}, '', {{ \Illuminate\Support\Js::from($workspace['name']) }})"
                                                    >
                                                        <span aria-hidden="true">+</span>
                                                        <span class="sr-only">{{ __('Add folder') }}</span>
                                                    </button>
                                                </div>
                                            </div>

                                            @if (count($workspace['folders']) === 0)
                                                <div class="project-folder-empty mt-4">
                                                    {{ __('No folders yet. Add a folder to start organizing your links.') }}
                                                </div>
                                            @else
                                                <ul class="project-tree-list">
                                                    {!! $renderTree($workspace['folders'], $workspace['id']) !!}
                                                </ul>
                                            @endif
                                        </section>
                                    @endforeach
                                </div>
                            @endif
                        </div>
                    </section>
                </div>

                <aside class="project-recent-wrap">
                    <section class="project-recent-card">
                        <div class="project-recent-head">
                            <p class="project-tree-label">{{ __('Recently opened') }}</p>
                        </div>

                        <div class="project-recent-list">
                            @forelse ($recentShortcuts as $shortcut)
                                <a href="{{ $shortcut['open_url'] }}" class="project-recent-link">
                                    <span class="project-recent-link-title">{{ $shortcut['name'] }}</span>
                                    <span class="project-recent-link-context">{{ $shortcut['context'] }}</span>
                                </a>
                            @empty
                                <p class="project-recent-empty">{{ __('Open a shortcut and it will appear here.') }}</p>
                            @endforelse
                        </div>
                    </section>
                </aside>
            </div>
        </div>

        <template x-if="workspaceModalOpen">
            <div class="fixed inset-0 z-50 flex items-center justify-center px-4">
                <div class="absolute inset-0 bg-slate-900/40" x-on:click="workspaceModalOpen = false"></div>
                <div class="relative z-10 w-full max-w-lg rounded-2xl bg-white p-6 shadow-2xl">
                    <div class="flex items-start justify-between gap-4">
                        <div>
                            <p class="text-sm text-slate-500">{{ __('New workspace') }}</p>
                            <h3 class="mt-1 text-lg font-semibold text-slate-900">{{ __('Create a workspace') }}</h3>
                        </div>
                        <button type="button" class="project-modal-close" x-on:click="workspaceModalOpen = false">{{ __('Close') }}</button>
                    </div>

                    <form method="POST" action="{{ route('project.workspaces.store') }}" class="mt-6 space-y-4">
                        @csrf
                        <input type="hidden" name="workspace_form" value="1">

                        <div>
                            <x-input-label for="workspace-name" :value="__('Workspace name')" />
                            <x-text-input id="workspace-name" name="name" type="text" class="mt-1 block w-full" :value="old('name')" required maxlength="255" />
                            <x-input-error class="mt-2" :messages="$errors->get('name')" />
                        </div>

                        <div class="flex items-center gap-3">
                            <x-primary-button>{{ __('Create workspace') }}</x-primary-button>
                            <p class="text-xs text-slate-500">{{ __('You can add folders after the workspace is created.') }}</p>
                        </div>
                    </form>
                </div>
            </div>
        </template>

        <template x-if="nodeModalOpen">
            <div class="fixed inset-0 z-50 flex items-center justify-center px-4">
                <div class="absolute inset-0 bg-slate-900/40" x-on:click="nodeModalOpen = false"></div>
                <div class="relative z-10 w-full max-w-lg rounded-2xl bg-white p-6 shadow-2xl">
                    <div class="flex items-start justify-between gap-4">
                        <div>
                            <p class="text-sm text-slate-500" x-text="nodeType === 'shortcut' ? '{{ __('New shortcut') }}' : '{{ __('New folder') }}'"></p>
                            <h3 class="mt-1 text-lg font-semibold text-slate-900" x-text="nodeType === 'shortcut' ? '{{ __('Add shortcut') }}' : '{{ __('Add folder') }}'"></h3>
                            <p class="mt-1 text-xs text-slate-500" x-show="nodeParentName">
                                {{ __('Inside') }} <span x-text="nodeParentName"></span>
                            </p>
                        </div>
                        <button type="button" class="project-modal-close" x-on:click="nodeModalOpen = false">{{ __('Close') }}</button>
                    </div>

                    <form method="POST" action="{{ route('project.nodes.store') }}" class="mt-6 space-y-4">
                        @csrf
                        <input type="hidden" name="workspace_id" :value="nodeWorkspaceId">
                        <input type="hidden" name="parent_id" :value="nodeParentId">
                        <input type="hidden" name="type" :value="nodeType">
                        <input type="hidden" name="parent_name" :value="nodeParentName">

                        <div>
                            <x-input-label for="node-name" :value="__('Name')" />
                            <x-text-input id="node-name" name="name" type="text" class="mt-1 block w-full" :value="old('name')" required maxlength="255" />
                            <x-input-error class="mt-2" :messages="$errors->get('name')" />
                        </div>

                        <div x-show="nodeType === 'shortcut'" x-cloak>
                            <x-input-label for="node-url" :value="__('Link URL')" />
                            <x-text-input id="node-url" name="url" type="url" class="mt-1 block w-full" :value="old('url')" placeholder="https://example.com" />
                            <x-input-error class="mt-2" :messages="$errors->get('url')" />
                        </div>

                        <div>
                            <x-input-label for="node-description" :value="__('Description')" />
                            <textarea id="node-description" name="description" rows="3" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-gray-500 focus:ring-gray-500">{{ old('description') }}</textarea>
                            <x-input-error class="mt-2" :messages="$errors->get('description')" />
                        </div>

                        <x-input-error class="mt-2" :messages="$errors->get('workspace_id')" />
                        <x-input-error class="mt-2" :messages="$errors->get('parent_id')" />
                        <x-input-error class="mt-2" :messages="$errors->get('type')" />

                        <div class="flex items-center gap-3">
                            <button type="submit" class="inline-flex items-center rounded-md bg-gray-800 px-4 py-2 text-xs font-semibold uppercase tracking-widest text-white transition hover:bg-gray-700" x-text="nodeType === 'shortcut' ? '{{ __('Create shortcut') }}' : '{{ __('Create folder') }}'"></button>
                            <p class="text-xs text-slate-500" x-show="nodeType === 'shortcut'">{{ __('Shortcuts can only be created inside folders.') }}</p>
                        </div>
                    </form>
                </div>
            </div>
        </template>
    </div>
</x-app-layout>
