<?php

namespace CodeZero\Translator\Tests\Feature\TranslationKeys;

use CodeZero\Translator\Models\TranslationFile;
use CodeZero\Translator\Models\TranslationKey;
use CodeZero\Translator\Tests\TestCase;
use Illuminate\Foundation\Testing\RefreshDatabase;

class DeleteTranslationKeyTest extends TestCase
{
    use RefreshDatabase;

    /** @test */
    public function it_deletes_a_translation_key()
    {
        $this->withoutExceptionHandling();

        $file = TranslationFile::create([
            'vendor' => null,
            'filename' => 'test-file',
        ]);

        $key = TranslationKey::create([
            'file_id' => $file->id,
            'key' => 'test-key',
        ]);

        $this->assertEquals(1, TranslationKey::count());

        $this->actingAsUser()->deleteJson(route('translator.keys.destroy', [$key]))
            ->assertSuccessful();

        $this->assertEquals(0, TranslationKey::count());
    }

    /** @test */
    public function it_returns_the_deleted_translation_key()
    {
        $this->withoutExceptionHandling();

        $file = TranslationFile::create([
            'vendor' => null,
            'filename' => 'test-file',
        ]);

        $key = TranslationKey::create([
            'file_id' => $file->id,
            'key' => 'test-key',
            'translations' => [
                'en' => 'existing translation [en]',
                'nl' => 'existing translation [nl]',
            ],
        ]);

        $response = $this->actingAsUser()->deleteJson(route('translator.keys.destroy', [$key]));

        $response->assertSuccessful();
        $this->assertTrue($response->original->is($key));
        $this->assertEquals('test-key', $response->original->key);
        $this->assertEquals([
            'en' => 'existing translation [en]',
            'nl' => 'existing translation [nl]',
        ], $response->original->translations);
    }
}
