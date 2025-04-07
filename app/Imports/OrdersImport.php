<?php

namespace App\Imports;

use App\Models\Order;
use App\Models\Offer;
use App\Models\Subclient;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use Maatwebsite\Excel\Concerns\ToModel;
use Maatwebsite\Excel\Concerns\WithHeadingRow;
use Maatwebsite\Excel\Concerns\WithChunkReading;
use Maatwebsite\Excel\Concerns\WithValidation;
use Maatwebsite\Excel\Concerns\SkipsEmptyRows;

class OrdersImport implements ToModel, WithHeadingRow, WithChunkReading, SkipsEmptyRows
{
    protected $clientId;
    protected $subclientId;
    protected $emailStatus;
    protected $emailTime;
    protected $offers;

    public function __construct($clientId, $subclientId, $emailStatus, $emailTime, $offers)
    {
        $this->clientId = $clientId;
        $this->subclientId = $subclientId;
        $this->emailStatus = $emailStatus;
        $this->emailTime = $emailTime;
        $this->offers = $offers;
    }

    /**
     * @param array $row
     *
     * @return \Illuminate\Database\Eloquent\Model|null
     */
    public function model(array $row)
    {
        // Handle date formatting
        $orderDate = !empty($row['order_date']) ? date('Y-m-d', strtotime($row['order_date'])) : now();
        $shipDate = !empty($row['ship_date']) ? date('Y-m-d', strtotime($row['ship_date'])) : now();

        // Create the order
        $order = new Order([
            'order_date'    => $orderDate,
            'ship_date'    => $shipDate,
            'client_id'     => $this->clientId,
            'subclient_id'  => $this->subclientId,
            'email_status'  => $this->emailStatus,
            'email_time'    => $this->emailTime,
            'customer_name'    => $row['customer_name'],
            'order_total'    => $row['order_total'] ?? 0,
            'client_offer_id'    => $row['client_offer_id'] ?? null,
            'items_ordered'    => $row['items_ordered'],
            'subtotal'    => $row['subtotal'],
            'currency'    => $row['currency'],
            'coverage_amount'    => $row['coverage_amount'],
            'order_number'    => $row['order_number'],
            'email'    => $row['email'],
            'phone'    => $row['phone'],
            'carrier'    => $row['carrier'],
            'tracking_number'    => $row['tracking_number'],
            'shipping_address1'    => $row['shipping_address1'],
            'shipping_address2'    => $row['shipping_address2'],
            'shipping_city'    => $row['shipping_city'],
            'shipping_state'    => $row['shipping_state'],
            'shipping_zip'    => $row['shipping_zip'],
            'shipping_country'    => $row['shipping_country'],
            'billing_address1'    => $row['billing_address1'],
            'billing_address2'    => $row['billing_address2'],
            'billing_city'    => $row['billing_city'],
            'billing_state'    => $row['billing_state'],
            'billing_zip'    => $row['billing_zip'],
            'billing_country'    => $row['billing_country'],
            'merchant_id'    => $row['merchant_id'],
            'merchant_name'    => $row['merchant_name'],
            'source'        => 'Admin Import',
        ]);
        $order->save();

        // Attach offers
        foreach ($this->offers as $offer) {
            DB::table('osis_order_offer')->insert([
                'offer_id' => $offer->main_offer_id,  // Use object notation
                'order_id' => $order->id,
                'terms'    => $offer->subclient_terms, // Use object notationF
            ]);
        }

        return $order;
    }

    public function chunkSize(): int
    {
        return 1000; // chunk for performance
    }
}
