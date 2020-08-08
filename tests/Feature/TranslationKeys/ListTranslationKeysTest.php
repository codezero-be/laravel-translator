<?php

namespace CodeZero\Translator\Tests\Feature\TranslationKeys;

use CodeZero\Translator\Models\TranslationFile;
use CodeZero\Translator\Models\TranslationKey;
use CodeZero\Translator\TranslatorRoutes;
use CodeZero\Translator\Tests\TestCase;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Foundation\Testing\RefreshDatabase;

class ListTranslationKeysTest extends TestCase
{
    use RefreshDatabase;

    /** @test */
    public function it_lists_translation_keys_of_a_translation_file()
    {
        $this->withoutExceptionHandling();

        TranslatorRoutes::register();

        $fileA = TranslationFile::create(['filename' => 'test-file']);
        $fileB = TranslationFile::create(['filename' => 'test-file']);

        $key1 = TranslationKey::create(['file_id' => $fileA->id, 'key' => 'key-1']);
        $key2 = TranslationKey::create(['file_id' => $fileA->id, 'key' => 'key-2']);
        TranslationKey::create(['file_id' => $fileB->id, 'key' => 'key-3']);

        $response = $this->actingAsUser()->getJson(route('translator.keys.index', [$fileA]));

        $response->assertStatus(200);

        $translationKeys = $response->original;
        $this->assertInstanceOf(Collection::class, $translationKeys);
        $this->assertCount(2, $translationKeys);
        $this->assertTrue($translationKeys->first()->is($key1));
        $this->assertTrue($translationKeys->last()->is($key2));
    }
}
