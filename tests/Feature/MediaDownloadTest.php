<?php

namespace Tests\Feature;

use App\Models\MediaAsset;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class MediaDownloadTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        Storage::fake('media_private');
    }

    /** @test */
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

    /** @test */
    public function admins_can_download_private_media()
    {
        $admin = User::factory()->create();
        // Simulujeme roli super_admin (pokud ji systém má, nebo prostě can())
        // V našem kontroleru kontrolujeme hasRole('super_admin')

        // Pokud nemáme role v testu, můžeme je přidat přes Spatie
        // Ale pro jednoduchost testu se ujistíme, že hasRole vrací true
        $admin = \Mockery::mock($admin)->makePartial();
        $admin->shouldReceive('hasRole')->with('super_admin')->andReturn(true);
        $this->actingAs($admin);

        $asset = MediaAsset::create([
            'title' => 'Secret',
            'access_level' => 'private',
        ]);
        $file = UploadedFile::fake()->create('secret.pdf');
        $media = $asset->addMedia($file)->toMediaCollection('default', 'media_private');

        // Musíme zajistit, aby soubor fyzicky existoval pro response()->download()
        $relativeDiskPath = app(\App\Services\Media\CustomPathGenerator::class)->getPath($media).$media->file_name;
        Storage::disk('media_private')->put($relativeDiskPath, 'content');

        $response = $this->get(route('media.download', ['uuid' => $media->uuid]));

        $response->assertStatus(200);
        $response->assertHeader('Content-Disposition', 'attachment; filename=secret.pdf');
    }
}
