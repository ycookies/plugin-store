<?php

namespace Dcat\Admin\PluginStore\Composer;

class ComposerOutput
{
    /**
     * @var int
     */
    protected $exitCode;

    /**
     * @var string
     */
    protected $contents;

    public function __construct(int $exitCode, string $contents)
    {
        $this->exitCode = $exitCode;
        $this->contents = $contents;
    }

    public function getExitCode(): int
    {
        return $this->exitCode;
    }

    public function getContents(): string
    {
        return $this->contents;
    }
}
