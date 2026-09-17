<x-app-layout>
    <div class="pr-page" x-data="projectBrowser(@js($workspaces), @js($recentShortcuts->values()))"
        data-store-node-url="{{ route('project.nodes.store') }}"
        x-on:beforeunload.window="if (noteDirty) { $event.preventDefault(); $event.returnValue = ''; }"
        x-on:keydown.escape.window="closeModals()"
        x-init="$nextTick(() => {
            @if ($errors->any())
                @if (old('workspace_form') === '1') openWorkspaceModal();
                @else
                    nodeType = @js(old('type', 'folder')); nodeWorkspaceId = @js(old('workspace_id', '')); nodeParentId = @js(old('parent_id', '')); nodeParentName = @js(old('parent_name', '')); nodeModalOpen = true;
                @endif
            @endif
        })">
        <header class="pr-page-header">
            <div><p class="pr-eyebrow">YOUR WORK, IN ONE PLACE</p><h1>Projects</h1></div>
        </header>
        @if (session('status'))
            <p class="pr-notice" role="status">{{ match(session('status')) { 'workspace-created' => 'Workspace created.', 'folder-created' => 'Folder created.', 'shortcut-created' => 'Link saved.', default => '' } }}</p>
        @endif
        <div class="pr-browser">
            <aside class="pr-sidebar" aria-label="Workspaces">
                <button type="button" class="pr-mobile-switch" :aria-expanded="mobileWorkspaceOpen" aria-controls="pr-workspace-panel" x-on:click="mobileWorkspaceOpen = !mobileWorkspaceOpen">
                    <x-project-icon name="grid"/><span><small>Workspace</small><strong x-text="active?.name ?? 'Choose workspace'"></strong></span><x-project-icon name="chevron" class="pr-switch-chevron"/>
                </button>
                <div id="pr-workspace-panel" class="pr-workspace-panel" :class="{ 'is-open': mobileWorkspaceOpen }">
                <div class="pr-sidebar-heading"><span>Workspaces</span><span class="pr-count" x-text="workspaces.length"></span></div>
                <nav class="pr-workspace-list" aria-label="Choose workspace">
                    <template x-for="workspace in workspaces" :key="workspace.id">
                        <div class="pr-workspace-row" :class="{ 'is-active': activeId === workspace.id }">
                        <button type="button" class="pr-workspace" :class="{ 'is-active': activeId === workspace.id }" :aria-current="activeId === workspace.id ? 'true' : null" x-on:click="navigate(workspace.id)">
                            <x-project-icon name="grid"/><span x-text="workspace.name"></span><span class="pr-workspace-count" x-text="workspace.shortcut_count + (workspace.note_count ?? 0)"></span>
                        </button>
                        <x-project-actions item="workspace" kind="workspace"/>
                        </div>
                    </template>
                </nav>
                <button class="pr-workspace pr-workspace-add" type="button" x-on:click="openWorkspaceModal()"><x-project-icon name="plus"/><span>New workspace</span></button>
                <div class="pr-sidebar-footer"><x-project-icon name="folder"/><span>Your links and notes, organized.</span></div>
                </div>
            </aside>
            <main class="pr-content">
                <div class="pr-welcome" x-show="!active">
                    <span class="pr-empty-icon"><x-project-icon name="grid"/></span>
                    <h2>A home for your project resources</h2>
                    <p>Keep useful links and important notes together.</p>
                    <button type="button" class="pr-button pr-button-dark" x-on:click="openWorkspaceModal()"><x-project-icon name="plus"/> Create workspace</button>
                </div>
                <section x-show="active" x-cloak>
                    <div class="pr-content-header">
                        <div class="pr-heading-copy"><p class="pr-eyebrow">WORKSPACE</p><h2 x-text="active?.name"></h2><p class="pr-muted" x-text="`${active?.folder_count ?? 0} folders · ${active?.shortcut_count ?? 0} links · ${active?.note_count ?? 0} notes`"></p></div>
                        <label class="pr-search"><x-project-icon name="search"/><input type="search" x-model="query" placeholder="Search this workspace" aria-label="Search this workspace"></label>
                    </div>
                    <div class="pr-toolbar">
                        <div class="pr-mobile-path">
                            <button type="button" x-show="folderId" x-on:click="navigate(activeId, parentFolder?.id ?? null)" :aria-label="'Back to ' + (parentFolder?.name ?? 'All folders')"><x-project-icon name="chevron"/><span>Back</span></button>
                            <h2 x-text="folder?.name ?? 'All folders'"></h2>
                        </div>
                        <nav class="pr-breadcrumbs" aria-label="Folder path">
                            <button type="button" x-on:click="navigate(activeId)" :aria-current="!folderId ? 'page' : null">All folders</button>
                            <template x-for="crumb in crumbs" :key="crumb.id"><span><x-project-icon name="chevron"/><button type="button" x-text="crumb.name" x-on:click="navigate(activeId, crumb.id)" :aria-current="folderId === crumb.id ? 'page' : null"></button></span></template>
                        </nav>
                        <div class="pr-actions">
                            <div class="pr-add-item" x-on:click.outside="addItemOpen = false">
                                <button class="pr-button pr-button-dark" type="button" x-on:click="addItemOpen = !addItemOpen" :aria-expanded="addItemOpen" aria-controls="pr-add-menu"><x-project-icon name="plus"/> Add item</button>
                                <div id="pr-add-menu" class="pr-item-menu" x-show="addItemOpen" x-cloak>
                                    <button type="button" x-on:click="openNodeModal('folder')"><x-project-icon name="folder"/> New folder</button>
                                    <button type="button" x-show="folderId" x-on:click="openNodeModal('shortcut')"><x-project-icon name="link"/> Link shortcut</button>
                                    <button type="button" x-show="folderId" x-on:click="openNote()"><x-project-icon name="document"/> Note</button>
                                </div>
                            </div>
                        </div>
                    </div>
                    <p class="pr-result-label" x-show="query.trim()" x-text="`${items.length} results in this workspace`" role="status"></p>
                    <div class="pr-folder-grid" x-show="items.length" aria-label="Folder contents">
                        <template x-for="item in items" :key="item.id">
                            <div class="pr-grid-item">
                                <template x-if="item.type === 'folder'">
