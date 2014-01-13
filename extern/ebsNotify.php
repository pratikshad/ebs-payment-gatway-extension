<?php

session_start( );

require_once '../civicrm.config.php';
require_once 'CRM/Core/Config.php';
require_once 'CRM/Utils/Request.php';

$config = CRM_Core_Config::singleton();

require_once 'ebsResponse.php';

static $store = null;
$ebsResponse = new CRM_Core_Payment_EbsResponse();

$qfKey = CRM_Utils_Request::retrieve('qfKey', 'String', $store, false, null, 'GET');
$module_name = CRM_Utils_Array::value('module', $_GET);
$ebsResponse->main($qfKey);
