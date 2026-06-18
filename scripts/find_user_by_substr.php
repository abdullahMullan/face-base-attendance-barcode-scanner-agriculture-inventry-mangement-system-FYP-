<?php

require __DIR__ . '/../vendor/autoload.php';

$app = require __DIR__ . '/../bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use App\Models\User;

$substr = 'muzammal';
$users = User::where('email','like', "%{$substr}%")->orWhere('name','like', "%{$substr}%")->limit(20)->get();
$out = $users->map(fn($u)=>[ 'id'=>$u->id, 'name'=>$u->name, 'email'=>$u->email ])->toArray();

echo json_encode($out, JSON_PRETTY_PRINT) . "\n";
