<!DOCTYPE html>
<html lang="id">
<head><meta charset="UTF-8"><title>Slip Gaji</title></head>
<body style="font-family: Arial, sans-serif; padding: 20px;">
    <h2>Slip Gaji {{ \Carbon\Carbon::createFromFormat('Y-m', $payroll->period)->locale('id')->translatedFormat('F Y') }}</h2>
    <p>Kepada {{ $payroll->employee?->full_name }},</p>
    <p>Berikut terlampir slip gaji Anda untuk periode tersebut.</p>
    <p>Terima kasih.</p>
    <hr>
    <p style="color: #666; font-size: 12px;">Email ini dikirim otomatis oleh sistem.</p>
</body>
</html>
