<?php

use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;

test('files on the public disk are served without a storage symlink', function () {
    Storage::fake('public');

    $file = UploadedFile::fake()->image('products/demo.png');
    Storage::disk('public')->putFileAs('products', $file, 'demo.png');

    $this->get('/storage/products/demo.png')
        ->assertOk()
        ->assertHeader('Content-Type', 'image/png');
});

test('missing files on the public disk return a 404', function () {
    Storage::fake('public');

    $this->get('/storage/products/missing.png')->assertNotFound();
});
