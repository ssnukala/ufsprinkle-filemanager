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
use League\Glide\ServerFactory;

class FlyBaseController extends SimpleController
{
    protected $basepath;
    protected $flysystem;
    protected $options;

    public function setFileSystem()
    {
        return false;
    }

    public function getFileSystem()
    {
        return $this->flysystem;
    }

    public function getBasePath()
    {
        return $this->basepath;
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


    public function readFile($file)
    {
        $hasfile =$this->flysystem->has($file);
        Debug::debug("Line 68 hasfile is ".$hasfile);
        if ($hasfile) {
            $contents = $this->flysystem->read($file);
            return $contents;
        } else {
            Debug::debug("Line 71 read file ($file) does not exist returning false");
            return false;
        }
    }

    public function writeFile($file, $contents, $overwrite=false)
    {
        if ($this->flysystem->has($file)) {
            if ($overwrite) {
                $response = $this->flysystem->delete($file);
            } else {
                $response = $this->flysystem->rename($file, $file.time());
            }
        }
        $response = $this->flysystem->write($file, $contents);
        return  $response;
    }

    public function getFileMime($file)
    {
        $filemime = $this->flysystem->getMimetype($file);
        return $filemime;
    }

    public function has($file)
    {
        $hasfile = $this->flysystem->has($file);
        return $hasfile;
    }

    public function getTimeStamp($file)
    {
        $filestamp= false;
        if ($this->flysystem->has($file)) {
            $filestamp = $this->flysystem->getTimestamp($file);
        }
        return $filestamp;
    }


    public function saveUploadedFiles($fileindex='')
    {
        $filelist=[];
        if ($fileindex=='') {
            $filelist = $_FILES;
        } else {
            $filelist[] = $_FILES[$fileindex];
        }
        foreach ($filelist as $filearray) {
            $filepath = $this->saveFileStream($filearray);
        }
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


    public function renderGlideFile($base_path, $filename)
    {

    // $filesystem = new Filesystem(new Adapter($install_path.'assets/img/logos'));
        // var_dump($filesystem->has('aflac.png'));
        // die();

        $sourcePath = rtrim("/listing/$base_path", "/");
        $cachePath = $sourcePath . "/cache";
        //$watermarkPath = $base_path . "assets/img/watermarks";
        $watermarkPath = "/theme/ilist2/images/";

        //$exception = new Exception('Danger, Will Robinson!', 100);
        //        $var_flyctrl = new \UserFrosting\iList\iListFlySystemTrxController($this->_app, ($base . "/" . $folder));
        //        error_log("Line 137 the following are the paths { $sourcePath }, { $cachePath }, { $watermarkPath } ");
        $fs = $this->getFlySystemObject($sourcePath);
        //        $fs = new \League\Flysystem\Filesystem(new \League\Flysystem\Adapter\Local($sourcePath));
        // image processing
        $server = ServerFactory::create([
                    'source' => $fs, // Source filesystem
                    //'source_path_prefix' =>      // Source filesystem path prefix
    //                    'cache' => new \League\Flysystem\Filesystem(new \League\Flysystem\Adapter\Local($cachePath)), // Cache filesystem
                    'cache' => $this->getFlySystemObject($cachePath), // Cache filesystem
                    //    'cache_path_prefix' =>       // Cache filesystem path prefix
                    'group_cache_in_folders' => true, // Whether to group cached images in folders
                    'watermarks' => $this->getFlySystemObject($watermarkPath, "/public"), // Watermarks filesystem
                    //    'watermarks_path_prefix' =>  // Watermarks filesystem path prefix
                    'driver' => 'gd', // Image driver (gd or imagick)
                    'max_image_size' => 2000 * 2000 // Image size limit
                        //    'defaults' =>                // Default image manipulations
                        //    'presets' =>                 // Preset image manipulations
                        //    'base_url' =>                // Base URL of the images
    //                    'response' => new \League\Glide\Responses\SymfonyResponseFactory()              // Response factory
        ]);


        $server->setDefaults([
            'mark' => 'logo_orange_tr2.png',
            'markw' => '15w',
            'markpad' => '5w'
        ]);

        $server->setPresets([
            'small' => [
                'w' => 380,
                'h' => 200,
                'fit' => 'fill',
            ],
            'medium' => [
                'w' => 760,
                'h' => 400,
                'fit' => 'fill',
            ],
            'large' => [
                'w' => 1520,
                'h' => 800,
                'fit' => 'fill',
            ]
        ]);

        //        error_log("Line 182 { $filename } checking if it exists");
        $fileExists = $server->sourceFileExists($filename);

        if ($fileExists) {
            $getarr = $_GET;
            if (!isset($getarr['p'])) {
                $getarr['p'] = 'small';
            }
            $server->outputImage($filename, $getarr);
            return true;
        } else {
            error_log("Line 193 $filename does not exist so returning false");
            return false;
        }
    }
}
