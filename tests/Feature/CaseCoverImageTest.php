<?php

namespace Tests\Feature;

use App\Livewire\Cases\CaseForm;
use App\Models\{CaseModel, User};
use App\Services\{CaseCoverImage, ReverseGeocoder};
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\{DB, Storage};
use Livewire\Livewire;
use Tests\TestCase;

class CaseCoverImageTest extends TestCase
{
    use RefreshDatabase;

    private int $statusId;

    private int $categoryId;

    protected function setUp(): void
    {
        parent::setUp();
        Storage::fake('public');

        // Halaman detail memanggil geocoder (Nominatim/PostGIS); di test diganti data tetap.
        $this->mock(ReverseGeocoder::class, function ($mock) {
            $mock->shouldReceive('getLocation')->andReturn(['province' => 'Kalimantan Timur', 'district' => 'Samarinda', 'village' => null]);
            $mock->shouldReceive('cached')->andReturn(null);
        });

        $this->statusId = DB::table('statuses')->insertGetId(['key' => 'investigation', 'name' => 'Penyidikan', 'created_at' => now(), 'updated_at' => now()]);
        $this->categoryId = DB::table('categories')->insertGetId(['slug' => 'tambang', 'created_at' => now(), 'updated_at' => now()]);
    }

    public function test_cover_is_redrawn_without_metadata_and_scaled_down(): void
    {
        $file = $this->jpegWithExif(2400, 1350);
        $this->assertStringContainsString('GPSLatitude', file_get_contents($file->getRealPath()));

        $path = app(CaseCoverImage::class)->store($file);

        $this->assertStringStartsWith('case-covers/', $path);
        $saved = Storage::disk('public')->get($path);
        [$width, $height, $type] = getimagesizefromstring($saved);
        $this->assertSame([CaseCoverImage::MAX_WIDTH, 900, IMAGETYPE_JPEG], [$width, $height, $type]);
        // Metadata kamera (termasuk lokasi GPS) tidak ikut tersimpan.
        $this->assertStringNotContainsString('Exif', $saved);
        $this->assertStringNotContainsString('GPSLatitude', $saved);
    }

    public function test_case_form_saves_replaces_and_removes_the_cover(): void
    {
        $case = $this->publicCase();
        $user = User::factory()->create();

        $form = fn () => Livewire::actingAs($user)->test(CaseForm::class, ['caseId' => $case->id]);

        $form()->set('cover', UploadedFile::fake()->image('tambang.jpg', 1200, 675))
            ->set('cover_caption_id', 'Bukaan tambang di hutan lindung')
            ->set('cover_credit', 'Dok. Auriga Nusantara')
            ->call('save')
            ->assertHasNoErrors();

        $first = $case->fresh()->cover_image;
        $this->assertNotNull($first);
        Storage::disk('public')->assertExists($first);
        $this->assertSame('Dok. Auriga Nusantara', $case->fresh()->cover_credit);
        $this->assertSame('Bukaan tambang di hutan lindung', $case->translations()->where('locale', 'id')->value('cover_caption'));

        // Foto baru menggantikan foto lama; berkas lama dihapus.
        $form()->set('cover', UploadedFile::fake()->image('baru.png', 1600, 900))->call('save')->assertHasNoErrors();
        $second = $case->fresh()->cover_image;
        $this->assertNotSame($first, $second);
        Storage::disk('public')->assertMissing($first);
        Storage::disk('public')->assertExists($second);

        // Tombol hapus mengosongkan foto sampul.
        $form()->call('removeCoverImage')->call('save')->assertHasNoErrors();
        $this->assertNull($case->fresh()->cover_image);
        Storage::disk('public')->assertMissing($second);
    }

    public function test_too_small_or_non_image_cover_is_rejected(): void
    {
        $case = $this->publicCase();
        $form = Livewire::actingAs(User::factory()->create())->test(CaseForm::class, ['caseId' => $case->id]);

        $form->set('cover', UploadedFile::fake()->image('kecil.jpg', 640, 360))->assertHasErrors(['cover' => 'dimensions']);
        $form->set('cover', UploadedFile::fake()->create('dokumen.pdf', 100, 'application/pdf'))->assertHasErrors(['cover']);
        $this->assertNull($case->fresh()->cover_image);
    }

    public function test_public_pages_use_the_cover_and_never_the_evidence_files(): void
    {
        $case = $this->publicCase(['bukti' => ['cases/bukti-rahasia.jpg']]);

        // Berkas bukti tidak muncul di halaman mana pun.
        $this->get('/id/verified-cases')->assertOk()->assertDontSee('bukti-rahasia.jpg');
        $this->get($case->publicUrl('id'))->assertOk()
            ->assertDontSee('bukti-rahasia.jpg')
            ->assertSee('<meta property="og:image" content="'.asset('img/image.png').'">', false);

        $case->update(['cover_image' => 'case-covers/sampul.jpg', 'cover_credit' => 'Dok. Auriga']);
        $case->translations()->where('locale', 'id')->update(['cover_caption' => 'Bukaan tambang']);
        $coverUrl = asset('storage/case-covers/sampul.jpg');

        $this->get($case->publicUrl('id'))->assertOk()
            ->assertSee('<meta property="og:image" content="'.$coverUrl.'">', false)
            ->assertSee('<figcaption', false)
            ->assertSee('Bukaan tambang')
            ->assertDontSee('bukti-rahasia.jpg');
    }

    private function publicCase(array $attributes = []): CaseModel
    {
        $case = CaseModel::create($attributes + [
            'case_number' => 'CASE-COVER',
            'status_id' => $this->statusId,
            'category_ids' => [$this->categoryId],
            'event_date' => '2026-03-01',
            'latitude' => -0.5022,
            'longitude' => 117.1536,
            'is_public' => true,
            'published_at' => now(),
        ]);
        $case->translations()->create(['locale' => 'id', 'title' => 'Suap Perizinan Tambang', 'description' => '<p>Ringkasan.</p>']);

        return $case;
    }

    /** JPEG dengan segmen EXIF (berisi penanda GPS), seperti foto dari ponsel. */
    private function jpegWithExif(int $width, int $height): UploadedFile
    {
        $image = imagecreatetruecolor($width, $height);
        imagefill($image, 0, 0, imagecolorallocate($image, 40, 120, 40));
        ob_start();
        imagejpeg($image);
        $jpeg = (string) ob_get_clean();

        $exif = "Exif\0\0".'MM'."\0\x2A\0\0\0\x08\0\0".'GPSLatitude -0.5022';
        $segment = "\xFF\xE1".pack('n', strlen($exif) + 2).$exif;
        $path = tempnam(sys_get_temp_dir(), 'cover').'.jpg';
        file_put_contents($path, substr($jpeg, 0, 2).$segment.substr($jpeg, 2));

        return new UploadedFile($path, 'foto-ponsel.jpg', 'image/jpeg', null, true);
    }
}
