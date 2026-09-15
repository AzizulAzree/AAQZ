@props(['item', 'kind'])
<div class="pr-item-actions" x-data="{ menuOpen: false }" x-on:click.outside="menuOpen = false" x-on:keydown.escape.stop="menuOpen = false">
    <button type="button" class="pr-more-button" x-on:click="menuOpen = !menuOpen" :aria-expanded="menuOpen" :aria-label="'Actions for ' + {{ $item }}.name"><span aria-hidden="true">•••</span></button>
    <div class="pr-item-menu" x-show="menuOpen" x-cloak>
        <button type="button" x-on:click="menuOpen = false; openManage('edit', {{ $item }}, '{{ $kind }}')"><x-project-icon name="edit"/> Edit</button>
        <button type="button" class="pr-danger-text" x-on:click="menuOpen = false; openManage('delete', {{ $item }}, '{{ $kind }}')"><x-project-icon name="trash"/> Delete</button>
    </div>
</div>
