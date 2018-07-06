<?php
/**
 * Helper - FileManager
 *
 * @link      https://github.com/ssnukala/ufsprinkle-filemanager
 * @copyright Copyright (c) 2013-2016 Srinivas Nukala
 */
 $app->group('/ufile', function () {
     $this->get('/ttntest', 'UserFrosting\Sprinkle\FileManager\Controller\FileManagerController:ttnTest');
 });
