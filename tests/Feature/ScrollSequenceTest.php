<?php

namespace Tests\Feature;

use App\Models\Role;
use App\Models\Setting;
use App\Models\User;
use App\Services\ScrollSequenceService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Tests\TestCase;

class ScrollSequenceTest extends TestCase
{
    use RefreshDatabase;

    private User $adminUser;
    private User $buyerUser;
    private string $tempPublicFolder;

    protected function setUp(): void
    {
        parent::setUp();

        $this->tempPublicFolder = public_path('images/_test_seq_frames');

        $this->buyerUser = User::factory()->create([
            'first_name' => 'Asha',
            'last_name' => 'Verma',
            'email' => 'asha.buyer@example.com',
        ]);

        $this->adminUser = User::factory()->create([
            'first_name' => 'Master',
            'last_name' => 'Admin',
            'email' => 'crab.hashir@gmail.com',
        ]);
        $adminRole = Role::firstOrCreate(['name' => 'admin'], ['display_name' => 'Admin']);
        $this->adminUser->roles()->syncWithoutDetaching([$adminRole->id]);
    }

    protected function tearDown(): void
    {
        ScrollSequenceService::clearUploadedFrames();

        if (is_dir($this->tempPublicFolder)) {
            foreach (glob($this->tempPublicFolder . DIRECTORY_SEPARATOR . '*') ?: [] as $file) {
                if (is_file($file)) {
                    @unlink($file);
                }
            }
            @rmdir($this->tempPublicFolder);
        }

        parent::tearDown();
    }

    private function tinyPngBytes(): string
    {
        return base64_decode('iVBORw0KGgoAAAANSUhEUgAAAAEAAAABCAQAAAC1HAwCAAAAC0lEQVR42mP8/x8AAwMCAO+ip1sAAAAASUVORK5CYII=');
    }

    public function test_homepage_renders_scroll_sequence_stage_with_resolved_source(): void
    {
        ScrollSequenceService::clearUploadedFrames();
        $expected = ScrollSequenceService::resolveSequence();

        $response = $this->get('/');

        $response->assertStatus(200);
        $response->assertSee('k71-scroll-stage', false);
        $response->assertSee('k71-seq-canvas', false);
        $response->assertSee('k71-seq-config', false);
        $response->assertSee('"source":"' . $expected['source'] . '"', false);
    }

    public function test_homepage_resolves_natural_sorted_images_from_custom_public_images_subfolder(): void
    {
        ScrollSequenceService::clearUploadedFrames();
        @mkdir($this->tempPublicFolder, 0775, true);

        $png = $this->tinyPngBytes();
        file_put_contents($this->tempPublicFolder . DIRECTORY_SEPARATOR . 'frame_10.png', $png);
        file_put_contents($this->tempPublicFolder . DIRECTORY_SEPARATOR . 'frame_2.png', $png);
        file_put_contents($this->tempPublicFolder . DIRECTORY_SEPARATOR . 'frame_1.png', $png);

        Setting::set('scroll_seq_folder', '_test_seq_frames', 'homepage', 'string');

        $response = $this->get('/');
        $response->assertStatus(200);

        $html = $response->getContent();
        $pos1 = strpos($html, '/images/_test_seq_frames/frame_1.png');
        $pos2 = strpos($html, '/images/_test_seq_frames/frame_2.png');
        $pos10 = strpos($html, '/images/_test_seq_frames/frame_10.png');

        $this->assertNotFalse($pos1);
        $this->assertNotFalse($pos2);
        $this->assertNotFalse($pos10);
        $this->assertTrue($pos1 < $pos2 && $pos2 < $pos10, 'Frames must resolve in ascending natural order.');
    }

