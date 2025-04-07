<?php

namespace App\Exports;

use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;

class OrdersExport implements FromCollection, WithHeadings, WithMapping
{
    protected $orders;

    public function __construct($orders)
    {
        $this->orders = $orders;
    }

    public function collection()
    {
        return $this->orders;
    }

    public function headings(): array
    {
        return [
            "Client",
            "Subclient",
            "Policy ID",
            "Client ID",
            "Subclient ID",
            "Merchant ID",
            "Merchant Name",
            "Customer Name",
            "Email",
            "Phone",
            "Shipping Address 1",
            "Shipping Address 2",
            "Shipping City",
            "Shipping State",
            "Shipping Zip",
            "Shipping Country",
            "Billing Address 1",
            "Billing Address 2",
            "Billing City",
            "Billing State",
            "Billing Zip",
            "Billing Country",
            "Order Number",
            "Items Ordered",
            "Order Total",
            "Subtotal",
            "Currency",
            "Coverage Amount",
            "Carrier",
            "Tracking Number",
            "Order Date",
            "Ship Date",
            "Source",
            "Void Date",
            "Campaign ID",
            "Status",
            "Created",
            "Updated"
        ];
    }

    public function map($order): array
    {
        // Clean items_ordered field
        $itemsOrdered = htmlentities($order->items_ordered);
        $itemsOrdered = preg_replace('/[\r\n]/', '', $itemsOrdered);

        return [
            $order->client_name,
            $order->subclient_name,
            $order->id ?? '',
            $order->client_id,
            $order->subclient_id,
            $order->merchant_id,
            $order->merchant_name ?? '',
            $order->customer_name,
            $order->email,
            $order->phone,
            $order->shipping_address_1,
            $order->shipping_address_2,
            $order->shipping_city,
            $order->shipping_state,
            $order->shipping_zip,
            $order->shipping_country,
            $order->billing_address_1,
            $order->billing_address_2,
            $order->billing_city,
            $order->billing_state,
            $order->billing_zip,
            $order->billing_country,
            $order->order_number ?? '',
            $itemsOrdered,
            $order->order_total,
            $order->subtotal,
            $order->currency,
            $order->coverage_amount ?? '',
            $order->carrier ?? '',
            $order->tracking_number ?? '',
            $order->order_date ?? '',
            $order->ship_date ?? '',
            $order->source ?? '',
            $order->void_date ?? '',
            $order->campaign_id ?? '',
            $order->status,
            $order->created,
            $order->updated
        ];
    }
}
