<?php

namespace App\Mail;

use App\Models\Payroll;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Attachment;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class PayslipMail extends Mailable
{
    use Queueable, SerializesModels;

    public Payroll $payroll;

    public function __construct(Payroll $payroll)
    {
        $this->payroll = $payroll;
    }

    public function envelope(): Envelope
    {
        $periodLabel = \Carbon\Carbon::createFromFormat('Y-m', $this->payroll->period)->locale('id')->translatedFormat('F Y');
        return new Envelope(
            subject: "Slip Gaji {$periodLabel} - {$this->payroll->employee?->full_name}",
        );
    }

    public function content(): Content
    {
        return new Content(
            view: 'emails.payslip',
        );
    }

    public function attachments(): array
    {
        $companySettings = \App\Models\Setting::where('group', 'company')->get()->keyBy('key');
        $companyName = $companySettings->get('company_name')?->value ?? 'PT. DEVANO SILVER INDONESIA';
        $companyAddress = $companySettings->get('company_address')?->value ?? '';

        $pdf = Pdf::loadView('payroll.slip', [
            'payroll' => $this->payroll,
            'companyName' => $companyName,
            'companyAddress' => $companyAddress,
        ]);

        return [
            Attachment::fromData(fn() => $pdf->output(), "payslip-{$this->payroll->employee?->nik}-{$this->payroll->period}.pdf")
                ->withMime('application/pdf'),
        ];
    }
}
