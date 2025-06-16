<?php

namespace App\Repositories;

use App\Models\Invoice;
use App\Models\InvoiceLineItem;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Arr;

class InvoiceRepository
{
    public function getInvoicesByStatus(string $status)
    {
        $invoices = Invoice::with(['client:id,name', 'lineItems'])
            ->where('status', $status)
            ->orderByRaw($status === 'Pending' ? 'DATE(created) DESC' : 'DATE(start_date) DESC')
            ->orderBy(
                DB::raw('(SELECT name FROM osis_client WHERE osis_client.id = osis_invoice.client_id)'),
                'ASC'
            )
            ->paginate(15);

        return [
            'total' => $invoices->total(),
            'current_page' => $invoices->currentPage(),
            'per_page' => $invoices->perPage(),
            'data' => $invoices->getCollection()->map(function ($invoice) {
                return [
                    'id' => $invoice->id,
                    'status' => $invoice->status,
                    'start_date' => $invoice->start_date,
                    'created' => $invoice->created,
                    'client_name' => $invoice->client->name ?? null,
                    'client_id' => $invoice->client->id ?? null,
                    'line_items' => $invoice->lineItems,
                ];
            })->values(),
        ];
    }

    public function getPendingInvoices()
    {
        return $this->getInvoicesByStatus('Pending');
    }

    public function getApprovedInvoices()
    {
        return $this->getInvoicesByStatus('Approved');
    }

    public function getPaidInvoices()
    {
        return $this->getInvoicesByStatus('Paid');
    }

    public function create($data, $user)
    {
        $startDate = sprintf('%d-%02d-01 00:00:00', $data['billing_year'], $data['billing_month']);
        $endDate   = date('Y-m-t 23:59:59', strtotime($startDate));
        return Invoice::create([
            'client_id'   => $data['client_id'],
            'start_date'  => $startDate,
            'end_date'    => $endDate,
            'status'      => 'Pending',
            'created_by'  => $user->id,
        ]);
    }

    public function getInvoiceDetails(int $invoiceId, int $adminId): ?array
    {
        $invoice = Invoice::with([
            'client:id,name',
            'lineItems',
        ])->findOrFail($invoiceId);

        $hasEditAbility = $this->checkAdminEditAbility($invoiceId, $adminId);

        return [
            'invoice'          => $invoice,
            'client'           => $invoice->client,
            'line_items'       => $invoice->lineItems,
            'has_edit_ability' => $hasEditAbility,
        ];
    }

    private function checkAdminEditAbility(int $invoiceId, int $adminId): bool
    {
        $allowedAdmins = [1, 56855];

        if (in_array($adminId, $allowedAdmins)) {
            return true;
        }

        return DB::table('osis_invoice as a')
            ->join('osis_client as b', 'a.client_id', '=', 'b.id')
            ->join('osis_account_management as c', 'b.id', '=', 'c.client_id')
            ->where('a.id', $invoiceId)
            ->where('c.admin_id', $adminId)
            ->exists();
    }

    public function syncLineItems(int $invoiceId, array $items): void
    {
        DB::transaction(function () use ($invoiceId, $items) {
            $existingItems = InvoiceLineItem::where('invoice_id', $invoiceId)->get()->keyBy('id');
            $idsToKeep = [];
            $itemsToInsert = [];
            $itemsToUpdate = [];

            foreach ($items as $item) {
                if (!empty($item['id']) && $existingItems->has($item['id'])) {
                    // Prepare updated fields
                    $itemsToUpdate[] = [
                        'id' => $item['id'],
                        'name' => $item['name'],
                        'description' => $item['description'] ?? null,
                        'quantity' => $item['quantity'],
                        'rate' => $item['rate'],
                    ];
                    $idsToKeep[] = $item['id'];
                } else {
                    // Prepare new items
                    $itemsToInsert[] = [
                        'invoice_id' => $invoiceId,
                        'name'       => $item['name'],
                        'description' => $item['description'] ?? null,
                        'quantity'   => $item['quantity'],
                        'rate'       => $item['rate'],
                        'created' => now(),
                        'updated' => now(),
                    ];
                }
            }

            // Delete removed items
            InvoiceLineItem::where('invoice_id', $invoiceId)
                ->whereNotIn('id', $idsToKeep)
                ->delete();

            // Batch insert
            if (!empty($itemsToInsert)) {
                InvoiceLineItem::insert($itemsToInsert);
            }

            // Batch update
            foreach ($itemsToUpdate as $item) {
                InvoiceLineItem::where('id', $item['id'])->update(Arr::except($item, ['id']));
            }
        });
    }

    public function updateNote(int $invoiceId, ?string $note): void
    {
        Invoice::where('id', $invoiceId)->update(['notes' => $note]);
    }

    public function deleteLineItem(int $lineItemId): void
    {
        InvoiceLineItem::where('id', $lineItemId)->delete();
    }

    public function deleteById(int $invoiceId): bool
    {
        $invoice = Invoice::with('lineItems')->findOrFail($invoiceId);
        $invoice->delete();

        return true;
    }

    public function markApproved(int $invoiceId): bool
    {
        return Invoice::where('id', $invoiceId)->update([
            'status' => 'Approved',
        ]);
    }
}
