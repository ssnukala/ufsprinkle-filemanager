<?php

/**
 * GoogleAdWords controller :
 *
 * @link      https://github.com/ssnukala/ufsprinkle-googleadwords
 * @copyright Copyright (c) 2013-2016 Srinivas Nukala
 *
 */

namespace UserFrosting\Sprinkle\TTNCampaigns\Controller;

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
use UserFrosting\Sprinkle\TTNCampaigns\Database\Models\EventCurrentStats;
use UserFrosting\Sprinkle\TTNCampaigns\Database\Models\EventDailyStats;
use UserFrosting\Sprinkle\TTNCampaigns\Database\Models\PerformerCurrentStats;
use UserFrosting\Sprinkle\TTNCampaigns\Database\Models\PerformerDailyStats;
use UserFrosting\Sprinkle\TTNCampaigns\Database\Models\VenueCurrentStats;
use UserFrosting\Sprinkle\TTNCampaigns\Database\Models\VenueDailyStats;
use UserFrosting\Sprinkle\TTNCampaigns\Controller\Datatables\EventCurrentStatsController;
use UserFrosting\Sprinkle\TTNCampaigns\Controller\Datatables\EventDailyStatsController;
use UserFrosting\Sprinkle\TTNCampaigns\Controller\Datatables\PerformerCurrentStatsController;
use UserFrosting\Sprinkle\TTNCampaigns\Controller\Datatables\PerformerDailyStatsController;
use UserFrosting\Sprinkle\GoogleAdWords\Controller\GoogleAdWordsController;
use UserFrosting\Sprinkle\GoogleAdWords\Controller\GoogleAdWordsUtilController as AdUtil;
use UserFrosting\Sprinkle\TTNCampaigns\Controller\Data\TTNCampaignDataStaticController as TTNData;
use UserFrosting\Sprinkle\TTNCampaigns\Controller\Data\TTNPerformerDataController;
use UserFrosting\Sprinkle\TTNCampaigns\Controller\Data\TTNVenueDataController;

/*
use UserFrosting\Sprinkle\GoogleAdWords\Controller\Adwords\BasicOperations\GetCampaigns;
use UserFrosting\Sprinkle\GoogleAdWords\Controller\Adwords\BasicOperations\AddCampaigns;
use UserFrosting\Sprinkle\GoogleAdWords\Controller\Adwords\BasicOperations\AddAdGroups;
use UserFrosting\Sprinkle\GoogleAdWords\Controller\Adwords\BasicOperations\AddKeywords;
use UserFrosting\Sprinkle\GoogleAdWords\Controller\Adwords\BasicOperations\AddExpandedTextAds;
*/
class TTNCampaignsController extends SimpleController
{
    protected $siteurl = 'https://www.goodseattickets.com';

    public function pageDashboard($request, $response, $args)
    {
        $repcontroller = new EventCurrentStatsController($this->ci);
        $repcontroller->setupDatatable();
        $eventcurrent = $repcontroller->getDatatableArray();

        $repcontroller2 = new EventDailyStatsController($this->ci);
        $repcontroller2->setupDatatable();
        $eventdaily = $repcontroller2->getDatatableArray();

        $repcontroller3 = new PerformerCurrentStatsController($this->ci);
        $repcontroller3->setupDatatable();
        $performercurrent = $repcontroller3->getDatatableArray();

        $repcontroller4 = new PerformerDailyStatsController($this->ci);
        $repcontroller4->setupDatatable();
        $performerdaily = $repcontroller4->getDatatableArray();

        //        $this->addCampaigns($request, $response, $args);

        return $this->ci->view->render($response, "pages/ttn-dashboard.html.twig", [
                    'info' => [
                        'environment' => $this->ci->environment,
                        'path' => [
                            'project' => \UserFrosting\ROOT_DIR
                        ]
                    ],
                    "eventcurrent" => $eventcurrent,
                    "eventdaily" => $eventdaily,
                    "performercurrent" => $performercurrent,
                    "performerdaily" => $performerdaily
        ]);
    }

    public function createDailyTTNCampaigns($request, $response, $args)
    {
        /*
        Ad
        Headline1: 75% Off {Performer/Event} Tickets
        Headline2: Cheapest Tickets, Fast & Easy
        Description: Trusted Source for {performer/event} Tickets. 100% Buyer Guarantee.
        Keywords:
            performer tickets
            [performer tickets]
            +performer +tickets
         */
        Debug::debug("Line 123 calling performer daily stats now");
        $this->createPerformerDailyStatsCampaigns(10);
        Debug::debug("Line 125: Done \n. calling Event daily stats now");
//        $this->createEventDailyStatsCampaigns(5);
//        Debug::debug("Line 127: Done with all");
        echo("<h3>All Done</h3>");
    }

    public function createEventDailyStatsCampaigns($limit=5)
    {
        $adController = new GoogleAdWordsController($this->ci);
        $topevents_today = EventDailyStats::topToday()->limit($limit)->get();
        $toprecs= $topevents_today->toArray();

        foreach ($toprecs as &$toprec) {
            $toprec = $this->prepareData($toprec, 'event_name');
//            $this->addGoogleCampaigns($toprec['adwords_input']);
            Debug::debug("Line 118 Adding campaigns with this ", $toprecs['adwords_input']);
            $adController->addMyCampaigns($toprec['adwords_input']);
        }
        Debug::debug("Line 162 this is the toprecs array", $toprecs);
    }

    public function createPerformerDailyStatsCampaigns($limit=5)
    {
        $adController = new GoogleAdWordsController($this->ci);
        $ttndata = new TTNPerformerDataController($this->ci, 'http://www.boxofficeticketsnow.com');
        $topperfrec = $ttndata->prepareData($limit);
//        Debug::debug("Line 128 Creating campaigns with this TopPerf array", $topperfrec);
        foreach ($topperfrec as $toprec) {
            $adController->addMyCampaigns($toprec['adwords_input']);
        }
        Debug::debug("Line 132 Done processing Campaigns");
    }


    public function ttnTest($request, $response, $args)
    {
        /*        $loader = new YamlFileLoader("schema://campaigns/test_campaign.json");
                $schemaData = $loader->load();
                Debug::debug("Line 273 loaded data is ", $schemaData);
                echo("Line 274 the budget is : ".$schemaData['budget']['microamount']);
        */
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
