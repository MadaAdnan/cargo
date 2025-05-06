<?php

namespace App\Http\Requests\Api;

use Illuminate\Foundation\Http\FormRequest;
use App\Enums\OrderTypeEnum;
use App\Enums\LevelUserEnum;
use App\Enums\ActivateStatusEnum;
use Illuminate\Validation\Rule;
class CreateOrderRequest extends FormRequest
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
            'type' => ['required', Rule::enum(OrderTypeEnum::class)],
            'sender_id' => ['required',  Rule::exists('users', 'id')
            ->where('level', LevelUserEnum::USER->value)
            ->whereNot('status', ActivateStatusEnum::BLOCK->value)],

            'sender_phone' => ['required', 'string'],
            'sender_address' => ['sometimes', 'string'],
            'city_source_id' => ['required', 'exists:cities,id'],
            'general_sender_name' => ['sometimes', 'string'],

            'receive_id' => ['sometimes', 'exists:users,id'],
            'city_target_id' => ['required', 'exists:cities,id'],
            'receive_address' => ['sometimes', 'string'],
            'receive_phone' => ['sometimes', 'string'],
            'global_name' => ['sometimes', 'string'],

            'unit_id' => ['required', 'exists:units,id'],
            'weight_id' => ['sometimes'],
            'size_id' => ['sometimes'],
            'note' => ['sometimes', 'string'],
            'shipping_date' => ['required', 'date'],

            'price' => ['sometimes', 'numeric', 'min:0'],
            'far' => ['sometimes', 'numeric', 'min:0'],
            'price_tr' => ['sometimes', 'numeric', 'min:0'],
            'far_tr' => ['sometimes', 'numeric', 'min:0'],

            'pick_id' => ['required', 'exists:users,id'],
            'far_sender' => ['required', 'boolean'],
            'qr_code' => ['required','max:255', 'unique:orders,qr_code'],
            // 'allow_duplicates' => ['sometimes', 'boolean'],
            // 'qr_code' => [
            //     Rule::requiredIf(function () {
            //         return $this->input('allow_duplicates', true);
            //     }),
            //     'string',
            //     'max:255',
            //     Rule::unique('orders', 'qr_code')->when($this->input('allow_duplicates', true), function ($rule) {
            //         return $rule;
            //     })
            // ]
        ];
    }
}
