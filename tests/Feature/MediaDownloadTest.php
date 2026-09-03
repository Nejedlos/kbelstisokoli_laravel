<?php

namespace Tests\Feature;

use App\Models\MediaAsset;
use App\Services\Media\CustomPathGenerator;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

class MediaDownloadTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        Storage::fake('media_private');
    }

    #[Test]
    public function guests_cannot_download_private_media()
    {
        $asset = MediaAsset::create([
            'title' => 'Secret',
            'access_level' => 'private',
        ]);
        $file = UploadedFile::fake()->create('secret.pdf');
        $media = $asset->addMedia($file)->toMediaCollection('default', 'media_private');

        $response = $this->get(route('media.download', ['uuid' => $media->uuid]));

        $response->assertStatus(401);
    }

    #[Test]
    public function admins_can_download_private_media()
    {
        $admin = $this->with2FA($this->createAdmin());
        $this->confirm2FA($admin);
        $this->actingAs($admin);

        $asset = MediaAsset::create([
            'title' => 'Secret',
            'access_level' => 'private',
        ]);
        $file = UploadedFile::fake()->create('secret.pdf');
        $media = $asset->addMedia($file)->toMediaCollection('default', 'media_private');

        // Musíme zajistit, aby soubor fyzicky existoval pro response()->download()
        $relativeDiskPath = app(CustomPathGenerator::class)->getPath($media).$media->file_name;
        Storage::disk('media_private')->put($relativeDiskPath, 'content');

        $response = $this->get(route('media.download', ['uuid' => $media->uuid]));

        $response->assertStatus(200);
        $response->assertHeader('Content-Disposition', 'attachment; filename=secret.pdf');
    }
}
