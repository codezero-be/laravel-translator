<?php

namespace CodeZero\Translator\Tests\Feature\TranslationFiles;

use CodeZero\Translator\Models\TranslationFile;
use CodeZero\Translator\Tests\TestCase;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Foundation\Testing\RefreshDatabase;

class ListTranslationFilesTest extends TestCase
{
    use RefreshDatabase;

    /** @test */
    public function it_lists_translation_files()
    {
        $this->withoutExceptionHandling();

        $first = TranslationFile::create(['filename' => 'first']);
        $second = TranslationFile::create(['filename' => 'second']);

        $response = $this->actingAsUser()->getJson(route('translator.files.index'));

        $response->assertStatus(200);
        $translationFiles = $response->original;
        $this->assertInstanceOf(Collection::class, $translationFiles);
        $this->assertCount(2, $translationFiles);
        $this->assertTrue($translationFiles->first()->is($first));
        $this->assertTrue($translationFiles->last()->is($second));
        $this->assertTrue($translationFiles->first()->relationLoaded('keys'));
        $this->assertTrue($translationFiles->last()->relationLoaded('keys'));
    }
}
