<?php

/**
 * 	Util functions to manipulate files
 */
class ThefFile
{

    const TYPE_ALL = 'all';
    const TYPE_IMAGE = 'image';
    const TYPE_AUDIO = 'audio';
    const TYPE_VIDEO = 'video';
    const TYPE_DOCUMENT = 'document';
    const TYPE_COMPRESSED = 'compressed';
    const FILE_OK = 'OK';
    const FILE_NOT_EXISTS = 'file_not_exists';
    const FILE_INVALID_SIZE = 'file_invalid_size';
    const FILE_INVALID_EXTENSION = 'file_invalid_extension';
    const FILE_INVALID_FOLDER = 'file_invalid_folder';

    private $maxFileSize = 10000000;  // MAX file size in bytes
    private $allowedExtensions = array(); // Array with allowed type of file to be uploaded
    private $allowedPaths = array();  // Array with allowed paths to upload files

    /**
     * Set the Max Size for upload files
     * @param Integer $size Size in bytes
     */

    public function setMaxSize($size)
    {
	$this->maxFileSize = $size;
    }



    /**
     * Set the array with allowed type of file to be uploaded
     * @param Array $allowedExtensions Array with file extensions
     */
    public function setAllowedExtensions(array $allowedExtensions)
    {
	$this->allowedExtensions = $allowedExtensions;
    }



    /**
     * Set the array with allowed paths to upload files
     * @param Array $allowedExtensions Array with file extensions
     */
    public function setAllowedPaths(array $allowedPaths)
    {
	$this->allowedPaths = $allowedPaths;
    }



    /**
     * Send headers to browser to download a file.
     * Checks for file to be in the allowed paths defined
     * @param String $filePath Relative path of the file
     * @param String $fileName Name of the file
     * @param String $fileNewName Name for downloading the file
     * @throws Reading file exception
     */
    public function download($filePath, $fileName, $fileNewName)
    {
	try {
	    if (in_array(dirname($filePath . $fileName) . '/', $this->allowedPaths)) {
		header('Content-type: application/force-download');
		header("Content-Disposition: attachment; filename=$fileNewName");
		readfile($filePath . $fileName);
	    }
	} catch (Exception $ex) {
	    throw $ex;
	}
    }



    /**
     * Upload a file to a specific folder
     *
     * Result of upload Enum defined in ThefFile with prefix FILE_
     *
     * @param Array $files Relative path of files to be uploaded
     * @param Enum $type Type or file, defined in ThefFile with prefix TYPE_
     * @param String $path Destination folder
     * @return Enum Result status
     */
    public function uploadFile(array $files, $type, $path = '/temp')
    {
	switch ($type) {
	    case self::TYPE_IMAGE:
		$allowedExtrensions = array('jpg', 'jpeg', 'gif', 'bmp', 'png');
		break;
	    case self::TYPE_COMPRESSED:
		$allowedExtrensions = array('zip');
		break;
	    case self::TYPE_AUDIO:
		$allowedExtrensions = array('mp3');
		break;
	    case self::TYPE_VIDEO:
		$allowedExtrensions = array('flv', 'mp4', 'wmv', 'mov', 'avi');
		break;
	    case self::TYPE_DOCUMENT:
		$allowedExtrensions = array('doc', 'docx', 'xls', 'xlsx', 'pdf');
		break;
	    case self::TYPE_ALL:
		$allowedExtrensions = null;
		break;
	}

	if (is_dir($path)) {
	    $this->setAllowedExtensions($allowedExtrensions);
	    return $this->upload($files, $path);
	} else {
	    return self::FILE_INVALID_FOLDER;
	}
    }



    /**
     * Upload a specific file to the selected path
     *
     * Result of upload Enum defined in ThefFile with prefix FILE_
     *
     * @param Array $files Relative path of files to be uploaded
     * @param String $filePath Folder to upload the file
     * @param type $filePrefix Prefix to the uploaded filename
     * @return Enum Result status
     */
    public function upload($fileArray, $filePath, $filePrefix = '')
    {
	$ext = strtolower(self::getExtension($fileArray['name']));
	if ($fileArray['tmp_name'] == '') {
	    $result = self::FILE_NOT_EXISTS;
	} else
	if ($fileArray['size'] > $this->maxFileSize) {
	    $result = self::FILE_INVALID_SIZE;
	} else
	if (is_array($this->allowedExtensions) && !in_array($ext, $this->allowedExtensions)) {
	    $result = self::FILE_INVALID_EXTENSION;
	} else
	if ($fileArray['error'] == 0) {
	    $filePath = $filePath . $filePrefix . $fileArray['name'];
	    if (@move_uploaded_file($fileArray['tmp_name'], $filePath)) {
		$result = self::FILE_OK;
	    }
	}
	return $result;
    }



    /**
     * Clean a string for file name to remove spaces and special chars
     * @param String $filename original file name
     * @return String new name
     */
    public static function sanitizeFilename($filename)
    {
	$filename = ThefText::noAccents($filename);
	$filename = preg_replace('/[^a-zA-Z0-9_. ]/', '', $filename);
	$filename = str_replace(' ', '-', $filename);
	$filename = strtolower($filename);
	return $filename;
    }



    /**
     * Returns the extension for a file
     * @param String $filename Name of the file
     * @return String Extension
     */
    public static function getExtension($filename)
    {
	$arr_filename = explode('.', $filename);
	$extension = end($arr_filename);
	return $extension;
    }



    /**
     * Save a specific string to a file (for loggin porpouses)
     *
     * If the specified folder doesnt exists, it will be created
     * Folders may contain dinamic dates values ({Y},{m},{d},{H},{i},{s})
     *
     * @param String $string String to be saved
     * @param String $file Relative path to file (default: _log/log.txt)
     */
    public static function log($string, $file = null)
    {
	if (is_null($file)) {
	    $file = '_log/log.txt';
	} else {
	    $file = str_replace('{s}', Date('s'), $file);
	    $file = str_replace('{i}', Date('i'), $file);
	    $file = str_replace('{H}', Date('H'), $file);
	    $file = str_replace('{d}', Date('d'), $file);
	    $file = str_replace('{m}', Date('m'), $file);
	    $file = str_replace('{Y}', Date('Y'), $file);

	    // Check if folder exists
	    $arr_dir = explode('/', $file);
	    if (count($arr_dir) > 0) {
		array_pop($arr_dir);
		$str_dir = ROOT_PATH . implode('/', $arr_dir);
		if (!is_dir($str_dir))
		    mkdir($str_dir, 0777, true);
	    } else {
		$file = '_log/' . $file;
	    }
	}

	$fp = fopen(ROOT_PATH . $file, 'a+');
	if ($fp) {
	    @chmod(ROOT_PATH . $file, 0777);
	    fwrite($fp, Date("Ymd - H:i:s") . ">" . $string . "\r\n");
	    fclose($fp);
	}
    }



    /**
     * Copy a file from a FTP account to local folder
     *
     * @param String $ftpPath Complete path of file in FTP Server (ie: /folder/file.ext)
     * @param String $localPath Local folder to save the file
     * @param String $server Server name or IP address of FTP Server
     * @param String $user FTP User
     * @param String $password FTP Password
     * @return Boolean True if file was successfully downloaded
     */
    public static function copyFromFtp($ftpPath, $localPath, $server, $user, $password)
    {
	$queryFrom = 'ftp://' . $user . ':' . $password . '@' . $server . '/' . $ftpPath;
	if (file_exists($queryFrom)) {
	    return @copy($queryFrom, $localPath);
	} else {
	    return false;
	}
    }



}