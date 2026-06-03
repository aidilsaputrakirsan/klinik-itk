<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;
use Illuminate\Mail\Mailables\Attachment;

class ScreeningResultMail extends Mailable
{
    use Queueable, SerializesModels;

    public $pasien;
    public $rekamMedis;
    public $pdfData;

    /**
     * Create a new message instance.
     */
    public function __construct($pasien, $rekamMedis, $pdfData)
    {
        $this->pasien = $pasien;
        $this->rekamMedis = $rekamMedis;
        $this->pdfData = $pdfData;
    }

    /**
     * Get the message envelope.
     */
    public function envelope(): Envelope
    {
        return new Envelope(
            subject: 'Hasil Screening Klinik ITK - ' . $this->pasien->nama,
        );
    }

    /**
     * Get the message content definition.
     */
    public function content(): Content
    {
        return new Content(
            view: 'emails.screening_result',
        );
    }

    /**
     * Get the attachments for the message.
     *
     * @return array<int, \Illuminate\Mail\Mailables\Attachment>
     */
    public function attachments(): array
    {
        return [
            Attachment::fromData(fn () => $this->pdfData, 'Hasil_Screening_' . str_replace(' ', '_', $this->pasien->nama) . '.pdf')
                    ->withMime('application/pdf'),
        ];
    }
}
