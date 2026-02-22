<?php

namespace Tests\Feature;

use App\Models\Post;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class PostImageUploadTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        Storage::fake('media_public');
        Storage::fake('public'); // Pro temporary nahrávání v Livewire
    }

    public function test_can_upload_featured_image_to_post()
    {
        $user = User::factory()->create();
        $this->actingAs($user);

        $post = Post::create([
            'title' => 'Testovaci clanek',
            'slug' => 'testovaci-clanek',
            'status' => 'published',
        ]);

        $file = UploadedFile::fake()->image('nahled.jpg');

        // Simulujeme přidání média přes Media Library (v adminu to dělá Filament)
        $post->addMedia($file)
            ->toMediaCollection('featured_image');

        $media = $post->getFirstMedia('featured_image');

        $this->assertNotNull($media);
        $this->assertEquals('featured_image', $media->collection_name);
        $this->assertEquals('media_public', $media->disk);

        // Kontrola cesty (CustomPathGenerator)
        // Formát: {kolekce}/{rok}/{mesic}/{id}/{filename}
        $expectedPath = 'featured_image/' . now()->format('Y/m') . '/' . $media->id . '/' . $media->file_name;
        Storage::disk('media_public')->assertExists($expectedPath);
    }

    public function test_post_image_is_renamed_when_title_changes()
    {
        $user = User::factory()->create();
        $this->actingAs($user);

        $post = Post::create([
            'title' => 'Puvodni titulek',
            'slug' => 'puvodni-titulek',
            'status' => 'published',
        ]);

        $file = UploadedFile::fake()->image('obrazek.jpg');
        $post->addMedia($file)->toMediaCollection('featured_image');

        $media = $post->getFirstMedia('featured_image');
        $this->assertEquals('obrazek.jpg', $media->file_name);

        // Změna titulku
        $post->update(['title' => 'Novy krasny titulek']);

        $media->refresh();
        $this->assertEquals('novy-krasny-titulek.jpg', $media->file_name);

        // Kontrola, že soubor na disku existuje pod novým jménem
        $expectedPath = 'featured_image/' . now()->format('Y/m') . '/' . $media->id . '/novy-krasny-titulek.jpg';
        Storage::disk('media_public')->assertExists($expectedPath);
    }
}
