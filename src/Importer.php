<?php

namespace CodeZero\Translator;

use CodeZero\Translator\Models\Translation;
use CodeZero\Translator\Models\TranslationFile;
use DB;
use File;

class Importer
{
    /**
     * TranslationFile Collection.
     *
     * @var \Illuminate\Support\Collection
     */
    protected $files;

    /**
     * Prefer database values in case of conflicts.
     *
     * @var bool
     */
    protected $databaseWins;

    /**
     * Create a new Importer instance.
     */
    public function __construct()
    {
        $this->files = collect();
    }

    /**
     * Scan all libraries in the given path for translations and save those to the database.
     * The root path is a library, but packages could have their own library in a vendor
     * subdirectory. These will be scanned automatically.
     * A library contains language directories.
     * Translator database tables will be truncated before import.
     *
     * @param null|string $root
     *
     * @return \Illuminate\Support\Collection
     */
    public function import($root = null)
    {
        $this->scan($root);
        $this->emptyDatabaseTables();
        $this->save();

        return $this->files;
    }

    /**
     * Scan all libraries in the given path for translations and save those to the database.
     * The root path is a library, but packages could have their own library in a vendor
     * subdirectory. These will be scanned automatically.
     * A library contains language directories.
     * Imported translations will be merges with existing ones.
     *
     * @param null|string $root
     *
     * @return \Illuminate\Support\Collection
     */
    public function sync($root = null)
    {
        $this->fetchDatabaseTranslations();
        $this->scan($root);
        $this->save();

        return $this->files;
    }

    /**
     * Scan the given path for translations.
     *
     * @param null|string $root
     *
     * @return \Illuminate\Support\Collection
     */
    public function scan($root = null)
    {
        $root = $root ?: resource_path('lang');

        $this->listLibraries($root)->each(function ($libraryPath) {
            $this->scanLibrary($libraryPath);
        });

        return $this->files;
    }

    /**
     * Save scanned translations to the database.
     *
     * @return \Illuminate\Support\Collection
     */
    public function save()
    {
        $this->files->each(function ($file) {
            $file->save();
            $file->translations->each(function ($translation) use ($file) {
                $translation->file_id = $file->id;
                $translation->save();
            });
        });

        return $this->files;
    }

    /**
     * Prefer database values in case of conflicts.
     *
     * @return $this
     */
    public function databaseWins()
    {
        $this->databaseWins = true;

        return $this;
    }

    /**
     * Scan a library.
     *
     * @param string $libraryPath
     *
     * @return void
     */
    protected function scanLibrary($libraryPath)
    {
        $this->listLanguages($libraryPath)->each(function ($languagePath) {
            $this->importLanguageFiles($languagePath);
        });
    }

    /**
     * Import all files from a language directory.
     *
     * @param string $languagePath
     *
     * @return void
     */
    protected function importLanguageFiles($languagePath)
    {
        $this->listLanguageFiles($languagePath)->each(function ($filePath) {
            $this->importLanguageFile($filePath);
        });
    }

    /**
     * Import a file from a language directory.
     *
     * @param string $filePath
     *
     * @return void
     */
    protected function importLanguageFile($filePath)
    {
        $file = $this->findOrCreateTranslationFile($filePath);
        $contents = array_dot(include $filePath);
        $locale = basename(dirname($filePath));

        foreach ($contents as $key => $body) {
            $this->addOrUpdateTranslation($file, $key, $locale, $body);
        }
    }

    /**
     * Find an existing or create a new TranslationFile based
     * on a filename and package name in a given path.
     *
     * @param string $filePath
     *
     * @return \App\TranslationFile
     */
    protected function findOrCreateTranslationFile($filePath)
    {
        $filename = pathinfo($filePath, PATHINFO_FILENAME);
        $packageName = $this->getPackageNameFromFilePath($filePath);

        $file = $this->files
            ->where('name', $filename)
            ->where('package', $packageName)
            ->first();

        if ($file) {
            return $file;
        }

        $file = new TranslationFile([
            'name' => $filename,
            'package' => $packageName,
        ]);

        $this->files->push($file);

        return $file;
    }

    /**
     * Get the package (if any) name from a given path.
     *
     * @param string $path
     *
     * @return null|string
     */
    protected function getPackageNameFromFilePath($path)
    {
        if (basename(dirname($path, 3)) !== 'vendor') {
            return null;
        }

        return basename(dirname($path, 2));
    }

    /**
     * Add or update a Translation to a TranslationFile.
     *
     * @param \App\TranslationFile $file
     * @param string $key
     * @param string $locale
     * @param string $body
     *
     * @return void
     */
    protected function addOrUpdateTranslation($file, $key, $locale, $body)
    {
        $translation = $file->translations->where('key', $key)->first();

        if ( ! $translation) {
            $translation = new Translation(['key' => $key]);
            $file->translations->push($translation);
        }

        if ( ! $this->databaseWins || ! $translation->{$locale}) {
            $translation->setTranslation('body', $locale, $body);
        }
    }

    /**
     * List all "library" directories that contain
     * subdirectories that represent a language.
     *
     * @param string $rootLangPath
     *
     * @return \Illuminate\Support\Collection
     */
    protected function listLibraries($rootLangPath)
    {
        $vendorPath = $rootLangPath.'/vendor';

        $packagePaths = File::isDirectory($vendorPath)
            ? $this->listSubdirectories($vendorPath)
            : collect();

        return $packagePaths->prepend($rootLangPath);
    }

    /**
     * List all subdirectories that represent a language.
     *
     * @param string $libraryPath
     *
     * @return \Illuminate\Support\Collection
     */
    protected function listLanguages($libraryPath)
    {
        return $this->listSubdirectories($libraryPath)->reject(function ($directory) {
            return basename($directory) === 'vendor';
        });
    }

    /**
     * List all PHP language files in a given path.
     *
     * @param string $languagePath
     *
     * @return \Illuminate\Support\Collection
     */
    protected function listLanguageFiles($languagePath)
    {
        return collect(File::files($languagePath))->filter(function ($filePath) {
            return pathinfo($filePath, PATHINFO_EXTENSION) === 'php';
        });
    }

    /**
     * List all subdirectories of a given path.
     *
     * @param string $path
     *
     * @return \Illuminate\Support\Collection
     */
    protected function listSubdirectories($path)
    {
        return collect(File::directories($path));
    }

    /**
     * Fetch translations from the database.
     *
     * @return void
     */
    protected function fetchDatabaseTranslations()
    {
        $this->files = TranslationFile::with('translations')->get();
    }

    /**
     * Empty translator database tables.
     *
     * @return void
     */
    protected function emptyDatabaseTables()
    {
        DB::table('translations')->delete();
        DB::table('translation_files')->delete();
    }
}
