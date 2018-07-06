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
use UserFrosting\Fortress\Adapter\JqueryValidationAdapter;
use UserFrosting\Sprinkle\Core\Facades\Debug;
use UserFrosting\Sprinkle\FormGenerator\Form;
use UserFrosting\Support\Repository\Loader\YamlFileLoader;
use UserFrosting\Fortress\RequestSchema\RequestSchemaRepository;
use UserFrosting\Sprinkle\FileManager\Controller\FlySFTPController as SFTPController;

class FileManagerController extends SimpleController
{
    public function ttnTest($request, $response, $args)
    {
        $options = [
        'host' => 'feeds.ticketnetwork.com',
        'username' => 'PF-4618',
        'password' => '$$4618ka$$'];

        $ttnftp = new SFTPController($this->ci, $options);
        $files = $ttnftp->getFileList('/');
        Debug::debug("Line 41 the file liset is  array", $files);
        var_dump($files);
    }
}
