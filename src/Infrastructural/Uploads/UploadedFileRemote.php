<?php

namespace App\Infrastructural\Uploads;

use Symfony\Component\HttpFoundation\File\Exception\FileException;
use Symfony\Component\HttpFoundation\File\File;
use Symfony\Component\HttpFoundation\File\UploadedFile;
use Symfony\Component\Mime\MimeTypes;

class UploadedFileRemote extends UploadedFile
{
    public function __construct(string $url)
    {
        $originalName = pathinfo($url, \PATHINFO_BASENAME);
        parent::__construct($url, $originalName, null, \UPLOAD_ERR_CANT_WRITE, false);
    }

    /**
     * move.
     */
    public function move(string $directory, string $name = null): File
    {
        if ($this->isValid()) {
            $targetFile = $this->getTargetFile($directory, $name);

            // We copy the source to the output
            $source = fopen($this->getPathname(), 'r');
            if (false === $source) {
                throw new FileException(sprintf('Unable to open file %s for reading', $this->getPathname()));
            }

            $target = fopen($targetFile->getPathname(), 'w+');
            if (false === $target) {
                throw new FileException(sprintf('Unable to open file %s for writing', $targetFile->getPathname()));
            }

            $copied = stream_copy_to_stream($source, $target);
            fclose($source);
            fclose($target);
            unset($source, $target);
            restore_error_handler();

            if (!$copied) {
                throw new FileException(sprintf('Could not move the file "%s" to "%s"', $this->getPathname(), $targetFile->getPathname()));
            }

            return $targetFile;
        }

        throw new FileException($this->getErrorMessage());
    }

    public function isValid(): bool
    {
        return true;
    }

    public function getMimeType(): string
    {
        $ext = pathinfo($this->getPathname(), \PATHINFO_EXTENSION);

        return MimeTypes::getDefault()->getMimeTypes($ext)[0];
    }

    public function getSize(): int
    {
        return 100; // We are not trying to get an exact value.
    }
}