    public function test_admin_scroll_frame_routes_require_admin_authorization(): void
    {
        $file = UploadedFile::fake()->image('frame_1.png', 80, 80);

        $this->post('/admin/homepage/scroll-frames/batch', ['frames' => [$file]])
            ->assertRedirect();

        $this->actingAs($this->buyerUser)
            ->post('/admin/homepage/scroll-frames/batch', ['frames' => [$file]])
            ->assertRedirect();

        $this->actingAs($this->buyerUser)
            ->delete('/admin/homepage/scroll-frames')
            ->assertRedirect();

        $this->assertNotSame('uploaded', ScrollSequenceService::resolveSequence()['source']);
    }

    public function test_admin_can_upload_batch_frames_update_settings_and_clear_frames(): void
    {
        $this->actingAs($this->adminUser)
            ->get('/admin/homepage')
            ->assertStatus(200)
            ->assertSee('Scroll Sequence', false)
            ->assertSee('Video to Frames (In-Browser)', false);

        $frame1 = UploadedFile::fake()->createWithContent('01.png', $this->tinyPngBytes());
        $frame2 = UploadedFile::fake()->createWithContent('02.png', $this->tinyPngBytes());

        $uploadResp = $this->actingAs($this->adminUser)
            ->postJson('/admin/homepage/scroll-frames/batch', [
                'start_index' => 1,
                'reset' => 1,
                'frames' => [$frame1, $frame2],
            ]);

        $uploadResp->assertStatus(200)
            ->assertJson([
                'ok' => true,
                'written' => 2,
                'sequence' => [
                    'source' => 'uploaded',
                    'count' => 2,
                ],
            ]);

        $this->actingAs($this->adminUser)
            ->put('/admin/homepage', [
                'scroll_seq_enabled' => '1',
                'scroll_seq_height_vh' => '320',
                'scroll_seq_smoothing' => '0.14',
                'scroll_seq_beat1_title' => 'Custom Motion Chapter',
            ])
            ->assertRedirect();

        $homeResp = $this->get('/');
        $homeResp->assertStatus(200)
            ->assertSee('height: 320vh;', false)
            ->assertSee('Custom Motion Chapter', false)
            ->assertSee('/storage/scroll-frames/frame_00001.png', false)
            ->assertSee('/storage/scroll-frames/frame_00002.png', false);

        $clearResp = $this->actingAs($this->adminUser)
            ->deleteJson('/admin/homepage/scroll-frames');

        $clearResp->assertStatus(200)
            ->assertJson(['ok' => true]);

        $this->assertNotSame('uploaded', ScrollSequenceService::resolveSequence()['source']);
    }

    public function test_admin_can_upload_zip_archive_with_traversal_protection(): void
    {
        if (!class_exists(\ZipArchive::class)) {
            $this->markTestSkipped('ZipArchive extension not installed.');
        }

        $zipPath = tempnam(sys_get_temp_dir(), 'k71seq_') . '.zip';
        $zip = new \ZipArchive();
        $this->assertTrue($zip->open($zipPath, \ZipArchive::CREATE | \ZipArchive::OVERWRITE) === true);

        $png = $this->tinyPngBytes();
        $zip->addFromString('../../evil.png', $png);
        $zip->addFromString('__MACOSX/._shot_1.png', $png);
        $zip->addFromString('notes.txt', 'not an image');
        $zip->addFromString('frames/shot_2.png', $png);
        $zip->addFromString('frames/shot_1.png', $png);
        $zip->close();

        try {
            $uploadedZip = new UploadedFile($zipPath, 'frames.zip', 'application/zip', null, true);
            $resp = $this->actingAs($this->adminUser)
                ->postJson('/admin/homepage/scroll-frames/zip', [
                    'archive' => $uploadedZip,
                ]);

            $resp->assertStatus(200)
                ->assertJson([
                    'ok' => true,
                    'sequence' => [
                        'source' => 'uploaded',
                        'count' => 2,
                    ],
                ]);

            $resolved = ScrollSequenceService::resolveSequence();
            $this->assertSame([
                '/storage/scroll-frames/frame_00001.png',
                '/storage/scroll-frames/frame_00002.png',
            ], $resolved['frames']);
        } finally {
            @unlink($zipPath);
        }
    }
}
