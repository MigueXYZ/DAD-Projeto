<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class GetTaskRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return true;
    }

    protected function prepareForValidation(): void
    {
        if ($this->has('project')) {
            if (in_array($this->project, ['0', '-1', 'null'])) {
                $this->merge(['project' => null]);
            }
        }
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'project' => 'sometimes|nullable|integer|exists:projects,id',
            'completed' => 'sometimes|in:0,1',
        ];
    }
}
