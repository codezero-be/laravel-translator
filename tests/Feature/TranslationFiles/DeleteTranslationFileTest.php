<?php

namespace CodeZero\Translator\Tests\Feature\TranslationFiles;

use CodeZero\Translator\Models\TranslationKey;
use CodeZero\Translator\Models\TranslationFile;
use CodeZero\Translator\TranslatorRoutes;
use CodeZero\Translator\Tests\TestCase;
use Illuminate\Foundation\Testing\RefreshDatabase;

class DeleteTranslationFileTest extends TestCase
{
    use RefreshDatabase;

    /** @test */
    public function it_deletes_a_translation_file_and_all_its_translations()
    {
        $this->withoutExceptionHandling();

        TranslatorRoutes::register();

        $file = TranslationFile::create([
            'vendor' => null,
            'filename' => 'test-file',
        ]);

        TranslationKey::create([
            'file_id' => $file->id,
            'key' => 'test-key',
        ]);

        $this->assertEquals(1, TranslationFile::count());
        $this->assertEquals(1, TranslationKey::count());

        $this->actingAsUser()->deleteJson(route('translator.files.destroy', [$file]))
            ->assertSuccessful();

        $this->assertEquals(0, TranslationFile::count());
        $this->assertEquals(0, TranslationKey::count());
    }

    /** @test */
    public function it_returns_the_deleted_translation_file()
    {
        $this->withoutExceptionHandling();

        TranslatorRoutes::register();

        $file = TranslationFile::create([
            'vendor' => 'vendor-name',
            'filename' => 'test-file',
        ]);

        $response = $this->actingAsUser()->deleteJson(route('translator.files.destroy', [$file]));

        $response->assertSuccessful();
        $this->assertTrue($response->original->is($file));
        $this->assertEquals('vendor-name', $response->original->vendor);
        $this->assertEquals('test-file', $response->original->filename);
    }
}
