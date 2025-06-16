<?php

namespace App\Services\Admin\Accounting;

use App\Repositories\InvoiceRepository;
use App\Repositories\ClientRepository;

class InvoiceService
{
    protected $invoiceRepo;
    protected $clientRepo;

    public function __construct(
        InvoiceRepository $invoiceRepo,
    ) {
        $this->invoiceRepo = $invoiceRepo;
    }

    public function getAllInvoices()
    {
        return [
            'pending'  => $this->invoiceRepo->getPendingInvoices(),
            'approved' => $this->invoiceRepo->getApprovedInvoices(),
            'paid'     => $this->invoiceRepo->getPaidInvoices(),
        ];
    }

    public function createInvoice(array $data, $user)
    {
        return $this->invoiceRepo->create($data, $user);
    }

    public function getInvoiceDetails(int $invoiceId, $admin): array
    {
        return $this->invoiceRepo->getInvoiceDetails($invoiceId, $admin->id);
    }

    public function updateInvoiceLineItems(int $invoiceId, array $lineItems): void
    {
        $this->invoiceRepo->syncLineItems($invoiceId, $lineItems);
    }

    public function updateNote(int $invoiceId, ?string $note): void
    {
        $this->invoiceRepo->updateNote($invoiceId, $note);
    }

    public function deleteLineItem(int $lineItemId): void
    {
        $this->invoiceRepo->deleteLineItem($lineItemId);
    }

    public function deleteInvoice(int $invoiceId, $user): void
    {
        $this->invoiceRepo->deleteById($invoiceId);
    }

    public function approveInvoice(int $invoiceId): bool
    {
        return $this->invoiceRepo->markApproved($invoiceId);
    }
}
