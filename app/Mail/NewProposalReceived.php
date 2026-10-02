<?php

namespace App\Mail;

use App\Models\Proposal;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Address;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

/** Aviso à equipe nordit de nova solicitação (SPEC-005 · RF-07; substitui o Web3Forms). */
class NewProposalReceived extends Mailable implements ShouldQueue
{
    use Queueable, SerializesModels;

    public function __construct(public Proposal $proposal) {}

    public function envelope(): Envelope
    {
        return new Envelope(
            subject: "Nova solicitação de proposta — {$this->proposal->protocol}",
            replyTo: [new Address($this->proposal->email, $this->proposal->contact_name)],
        );
    }

    public function content(): Content
    {
        return new Content(view: 'mail.new-proposal');
    }
}
