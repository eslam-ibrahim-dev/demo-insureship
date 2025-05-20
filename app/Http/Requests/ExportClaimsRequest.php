<?php
// app/Http/Requests/ExportClaimsRequest.php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class ExportClaimsRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true; 
    }

    public function rules(): array
    {
        $fields = implode(',', config('claims.exportable_fields'));
        return [
            'file_fields'                       => ['required', 'array', 'min:1'],
            'file_fields.*'                     => ['string', "in:$fields"],
            'status'                            => ['nullable', 'string'],
            'assigned_type'                     => ['nullable', 'string', 'in:assigned,unassigned'],
            'include_claimant_payment_supplied' => ['nullable', 'boolean'],
            'start_date'                        => ['nullable', 'date'],
            'end_date'                          => ['nullable', 'date'],
            'tracking_number'                   => ['nullable', 'string'],
            'order_number'                      => ['nullable', 'string'],
            'claim_id'                          => ['nullable', 'integer'],
            'claimant_name'                     => ['nullable', 'string'],
            'filed_type'                        => ['nullable', 'in:matched,unmatched'],
            'admin_id'                          => ['nullable', 'integer'],
            'superclient_id'                    => ['nullable', 'integer'],
            'sort_field'                        => ['nullable', 'string'],
            'sort_direction'                    => ['nullable', 'in:asc,desc'],
        ];
    }
}
