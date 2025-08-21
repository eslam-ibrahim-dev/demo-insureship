<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class CreateOrderRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'subclient_id'      => ['required', 'string'],
            'firstname'         => ['required', 'string', 'max:255'],
            'lastname'          => ['required', 'string', 'max:255'],
            'order_number'      => ['required', 'string', 'max:255'],
            'items_ordered'     => ['required', 'string'],
            'order_total'       => ['required', 'numeric', 'min:0'],
            'subtotal'          => ['required', 'numeric', 'min:0'],
            'email'             => ['nullable', 'email'],
            'phone'             => ['nullable', 'string', 'max:20'],
            'shipping_amount'   => ['nullable', 'numeric', 'min:0'],
            'coverage_amount'   => ['nullable', 'numeric', 'min:0'],
            'currency'          => ['nullable', 'string', 'size:3'], // default USD
            'carrier'           => ['nullable', 'string', 'max:255'],
            'tracking_number'   => ['nullable', 'string', 'max:255'],
            'order_date'        => ['nullable', 'date'],
            'ship_date'         => ['nullable', 'date'],
            'shipping_address1' => ['nullable', 'string', 'max:255'],
            'shipping_address2' => ['nullable', 'string', 'max:255'],
            'shipping_city'     => ['nullable', 'string', 'max:255'],
            'shipping_state'    => ['nullable', 'string', 'max:255'],
            'shipping_zip'      => ['nullable', 'string', 'max:20'],
            'shipping_country'  => ['nullable', 'string', 'size:2'], // default US
            'billing_address1'  => ['nullable', 'string', 'max:255'],
            'billing_address2'  => ['nullable', 'string', 'max:255'],
            'billing_city'      => ['nullable', 'string', 'max:255'],
            'billing_state'     => ['nullable', 'string', 'max:255'],
            'billing_zip'       => ['nullable', 'string', 'max:20'],
            'billing_country'   => ['nullable', 'string', 'size:2'], // default US
        ];
    }

    public function messages(): array
    {
        return [
            'subclient_id.required' => 'Subclient is required.',
            'firstname.required'    => 'First name is required.',
            'lastname.required'     => 'Last name is required.',
            'order_number.required' => 'Order number is required.',
            'order_number.unique' => 'This order number already exists.',
            'items_ordered.required' => 'Items ordered is required.',
            'order_total.required'  => 'Order total is required.',
            'subtotal.required'     => 'Subtotal is required.',
        ];
    }

    public function prepareForValidation()
    {
        $this->merge([
            'currency'         => $this->currency ?? 'USD',
            'shipping_country' => $this->shipping_country ?? 'US',
            'billing_country'  => $this->billing_country ?? 'US',
        ]);
    }
}
