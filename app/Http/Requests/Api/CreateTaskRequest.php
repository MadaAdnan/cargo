<?php

namespace App\Http\Requests\Api;

use Illuminate\Foundation\Http\FormRequest;

class CreateTaskRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return true;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
           'user_id' => 'required|exists:users,id',
            'delegate_id' => 'nullable|exists:users,id',
            'from' => 'nullable|string|max:255',
            'sender_phone' => 'nullable|string|max:20',
            'is_sender' => 'nullable|boolean',
            'to' => 'nullable|string|max:255',
            'receive_phone' => 'nullable|string|max:20',
            'is_receive' => 'nullable|boolean',
            'task' => 'nullable|string|max:1000'
        ];
    }
}
