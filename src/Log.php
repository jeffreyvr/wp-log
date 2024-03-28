<?php

namespace Jeffreyvr\WPLog;

class Log
{
    public string $filePath;

    public int $maxItems = 1000;

    public ?LogInterface $interface = null;

    public function __construct(public string $name)
    {
        $this->filePath = wp_upload_dir()['basedir'].sanitize_title($name).'.log';
    }

    public function interface(): LogInterface
    {
        if ($this->interface === null) {
            $this->interface = new LogInterface($this);
        }

        return $this->interface;
    }

    public function isFileWritable(): bool
    {
        return is_writable($this->filePath);
    }

    public function setClearLimit(int $maxItems): self
    {
        $this->maxItems = $maxItems;

        return $this;
    }

    public function setFilePath(string $filePath): self
    {
        $this->filePath = $filePath;

        return $this;
    }

    public function clear(): int|bool
    {
        return file_put_contents($this->filePath, '');
    }

    public function getItems($limit = 'none'): array
    {
        $contents = trim(file_get_contents($this->filePath));

        $items = explode(PHP_EOL, $contents);

        if ($limit != 'none') {
            $items = array_slice($items, -$limit);
        }

        return $items;
    }

    public function prepareMessage($message): string
    {
        return '['.date('Y-m-d H:i:s').'] '.$message.PHP_EOL;
    }

    public function handleMaxItemLimit(): void
    {
        $items = $this->getItems();

        if (($this->maxItems !== 0) && (count($items) >= $this->maxItems)) {
            $items = array_slice($items, $this->maxItems * -1);

            file_put_contents(
                $this->filePath,
                implode(PHP_EOL, $items).PHP_EOL
            );
        }
    }

    public function write($entry): int|bool
    {
        $this->handleMaxItemLimit();

        if (is_array($entry) || is_object($entry)) {
            $message = print_r($entry, true);
        } else {
            $message = $entry;
        }

        return file_put_contents(
            $this->filePath,
            $this->prepareMessage($message),
            FILE_APPEND
        );
    }
}
