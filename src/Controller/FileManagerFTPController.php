<?php
/**
 * FileManager controller :
 *
 * @link      https://github.com/ssnukala/ufsprinkle-filemanager
 * @copyright Copyright (c) 2013-2016 Srinivas Nukala
 *
 */

namespace UserFrosting\Sprinkle\FileManager\Controller;

use Carbon\Carbon;
use Illuminate\Database\Capsule\Manager as Capsule;
use UserFrosting\Sprinkle\Core\Controller\SimpleController;
use UserFrosting\Support\Exception\ForbiddenException;
use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;
use Slim\Exception\NotFoundException;
use UserFrosting\Fortress\RequestDataTransformer;
use UserFrosting\Fortress\RequestSchema;
use UserFrosting\Fortress\ServerSideValidator;
use UserFrosting\Sprinkle\Core\Facades\Debug;
use UserFrosting\Support\Repository\Loader\YamlFileLoader;
use UserFrosting\Fortress\RequestSchema\RequestSchemaRepository;
use UserFrosting\Sprinkle\FileManager\Controller\FlySystemResponse;

use League\Flysystem\Filesystem;
use League\Flysystem\Adapter\Ftp as Adapter;

class FileManagerFTPController extends SimpleController
{
    protected $flysystem;
    protected $options=[
        'host' => 'ftp.example.com',
        'username' => 'username',
        'password' => 'password',

        /** optional config settings */
        'port' => 21,
        'root' => '/path/to/root',
        'passive' => true,
        'ssl' => true,
        'timeout' => 30,
    ];

    public function __construct(ContainerInterface $ci, $options)
    {
        $this->options=$options;
        $this->setFileSystem();
        return parent::__construct($ci);
    }

    public function setFileSystem()
    {
        $config = $ci->config;

        $adapter = new Adapter($this->options);

        $this->flysystem = new Filesystem($adapter);
    }

    public function getFileSystem()
    {
        return $this->flysystem;
    }

    public function getFileList($path)
    {
//        $fileroot = str_replace('//', '/', $this->_fileroot . $path);
//        error_log("Line 28 file root is $fileroot");
        $contents = $this->flysystem->listContents($path, true);
//        logarr($contents, "Line 80 contents");
        foreach ($contents as &$object) {
            $object['name'] = $object['basename'];
            //$date = new \DateTime("@".);
            $object['date'] = date('Y-m-d H:i:s', $object['timestamp']);
        }
        return $contents;
    }


    public function renderFile($fileName, $filePath = '', $param = ['preview' => 'Y'])
    {
        $var_filepath = trim($filePath, "/") . "/" . trim($fileName, "/");
//        $var_fullpath = $this->_fileroot."/".trim($var_filepath,"/");
        $var_fullpath = trim($var_filepath, "/");
        //error_log("Line 78 full path is $var_filepath");
        $fileContent = $this->getFile($var_fullpath);
        if ($fileContent) {
            //error_log("Line 82 rendering $var_fullpath");
            $oResponse = new FlySystemResponse($this->ci);
            $oResponse->setData($fileContent);
            $var_fmime = $this->getFileMime($var_fullpath);

            $download = $param['preview'] == 'Y' ? '' : 'attachment;';
            $var_headerparam = array(
                'Content-Type' => ($var_fmime == 'text/plain' ? 'text/html' : $var_fmime),
                'Content-disposition' => "$download filename=$fileName"
            );
            //logarr($var_headerparam,"Line 90 header params");
            $oResponse->setHeaders($var_headerparam);
            $oResponse->flush();
            return true;
        } else {
            //error_log("Line 98 returning false");
            return false;
        }
    }


    public function getFile($file)
    {
        if ($this->flysystem->has($file)) {
            $contents = $this->flysystem->read($file);
            return $contents;
        } else {
            return false;
        }
    }

    public function getFileMime($file)
    {
        $var_fmime = $this->flysystem->getMimetype($file);
        return $var_fmime;
    }

    public function saveFileStream($file, $folder='')
    {
        $stream = fopen($file['tmp_name'], 'r+');
//        $filleext = end((explode(".", $_FILES[$fileindex]['name']))); # extra () to prevent notice
        $path_parts = pathinfo($file["name"]);
        $random = substr(md5(rand()), 0, 7);
        $filename = time() . '_' . $random . '_' . rtrim(substr(str_replace(' ', '_', $path_parts['filename']), 0, 20) . "." . $path_parts['extension'], '.');
        $newfilename = "$folder/$filename";
        $this->flysystem->writeStream($newfilename, $stream);
        fclose($stream);

        $fullpath = rtrim($this->flysystem->getBasePath(), "/") . "/" . trim($newfilename, "/");
        return $fullpath;
    }
}
