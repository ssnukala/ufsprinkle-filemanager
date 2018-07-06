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
use UserFrosting\Sprinkle\FileManager\Controller\FileManagerFTPController;

class FileManagerController extends SimpleController
{
    public function fileTest($request, $response, $args)
    {
        $limit=5;

        $ttndata = new TTNPerformerDataController($this->ci, 'http://www.boxofficeticketsnow.com');
        $topperfrec = $ttndata->prepareData($limit);
        Debug::debug("Line 244 this is the TopPerf array", $topperfrec);
        var_dump($topperfrec);
//        $ttndata2 = new TTNVenueDataController($this->ci, 'https://goodseatticktes.com');
/*
        $topperf_today = PerformerDailyStats::topToday()->limit($limit)->get();
        $toprecs= $topperf_today->toArray();
        foreach ($toprecs as &$toprec) {
//            $toprec = $this->getTTNAdData($toprec, 'performer_name');
            $toprec = $ttndata->prepareData($toprec);
        }
        $topvenue_today = VenueDailyStats::topToday()->limit($limit)->get();
        $toprecs2= $topvenue_today->toArray();
        foreach ($toprecs2 as &$toprec2) {
//            $toprec = $this->getTTNAdData($toprec, 'performer_name');
            $toprec2 = $ttndata2->prepareData($toprec2);
        }
        Debug::debug("Line 242 this is the toprecs array", $toprecs2);
*/
//        $cleanname = 'nhl stanley cup finals washington capitals vs vegas golden knights home game 3 series game 6';
//        $updstring = $this->createCleanKeywords($cleanname);
//        $updstring = AdUtil::createCleanKeywords($cleanname);
//        echo($updstring);
    }
}
