<?php

namespace CodeZero\Translator\Tests\Feature\TranslationFiles;

use CodeZero\Translator\Models\TranslationFile;
use CodeZero\Translator\Tests\TestCase;
use Illuminate\Foundation\Testing\RefreshDatabase;

class StoreTranslationFileTest extends TestCase
{
    use RefreshDatabase;

    /** @test */
    public function it_adds_a_translation_file_to_the_database()
    {
        $this->withoutExceptionHandling();

        $response = $this->actingAsUser()->postJson(route('translator.files.store'), [
            'vendor' => 'vendor-name',
            'filename' => 'test-file',
        ]);

        $response->assertSuccessful();
        $this->assertDatabaseHas('translation_files', [
            'vendor' => 'vendor-name',
            'filename' => 'test-file',
        ]);
    }

    /** @test */
    public function it_returns_the_new_translation_file()
    {
        $this->withoutExceptionHandling();

        $response = $this->actingAsUser()->postJson(route('translator.files.store'), [
            'vendor' => 'vendor-name',
            'filename' => 'test-file',
        ]);

        $response->assertSuccessful();
        $this->assertEquals('vendor-name', $response->original->vendor);
        $this->assertEquals('test-file', $response->original->filename);
    }

    /** @test */
    public function translation_vendor_is_optional()
    {
        $this->withoutExceptionHandling();

        $response = $this->actingAsUser()->postJson(route('translator.files.store'), [
            'vendor' => null,
            'filename' => 'test-file',
        ]);

        $response->assertSuccessful();

        $this->assertDatabaseHas('translation_files', [
            'vendor' => null,
            'filename' => 'test-file',
        ]);
    }

    /** @test */
    public function translation_vendor_can_be_omitted()
    {
        $this->withoutExceptionHandling();

        $response = $this->actingAsUser()->postJson(route('translator.files.store'), [
            'filename' => 'test-file',
        ]);

        $response->assertSuccessful();

        $this->assertDatabaseHas('translation_files', [
            'vendor' => null,
            'filename' => 'test-file',
        ]);
    }

    /** @test */
    public function translation_file_name_is_required()
    {
        $response = $this->actingAsUser()->postJson(route('translator.files.store'), [
            'vendor' => 'vendor-name',
            'filename' => '',
        ]);

        $response->assertStatus(422);
        $response->assertJsonValidationErrors('filename');
    }

    /** @test */
    public function translation_file_name_should_be_unique_with_a_vendor()
    {
        $existingFile = TranslationFile::create([
            'vendor' => 'existing-vendor',
            'filename' => 'existing-file',
        ]);

        $response = $this->actingAsUser()->postJson(route('translator.files.store'), [
            'vendor' => $existingFile->vendor,
            'filename' => $existingFile->filename,
        ]);

        $response->assertStatus(422);
        $response->assertJsonValidationErrors('filename');

        $response = $this->actingAsUser()->postJson(route('translator.files.store'), [
            'vendor' => 'other-vendor',
            'filename' => $existingFile->filename,
        ]);

        $response->assertSuccessful();

        $this->assertDatabaseHas('translation_files', [
            'vendor' => $existingFile->vendor,
            'filename' => $existingFile->filename,
        ]);

        $this->assertDatabaseHas('translation_files', [
            'vendor' => 'other-vendor',
            'filename' => $existingFile->filename,
        ]);
    }

    /** @test */
    public function a_json_file_can_only_exist_without_a_vendor()
    {
        $response = $this->actingAsUser()->postJson(route('translator.files.store'), [
            'vendor' => 'vendor-name',
            'filename' => '_json',
        ]);

        $response->assertStatus(422);
        $response->assertJsonValidationErrors('filename');
    }

    /** @test */
    public function translation_file_name_may_contain_only_letters_numbers_dashes_and_underscores()
    {
        $response = $this->actingAsUser()->postJson(route('translator.files.store'), [
            'vendor' => null,
            'filename' => 'Test_File-1',
        ]);

        $response->assertSuccessful();

        $response = $this->actingAsUser()->postJson(route('translator.files.store'), [
            'vendor' => null,
            'filename' => 'Test.File',
        ]);

        $response->assertStatus(422);
        $response->assertJsonValidationErrors(['filename']);
    }

    /** @test */
    public function translation_vendor_may_contain_only_letters_numbers_dashes_and_underscores()
    {
        $response = $this->actingAsUser()->postJson(route('translator.files.store'), [
            'vendor' => 'Vendor_Name-1',
            'filename' => 'test-file',
        ]);

        $response->assertSuccessful();

        $response = $this->actingAsUser()->postJson(route('translator.files.store'), [
            'vendor' => 'Vendor.Name',
            'filename' => 'test_file',
        ]);

        $response->assertStatus(422);
        $response->assertJsonValidationErrors(['vendor']);
    }
}
