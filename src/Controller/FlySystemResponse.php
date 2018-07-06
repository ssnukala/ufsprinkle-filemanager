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
use UserFrosting\Sprinkle\Core\Facades\Debug;

class FlySystemResponse extends SimpleController
{
    protected $filedata;

    public function __construct($data = null)
    {
        $this->setData($data);
    }

    public function flushJson()
    {
        $this->filedata = json_encode(array('result' => $this->filedata));
        return $this->flush();
    }

    public function flush()
    {
        echo $this->filedata;
        exit;
    }

    public function setData($data)
    {
        $this->filedata = $data;
        return $this;
    }

    public function setHeaders($params)
    {
        if (!headers_sent()) {
            if (is_scalar($params)) {
                header($params);
            } else {
                foreach ($params as $key => $value) {
                    header(sprintf('%s: %s', $key, $value));
                }
            }
        }
        return $this;
    }
}