<div class="pr-folder-card">
                            <x-project-actions item="item" kind="folder"/>
                            <button class="pr-folder-open" type="button" x-on:click="navigate(activeId, item.id)">
                                <span class="pr-folder-symbol" aria-hidden="true">
                                    <svg class="pr-folder-art" viewBox="0 0 120 96" fill="none">
                                        <path d="M9 22a7 7 0 0 1 7-7h27l11 10h50a7 7 0 0 1 7 7v46a8 8 0 0 1-8 8H17a8 8 0 0 1-8-8Z" fill="#8fa681" stroke="#7e9771"/>
                                        <path d="M19 32h82v44H19z" fill="#f1f4e9"/>
                                        <path d="M16 37h88a7 7 0 0 1 7 8l-5 34a8 8 0 0 1-8 7H22a8 8 0 0 1-8-7L9 45a7 7 0 0 1 7-8Z" fill="#c9d6b7" stroke="#a4b992"/>
                                        <path d="M18 40h84" stroke="#e6eddc" stroke-width="2" stroke-linecap="round"/>
                                    </svg>
                                </span>
                                <strong x-text="item.name"></strong><span class="pr-muted" x-text="`${item.children.length} ${item.children.length === 1 ? 'item' : 'items'}`"></span>
                                <span class="pr-folder-description" x-show="item.description" x-text="item.description"></span>
                                <span class="pr-item-path" x-show="query.trim() && item.trail.length" x-text="item.trail.map(p => p.name).join(' / ')"></span>
                            </button>
                            </div>
                                </template>
                                <template x-if="item.type === 'shortcut'">
