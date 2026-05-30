<?php
require __DIR__.'/vendor/autoload.php';
$app = require_once __DIR__.'/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

try {
    Illuminate\Support\Facades\Mail::raw('This is a test email to verify SMTP configuration.', function($message) {
        $message->to('jonathanjetro10@gmail.com')->subject('Test Email from Klinik ITK');
    });
    echo "Email sent successfully.\n";
} catch (\Exception $e) {
    echo "Exception: " . $e->getMessage() . "\n";
}
