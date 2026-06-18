<?php

require __DIR__ . '/../vendor/autoload.php';

$app = require __DIR__ . '/../bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use App\Models\User;

$email = $argv[1] ?? 'muzammalmuzammal123@gmail.com';

$u = User::where('email', $email)->first();
if (! $u) {
    echo "NOUSER\n";
    exit(0);
}

$rows = $u->attendances()->where('attendance_date', date('Y-m-d'))->get();

$out = $rows->map(function ($r) {
    return [
        'id' => $r->id,
        'attendance_date' => (string) $r->attendance_date,
        'check_in_at' => $r->check_in_at ? $r->check_in_at->toDateTimeString() : null,
        'check_out_at' => $r->check_out_at ? $r->check_out_at->toDateTimeString() : null,
        'check_in_method' => $r->check_in_method ?? null,
        'check_out_method' => $r->check_out_method ?? null,
    ];
})->toArray();

echo json_encode($out, JSON_PRETTY_PRINT) . "\n";