<div class="pr-folder-card pr-shortcut-card">
                            <a class="pr-folder-open pr-shortcut-open" :href="item.open_url" target="_blank" rel="noopener noreferrer">
                                <span class="pr-folder-symbol" aria-hidden="true">
                                    <svg class="pr-folder-art" viewBox="0 0 120 96" fill="none">
                                        <rect x="13" y="15" width="94" height="70" rx="9" fill="#e9efe2" stroke="#a4b992"/>
                                        <path d="M13 24a9 9 0 0 1 9-9h76a9 9 0 0 1 9 9v9H13Z" fill="#b7c9a7"/>
                                        <path d="M24 24h1m7 0h1m7 0h1" stroke="#6e8962" stroke-width="3" stroke-linecap="round"/>
                                        <path d="m55 55 6-6a9 9 0 0 1 13 13l-6 6m-3-16-6 6m-3-3-6 6a9 9 0 0 0 13 13l6-6" stroke="#7b976c" stroke-width="4" stroke-linecap="round" stroke-linejoin="round" transform="translate(-3 -4)"/>
                                        <rect x="8" y="65" width="27" height="25" rx="6" fill="#fff" stroke="#b2c5a4"/>
                                        <path d="M15 82v-5h12m-5-5 5 5-5 5" stroke="#648257" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
                                    </svg>
                                </span>
                                    <strong x-text="item.name"></strong>
                                    <span class="pr-folder-description" x-show="item.description" x-text="item.description"></span>
                                    <span class="pr-shortcut-url" x-text="item.url"></span>
                                    <span class="pr-item-path" x-show="query.trim()" x-text="item.trail.map(p => p.name).join(' / ')"></span>
                            </a>
                            <x-project-actions item="item" kind="link"/>
                            </div>
                                </template>
                                <template x-if="item.type === 'note'">
