<?php

namespace App\Repositories\Libraries\Storage;

use Exception;
use BadMethodCallException;
use App\Repositories\Libraries\Storage\Contract\ErrorHandlerTrait;

class Factory
{

    use ErrorHandlerTrait;

    /**
     * Underlying storage manager
     *
     * @var type
     */
    protected $store;

    /**
     * Constructor runs the setStorage()
     * as late static binding
     */
    public function __construct()
    {
        static::setStorage();
    }

    /**
     * Do not allow to create an object from Factory class
     *
     * @throws BadMethodCallException
     */
    protected function setStorage()
    {
        throw new BadMethodCallException('\setStorage()\' should be called in a child class.');
    }

    /**
     *
     * @return type
     */
    public function getStorage()
    {
        return $this->store;
    }

    /**
     * Checks whether a resource exists or not
     *
     * @param string $resource
     * @return boolean
     */
    public function has($resource)
    {
        return $this->store->has($resource);
    }

    /**
     * Create directory
     *
     * @param string $directory
     * @return boolean
     */
    public function makeDir($directory)
    {
        try {
            $this->store->makeDirectory($directory);
            return true;
        } catch (Exception $ex) {
            return $this->error($ex);
        }
    }

    /**
     * Remove a directory
     *
     * @param string $directory
     * @return boolean
     */
    public function removeDir($directory)
    {
        try {
            $this->store->deleteDirectory($directory);
            return true;
        } catch (Exception $ex) {
            return $this->error($ex);
        }
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
        try {
            return ($recursive === false) ? $this->store->files($path) : $this->store->allFiles($path);
        } catch (Exception $ex) {
            return $this->error($ex);
        }
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
        try {
            return ($recursive === false) ? $this->store->directories($path) : $this->store->allDirectories($path);
        } catch (Exception $ex) {
            return $this->error($ex);
        }
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
        try {
            if ($overwrite) {
                $this->store->put($file, $content);
            } elseif (!$this->store->has($file)) {
                $this->store->put($file, $content);
            }

            return true;
        } catch (Exception $ex) {
            return $this->error($ex);
        }
    }

    /**
     * Rename a resource.
     *
     * @param string $path
     * @param string $newPath
     * @return boolean
     */
    public function rename($path, $newPath)
    {
        return $this->storage->rename($path, $newPath);
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
        try {
            $this->store->append($file, $content);
            return true;
        } catch (Exception $ex) {
            return $this->error($ex);
        }
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
        try {
            $this->store->prepend($file, $content);
            return true;
        } catch (Exception $ex) {
            return $this->error($ex);
        }
    }

    /**
     * Get content from a file
     *
     * @param string $file
     * @return string
     */
    public function getContent($file)
    {
        try {
            return $this->store->get($file);
        } catch (Exception $ex) {
            return $this->error($ex);
        }
    }

    /**
     * Deletes one or multiple files
     *
     * @param mixed string|array $files
     * @return boolean
     */
    public function deleteFile($files)
    {
        if (! is_array($files)) {
            $files = [$files];
        }

        try {
            $this->store->delete($files);
            return true;
        } catch (Exception $ex) {
            return $this->error($ex);
        }
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
        try {
            return $this->store->copy($path, $newPath);
        } catch (Exception $ex) {
            return $this->error($ex);
        }
    }

}
