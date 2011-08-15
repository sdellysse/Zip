<?php
namespace Dellysse\Zip;

use Doctrine\Common\Collections\ArrayCollection;
use Dellysse\Zip\Exception\FileNotFound as FileNotFoundException;
use ZipArchive;

class Zip {
    public function __construct () {
        $this->files = new ArrayCollection;
    }

    protected $files;
    public function addFile ($filepath, File $file) {
        if (file_exists($file->getPathname())) {
            $this->files[$filepath] = $file;
        } else {
            throw new FileNotFoundException($file->getPathname());
        }
    }
    public function addFileFromContent ($filepath, $content) {
        $temporaryFilename = $this->generateTemporaryFilename();
        file_put_contents($temporaryFilename, $content);
        return $this->addFile($filepath, new File($temporaryFilename));
    }
    public function removeFile ($filepath) {
        $this->files->remove($filepath);
    }
    public function getFile ($filepath) {
        return $this->files->get($filepath);
    }
    public function getFiles () {
        return $this->files;
    }

    public function zip () {
        $zip = new ZipArchive();
        $zipFilename = $this->generateTemporaryFilename();
        $zip->open($zipFilename);

        foreach ($this->files as $filepath => $file) {
            $zip->addFile($file->getPathname(), ltrim($filepath, '/'));
        }

        $zip->close();

        return file_get_contents($zipFilename);
    }

    protected function generateTemporaryFilename () {
        $filename = tempnam(sys_get_temp_dir(), 'Dellysse_Zip_Zip_');
        register_shutdown_function(function () use ($filename) {
            unlink($filename);
        });
        return $filename;
    }
}
