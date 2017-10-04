<?php

namespace CodeZero\Translator\Tests\Feature;

use CodeZero\Translator\Models\Translation;
use CodeZero\Translator\Models\TranslationFile;
use CodeZero\Translator\Tests\TestCase;

class DeleteTranslationFileTest extends TestCase
{
    /** @test */
    public function it_deletes_a_translation_file_and_all_its_translations()
    {
        $file = factory(TranslationFile::class)->create();
        factory(Translation::class, 2)->create(['file_id' => $file->id]);

        $this->deleteJson(route('translator.files.destroy', $file))
            ->assertStatus(200);

        $this->assertEmpty(TranslationFile::all());
        $this->assertEmpty(Translation::all());
    }
}
