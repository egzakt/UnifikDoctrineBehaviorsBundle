<?php

/*
 * This file is part of the YtkoDoctrineBehaviors package.
 *
 * (c) Ytko <http://ytko.ru/>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Egzakt\DoctrineBehaviorsBundle\Model\Uploadable;

use Symfony\Component\HttpFoundation\File\UploadedFile;

trait Uploadable
{
    /**
     * @var UploadedFile $file
     */
    private $file;

    /**
     * @var string $uploadPath
     */
    private $uploadPath;

    /**
     * @var string $previousUploadPath
     */
    private $previousUploadPath;

    /**
     * @var string $uploadRootDir
     */
    private $uploadRootDir;

    /**
     * Set File
     *
     * @param UploadedFile $file
     *
     * @return $this
     */
    public function setFile(UploadedFile $file)
    {
        $this->file = $file;

        // keeping old file path fore later removing
        // shouldn't be changed many times, as the first one is from db
        $this->previousUploadPath = $this->previousUploadPath ?: $this->getUploadPath();

        // make Doctrine to understand that changes are made
        if (null !== $this->file) {
            // generate an unique name
            $filename = sha1(uniqid(mt_rand(), true));
            $this->setUploadPath($filename.'.'.$this->file->guessExtension());
        } else {
            $this->setUploadPath(null);
        }

        return $this;
    }

    /**
     * Get File
     *
     * @return UploadedFile
     */
    public function getFile()
    {
        return $this->file;
    }

    /**
     * Set Upload Path
     *
     * @param $path
     *
     * @return $this
     */
    public function setUploadPath($path)
    {
        $this->uploadPath = $path;
        return $this;
    }

    /**
     * Get Upload Path
     *
     * @return string
     */
    public function getUploadPath()
    {
        return $this->uploadPath;
    }

    /**
     * Get Absolute Path
     *
     * @return null|string
     */
    public function getAbsolutePath()
    {
        return null === $this->uploadPath
            ? null
            : $this->getUploadRootDir().'/'.$this->uploadPath;
    }

    /**
     * Get Web Path
     *
     * @return null|string
     */
    public function getWebPath()
    {
        return null === $this->uploadPath
            ? null
            : $this->getUploadDir().'/'.$this->uploadPath;
    }

    /**
     * Get Previous Upload Absolute Path
     *
     * @return null|string
     */
    private function getPreviousUploadAbsolutePath()
    {
        return null === $this->previousUploadPath
            ? null
            : $this->getUploadRootDir().'/'.$this->previousUploadPath;
    }

    /**
     * Get Upload Root Dir
     *
     * @return string
     */
    public function getUploadRootDir()
    {
        // the absolute directory path where uploaded
        // documents should be saved
        return $this->uploadRootDir;
    }

    /**
     * Set Upload Root Dir
     *
     * @param $uploadRootDir
     */
    public function setUploadRootDir($uploadRootDir)
    {
        $this->uploadRootDir = $uploadRootDir;
    }

    /**
     * Get Upload Dir
     *
     * @return string
     */
    public function getUploadDir()
    {
        // get rid of the __DIR__ so it doesn't screw up
        // when displaying uploaded doc/image in the view.
        return 'documents';
    }

    /**
     * Upload
     */
    public function upload()
    {
        if (null === $this->getFile()) {
            return;
        }

        // if there is an error when moving the file, an exception will
        // be automatically thrown by move(). This will properly prevent
        // the entity from being persisted to the database on error
        $this->file->move(
            $this->getUploadRootDir(),
            $this->getUploadPath()
        );

        // remove the previous file
        $this->removeUpload(true);

        unset($this->file);
    }

    /**
     * Remove Upload
     *
     * Removes file from server
     *
     * @param bool $previous = false When true removes the file from $previousUploadPath (not $uploadPath) property
     */
    public function removeUpload($previous = false)
    {
        if ($file = $previous
                ? $this->getPreviousUploadAbsolutePath()
                : $this->getAbsolutePath()
        ) {
            try {
                unlink($file);
            } catch (\Exception $exception) {
                //TODO: send to logger
            }
        }
    }
}
