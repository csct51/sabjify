<?php

use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;

test('files on the public disk are served without a storage symlink', function () {
    Storage::fake('public');

    $file = UploadedFile::fake()->image('products/demo.png');
    Storage::disk('public')->putFileAs('products', $file, 'demo.png');

    // Serve route follows the real disk URL config (fake keeps its own URL).
    $path = parse_url((string) config('filesystems.disks.public.url'), PHP_URL_PATH).'/products/demo.png';

    $this->get($path)
        ->assertOk()
        ->assertHeader('Content-Type', 'image/png');
});

test('missing files on the public disk return a 404', function () {
    Storage::fake('public');

    $path = parse_url((string) config('filesystems.disks.public.url'), PHP_URL_PATH).'/products/missing.png';

    $this->get($path)->assertNotFound();
});
