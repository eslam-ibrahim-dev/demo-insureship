<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class ClaimDetailResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        $claim = $this->resource; // this is the Claim model instance

        return [
            'claim_info' => [
                'id'                => $claim->id,
                'customer_name'     => $claim->customer_name,
                'agent'             => $claim->assignedAdmin->name ?? 'Unassigned',
                'admin_id'             => $claim->assignedAdmin->id ?? 0,
                'filed_date'        => $claim->filed_date,
                'ship_date'         => $claim->ship_date,
                'claim_amount'      => $claim->claim_amount,
                'status'            => $claim->status,
                'amount_to_pay_out' => $claim->amount_to_pay_out,
                'policy_id'         => $claim->policy_id,
                'issue_type'        => $claim->issue_type,
                'date_of_issue'     => $claim->date_of_issue,
                'ip_address'        => $claim->filed_ip_address,
                'location'          => $claim->location,
                'currency'          => $claim->currency,
                'delivery_date'     => $claim->delivery_date,
                'items_purchased'   => $claim->items_purchased,
                'extra_info'        => optional($claim?->order?->extraInfo)->toArray(),
                'comments'          => $claim->comments,
                'description'       => $claim->description,
            ],
            'order_info' => [
                'id'                 => $claim->order->id ?? null,
                'order_customer_name' => $claim->order->customer_name ?? null,
                'phone'              => $claim->order->phone ?? null,
                'order_amount'       => $claim->order->order_amount ?? null,
                'order_date'         => $claim->order->order_date ?? null,
                'tracking_number'    => $claim->order->tracking_number ?? null,
                'carrier'            => $claim->order->carrier ?? null,
                'coverage_amount'    => $claim->order->coverage_amount ?? null,
                'order_number'       => $claim->order->order_number ?? null,
            ],
            'shipping_address' => [
                'address_1'          => $claim->order->shipping_address1 ?? null,
                'address_2'          => $claim->order->shipping_address2 ?? null,
                'city'               => $claim->order->shipping_city ?? null,
                'state'              => $claim->order->shipping_state ?? null,
                'zip'                => $claim->order->shipping_zip ?? null,
                'country'            => $claim->order->shipping_country ?? null,
            ],
            'billing_address' => [
                'address_1'          => $claim->order->billing_address1 ?? null,
                'address_2'          => $claim->order->billing_address2 ?? null,
                'city'               => $claim->order->billing_city ?? null,
                'state'              => $claim->order->billing_state ?? null,
                'zip'                => $claim->order->billing_zip ?? null,
                'country'            => $claim->order->billing_country ?? null,
            ],
            'client_notes' => $claim?->client?->notes?->map(function ($note) {
                return [
                    'id'         => $note->id,
                    'note'       => $note->note,
                    'type'       => $note->note_type,
                    'admin'      => $note->admin->name ?? null,
                    'created_at' => $note->created,
                ];
            }),
            'subclient_notes' => $claim?->subclient?->notes?->map(function ($note) {
                return [
                    'id'         => $note->id,
                    'note'       => $note->note,
                    'type'       => $note->note_type,
                    'admin'      => $note->admin->name ?? null,
                    'created_at' => $note->created,
                ];
            }),
            'offers_info' => method_exists($claim, 'offers')
                ? $claim?->offers?->map(function ($offer) {
                    return [
                        'name'                 => $offer->name,
                        'coverage_start'       => $offer->coverage_start,
                        'coverage_duration'    => $offer->coverage_duration,
                        'file_claim_start'     => $offer->file_claim_start,
                        'file_claim_duration'  => $offer->file_claim_duration,
                        'terms'                => $offer->terms,
                    ];
                }) : [],
            'client_info' => [
                'client_name'     => $claim->client->name ?? null,
                'domain'          => $claim->client->domain ?? null,
                'client_contacts' => $claim->client->contacts ?? [],
                'client_notes'    => $claim->client?->notes?->map(function ($note) {
                    return [
                        'note'  => $note->note,
                        'admin' => $note->admin->name ?? null,
                        'date'  => $note->created,
                    ];
                }),
            ],
            'subclient_info' => [
                'subclient_name'     => $claim->subclient->name ?? null,
                'subclient_contacts' => $claim->subclient->contacts ?? [],
                'subclient_notes'    => $claim->subclient?->notes?->map(function ($note) {
                    return [
                        'note'  => $note->note,
                        'admin' => $note->admin->name ?? null,
                        'date'  => $note->created,
                    ];
                }),
            ],
        ];
    }
}
