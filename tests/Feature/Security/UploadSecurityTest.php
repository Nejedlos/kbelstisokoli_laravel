<?php

namespace Tests\Feature\Security;

use App\Models\User;
use App\Livewire\Member\AvatarModal;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Livewire\Livewire;
use Tests\TestCase;

class UploadSecurityTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        Storage::fake('public');
        Storage::fake('local');
    }

    /**
     * Test that user cannot upload non-image file as avatar.
     * ID: TEST-UPLOAD-01
     */
    public function test_user_cannot_upload_php_file_as_avatar(): void
    {
        $user = User::factory()->create(['is_active' => true]);

        $phpFile = UploadedFile::fake()->create('shell.php', 100, 'application/x-php');

        $component = Livewire::actingAs($user)
            ->test(AvatarModal::class)
            ->set('avatarFile', [$phpFile])
            ->assertHasErrors(['avatarFile.*']);
    }

    /**
     * Test that guest cannot upload avatar.
     * ID: TEST-UPLOAD-02
     */
    public function test_guest_cannot_upload_avatar(): void
    {
        $file = UploadedFile::fake()->image('avatar.jpg');

        Livewire::test(AvatarModal::class)
            ->set('avatarFile', [$file])
            ->call('saveAvatar', 'data:image/png;base64,iVBORw0KGgoAAAANSUhEUgAAAAEAAAABCAYAAAAfFcSJAAAADUlEQVR42mP8/5+hHgAHggJ/PchI7wAAAABJRU5ErkJggg==')
            ->assertForbidden();
    }

    /**
     * Test that user cannot upload avatar for another user.
     * ID: TEST-UPLOAD-03
     */
    public function test_user_cannot_upload_avatar_for_another_user(): void
    {
        $userA = User::factory()->create(['is_active' => true]);
        $userB = User::factory()->create(['is_active' => true]);

        Livewire::actingAs($userA)
            ->test(AvatarModal::class, ['userId' => $userB->id])
            ->call('saveAvatar', 'data:image/png;base64,iVBORw0KGgoAAAANSUhEUgAAAAEAAAABCAYAAAAfFcSJAAAADUlEQVR42mP8/5+hHgAHggJ/PchI7wAAAABJRU5ErkJggg==')
            ->assertForbidden();
    }

    /**
     * Test that admin can upload avatar for another user.
     * ID: TEST-UPLOAD-04
     */
    public function test_admin_can_upload_avatar_for_another_user(): void
    {
        $admin = $this->createAdmin();
        $userB = User::factory()->create(['is_active' => true]);

        Livewire::actingAs($admin)
            ->test(AvatarModal::class, ['userId' => $userB->id])
            ->call('saveAvatar', 'data:image/png;base64,iVBORw0KGgoAAAANSUhEUgAAAAEAAAABCAYAAAAfFcSJAAAADUlEQVR42mP8/5+hHgAHggJ/PchI7wAAAABJRU5ErkJggg==')
            ->assertStatus(200);

        $userB->refresh();
        $this->assertNotEmpty($userB->getMedia('avatar'));
    }
}
