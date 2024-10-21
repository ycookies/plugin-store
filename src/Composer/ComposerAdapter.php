<?php

namespace Dcat\Admin\PluginStore\Composer;

use Composer\Config;
use Composer\Console\Application;
use Dcat\Admin\PluginStore\OutputLogger;
use Dcat\Admin\PluginStore\Paths;
use Illuminate\Filesystem\Filesystem;
use Dcat\Admin\PluginStore\Util\Util;
use Dcat\Admin\PluginStore\Models\Task;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\BufferedOutput;
use Symfony\Component\Console\Input\StringInput;
use Illuminate\Contracts\Container\Container;
use Monolog\Formatter\LineFormatter;
use Monolog\Handler\RotatingFileHandler;
use Monolog\Logger;

/**
 * @internal
 */
class ComposerAdapter
{
    private $container;
    /**
     * @var Application
     */
    private $application;

    /**
     * @var OutputLogger
     */
    private $logger;

    /**
     * @var Paths
     */
    private $paths;

    /**
     * @var BufferedOutput|null
     */
    private $output = null;

    /**
     * @var Filesystem
     */
    private $filesystem;

    public function __construct(Container $container)
    {
        $this->container = $container;
        /*$this->application = $application;
        $this->logger = $logger;*/

        //$this->filesystem = $filesystem;
    }

    /**
     * @desc
     * @param $package_name
     * @param $task
     * @param $safeMode
     * @return ComposerOutput
     * @throws \Exception
     */

    public function runExe($package_name,$task = null, $safeMode = false){
        //$this->application = $this->container->make(Application::class);

        $composer = new Application();
        $composer->setAutoExit(false);

        $this->application = $composer;
        $this->filesystem = $this->container->make(Filesystem::class);

        $this->container->singleton(OutputLogger::class, function (Container $container) {
            $logPath = storage_path().'/logs/composer/output.log';
            $handler = new RotatingFileHandler($logPath, Logger::INFO);
            $handler->setFormatter(new LineFormatter(null, null, true, true));

            $logger = new Logger('composer', [$handler]);

            return new OutputLogger($logger);
        });

        $this->logger = $this->container->make(OutputLogger::class);

        $this->paths =  new paths([
            'base' => base_path(),
            'public' => public_path(),
            'storage' => storage_path(),
            'vendor' => base_path().'/vendor',
        ]);

        $this->application->resetComposer();
        $input = new StringInput($package_name);
        $this->output = new BufferedOutput();

        // This hack is necessary so that relative path repositories are resolved properly.
        $currDir = getcwd();
        chdir($this->paths->base);

        if ($safeMode) {
            $temporaryVendorDir = $this->paths->base.DIRECTORY_SEPARATOR.'temp-vendor';
            if (! $this->filesystem->isDirectory($temporaryVendorDir)) {
                $this->filesystem->makeDirectory($temporaryVendorDir);
            }
            Config::$defaultConfig['vendor-dir'] = $temporaryVendorDir;
        }
        $exitCode = $this->application->run($input, $this->output);


        if ($safeMode) {
            // Move the temporary vendor directory to the real vendor directory.
            if ($this->filesystem->isDirectory($temporaryVendorDir) && count($this->filesystem->allFiles($temporaryVendorDir))) {
                $vendorDir = $this->paths->vendor;
                if (file_exists($vendorDir)) {
                    $this->filesystem->deleteDirectory($vendorDir);
                }
                $this->filesystem->moveDirectory($temporaryVendorDir, $vendorDir);
            }
            Config::$defaultConfig['vendor-dir'] = $this->paths->vendor;
        }

        chdir($currDir);

        $command = Util::readableConsoleInput($input);
        $outputContent = $this->output->fetch();
        if ($task) {
            $task->update([
                'command' => $command,
                'output' => $outputContent,
            ]);
        } else {
            $this->logger->log($command, $outputContent, $exitCode);
        }

        return new ComposerOutput($exitCode, $outputContent);
    }

    public function run(InputInterface $input, $task = null, $safeMode = false)
    {
        $application = new Application();
        $application->resetComposer();

        $this->output = $this->output ?? new BufferedOutput();

        // This hack is necessary so that relative path repositories are resolved properly.
        $currDir = getcwd();
        chdir($this->paths->base);

        if ($safeMode) {
            $temporaryVendorDir = $this->paths->base.DIRECTORY_SEPARATOR.'temp-vendor';
            if (! $this->filesystem->isDirectory($temporaryVendorDir)) {
                $this->filesystem->makeDirectory($temporaryVendorDir);
            }
            Config::$defaultConfig['vendor-dir'] = $temporaryVendorDir;
        }

        $exitCode = $application->run($input, $this->output);

        if ($safeMode) {
            // Move the temporary vendor directory to the real vendor directory.
            if ($this->filesystem->isDirectory($temporaryVendorDir) && count($this->filesystem->allFiles($temporaryVendorDir))) {
                $vendorDir = $this->paths->vendor;
                if (file_exists($vendorDir)) {
                    $this->filesystem->deleteDirectory($vendorDir);
                }
                $this->filesystem->moveDirectory($temporaryVendorDir, $vendorDir);
            }
            Config::$defaultConfig['vendor-dir'] = $this->paths->vendor;
        }

        chdir($currDir);

        $command = Util::readableConsoleInput($input);
        $outputContent = $this->output->fetch();

        if ($task) {
            $task->update([
                'command' => $command,
                'output' => $outputContent,
            ]);
        } else {
            $this->logger->log($command, $outputContent, $exitCode);
        }

        return new ComposerOutput($exitCode, $outputContent);
    }

    public static function setPhpVersion(string $phpVersion)
    {
        Config::$defaultConfig['platform']['php'] = $phpVersion;
    }
}
