<?php

namespace CodeZero\Translator\Tests\Unit\Models;

use CodeZero\Translator\Models\TranslationKey;
use CodeZero\Translator\Models\TranslationFile;
use CodeZero\Translator\Tests\TestCase;
use Illuminate\Support\Collection;

class TranslationFileTest extends TestCase
{
    /** @test */
    public function it_gets_translation_keys_of_a_file()
    {
        $translationFile = TranslationFile::make();
        $translationFile->setRelation('translationKeys', Collection::make([
            $keyA = TranslationKey::make([
                'key' => 'key-a',
                'translations' => [
                    'en' => 'translation [en]',
                    'nl' => 'translation [nl]',
                ],
            ]),
            $keyB = TranslationKey::make([
                'key' => 'key-b',
                'translations' => [
                    'en' => 'translation [en]',
                    'nl' => 'translation [nl]',
                ],
            ]),
        ]));

        $this->assertEquals($keyA, $translationFile->getTranslationKeys()->first());
        $this->assertEquals($keyB, $translationFile->getTranslationKeys()->last());
    }

    /** @test */
    public function it_gets_a_specific_translations_key()
    {
        $translationFile = TranslationFile::make();
        $translationFile->setRelation('translationKeys', Collection::make([
            TranslationKey::make([
                'key' => 'key-a',
                'translations' => [
                    'en' => 'translation a [en]',
                    'nl' => 'translation a [nl]',
                ],
            ]),
            $keyB = TranslationKey::make([
                'key' => 'key-b',
                'translations' => [
                    'en' => 'translation b [en]',
                    'nl' => 'translation b [nl]',
                ],
            ]),
        ]));

        $this->assertEquals($keyB, $translationFile->getTranslationKey('key-b'));
    }
}
