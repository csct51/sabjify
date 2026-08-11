<?php

use Illuminate\Contracts\Http\Kernel;
use Illuminate\Http\Request;

require __DIR__.'/vendor/autoload.php';

$app = require_once __DIR__.'/bootstrap/app.php';
$kernel = $app->make(Kernel::class);
$response = $kernel->handle($request = Request::capture());

$html = view('layouts.store', [
    'slot' => view('livewire.home')->render(),
    'title' => 'Home',
])->render();

file_put_contents(__DIR__.'/storage/app/render-home.html', $html);

echo 'done '.strlen($html);
