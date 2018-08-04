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
//use UserFrosting\Sprinkle\FileManager\Controller\FlySystemResponse;
use League\Flysystem\Filesystem;
use League\Flysystem\Adapter\Local as Adapter;
//use League\Glide\ServerFactory;
use UserFrosting\Sprinkle\FileManager\Controller\FlyBaseController;

class FlyLocalController extends FlyBaseController
{
    public function __construct($ci, $subdir = '')
    {
        $config = $ci->config;
        Debug::debug("Line 36 path RootDir is ".\UserFrosting\ROOT_DIR);

//        $docroot = str_replace('/public', '', $config['path.document_root']);
        $this->basepath = rtrim(\UserFrosting\ROOT_DIR . "/docs/$subdir", "/");
//        Debug::debug("Line 43 the base path is ".$this->basepath);
        $this->setFileSystem();
        return parent::__construct($ci);
    }

    public function setFileSystem()
    {
//        $config = $this->ci->config;
//        Debug::debug("Line 146 path config is ", $config['path']);
//        $docroot = str_replace('/public', '', $config['path.document_root']);
//        $this->basepath = rtrim(str_replace('/public', '', $config['path.document_root']) . "/docs/$subdir", "/");
        Debug::debug("Line 43 the base path is ".$this->basepath);
        $adapter = new Adapter($this->basepath, LOCK_EX, Adapter::DISALLOW_LINKS, [
            'file' => [
                'public' => 0744,
                'private' => 0700,
            ],
            'dir' => [
                'public' => 0755,
                'private' => 0700,
            ]
        ]);
        $this->flysystem = new Filesystem($adapter);
    }
}
