<?php

namespace CodeZero\Translator\Tests\Feature;

use CodeZero\Translator\Models\TranslationFile;
use CodeZero\Translator\Tests\TestCase;

class CreateTranslationFileTest extends TestCase
{
    /** @test */
    public function it_adds_a_translation_file_to_the_database()
    {
        $response = $this->postJson(route('translator.files.store'), [
            'name' => 'my-file',
            'package' => 'my-package',
        ]);

        $response->assertStatus(201);

        $this->assertDatabaseHas('translation_files', [
            'name' => 'my-file',
            'package' => 'my-package',
        ]);
    }

    /** @test */
    public function translation_file_name_is_required()
    {
        $response = $this->postJson(route('translator.files.store'), [
            'name' => null,
        ]);

        $response->assertStatus(422);
        $response->assertJsonValidationErrors('name');
    }

    /** @test */
    public function translation_file_name_should_be_unique_within_a_package()
    {
        $existingFile = factory(TranslationFile::class)->create([
            'name' => 'existing-file',
            'package' => 'some-package',
        ]);

        $response = $this->postJson(route('translator.files.store'), [
            'name' => $existingFile->name,
            'package' => $existingFile->package,
        ]);

        $response->assertStatus(422);
        $response->assertJsonValidationErrors('name');

        $response = $this->postJson(route('translator.files.store'), [
            'name' => $existingFile->name,
            'package' => 'other-package',
        ]);

        $response->assertStatus(201);
    }

    /** @test */
    public function translation_file_name_and_package_must_contain_only_letters_numbers_and_dashes()
    {
        $response = $this->postJson(route('translator.files.store'), [
            'name' => 'my-file-1',
            'package' => 'some-package-1',
        ]);

        $response->assertStatus(201);

        $response = $this->postJson(route('translator.files.store'), [
            'name' => 'my_file',
            'package' => 'some_package',
        ]);

        $response->assertStatus(422);
        $response->assertJsonValidationErrors(['name', 'package']);
    }

    /** @test */
    public function translation_package_is_optional()
    {
        $response = $this->postJson(route('translator.files.store'), [
            'name' => 'my-file',
            'package' => null,
        ]);

        $response->assertStatus(201);
    }
}
