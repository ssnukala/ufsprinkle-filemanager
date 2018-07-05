<?php

namespace UserFrosting\Sprinkle\TTNCampaigns\Controller\Datatables;

use Carbon\Carbon;
use UserFrosting\Support\Exception\ForbiddenException;
use UserFrosting\Sprinkle\Core\Facades\Debug;
use UserFrosting\Sprinkle\Core\Util\EnvironmentInfo;
use UserFrosting\Sprinkle\Datatables\Controller\DatatablesController;

/**
 * iListDTDBController
 *
 * @package UserFrosting-Datatables
 * @author Srinivas Nukala
 * @link http://srinivasnukala.com
 */
class PerformerCurrentStatsController extends DatatablesController
{
    protected $sprunje_name='performer_current_stats_spruje';

    public function setupDatatable($properties = [])
    {
        $repprop = ['htmlid' => 'ttnrep_dt_2',
            'schema' => 'schema://datatable/performer_current_stats.yaml',
            "ajax_url" => "/ttn/performer/current/fullreport",
            "initial_sort" => [[ 8, 'desc' ]]
        ];

        $repprop['formatters'] = [
                    "tables/formatters/performer_details.html.twig"
                ];
        $newproperties = array_merge($repprop, $properties);

        parent::setupDatatable($newproperties);
    }

    public function fullReportData($request, $response, $args)
    {
        $this->setSprunje($request, $response, $args);
        return $this->sprunje->toResponse($response);
    }
}
