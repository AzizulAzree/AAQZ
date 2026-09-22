<header class="fm-dialog-heading">
    <div><h2 id="fm-editor-title" x-ref="editorTitle" tabindex="-1">Edit form</h2><p>Customer form</p></div>
    <button type="button" class="fm-icon-button" aria-label="Close form editor" @click="close()" :disabled="busy"><span aria-hidden="true">×</span></button>
</header>
<div class="fq-toolbar">
    <div class="fq-tabs" aria-label="Editor view"><button type="button" @click="preview = false" :aria-pressed="!preview" :class="{ 'is-active': !preview }">Questions <span x-text="rows.length"></span></button><button type="button" @click="preview = true" :aria-pressed="preview" :class="{ 'is-active': preview }">Preview</button></div>
    <button class="fm-button fm-primary" type="button" @click="addField()" :disabled="busy || rows.length >= 100"><x-project-icon name="plus"/> Add question</button>
</div>
<div class="fm-editor-body fq-canvas">
    <div x-show="!preview">
        <template x-for="(row, index) in rows" :key="row.id">
            <section class="fq-question" :class="{ 'is-selected': selectedId === row.id }" @focusin="selectedId = row.id" @click="selectedId = row.id">
                <div class="fq-question-top"><span x-text="'Question ' + (index + 1)"></span><div><button class="fm-icon-button" type="button" @click="moveField(index, -1)" :disabled="busy || index === 0" :aria-label="'Move question ' + (index + 1) + ' up'"><span aria-hidden="true">↑</span></button><button class="fm-icon-button" type="button" @click="moveField(index, 1)" :disabled="busy || index === rows.length - 1" :aria-label="'Move question ' + (index + 1) + ' down'"><span aria-hidden="true">↓</span></button></div></div>
                <div class="fq-question-main">
                    <label class="fq-title"><span class="sr-only" x-text="'Question ' + (index + 1) + ' title'"></span><input :id="'field-label-' + row.id" x-model="row.title" maxlength="255" placeholder="Untitled question" :disabled="busy"></label>
                    <label class="fq-type"><span class="sr-only" x-text="'Question ' + (index + 1) + ' type'"></span><select x-model="row.type" :disabled="busy"><option value="text">Short answer</option><option value="radio button">Multiple choice</option><option value="checkbox">Checkboxes</option><option value="dropdown">Dropdown</option><option value="number">Number</option><option value="money">Amount</option><option value="date">Date</option></select></label>
                </div>
                <div class="fq-answer-sample" x-show="!isChoice(row.type)" aria-hidden="true"><span x-text="{ text: 'Short answer text', number: 'Number', money: '0.00', date: 'Day / Month / Year' }[row.type]"></span><x-project-icon name="clock" x-show="row.type === 'date'"/></div>
                <div class="fq-options" x-show="isChoice(row.type)">
                    <template x-for="(option, optionIndex) in options(row)" :key="optionIndex">
                        <div class="fq-option"><span class="fq-option-marker" :class="{ 'fq-square': row.type === 'checkbox', 'fq-number': row.type === 'dropdown' }" aria-hidden="true" x-text="row.type === 'dropdown' ? (optionIndex + 1) + '.' : ''"></span><label><span class="sr-only" x-text="'Question ' + (index + 1) + ', option ' + (optionIndex + 1)"></span><input :id="'option-' + row.id + '-' + optionIndex" :value="option" @input="setOption(row, optionIndex, $event.target.value)" @keydown.enter.prevent="addOption(row)" :placeholder="'Option ' + (optionIndex + 1)" maxlength="255" :disabled="busy"></label><button class="fm-icon-button" type="button" @click="removeOption(row, optionIndex)" :disabled="busy || options(row).length === 1" :aria-label="'Remove option ' + (optionIndex + 1)"><span aria-hidden="true">×</span></button></div>
                    </template>
                    <button class="fq-add-option" type="button" @click="addOption(row)" :disabled="busy"><x-project-icon name="plus"/> Add option</button>
                </div>
                <footer class="fq-question-footer"><div><button class="fq-action" type="button" @click.stop="duplicateField(index)" :disabled="busy || rows.length >= 100"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.7" aria-hidden="true"><rect x="8" y="8" width="12" height="13" rx="2"/><path d="M15 8V3H3v13h5"/></svg>Duplicate</button><button class="fq-action fq-delete" type="button" @click="removeField(row.id)" :disabled="busy"><x-project-icon name="trash"/>Delete</button></div><label class="fq-required"><input type="checkbox" x-model="row.required" :disabled="busy"><span class="fq-switch" aria-hidden="true"></span><span>Required</span></label></footer>
            </section>
        </template>
        <div class="fm-empty" x-show="!rows.length"><h3>Start with your first question</h3></div>
        <button class="fm-add-field" type="button" @click="selectedId = null; addField()" :disabled="busy || rows.length >= 100"><x-project-icon name="plus"/> Add question</button>
    </div>
    <div class="fq-preview" x-show="preview">
        <header><h3>Customer form</h3><p>Preview · Responses aren’t submitted here.</p></header>
        <template x-for="(row, index) in rows" :key="row.id">
            <section class="fq-preview-question"><h4><span x-text="row.title || 'Untitled question'"></span><span class="fq-required-star" x-show="row.required" aria-label="Required"> *</span></h4>
                <template x-if="!isChoice(row.type)"><input :type="['money', 'number'].includes(row.type) ? 'number' : row.type" :aria-label="row.title || 'Question ' + (index + 1)" :placeholder="row.type === 'money' ? '0.00' : 'Your answer'" :step="row.type === 'money' ? '0.01' : '1'"></template>
                <template x-if="row.type === 'dropdown'"><select :aria-label="row.title || 'Question ' + (index + 1)"><option value="">Select an option</option><template x-for="(option, i) in options(row)" :key="i"><option x-text="option || 'Option ' + (i + 1)"></option></template></select></template>
                <template x-if="['radio button', 'checkbox'].includes(row.type)"><div><template x-for="(option, i) in options(row)" :key="i"><label class="fq-preview-choice"><input :type="row.type === 'checkbox' ? 'checkbox' : 'radio'" :name="'preview-' + row.id" :value="option"><span x-text="option || 'Option ' + (i + 1)"></span></label></template></div></template>
            </section>
        </template>
        <p class="fm-empty" x-show="!rows.length">Add a question to preview your form.</p>
    </div>
</div>
