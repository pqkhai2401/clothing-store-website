<?php

declare(strict_types=1);

namespace App\Http\Requests;

use App\Enums\OrderStatus;
use App\Enums\PaymentStatus;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StoreOrderRequest extends FormRequest
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
            'address_id' => ['required', 'exists:addresses,id'],
            'payment_method_id' => ['required', 'exists:payment_methods,id'],
            'phone' => ['required', 'string'],
            'total_money' => ['required', 'numeric', 'min:0'],
            'shipping_fee' => ['nullable', 'numeric', 'min:0'],
            'status' => ['nullable', 'string', Rule::in(OrderStatus::values())],
            'payment_status' => ['nullable', 'string', Rule::in(PaymentStatus::values())],
        ];
    }
}
