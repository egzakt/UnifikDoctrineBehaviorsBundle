<?php

namespace Egzakt\DoctrineBehaviorsBundle\Model\Uploadable;

use Symfony\Component\HttpFoundation\File\UploadedFile;

/**
 * Uploadable Trait
 *
 * Should be in the entity that should be uploadable.
 */
trait Uploadable
{
    /**
     * @var array $uploadPath The upload path of each field
     */
    private $uploadPaths;

    /**
     * @var array $previousUploadPath The previous upload path of each field
     */
    private $previousUploadPaths = [];

    /**
     * @var string $uploadRootDir The upload root dir, common to all fields
     */
    private $uploadRootDir;

    /**
     * Get the list of uploabable fields and their respective upload directory in a key => value array format.
     * This method should always be redeclared in the entity.
     *
     * ex:  return [
     *          'image' => 'images',
     *          'banner' => 'banners'
     *      ]
     *
     * @return array
     */
    private function getUploadableFields()
    {
        return [];
    }

    /**
     * Check whether the uploaded field has been defined or not.
     *
     * @param $field
     *
     * @return bool
     */
    private function uploadableFieldExists($field)
    {
        return array_key_exists($field, array_keys($this->getUploadableFields()));
    }

    /**
     * This method is used to set an uploaded file and called by the form
     *
     * @param UploadedFile $file
     * @param $field
     */
    public function setUploadedFile(UploadedFile $file, $field)
    {
        $this->$field = $file;

        // keeping old file path for later removing
        // shouldn't be changed many times, as the first one is from db
        $this->previousUploadPaths[$field] = $this->getPreviousUploadPath($field) ?: $this->getUploadPath($field);

        // make Doctrine to understand that changes are made
        if (null !== $this->$field) {
            // generate an unique name
            $filename = sha1(uniqid(mt_rand(), true));
            $this->setUploadPath($filename.'.'.$file->guessExtension(), $field);
        } else {
            $this->setUploadPath(null, $field);
        }
    }

    /**
     * Get Upload Path
     *
     * @param $field
     *
     * @return string
     */
    public function getUploadPath($field)
    {
        return $this->{$field . 'Path'};
    }

    /**
     * Set Upload Path
     *
     * @param $uploadPath
     * @param $field
     */
    public function setUploadPath($uploadPath, $field)
    {
        $this->{$field . 'Path'} = $uploadPath;
    }

    /**
     * Get Absolute Path
     *
     * @param string $field
     *
     * @return null|string
     */
    public function getAbsolutePath($field)
    {
        return null === $this->uploadPaths[$field]
            ? null
            : $this->getUploadRootDir($field).'/'.$this->uploadPaths[$field];
    }

    /**
     * Get Web Path
     *
     * @param string $field
     *
     * @return null|string
     */
    public function getWebPath($field)
    {
        return null === $this->uploadPaths[$field]
            ? null
            : $this->getUploadDir($field).'/'.$this->uploadPaths[$field];
    }

    /**
     * Get Previous Upload Path
     *
     * @param $field
     *
     * @return null
     */
    private function getPreviousUploadPath($field)
    {
        return array_key_exists($field, $this->previousUploadPaths) ? $this->previousUploadPaths[$field] : null;
    }

    /**
     * Get Previous Upload Absolute Path
     *
     * @param string $field
     *
     * @return null|string
     */
    private function getPreviousUploadAbsolutePath($field)
    {
        return null === $this->previousUploadPaths[$field]
            ? null
            : $this->getUploadRootDir($field).'/'.$this->previousUploadPaths[$field];
    }

    /**
     * Get the upload dir of an uploadable field
     *
     * @param $field
     *
     * @return mixed
     */
    private function getUploadDir($field)
    {
        return $this->getUploadableFields()[$field];
    }

    /**
     * Get Upload Root Dir
     *
     * @param string $field
     *
     * @return string
     */
    public function getUploadRootDir($field)
    {
        // the absolute directory path where uploaded
        // documents should be saved
        return $this->uploadRootDir . '/' . $this->getUploadDir($field);
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
     * Upload
     *
     * Shoud be called when the form is valid, before flushing the EntityManager
     */
    public function upload()
    {
        if (0 === count($this->getUploadableFields())) {
            return;
        }

        // if there is an error when moving the file, an exception will
        // be automatically thrown by move(). This will properly prevent
        // the entity from being persisted to the database on error
        foreach($this->getUploadableFields() as $field => $uploadDir) {
            $this->$field->move(
                $this->getUploadRootDir($field),
                $this->getUploadPath($field)
            );

            // remove the previous file
            $this->removeUpload($field, true);

            unset($this->file);
        }
    }

    /**
     * Removes file from server on postRemove Doctrine Event
     */
    public function removeUploads()
    {
        foreach($this->getUploadableFields() as $field => $uploadDir) {
            $this->removeUpload($field, $previous);
        }
    }

    /**
     * Removes a single file from server
     *
     * @param string $field
     * @param bool $previous = false When true removes the file from $previousUploadPath (not $uploadPath) property
     */
    public function removeUpload($field = null, $previous = false)
    {
        if ($file = $previous
                ? $this->getPreviousUploadAbsolutePath($field)
                : $this->getAbsolutePath($field)
        ) {
            try {
                unlink($file);
            } catch (\Exception $exception) {
                //TODO: send to logger
            }
        }
    }
}
