<?php

namespace CodeZero\Translator\Tests\Feature;

use CodeZero\Translator\Models\Translation;
use CodeZero\Translator\Tests\TestCase;

class DeleteTranslationTest extends TestCase
{
    /** @test */
    public function it_deletes_a_translation()
    {
        $translation = factory(Translation::class)->create();

        $this->deleteJson(route('translator.translations.destroy', $translation))
            ->assertStatus(200);

        $this->assertEmpty(Translation::all());
    }
}
