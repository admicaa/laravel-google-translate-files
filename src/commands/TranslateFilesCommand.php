<?php

namespace Admica\transFiles\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\File;
use Stichoza\GoogleTranslate\GoogleTranslate;

class TranslateFilesCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'files:translate {main-language} {other-language} {--mainPath=} {--absolutePath=}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'translate your translation files from one language to another, using google translate.';

    /**
     * Create a new command instance.
     *
     * @return void
     */
    public function __construct()
    {
        parent::__construct();
    }

    /**
     * Execute the console command.
     *
     * @return mixed
     */
    public function handle()
    {
        $mainDirectory =  resource_path('lang');
        if ($this->option('mainPath')) {

            $mainDirectory = resource_path($this->option('mainPath'));
            if ($this->option('absolutePath')) {
                $mainDirectory = $this->option('mainPath');
            }
        }


        $mainLanguage = $this->argument('main-language');
        $secondLanugage = $this->argument('other-language');
        //go through every folder and file

        $this->checkDirectory($mainDirectory, $mainLanguage, $secondLanugage);
        $this->info('translation done successfully :) !');
    }
    public function getFilesList($path, $mainLanguage, $secondLanugage = null, $directoryPath = '')
    {
        $FilesList = [];

        $languages =  [$mainLanguage];
        if ($secondLanugage != null) {
            array_push($languages, $secondLanugage);
        }
        foreach ($languages as $language) {
            $exactPath = $path . '/' . $language . $directoryPath;
            $languagePath = $path . '/' . $language;
            $files = $this->getFiles($exactPath);
            foreach ($files as $file) {
                $fileName = preg_replace('/^' . preg_quote($languagePath, '/') . '/', '', $file);
                if (!isset($FilesList[$fileName])) {
                    $FilesList[$fileName] = [];
                }
                array_push($FilesList[$fileName], $language);
                if (is_dir($file)) {
                    $FilesList = array_merge($FilesList, $this->getFilesList(
                        $path,
                        $mainLanguage,
                        $secondLanugage,
                        $fileName
                    ));
                }
            }
        }
        return $FilesList;
    }
    public function checkDirectory($path, $mainLanguage, $secondLanugage)
    {
        $filesList = $this->getFilesList($path, $mainLanguage);
        foreach ($filesList as $fileName => $existance) {
            if (!in_array($mainLanguage, $existance)) {
                $fileMainPath = $path . '/' . $secondLanugage . $fileName;
                $filePath = $path . '/' . $mainLanguage . $fileName;
                if (!file_exists($path . '/' . $secondLanugage . $fileName)) {
                    $this->copyFiles($fileMainPath, $filePath);
                }
                $this->transFile($fileMainPath, $filePath, $mainLanguage, $secondLanugage);
            } else {
                $fileMainPath = $path . '/' . $mainLanguage . $fileName;
                $filePath = $path . '/' . $secondLanugage . $fileName;
                if (!file_exists($path . '/' . $secondLanugage . $fileName)) {
                    $this->copyFiles($fileMainPath, $filePath);
                }
                $this->transFile($fileMainPath, $filePath, $mainLanguage, $secondLanugage);
            }
        }
    }
    public function translateWorld($locale, $base_locale, $text)
    {
        $tr = new GoogleTranslate($base_locale, $locale, [
            'timeout' => 10,

            'headers' => [
                'User-Agent' => 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/73.0.3683.86 Safari/537.36'
            ]
        ]);

        $translation =  $tr->translate($text);

        $this->line($text . ' => ' . $translation);
        sleep(2);
        return $translation;
    }

    public function transFile($mainPath, $translatedPath, $mainLanguage, $secondLanugage)
    {
        $this->info('checking ' . $translatedPath . ' ....');
        if (!is_dir($mainPath)) {
            $mainWords = require $mainPath;

            $otherWords = require $translatedPath;
            $otherWords = $this->translateArray($mainWords, $otherWords, $mainLanguage, $secondLanugage);

            $this->UpdateFile($translatedPath, $otherWords);
            $this->info('File : ' . $translatedPath . '. Had been translated succesfully');
        }
    }
    public function UpdateFile($fullPath, $content)
    {
        $content = '<?php return ' . var_export($content, true) . ';';

        File::put($fullPath, $content);
    }
    public function translateArray($mainWords, $otherWords, $mainLanguage, $otherLanguage)
    {
        foreach ($mainWords as $key => $word) {
            if (!array_key_exists($key, $otherWords) || gettype($mainWords[$key]) != gettype($otherWords[$key])) {
                $otherWords[$key] = $mainWords[$key];
            }
            if (is_array($mainWords[$key])) {
                $otherWords[$key] = $this->translateArray($mainWords[$key], $otherWords[$key], $mainLanguage, $otherLanguage);
            } else {
                if ($otherWords[$key] == $mainWords[$key]) {
                    $otherWords[$key] = $this->translateWorld($mainLanguage, $otherLanguage, $mainWords[$key]);
                }
            }
        }
        return $otherWords;
    }
    public function copyFiles($from, $to)
    {
        if (is_dir($from)) {
            File::copyDirectory($from, $to);
        } else {
            File::copy($from, $to);
        }
    }
    public function getFiles($langPath)
    {
        $files   = glob($langPath . '/*');
        return $files;
    }
}
