<?php

namespace App\Mail;

use App\Models\PedidoWeb;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Attachment;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class PedidoWebComprobante extends Mailable
{
    use Queueable, SerializesModels;

    public function __construct(public PedidoWeb $pedido) {}

    public function envelope(): Envelope
    {
        return new Envelope(
            subject: 'Comprobante del pedido #'.$this->pedido->id_pedido,
        );
    }

    public function content(): Content
    {
        return new Content(
            view: 'emails.pedidos.confirmacion',
        );
    }

    public function attachments(): array
    {
        return [
            Attachment::fromData(
                fn () => Pdf::loadView('emails.pedidos.comprobante', ['pedido' => $this->pedido])
                    ->setPaper('A4', 'portrait')
                    ->output(),
                'comprobante_pedido_'.$this->pedido->id_pedido.'.pdf'
            )->withMime('application/pdf'),
        ];
    }
}
