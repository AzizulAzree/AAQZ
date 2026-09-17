<section class="dw-widget" x-data="workspaceWidget(@js($workspaceWidget))" aria-label="Workspace quick access" @keydown.escape.window="if (note) closeNote()">
    <div class="dw-heading"><h2><x-project-icon name="folder"/> Workspaces</h2><a :href="workspaceUrl" aria-label="Open full workspace"><x-project-icon name="arrow"/></a></div>
    <template x-if="!workspaces.length"><div class="dw-empty"><p>Keep useful links and notes close by.</p><a href="{{ route('project.index') }}">Create a workspace <span aria-hidden="true">→</span></a></div></template>
    <div x-show="workspaces.length" x-cloak>
        <label class="sr-only" for="dw-workspace">Choose workspace</label>
        <select id="dw-workspace" x-model="workspaceId" @change="changeWorkspace()"><template x-for="workspace in workspaces" :key="workspace.id"><option :value="workspace.id" x-text="workspace.name"></option></template></select>
        <div class="dw-path"><button type="button" x-show="folderId" @click="back()" aria-label="Back to parent folder"><x-project-icon name="chevron"/></button><span x-text="folder?.name ?? 'All folders'"></span></div>
        <div class="dw-items">
            <template x-for="item in visibleItems" :key="item.id">
                <div class="dw-item">
                    <template x-if="item.type === 'shortcut'"><a :href="item.url" target="_blank" rel="noopener noreferrer"><span class="dw-icon"><x-project-icon name="link"/></span><span x-text="item.name"></span></a></template>
                    <template x-if="item.type !== 'shortcut'"><button type="button" @click="item.type === 'folder' ? openFolder(item) : openNote(item)"><span class="dw-icon" :class="{ 'dw-folder': item.type === 'folder' }"><template x-if="item.type === 'folder'"><x-project-icon name="folder"/></template><template x-if="item.type === 'note'"><x-project-icon name="document"/></template></span><span x-text="item.name"></span></button></template>
                </div>
            </template>
        </div>
        <p class="dw-empty" x-show="!items.length">This folder is empty.</p>
        <div class="dw-pages" x-show="pageCount > 1"><button type="button" @click="page--" :disabled="page === 0" aria-label="Previous workspace items"><x-project-icon name="chevron"/></button><span x-text="`${page + 1} / ${pageCount}`"></span><button type="button" @click="page++" :disabled="page + 1 >= pageCount" aria-label="Next workspace items"><x-project-icon name="chevron"/></button></div>
    </div>
    <template x-if="note">
        <div class="pr-note-overlay" @click.self="closeNote()">
            <section class="pr-note-dialog pr-note-readonly" role="dialog" aria-modal="true" aria-labelledby="dw-note-heading" @keydown="trap($event)" :aria-busy="loading">
                <div class="pr-note-reader">
                    <button id="dw-note-close" class="pr-note-reader-close" type="button" @click="closeNote()" aria-label="Close note"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.6" stroke-linecap="round" aria-hidden="true"><path d="m6 6 12 12M18 6 6 18"/></svg></button>
                    <article class="pr-note-paper" tabindex="0" aria-label="Note content"><h3 id="dw-note-heading" x-text="note.name"></h3><p class="dw-empty" x-show="loading" role="status">Loading note…</p><div x-show="error" role="alert"><p x-text="error"></p><button class="pr-button" type="button" @click="openNote(note)">Try again</button></div><div class="pr-note-text" x-show="!loading && !error" x-text="note.content"></div></article>
                </div>
            </section>
        </div>
    </template>
</section>
