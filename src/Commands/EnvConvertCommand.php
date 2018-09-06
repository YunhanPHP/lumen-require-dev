<?php

namespace YunhanDev\Commands;

use Dotenv\Loader;
use Illuminate\Console\Command;
use Illuminate\Filesystem\Filesystem;
use Symfony\Component\Finder\Finder;

class EnvConvertCommand extends Command {
    protected $signature = 'dev:env-convert';

    protected $description = '将 .env 配置转为 PHP 配置';

    /**
     * @var \Dotenv\Loader
     */
    public $loader;

    public function handle()
    {
        $finder = new Finder();
        $finder
            ->files()
            ->depth('== 0')
            ->ignoreDotFiles(false)
            ->name('/\.env\./')
            ->in(base_path());

        $this->loader = new Loader('');
        $fileSystem = new Filesystem();
        foreach ($finder as $file) {
            $filename = $file->getFilename();
            $lines = $this->readLinesFromFile(base_path($filename));
            $php = "<?php\n\nreturn [\n";
            foreach ($lines as $line) {
                if (empty($line)) {
                    $php .= "\n";
                } elseif ($this->isComment($line)) {
                    $php .= "    // ${line}\n";
                } elseif ($this->looksLikeSetter($line)) {
                    list($name, $value) = $this->normaliseEnvironmentVariable($line, null);
                    $php .= "    '${name}'=>'${value}',\n";
                }
            }
            $newFilename = base_path(trim($filename, '.') . '.php');
            $fileSystem->put($newFilename, $php . '];');
            print_r('CONVERT SUCCESS: ' . $newFilename . PHP_EOL);
        }
    }

    protected function readLinesFromFile($filePath)
    {
        // Read file into an array of lines with auto-detected line endings
        $autodetect = ini_get('auto_detect_line_endings');
        ini_set('auto_detect_line_endings', '1');
        $lines = file($filePath, FILE_IGNORE_NEW_LINES);
        ini_set('auto_detect_line_endings', $autodetect);

        return $lines;
    }

    protected function isComment($line)
    {
        $line = ltrim($line);

        return isset($line[0]) && $line[0] === '#';
    }

    protected function looksLikeSetter($line)
    {
        return strpos($line, '=') !== false;
    }

    protected function normaliseEnvironmentVariable($name, $value)
    {
        list($name, $value) = $this->loader->processFilters($name, $value);
        return array($name, $value);
    }
}
