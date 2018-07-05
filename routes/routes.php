<?php
/**
 * Helper - Flysystem
 *
 * @link      https://github.com/ssnukala/ufsprinkle-flysystem
 * @copyright Copyright (c) 2013-2016 Srinivas Nukala
 */
 $app->group('/ufile', function () {
     $this->get('/dashboard', 'UserFrosting\Sprinkle\TTNCampaigns\Controller\TTNCampaignsController:pageDashboard');
     $var_currentevent = 'UserFrosting\Sprinkle\TTNCampaigns\Controller\Datatables\EventCurrentStatsController';
     $this->post('/event/current/fullreport', "$var_currentevent:fullReportData");
     $var_dailyevent = 'UserFrosting\Sprinkle\TTNCampaigns\Controller\Datatables\EventDailyStatsController';
     $this->post('/event/daily/fullreport', "$var_dailyevent:fullReportData");
     $var_currentperf = 'UserFrosting\Sprinkle\TTNCampaigns\Controller\Datatables\PerformerCurrentStatsController';
     $this->post('/performer/current/fullreport', "$var_currentperf:fullReportData");
     $var_dailyperf = 'UserFrosting\Sprinkle\TTNCampaigns\Controller\Datatables\PerformerDailyStatsController';
     $this->post('/performer/daily/fullreport', "$var_dailyperf:fullReportData");

     $this->get('/ttndata', 'UserFrosting\Sprinkle\TTNCampaigns\Controller\TTNCampaignsController:createDailyTTNCampaigns');
     $this->get('/ttntest', 'UserFrosting\Sprinkle\TTNCampaigns\Controller\TTNCampaignsController:ttnTest');
 });
