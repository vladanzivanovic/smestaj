<?php

namespace SiteBundle\Services;


use SiteBundle\Constants\MessageConstants;
use Symfony\Component\HttpFoundation\File\Exception\FileException;
use Symfony\Component\HttpFoundation\File\Exception\UploadException;

class UploadService
{
    protected $rootDir;
    protected $uploadTmpDir;

    /**
     * UploadService constructor.
     * @param $rootDir
     * @param $uploadTmpDir
     */
    public function __construct($rootDir, $uploadTmpDir)
    {
        $this->rootDir = $rootDir;
        $this->uploadTmpDir = $uploadTmpDir;
    }

    /**
     * Decode base64 string and put in file
     * location: upload/tmp
     * @param $base64Str
     * @param $filename
     * @return bool
     * @throws \Symfony\Component\HttpFoundation\File\Exception\UploadException
     */
    public function base64ToFile($base64Str, $filename)
    {
        if( isset($base64Str, $filename) ){
            $splitDocStr = explode(",", $base64Str, 2);
            $docData = $splitDocStr[1];
            if(strpos($docData, "data:application/pdfbase64") !== false){
                $docData = str_replace("data:application/pdfbase64", "", $docData);
            }
            $documentData = base64_decode($docData);
            if(file_put_contents($filename, $documentData)){
                return true;
            }
        }
        throw new UploadException(MessageConstants::UPLOAD_FAILED);
    }

    /**
     * Delete file from upload/tmp folder
     * @param $fileName
     * @return bool
     * @throws \Symfony\Component\HttpFoundation\File\Exception\FileException
     */
    public function deleteFromTmp($fileName)
    {
        if(unlink("{$this->uploadTmpDir}/{$fileName}")){
            return true;
        }
        throw new FileException(MessageConstants::DELETE_FILE);
    }
}