<div class="pr-folder-card">
                                <button class="pr-folder-open" type="button" x-on:click="openNote(item)">
                                    <span class="pr-folder-symbol" aria-hidden="true">
                                        <svg class="pr-folder-art" viewBox="0 0 120 96" fill="none">
                                            <path d="M32 9h40l20 20v53a6 6 0 0 1-6 6H32a6 6 0 0 1-6-6V15a6 6 0 0 1 6-6Z" fill="#eff3e7" stroke="#a4b992"/>
                                            <path d="M72 9v14a6 6 0 0 0 6 6h14" fill="#c9d6b7" stroke="#a4b992" stroke-linejoin="round"/>
                                            <path d="M39 41h39M39 52h39M39 63h31M39 74h22" stroke="#afc09d" stroke-width="3" stroke-linecap="round"/>
                                        </svg>
                                    </span>
                                    <strong x-text="item.name"></strong>
                                    <span class="pr-item-path" x-show="query.trim()" x-text="item.trail.map(p => p.name).join(' / ')"></span>
                                </button>
                                <x-project-actions item="item" kind="note"/>
                            </div>
                                </template>
                            </div>
                        </template>
                    </div>
                    <div class="pr-empty" x-show="!items.length">
                        <span class="pr-empty-icon"><template x-if="query.trim()"><x-project-icon name="search"/></template><template x-if="!query.trim()"><x-project-icon name="folder"/></template></span>
                        <h3 x-text="query.trim() ? 'No matches found' : folderId ? 'This folder is empty' : 'Your workspace is ready'"></h3>
                        <p x-text="query.trim() ? 'Try another name or keyword.' : folderId ? 'Add a link or save an important note here.' : 'Add a folder for your links and notes.'"></p>
                        <button type="button" class="pr-button pr-button-dark" x-show="!query.trim()" x-on:click="openNodeModal(folderId ? 'shortcut' : 'folder')"><x-project-icon name="plus"/><span x-text="folderId ? 'Add link' : 'New folder'"></span></button>
                        <button type="button" class="pr-button" x-show="!query.trim() && folderId" x-on:click="openNote()"><x-project-icon name="document"/> Add note</button>
                    </div>
                </section>
            </main>
        </div>
        <section class="pr-recent" aria-label="Recently opened">
            <div class="pr-section-title"><x-project-icon name="clock"/><h2>Recently opened</h2></div>
            <div class="pr-recent-grid">
                @forelse ($recentShortcuts as $shortcut)
                <a class="pr-recent-item" href="{{ $shortcut['open_url'] }}" target="_blank" rel="noopener noreferrer"><span class="pr-link-symbol"><x-project-icon name="link"/></span><span><strong>{{ $shortcut['name'] }}</strong><small>{{ $shortcut['context'] }}</small></span><x-project-icon name="arrow"/></a>
                @empty
                <p class="pr-muted">Your recently opened links will appear here.</p>
                @endforelse
            </div>
        </section>
        <template x-if="noteOpen">
            <div class="pr-note-overlay" x-on:click.self="closeModals()">
                <section class="pr-note-dialog" :class="{ 'pr-note-readonly': !noteEditing && !discardNote }" role="dialog" aria-modal="true" aria-labelledby="pr-note-heading" x-on:keydown="trapFocus($event)" :aria-busy="busy">
                    <template x-if="!discardNote && !noteEditing">
                        <div class="pr-note-reader">
                            <button class="pr-note-reader-close" type="button" x-on:click="closeModals()" aria-label="Close note"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.6" stroke-linecap="round" aria-hidden="true"><path d="m6 6 12 12M18 6 6 18"/></svg></button>
                            <article id="pr-note-paper" class="pr-note-paper" tabindex="0" aria-label="Note content">
                                <h3 id="pr-note-heading" x-text="noteName"></h3>
                                <div class="pr-note-text" x-text="noteContent"></div>
                            </article>
                        </div>
                    </template>
                    <template x-if="!discardNote && noteEditing">
                        <form x-on:submit.prevent="saveNote()">
                            <div class="pr-note-header"><h2 id="pr-note-heading"><x-project-icon name="document"/><span x-text="noteItem ? 'Note' : 'New note'"></span></h2><button class="pr-button" type="button" x-on:click="closeModals()" :disabled="busy">Close</button></div>
                            <div class="pr-note-fields">
                                <label for="pr-note-name">Title</label><input id="pr-note-name" x-model="noteName" required maxlength="255" :disabled="busy" placeholder="Give your note a title">
                                <label for="pr-note-content">Note</label><textarea id="pr-note-content" x-model="noteContent" required maxlength="100000" :disabled="busy" placeholder="Write your note…"></textarea>
                                <p class="pr-manage-error" role="alert" x-show="noteError" x-text="noteError"></p>
                            </div>
                            <div class="pr-note-footer"><button type="button" class="pr-button" x-on:click="closeModals()" :disabled="busy">Cancel</button><button type="submit" class="pr-button pr-button-dark" :disabled="busy || !noteDirty" x-text="busy ? 'Saving…' : 'Save changes'"></button></div>
                        </form>
                    </template>
                    <template x-if="discardNote">
                        <div class="pr-note-discard"><x-project-icon name="warning"/><h2 id="pr-note-heading">Discard changes?</h2><p>Your changes to this note have not been saved.</p><div class="pr-note-footer"><button id="pr-keep-editing" type="button" class="pr-button pr-button-dark" x-on:click="discardNote = false; $nextTick(() => document.getElementById('pr-note-name')?.focus())">Keep editing</button><button type="button" class="pr-button pr-button-danger" x-on:click="discardNoteChanges()">Discard changes</button></div></div>
                    </template>
                </section>
            </div>
        </template>
        <template x-if="manage">
            <div class="pr-manage-overlay" x-on:click.self="closeModals()">
                <section class="pr-manage-dialog" :role="manage.mode === 'delete' ? 'alertdialog' : 'dialog'" aria-modal="true" aria-labelledby="manage-title" aria-describedby="manage-warning" x-on:keydown="trapFocus($event)" :aria-busy="busy">
                    <span class="pr-delete-symbol" x-show="manage.mode === 'delete'"><x-project-icon name="warning"/></span>
                    <h2 id="manage-title" x-text="`${manage.mode === 'delete' ? 'Delete' : 'Edit'} ${manage.kind}`"></h2>
                    <form x-on:submit.prevent="submitManage()">
                        <template x-if="manage.mode === 'edit'">
                            <div class="pr-manage-fields">
                                <label for="manage-name">Name</label><input id="manage-name" x-model="manageName" required maxlength="255" :disabled="busy">
                                <template x-if="manage.kind === 'link'"><div class="pr-manage-fields">
                                    <label for="manage-url">Link URL</label><input id="manage-url" type="url" x-model="manageUrl" required maxlength="255" :disabled="busy">
                                    <label for="manage-description">Description (optional)</label><textarea id="manage-description" x-model="manageDescription" rows="3" :disabled="busy"></textarea>
                                </div></template>
                            </div>
                        </template>
                        <div id="manage-warning" x-show="manage.mode === 'delete'" class="pr-delete-warning"><strong x-text="manage.item.name"></strong><p x-text="deleteWarning"></p><p class="pr-danger-text">This cannot be undone.</p></div>
                        <p class="pr-manage-error" role="alert" x-show="manageError" x-text="manageError"></p>
                        <div class="pr-manage-footer"><button id="manage-cancel" class="pr-button" type="button" x-on:click="closeModals()" :disabled="busy">Cancel</button><button class="pr-button" :class="manage.mode === 'delete' ? 'pr-button-danger' : 'pr-button-dark'" type="submit" :disabled="busy" x-text="busy ? 'Working…' : manage.mode === 'delete' ? `Delete ${manage.kind}` : 'Save changes'"></button></div>
                    </form>
                </section>
            </div>
        </template>
        <template x-if="workspaceModalOpen">
            <div class="pr-create-overlay fixed inset-0 z-50 flex items-center justify-center px-4">
                <div class="absolute inset-0 bg-slate-900/40" x-on:click="workspaceModalOpen = false"></div>
                <div class="pr-dialog relative z-10 w-full max-w-lg rounded-2xl bg-white p-6 shadow-2xl" role="dialog" aria-modal="true" aria-label="Create item" x-on:keydown="trapFocus($event)">
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

                        </div>
                    </form>
                </div>
            </div>
        </template>

        <template x-if="nodeModalOpen">
            <div class="pr-create-overlay fixed inset-0 z-50 flex items-center justify-center px-4">
                <div class="absolute inset-0 bg-slate-900/40" x-on:click="nodeModalOpen = false"></div>
                <div class="pr-dialog relative z-10 w-full max-w-lg rounded-2xl bg-white p-6 shadow-2xl" role="dialog" aria-modal="true" aria-label="Create item" x-on:keydown="trapFocus($event)">
                    <div class="flex items-start justify-between gap-4">
                        <div>
                            <p class="text-sm text-slate-500" x-text="nodeType === 'shortcut' ? '{{ __('New link') }}' : '{{ __('New folder') }}'"></p>
                            <h3 class="mt-1 text-lg font-semibold text-slate-900" x-text="nodeType === 'shortcut' ? '{{ __('Add link') }}' : '{{ __('Add folder') }}'"></h3>
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
                            <x-input-label for="node-description" :value="__('Description (optional)')" />
                            <textarea id="node-description" name="description" rows="3" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-gray-500 focus:ring-gray-500">{{ old('description') }}</textarea>
                            <x-input-error class="mt-2" :messages="$errors->get('description')" />
                        </div>

                        <x-input-error class="mt-2" :messages="$errors->get('workspace_id')" />
                        <x-input-error class="mt-2" :messages="$errors->get('parent_id')" />
                        <x-input-error class="mt-2" :messages="$errors->get('type')" />

                        <div class="flex items-center gap-3">
                            <button type="submit" class="inline-flex items-center rounded-md bg-gray-800 px-4 py-2 text-xs font-semibold uppercase tracking-widest text-white transition hover:bg-gray-700" x-text="nodeType === 'shortcut' ? '{{ __('Save link') }}' : '{{ __('Create folder') }}'"></button>

                        </div>
                    </form>
                </div>
            </div>
        </template>
    </div>
</x-app-layout>
