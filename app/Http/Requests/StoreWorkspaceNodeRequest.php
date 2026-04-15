<?php

namespace App\Http\Requests;

use App\Models\Workspace;
use App\Models\WorkspaceNode;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;
use Illuminate\Validation\Validator;

class StoreWorkspaceNodeRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user() !== null;
    }

    public function rules(): array
    {
        return [
            'workspace_id' => ['required', 'integer', 'exists:workspaces,id'],
            'parent_id' => ['nullable', 'integer', 'exists:workspace_nodes,id'],
            'type' => ['required', Rule::in(['folder', 'shortcut'])],
            'name' => ['required', 'string', 'max:255'],
            'url' => ['nullable', 'url', 'required_if:type,shortcut', 'max:2048'],
            'description' => ['nullable', 'string'],
        ];
    }

    public function withValidator(Validator $validator): void
    {
        $validator->after(function (Validator $validator): void {
            $workspace = Workspace::query()->find($this->integer('workspace_id'));

            if (! $workspace || $workspace->user_id !== $this->user()?->id) {
                $validator->errors()->add('workspace_id', __('You do not have access to that workspace.'));

                return;
            }

            $parentId = $this->input('parent_id');

            if ($parentId === null || $parentId === '') {
                if ($this->string('type')->toString() === 'shortcut') {
                    $validator->errors()->add('parent_id', __('Shortcuts must be created inside a folder.'));
                }

                return;
            }

            $parent = WorkspaceNode::query()->find((int) $parentId);

            if (! $parent || $parent->workspace_id !== $workspace->id) {
                $validator->errors()->add('parent_id', __('The selected parent folder is invalid.'));

                return;
            }

            if (! $parent->isFolder()) {
                $validator->errors()->add('parent_id', __('Items can only be created inside folders.'));
            }
        });
    }
}
