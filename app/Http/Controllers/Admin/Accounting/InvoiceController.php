<?php

namespace App\Http\Controllers\Admin\Accounting;

use App\Http\Controllers\Controller;
use App\Models\Rate;
use App\Services\Admin\Accounting\InvoiceService;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Pagination\LengthAwarePaginator;

class InvoiceController extends Controller
{
    protected $invoiceService;

    public function __construct(InvoiceService $invoiceService)
    {
        $this->invoiceService = $invoiceService;
    }


    public function index(): JsonResponse
    {
        $user = auth('admin')->user();

        if (!$user || $user->level === 'Guest Admin') {
            return response()->json([
                'message' => 'Access denied: Guest Admins are not allowed.'
            ], 403);
        }
        $invoices = $this->invoiceService->getAllInvoices();

        $paginate = fn($collection) => new LengthAwarePaginator(
            $collection->forPage(LengthAwarePaginator::resolveCurrentPage(), 20),
            $collection->count(),
            20,
        );
        return response()->json([
            'data' => [
                'pending'  => $paginate(collect($invoices['pending'])),
                'approved' => $paginate(collect($invoices['approved'])),
                'paid'     => $paginate(collect($invoices['paid'])),
            ]
        ]);
    }


    public function createInvoice(Request $request): JsonResponse
    {
        $user = auth('admin')->user();

        if (!$user || $user->level === 'Guest Admin') {
            return response()->json([
                'message' => 'Access denied: Guest Admins are not allowed.'
            ], 403);
        }

        $validated = $request->validate([
            'client_id'     => 'required|integer|exists:osis_client,id',
            'billing_year'  => 'required|integer|min:2000',
            'billing_month' => 'required|integer|between:1,12',
        ]);

        $invoice = $this->invoiceService->createInvoice($validated, $user);

        return response()->json([
            'message'    => 'Success',
            'invoice_id' => $invoice->id,
        ], 201);
    }
    public function createRate(Request $request): JsonResponse
    {
        $user = auth('admin')->user();

        if (!$user || $user->level === 'Guest Admin') {
            return response()->json([
                'message' => 'Access denied: Guest Admins are not allowed.'
            ], 403);
        }

        $validated = $request->validate([
            'client_id'     => 'required|integer|exists:osis_client,id',
            'info'                  => 'nullable|string|max:255',
            'carrier'               => 'nullable|string|max:255',
            'rate_domestic'         => 'nullable|numeric|min:0',
            'rate_type_domestic'    => 'required|in:value,percentage',
            'rate_international'    => 'nullable|numeric|min:0',
            'rate_type_international' => 'required|in:value,percentage',
        ]);

        $rate = Rate::create($validated);

        return response()->json([
            'message'    => 'Success',
            'rate_id' => $rate->id,
        ], 201);
    }

    public function showInvoiceDetail(int $invoiceId): JsonResponse
    {
        $user = auth('admin')->user();

        if (!$user || $user->level === 'Guest Admin') {
            return response()->json([
                'message' => 'Access denied: Guest Admins are not allowed.'
            ], 403);
        }

        $details = $this->invoiceService->getInvoiceDetails($invoiceId, $user);

        return response()->json([
            'invoice'          => $details['invoice'],
            'has_edit_ability' => $details['has_edit_ability'],
        ]);
    }


    public function updateInvoiceDetails(Request $request, int $invoiceId): JsonResponse
    {
        $user = auth('admin')->user();

        if (!$user || $user->level === 'Guest Admin') {
            return response()->json([
                'message' => 'Access denied: Guest Admins are not allowed.'
            ], 403);
        }
        $validated = $request->validate([
            'line_items' => 'required|array|min:1',
            'line_items.*.name' => 'required|string',
            'line_items.*.quantity' => 'required|numeric',
            'line_items.*.rate' => 'required|numeric',
            'line_items.*.id' => 'nullable|integer|exists:osis_invoice_line_item,id',
            'line_items.*.description' => 'nullable|string',
        ]);
        try {
            $this->invoiceService->updateInvoiceLineItems($invoiceId, $validated['line_items']);
            return response()->json(['message' => 'Invoice line items updated successfully.'], 200);
        } catch (\Exception $e) {
            return response()->json(['message' => $e->getMessage()], 403);
        }
    }

    public function updateInvoiceNote(Request $request, int $invoiceId): JsonResponse
    {
        $user = auth('admin')->user();

        if (!$user || $user->level === 'Guest Admin') {
            return response()->json(['message' => 'Access denied.'], 403);
        }

        $validated = $request->validate([
            'notes' => 'nullable|string|max:1000',
        ]);

        $this->invoiceService->updateNote($invoiceId, $validated['notes']);

        return response()->json(['message' => 'Note updated successfully.'], 200);
    }

    public function deleteInvoiceLineItem(int $lineItemId): JsonResponse
    {
        $user = auth('admin')->user();

        if (!$user || $user->level === 'Guest Admin') {
            return response()->json(['message' => 'Access denied.'], 403);
        }

        $this->invoiceService->deleteLineItem($lineItemId);

        return response()->json(['message' => 'Line item deleted successfully.'], 200);
    }

    public function delete(Request $request, int $invoiceId): JsonResponse
    {
        $user = auth('admin')->user();

        if (!$user || $user->level === 'Guest Admin') {
            return response()->json(['message' => 'Access denied.'], 403);
        }

        try {
            $this->invoiceService->deleteInvoice($invoiceId, $user);

            return response()->json([
                'message' => 'Invoice deleted successfully'
            ], 200);
        } catch (\Exception $e) {
            return response()->json([
                'message' => $e->getMessage()
            ], 403);
        }
    }

    public function approve(Request $request, int $invoiceId): JsonResponse
    {
        $user = auth('admin')->user();

        if (!$user || $user->level === 'Guest Admin') {
            return response()->json([
                'message' => 'Access denied: Guest Admins are not allowed.',
            ], 403);
        }

        $success = $this->invoiceService->approveInvoice($invoiceId);

        if (!$success) {
            return response()->json([
                'message' => 'Invoice not found or could not be updated.',
            ], 404);
        }

        return response()->json([
            'message' => 'Invoice successfully approved',
        ], 200);
    }
}
