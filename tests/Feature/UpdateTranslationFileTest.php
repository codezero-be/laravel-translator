<?php

namespace CodeZero\Translator\Tests\Feature;

use CodeZero\Translator\Models\TranslationFile;
use CodeZero\Translator\Tests\TestCase;

class UpdateTranslationFileTest extends TestCase
{
    /** @test */
    public function it_updates_a_translation_file()
    {
        $file = factory(TranslationFile::class)->create([
            'name' => 'my-file',
        ]);

        $response = $this->patchJson(route('translator.files.update', $file), [
            'name' => 'new-file',
        ]);

        $response->assertStatus(200);
        $this->assertEquals('new-file', $file->fresh()->name);
    }

    /** @test */
    public function translation_file_name_is_required()
    {
        $file = factory(TranslationFile::class)->create([
            'name' => 'my-file',
        ]);

        $response = $this->patchJson(route('translator.files.update', $file), [
            'name' => null,
        ]);

        $response->assertStatus(422);
        $response->assertJsonValidationErrors('name');
    }

    /** @test */
    public function translation_file_name_should_be_unique_within_a_package()
    {
        $currentFile = factory(TranslationFile::class)->create([
            'name' => 'current-file',
            'package' => 'some-package',
        ]);

        $otherFile = factory(TranslationFile::class)->create([
            'name' => 'other-file',
            'package' => $currentFile->package,
        ]);

        $response = $this->patchJson(route('translator.files.update', $currentFile), [
            'name' => $otherFile->name,
            'package' => $currentFile->package,
        ]);

        $response->assertStatus(422);
        $response->assertJsonValidationErrors('name');

        $this->patchJson(route('translator.files.update', $currentFile), [
            'name' => $currentFile->name,
            'package' => $currentFile->package,
        ])->assertStatus(200);

        $this->patchJson(route('translator.files.update', $currentFile), [
            'name' => $otherFile->name,
            'package' => 'new-package',
        ])->assertStatus(200);
    }

    /** @test */
    public function translation_file_name_and_package_must_contain_only_letters_numbers_and_dashes()
    {
        $file = factory(TranslationFile::class)->create([
            'name' => 'my-file',
            'package' => 'some-package',
        ]);

        $response = $this->patchJson(route('translator.files.update', $file), [
            'name' => 'my_file',
            'package' => 'some_package',
        ]);

        $response->assertStatus(422);
        $response->assertJsonValidationErrors(['name', 'package']);

        $response = $this->patchJson(route('translator.files.update', $file), [
            'name' => 'my-file-1',
            'package' => 'some-package-1',
        ]);

        $response->assertStatus(200);
    }

    /** @test */
    public function translation_package_can_be_omitted_in_update_requests()
    {
        $file = factory(TranslationFile::class)->create([
            'name' => 'my-file',
            'package' => 'my-package',
        ]);

        $this->patchJson(route('translator.files.update', $file), [
            'name' => 'my-file',
        ])->assertStatus(200);

        $this->assertEquals('my-package', $file->fresh()->package);
    }

    /** @test */
    public function translation_package_is_optional()
    {
        $file = factory(TranslationFile::class)->create([
            'name' => 'my-file',
            'package' => 'my-package',
        ]);

        $this->patchJson(route('translator.files.update', $file), [
            'name' => 'my-file',
            'package' => null,
        ])->assertStatus(200);

        $this->assertNull($file->fresh()->package);
    }
}
