<?php

namespace App\B2c\Repositories\Libraries\Storage;

use Exception;
use Carbon\Carbon;
use Illuminate\Filesystem\Filesystem;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\Storage;
use App\B2c\Repositories\Libraries\Storage\Factory;

class Cloud extends Factory
{

    /**
     * Prefix for temporary files
     *
     * @var string
     */
    protected $tempPrefix = 'b2ctempcloud_';

    /**
     * Temporary folder for file to store
     *
     * @var string
     */
    protected $tempFolder;

    /**
     * Cleanup the temp folder
     */
    protected function cleanupTemp()
    {
        $now = Carbon::now()->timestamp;
        $fs = new Filesystem();
        $tempFiles = $fs->glob($this->tempFolder.'/'.$this->tempPrefix.'*');

        foreach ($tempFiles as $file) {
            if ($now - $fs->lastModified($file) > 50) {
                $fs->delete($file);
            }
        }
    }

    /**
     * Get the resource path with added S3 sub-folder after the bucket name, if any.
     *
     * @param string $resource
     * @return string
     */
    protected function finalizePath($resource)
    {
        if (($subfolder = Config::get('b2cstorage.s3subfolder')) !== false) {
            return $subfolder.'/'.ltrim($resource, '/');
        }

        return $resource;
    }

    /**
     * Class instance
     */
    public function __construct()
    {
        $this->tempFolder = sys_get_temp_dir();
        $this->cleanupTemp();

        parent::__construct();
    }

    /**
     * Set the storage driver
     *
     * @return \App\B2c\Repositories\Libraries\Storage\Cloud
     */
    protected function setStorage()
    {
        $this->store = Storage::disk(Config::get('filesystems.cloud', 's3'));
    }

    /**
     * Checks whether a resource exists or not
     *
     * @param string $resource
     * @return boolean
     */
    public function has($resource)
    {
        return parent::has($this->finalizePath($resource));
    }

    /**
     * Create directory
     *
     * @param string $directory
     * @return boolean
     */
    public function makeDir($directory)
    {
        return parent::makeDir($this->finalizePath($directory));
    }

    /**
     * Remove a directory
     *
     * @param string $directory
     * @return boolean
     */
    public function removeDir($directory)
    {
        return parent::removeDir($this->finalizePath($directory));
    }

    /**
     * Get files under a directory
     *
     * @param string $path
     * @param boolean $recursive
     * @return mixed array | boolean
     */
    public function getFiles($path, $recursive = false)
    {
        return parent::getFiles($this->finalizePath($path), $recursive);
    }

    /**
     * Get directories
     *
     * @param string $path
     * @param boolean $recursive
     * @return mixed array | boolean
     */
    public function getDirectories($path, $recursive = false)
    {
        return parent::getDirectories($this->finalizePath($path), $recursive);
    }

    /**
     * Creates / overwrites a file
     *
     * @param string $file
     * @param string|resource $content
     * @param boolean $overwrite
     * @return boolean
     */
    public function put($file, $content, $overwrite = false)
    {
        $s3encryption = config('b2cstorage.s3encryption', true);
        $s3encryption_algorithm = config('b2cstorage.s3encryption_algorithm', 'AES256');

        try {
            if ($overwrite) {
                if ($s3encryption) {
                    return $this->store->getDriver()->put($this->finalizePath($file), $content, ['ServerSideEncryption' => $s3encryption_algorithm]);
                } else {
                    return $this->store->put($this->finalizePath($file), $content, $overwrite);
                }
            } elseif (!$this->store->has($file)) {
                return $this->store->getDriver()->put($this->finalizePath($file), $content, ['ServerSideEncryption' => $s3encryption_algorithm]);
            }
        } catch (Exception $ex) {
            return $this->error($ex);
        }
    }

    /**
     * Create a file and/or append the content in it
     *
     * @param string $file
     * @param string $content
     * @param boolean $prepend
     * @return boolean
     */
    public function append($file, $content)
    {
        return parent::append($this->finalizePath($file), $content);
    }

    /**
     * Create a file and/or prepend the content in it
     *
     * @param string $file
     * @param string $content
     * @return boolean
     */
    public function prepend($file, $content)
    {
        return parent::prepend($this->finalizePath($file), $content);
    }

    /**
     * Get content from a file
     *
     * @param string $file
     * @return string
     */
    public function getContent($file)
    {
        return parent::getContent($this->finalizePath($file));
    }

    /**
     * Deletes one or multiple files
     *
     * @param mixed string|array $files
     * @return boolean
     */
    public function deleteFile($files)
    {
        return true;
    }

    /**
     * Prepare the file for download
     *
     * @param string $file
     * @return boolean
     */
    public function prepare($file)
    {
        try {
            $finalPath = $this->finalizePath($file);
            if (!$this->store->has($finalPath)) {
                return false;
            }

            $tempName = tempnam($this->tempFolder, $this->tempPrefix);
            with(new Filesystem())->put($tempName, $this->getContent($file));

            return $tempName;
        } catch (Exception $ex) {
            return $this->error($ex);
        }
    }

    /**
     * Rename a resource on cloud.
     *
     * @param string $path
     * @param string $newPath
     * @return boolean
     */
    public function rename($path, $newPath)
    {
        $source = $this->finalizePath($path);
        $target = $this->finalizePath($newPath);

        return parent::rename($source, $target);
    }

    /**
     * Copy a file.
     *
     * @param string $path
     * @param string $newPath
     *
     * @return bool
     */
    public function copy($path, $newPath)
    {
        $source = $this->finalizePath($path);
        $target = $this->finalizePath($newPath);

        return parent::copy($source, $target);
    }
}
