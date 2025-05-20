<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class ClaimSubmitted extends Mailable
{
    use Queueable, SerializesModels;

    /**
     * Create a new message instance.
     */
    public function __construct(
        public $claim,
        public array $config,
        public int $displayedClaimId
    ) {
        //
    }

    public function build()
    {
        return $this->from($this->config['email'])
            ->subject("Your {$this->config['company_name']} Claim Submission")
            ->view("emails.claims.{$this->config['template']}.submitted")
            ->with([
                'claim' => $this->claim,
                'config' => $this->config,
                'claimLinkId' => $this->displayedClaimId
            ]);
    }
    /**
     * Get the attachments for the message.
     *
     * @return array<int, \Illuminate\Mail\Mailables\Attachment>
     */
    public function attachments(): array
    {
        return [];
    }
}
