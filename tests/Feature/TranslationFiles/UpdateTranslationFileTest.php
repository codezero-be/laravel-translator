<?php

namespace CodeZero\Translator\Tests\Feature\TranslationFiles;

use CodeZero\Translator\Models\TranslationFile;
use CodeZero\Translator\TranslatorRoutes;
use CodeZero\Translator\Tests\TestCase;
use Illuminate\Foundation\Testing\RefreshDatabase;

class UpdateTranslationFileTest extends TestCase
{
    use RefreshDatabase;

    /** @test */
    public function it_updates_a_translation_file()
    {
        $this->withoutExceptionHandling();

        TranslatorRoutes::register();

        $file = TranslationFile::create([
            'vendor' => 'existing-vendor',
            'filename' => 'existing-file',
        ]);

        $response = $this->actingAsUser()->patchJson(route('translator.files.update', [$file]), [
            'vendor' => 'new-vendor',
            'filename' => 'new-file',
        ]);

        $response->assertSuccessful();

        $file = $file->fresh();
        $this->assertEquals('new-vendor', $file->vendor);
        $this->assertEquals('new-file', $file->filename);
    }

    /** @test */
    public function it_returns_the_updated_translation_file()
    {
        $this->withoutExceptionHandling();

        TranslatorRoutes::register();

        $file = TranslationFile::create([
            'vendor' => 'existing-vendor',
            'filename' => 'existing-file',
        ]);

        $response = $this->actingAsUser()->patchJson(route('translator.files.update', [$file]), [
            'vendor' => 'new-vendor',
            'filename' => 'new-file',
        ]);

        $response->assertSuccessful();
        $this->assertTrue($response->original->is($file));
        $this->assertEquals('new-vendor', $response->original->vendor);
        $this->assertEquals('new-file', $response->original->filename);
    }

    /** @test */
    public function translation_vendor_is_optional()
    {
        $this->withoutExceptionHandling();

        TranslatorRoutes::register();

        $file = TranslationFile::create([
            'vendor' => 'existing-vendor',
            'filename' => 'existing-file',
        ]);

        $response = $this->actingAsUser()->patchJson(route('translator.files.update', [$file]), [
            'vendor' => null,
            'filename' => 'new-file',
        ]);

        $response->assertSuccessful();

        $file = $file->fresh();
        $this->assertEquals(null, $file->vendor);
        $this->assertEquals('new-file', $file->filename);
    }

    /** @test */
    public function translation_vendor_can_not_be_omitted()
    {
        TranslatorRoutes::register();

        $file = TranslationFile::create([
            'vendor' => 'existing-vendor',
            'filename' => 'existing-file',
        ]);

        $response = $this->actingAsUser()->patchJson(route('translator.files.update', [$file]), [
            'filename' => 'new-file',
        ]);

        $response->assertStatus(422);
        $response->assertJsonValidationErrors('vendor');
    }

    /** @test */
    public function translation_file_name_is_required()
    {
        TranslatorRoutes::register();

        $file = TranslationFile::create([
            'vendor' => 'existing-vendor',
            'filename' => 'existing-file',
        ]);

        $response = $this->actingAsUser()->patchJson(route('translator.files.update', [$file]), [
            'vendor' => 'new-vendor',
            'filename' => '',
        ]);

        $response->assertStatus(422);
        $response->assertJsonValidationErrors('filename');
    }

    /** @test */
    public function translation_file_name_should_be_unique_with_a_vendor()
    {
        TranslatorRoutes::register();

        $currentFile = TranslationFile::create([
            'vendor' => 'vendor',
            'filename' => 'current-file',
        ]);

        $existingFile = TranslationFile::create([
            'vendor' => 'vendor',
            'filename' => 'existing-file',
        ]);

        $response = $this->actingAsUser()->patchJson(route('translator.files.update', $currentFile), [
            'vendor' => 'vendor',
            'filename' => $existingFile->filename,
        ]);

        $response->assertStatus(422);
        $response->assertJsonValidationErrors('filename');

        $response = $this->actingAsUser()->patchJson(route('translator.files.update', $currentFile), [
            'vendor' => 'vendor',
            'filename' => $currentFile->filename,
        ]);

        $response->assertSuccessful();
    }

    /** @test */
    public function translation_file_name_may_contain_only_letters_numbers_dashes_and_underscores()
    {
        TranslatorRoutes::register();

        $file = TranslationFile::create([
            'vendor' => 'vendor',
            'filename' => 'existing-file',
        ]);

        $response = $this->actingAsUser()->patchJson(route('translator.files.update', [$file]), [
            'vendor' => 'vendor',
            'filename' => 'Test_File-1',
        ]);

        $response->assertSuccessful();

        $response = $this->actingAsUser()->patchJson(route('translator.files.update', [$file]), [
            'vendor' => 'vendor',
            'filename' => 'Test.File',
        ]);

        $response->assertStatus(422);
        $response->assertJsonValidationErrors('filename');
    }

    /** @test */
    public function translation_vendor_may_contain_only_letters_numbers_dashes_and_underscores()
    {
        TranslatorRoutes::register();

        $file = TranslationFile::create([
            'vendor' => 'existing-vendor',
            'filename' => 'filename',
        ]);

        $response = $this->actingAsUser()->patchJson(route('translator.files.update', [$file]), [
            'vendor' => 'Test_Vendor-1',
            'filename' => 'filename',
        ]);

        $response->assertSuccessful();

        $response = $this->actingAsUser()->patchJson(route('translator.files.update', [$file]), [
            'vendor' => 'Test.Vendor',
            'filename' => 'filename',
        ]);

        $response->assertStatus(422);
        $response->assertJsonValidationErrors('vendor');
    }
}
