<?php

namespace CodeZero\Translator\Tests\Feature;

use CodeZero\Translator\Models\TranslationFile;
use CodeZero\Translator\Tests\Concerns\ChecksForValidationErrors;
use CodeZero\Translator\Tests\TestCase;

class CreateTranslationFileTest extends TestCase
{
    use ChecksForValidationErrors;

    /** @test */
    public function it_adds_a_translation_file()
    {
        $this->postJson(route('translator.files.store'), [
            'name' => 'my-file',
            'package' => 'my-package',
        ])->assertStatus(200);

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

        $this->assertValidationError($response, 'name');
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

        $this->assertValidationError($response, 'name');

        $response = $this->postJson(route('translator.files.store'), [
            'name' => $existingFile->name,
            'package' => 'other-package',
        ]);

        $response->assertStatus(200);
    }

    /** @test */
    public function translation_file_name_and_package_must_contain_only_letters_numbers_and_dashes()
    {
        $response = $this->postJson(route('translator.files.store'), [
            'name' => 'my-file-1',
            'package' => 'some-package-1',
        ]);

        $response->assertStatus(200);

        $response = $this->postJson(route('translator.files.store'), [
            'name' => 'my_file',
            'package' => 'some_package',
        ]);

        $this->assertValidationError($response, ['name', 'package']);
    }

    /** @test */
    public function translation_package_is_optional()
    {
        $this->postJson(route('translator.files.store'), [
            'name' => 'my-file',
            'package' => null,
        ])->assertStatus(200);
    }
}
