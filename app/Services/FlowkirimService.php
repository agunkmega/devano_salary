<?php

namespace App\Services;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Storage;

class FlowkirimService
{
    protected string $baseUrl;
    protected string $apiKey;
    protected string $sessionId;

    public function __construct()
    {
        $this->baseUrl = config('flowkirim.base_url');
        $this->apiKey = config('flowkirim.api_key');
        $this->sessionId = config('flowkirim.session_id');
    }

    public function sendText(string $to, string $message): array
    {
        $response = Http::withHeaders([
            'Authorization' => 'Bearer ' . $this->apiKey,
        ])->post("{$this->baseUrl}/api/whatsapp/messages/text", [
            'session_id' => $this->sessionId,
            'to' => $to,
            'message' => $message,
        ]);

        return [
            'success' => $response->successful(),
            'status' => $response->status(),
            'body' => $response->json(),
        ];
    }

    public function sendMedia(string $to, string $mediaUrl, string $type = 'document', ?string $caption = null): array
    {
        $payload = [
            'session_id' => $this->sessionId,
            'to' => $to,
            'media_url' => $mediaUrl,
            'type' => $type,
        ];

        if ($caption) {
            $payload['caption'] = $caption;
        }

        $response = Http::withHeaders([
            'Authorization' => 'Bearer ' . $this->apiKey,
        ])->post("{$this->baseUrl}/api/whatsapp/messages/media", $payload);

        return [
            'success' => $response->successful(),
            'status' => $response->status(),
            'body' => $response->json(),
        ];
    }

    public function sendPayslip(\App\Models\Payroll $payroll): array
    {
        $emp = $payroll->employee;
        $phone = $emp->phone;

        if (!$phone) {
            return ['success' => false, 'error' => 'Nomor telepon karyawan tidak tersedia'];
        }

        $phone = $this->normalizePhone($phone);
        $periodLabel = \Carbon\Carbon::createFromFormat('Y-m', $payroll->period)->locale('id')->translatedFormat('F Y');
        $fmt = fn($v) => 'Rp' . number_format((float) $v, 0, ',', '.');

        $message = "Slip Gaji *{$periodLabel}*\n\n";
        $message .= "Nama: {$emp->full_name}\n";
        $message .= "NIK: {$emp->nik}\n";
        $message .= "Jabatan: " . ($emp->position->name ?? '-') . "\n\n";
        $message .= "Gaji Pokok: {$fmt($payroll->base_salary)}\n";
        $message .= "Tunjangan: {$fmt($payroll->allowance)}\n";
        $message .= "Lembur: {$fmt($payroll->overtime_pay)}\n";

        if ($payroll->bonus > 0) {
            $message .= "Bonus: {$fmt($payroll->bonus)}\n";
        }

        $message .= "\n_Gaji Bersih: {$fmt($payroll->net_salary)}_\n\n";
        $message .= "Terima kasih.\n";
        $message .= "PT. Devano Silver Indonesia";

        $textResult = $this->sendText($phone, $message);

        $mediaResult = null;
        $pdfUrl = $this->getPayslipPublicUrl($payroll);
        if ($pdfUrl) {
            $mediaResult = $this->sendMedia($phone, $pdfUrl, 'document', "Slip Gaji {$periodLabel}");
        }

        return [
            'success' => $textResult['success'],
            'text_status' => $textResult,
            'pdf_status' => $mediaResult,
        ];
    }

    protected function getPayslipPublicUrl(\App\Models\Payroll $payroll): ?string
    {
        try {
            $companySettings = \App\Models\Setting::where('group', 'company')->get()->keyBy('key');
            $companyName = $companySettings->get('company_name')?->value ?? 'PT. DEVANO SILVER INDONESIA';
            $companyAddress = $companySettings->get('company_address')?->value ?? '';
            $pdf = \Barryvdh\DomPDF\Facade\Pdf::loadView('payroll.slip', compact('payroll', 'companyName', 'companyAddress'));
            $filename = "payslip-{$payroll->employee->nik}-{$payroll->period}.pdf";
            $path = "payslips/{$filename}";
            Storage::disk('public')->put($path, $pdf->output());

            return url("storage/{$path}");
        } catch (\Exception $e) {
            return null;
        }
    }

    protected function normalizePhone(string $phone): string
    {
        $phone = preg_replace('/[^0-9]/', '', $phone);
        if (substr($phone, 0, 1) === '0') {
            $phone = '62' . substr($phone, 1);
        } elseif (substr($phone, 0, 2) !== '62') {
            $phone = '62' . $phone;
        }
        return $phone;
    }
}
