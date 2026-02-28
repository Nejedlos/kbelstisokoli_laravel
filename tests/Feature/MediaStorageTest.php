<?php

namespace Tests\Feature;

use App\Models\MediaAsset;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class MediaStorageTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        Storage::fake('media_public');
        Storage::fake('media_private');
    }

    /** @test */
    public function it_stores_public_files_in_correct_structure()
    {
        $asset = MediaAsset::create([
            'title' => 'Testovaci obrazek',
            'access_level' => 'public',
        ]);

        $file = UploadedFile::fake()->image('test-image.jpg');

        $asset->addMedia($file)
            ->toMediaCollection('default', 'media_public');

        $media = $asset->getFirstMedia('default');

        // Očekávaná cesta: default/YEAR/MONTH/ID/testovaci-obrazek.jpg (pokud by se přejmenoval)
        // V tomto testu voláme addMedia přímo, takže se neaplikuje Filament naming,
        // ale náš PathGenerator by měl fungovat.

        $year = now()->format('Y');
        $month = now()->format('m');
        $id = $media->id;

        $expectedPath = "default/{$year}/{$month}/{$id}/".$media->file_name;

        Storage::disk('media_public')->assertExists($expectedPath);
    }

    /** @test */
    public function it_generates_secure_url_for_private_files()
    {
        $asset = MediaAsset::create([
            'title' => 'Soukromy dokument',
            'access_level' => 'private',
        ]);

        $file = UploadedFile::fake()->create('contract.pdf', 100);

        $asset->addMedia($file)
            ->toMediaCollection('default', 'media_private');

        $url = $asset->getUrl();

        $this->assertStringContainsString('/media/download/', $url);
        $this->assertStringContainsString($asset->getFirstMedia('default')->uuid, $url);
    }

    /** @test */
    public function it_renames_file_on_title_change()
    {
        $asset = MediaAsset::create([
            'title' => 'Puvodni nazev',
            'access_level' => 'public',
        ]);

        $file = UploadedFile::fake()->image('old.jpg');
        $asset->addMedia($file)->toMediaCollection('default', 'media_public');

        $asset->update(['title' => 'Novy krasny nazev']);

        $media = $asset->fresh()->getFirstMedia('default');

        $this->assertEquals('novy-krasny-nazev.jpg', $media->file_name);

        $year = now()->format('Y');
        $month = now()->format('m');
        $expectedPath = "default/{$year}/{$month}/{$media->id}/novy-krasny-nazev.jpg";

        Storage::disk('media_public')->assertExists($expectedPath);
    }
}
