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
use UserFrosting\Sprinkle\FileManager\Controller\FlyBaseController;

use League\Flysystem\Sftp\SftpAdapter as Adapter;

class FlySFTPController extends FlyBaseController
{
    protected $flysystem;
    protected $options=[
        'host' => 'example.com',
        'port' => 22,
        'username' => 'username',
        'password' => 'password'
//        'privateKey' => 'path/to/or/contents/of/privatekey',
//        'root' => '/path/to/root',
//        'timeout' => 10,
//        'directoryPerm' => 0755
    ];

    public function __construct($ci, $options)
    {
        $options['port'] = 22;
        $this->options=$options;
        $this->setFileSystem();
        return parent::__construct($ci);
    }

    public function setFileSystem()
    {
//        $config = $ci->config;
        $adapter = new Adapter($this->options);
        $this->flysystem = new Filesystem($adapter);
    }
}
