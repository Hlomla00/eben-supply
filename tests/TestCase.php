<?php

namespace Tests;

use Illuminate\Foundation\Testing\TestCase as BaseTestCase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\File;

abstract class TestCase extends BaseTestCase
{
    private const UPLOAD_DIR = 'images/products/uploads';

    private array $uploadsBefore = [];

    protected function setUp(): void
    {
        parent::setUp();
        $this->uploadsBefore = $this->uploadedFiles();
    }

    protected function tearDown(): void
    {
        // Remove product images uploaded by the test so public/ stays clean.
        File::delete(array_diff($this->uploadedFiles(), $this->uploadsBefore));
        parent::tearDown();
    }

    /**
     * A real 1x1 PNG upload (works without the GD extension).
     */
    protected function fakeImage(string $name = 'product.png'): UploadedFile
    {
        $png = base64_decode('iVBORw0KGgoAAAANSUhEUgAAAAEAAAABCAQAAAC1HAwCAAAAC0lEQVR42mNkYAAAAAYAAjCB0C8AAAAASUVORK5CYII=');

        return UploadedFile::fake()->createWithContent($name, $png);
    }

    private function uploadedFiles(): array
    {
        $dir = public_path(self::UPLOAD_DIR);

        return is_dir($dir) ? array_map(fn ($f) => $f->getPathname(), File::files($dir)) : [];
    }
}
