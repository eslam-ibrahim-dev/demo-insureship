<?php
// app/Http/Requests/ExportClaimsRequest.php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class ListClaimsRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'status'                            => 'nullable|string|in:all,open,paid,denied',
            'assigned_type'                     => 'nullable|string|in:assigned,unassigned',
            'include_claimant_payment_supplied' => 'nullable|boolean',
            'start_date'                        => 'nullable|date',
            'end_date'                          => 'nullable|date',
            'tracking_number'                   => 'nullable|string',
            'order_number'                      => 'nullable|string',
            'claim_id'                          => 'nullable|integer',
            'client_id'                          => 'nullable|integer',
            'claimant_name'                     => 'nullable|string',
            'filed_type'                        => 'nullable|string|in:matched,unmatched',
            'admin_id'                          => 'nullable|integer',
            'superclient_id'                    => 'nullable|integer',
            'sort_field'                        => 'nullable|string|in:a.created,claim_id,order_number,paid_date',
            'sort_direction'                    => 'nullable|string|in:asc,desc',
            'page'                              => 'nullable|integer|min:1',
            'per_page'                          => 'nullable|integer|min:1|max:100',
        ];
    }
}
