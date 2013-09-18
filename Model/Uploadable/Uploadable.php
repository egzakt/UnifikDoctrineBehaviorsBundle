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
     * @var array $previousUploadPath The previous upload path of each field
     */
    protected $previousUploadPaths = [];

    /**
     * @var string $uploadRootDir The upload root dir, common to all fields
     */
    protected $uploadRootDir;

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
    public function getUploadableFields()
    {
        return [];
    }

    /**
     * Return the naming strategy to use to rename uploaded files.
     *
     * Available options are :
     * - alphanumeric (converts tu alphanumeric characters only, spaces are replaced by an hyphen (-))
     * - random hash (a random unique hash)
     * - none (filename remains the same as the original uploaded file)
     *
     * @return string
     */
    public function getNamingStrategy()
    {
        return 'alphanumeric';
    }

    /**
     * Returns the delemiter to use when choosing the Alphanumeric naming strategy.
     *
     * @return string
     */
    public function getAlphanumericDelimiter()
    {
        return '-';
    }

    /**
     * Determines whether the filename should be unique or not.
     *
     * If set to true, the trait will generate a unique filename by appending "-1", "-2" and so on to the filename.
     * If set to false and the uploaded file name already exists on the disk, it will be overwrited.
     *
     * @return bool
     */
    public function getIsUnique()
    {
        return true;
    }

    /**
     * Check whether the uploaded field has been defined or not.
     *
     * @param $field
     *
     * @throws \Exception
     */
    private function uploadableFieldExists($field)
    {
        if (!array_key_exists($field, $this->getUploadableFields())) {
            throw new \Exception(sprintf('The field «%s» is not defined in getUploadableFields method in «%s»', $field, __CLASS__));
        }
    }

    /**
     * This method is used to set an uploaded file and called by the form
     *
     * @param UploadedFile $file
     * @param $field
     */
    public function setUploadedFile(UploadedFile $file, $field)
    {
        $this->uploadableFieldExists($field);

        $this->$field = $file;

        // Keep old file path for later removing
        $this->previousUploadPaths[$field] = $this->getPreviousUploadPath($field) ?: $this->getUploadPath($field);

        // Make Doctrine to understand that changes are made
        if (null !== $this->$field) {

            // Generate the filename
            $filename = $this->generateFilename($file, $field);

            // Set the filename
            $this->setUploadPath($filename, $field);
        } else {

            // File is null
            $this->setUploadPath(null, $field);
        }
    }

    /**
     * Generate the filename depending on the configured naming strategy
     *
     * @param UploadedFile $file
     * @param $field
     *
     * @return string
     *
     * @throws \UnexpectedValueException
     */
    private function generateFilename(UploadedFile $file, $field)
    {
        $extension = '.' . $file->getClientOriginalExtension();
        $filename = str_replace($extension, '', $file->getClientOriginalName());

        switch ($this->getNamingStrategy()) {

            case 'alphanumeric':
                $filename = $this->urlize($filename, $this->getAlphanumericDelimiter());
                break;

            case 'random':
                $filename = sha1(uniqid(mt_rand(), true));
                break;

            case 'none': break;

            default:
                // Not a valid naming strategy
                throw new \UnexpectedValueException(sprintf('The selected naming strategy «%s» does not exist.', $this->getNamingStrategy()));
                break;
        }

        // Make a unique filename?
        if ($this->getIsUnique()) {
            $filename = $this->makeUniqueFilename($filename, $extension, $field);
        }

        return $filename . $extension;
    }

    /**
     * Returns an urlized version of a string
     *
     * @param $filename
     * @param $delemiter
     *
     * @return mixed
     */
    private function urlize($filename, $delemiter = '-')
    {
        $urlized = strtolower(trim(preg_replace("/[^a-zA-Z0-9\/_|+ -]/", '', iconv('UTF-8', 'ASCII//TRANSLIT', $filename)), $delemiter));
        $urlized = preg_replace("/[\/_|+ -]+/", $delemiter, $urlized);
        $urlized = trim($urlized, '-');

        return $urlized;
    }

    /**
     * Generate a unique filename
     *
     * @param string $filename
     * @param string $extension
     * @param string $field
     *
     * @return mixed
     */
    private function makeUniqueFilename($filename, $extension, $field)
    {
        $exposant = 0;
        $uniqueFilename = $filename;

        do {

            if ($exposant) {
                $uniqueFilename = $filename . '-' . $exposant;
            }

            $exposant++;

        } while (file_exists($this->getUploadRootDir($field) . '/' . $uniqueFilename . $extension));

        return $uniqueFilename;
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
        $this->uploadableFieldExists($field);

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
        $this->uploadableFieldExists($field);

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
        $this->uploadableFieldExists($field);

        return null === $this->getUploadPath($field)
            ? null
            : $this->getUploadRootDir($field).'/'.$this->getUploadPath($field);
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
        $this->uploadableFieldExists($field);

        return null === $this->getUploadPath($field)
            ? null
            : $this->getUploadDir($field).'/'.$this->getUploadPath($field);
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
        $this->uploadableFieldExists($field);

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
        $this->uploadableFieldExists($field);

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
        $this->uploadableFieldExists($field);

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
        $this->uploadableFieldExists($field);

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

        // If there is an error when moving the file, an exception will
        // be automatically thrown by move(). This will properly prevent
        // the entity from being persisted to the database on error
        foreach($this->getUploadableFields() as $field => $uploadDir) {

            // If a file has been uploaded
            if (null !== $this->$field) {

                $this->$field->move(
                    $this->getUploadRootDir($field),
                    $this->getUploadPath($field)
                );

                // Remove the previous file if necessary
                $this->removeUpload($field, true);

                unset($this->$field);
            }
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
        $this->uploadableFieldExists($field);

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
