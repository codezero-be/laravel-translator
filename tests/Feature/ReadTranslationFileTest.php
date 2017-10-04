<?php

namespace CodeZero\Translator\Tests\Feature;

use CodeZero\Translator\Models\TranslationFile;
use CodeZero\Translator\Tests\TestCase;

class ReadTranslationFileTest extends TestCase
{
    /** @test */
    public function it_lists_translation_files()
    {
        factory(TranslationFile::class)->create(['name' => 'first']);
        factory(TranslationFile::class)->create(['name' => 'second']);

        $this->get(route('translator.files'))
            ->assertStatus(200)
            ->assertSee('first')
            ->assertSee('second');
    }
}
