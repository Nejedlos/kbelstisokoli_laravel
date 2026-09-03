<?php

namespace Tests\Feature\Security;

use App\Models\MediaAsset;
use App\Models\User;
use Database\Seeders\PermissionSeeder;
use Database\Seeders\RoleSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class IdorProtectionTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        $this->seed(PermissionSeeder::class);
        $this->seed(RoleSeeder::class);

        Storage::fake('public');
        Storage::fake('media_private');
    }

    /**
     * Test that user cannot download another user's private media.
     * ID: TEST-IDOR-01
     */
    public function test_user_cannot_download_other_user_avatar(): void
    {
        $userA = User::factory()->create(['is_active' => true]);
        $userB = User::factory()->create(['is_active' => true]);

        // Add private media to user A
        $file = UploadedFile::fake()->image('avatar.jpg');
        $media = $userA->addMedia($file)->toMediaCollection('avatar');

        // User B tries to download User A's avatar via uuid
        $response = $this->actingAs($userB)->get("/media/download/{$media->uuid}");

        // Based on UserPolicy, userB cannot view userA
        $response->assertStatus(403);
    }

    /**
     * Test that guest cannot download member-only MediaAsset.
     * ID: TEST-IDOR-02
     */
    public function test_guest_cannot_download_member_media_asset(): void
    {
        $asset = MediaAsset::create([
            'title' => 'Member Only Asset',
            'access_level' => 'member',
        ]);

        $file = UploadedFile::fake()->image('document.pdf');
        $media = $asset->addMedia($file)->toMediaCollection('default');

        $response = $this->get("/media/download/{$media->uuid}");

        $response->assertStatus(401); // Unauthorized for guest
    }

    /**
     * Test that player can download member-only MediaAsset.
     * ID: TEST-IDOR-03
     */
    public function test_player_can_download_member_media_asset(): void
    {
        $player = User::factory()->create(['is_active' => true]);
        $player->assignRole('player');

        $asset = MediaAsset::create([
            'title' => 'Member Only Asset',
            'access_level' => 'member',
        ]);

        $file = UploadedFile::fake()->image('document.pdf');
        $media = $asset->addMedia($file)->toMediaCollection('default');

        // We need to make sure the file exists on the fake disk for download to work without 404
        // The addMedia above should have placed it there.

        $response = $this->actingAs($player)->get("/media/download/{$media->uuid}");

        $response->assertStatus(200);
    }

    /**
     * Test that user cannot download private MediaAsset without permission.
     * ID: TEST-IDOR-04
     */
    public function test_user_cannot_download_private_media_asset(): void
    {
        $player = User::factory()->create(['is_active' => true]);
        $player->assignRole('player'); // player doesn't have view_private_media

        $asset = MediaAsset::create([
            'title' => 'Private Asset',
            'access_level' => 'private',
        ]);

        $file = UploadedFile::fake()->image('secret.pdf');
        $media = $asset->addMedia($file)->toMediaCollection('default');

        $response = $this->actingAs($player)->get("/media/download/{$media->uuid}");

        $response->assertStatus(403);
    }
}
