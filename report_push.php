<?php

namespace DynamicReportPush\DynamicReportPushExternalModule;

use ExternalModules\AbstractExternalModule;
use ExternalModules\ExternalModules;

require_once ExternalModules::getProjectHeaderPath();

# requires a GET['pid'], a configuration for the current project, and a GET['location'] (this [project] or all)
# for repeating forms/instruments/events, the first instance on the earliest event is used


$pushvars = new DynamicReportPushExternalModule();

$settings = ExternalModules::getProjectSettingsAsArray($pushvars->PREFIX, $_GET['pid']);

$ReportID = $settings['report_id']['value'];

$curtoken = $settings['token1']['value'];

$pushtoken = $settings['token2']['value'];


$data = array(
    'token' => $curtoken,
    'content' => 'report',
    'format' => 'json',
    'report_id' => $ReportID,
    'rawOrLabel' => 'raw',
    'rawOrLabelHeaders' => 'raw',
    'exportCheckboxLabel' => 'false',
    'returnFormat' => 'json'
);
$ch = curl_init();
curl_setopt($ch, CURLOPT_URL, APP_PATH_WEBROOT_FULL.'api/');
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
curl_setopt($ch, CURLOPT_VERBOSE, 0);
curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true);
curl_setopt($ch, CURLOPT_AUTOREFERER, true);
curl_setopt($ch, CURLOPT_MAXREDIRS, 10);
curl_setopt($ch, CURLOPT_CUSTOMREQUEST, 'POST');
curl_setopt($ch, CURLOPT_FRESH_CONNECT, 1);
curl_setopt($ch, CURLOPT_POSTFIELDS, http_build_query($data, '', '&'));

$result = curl_exec($ch);

curl_close($ch);


$data = array(
    'token' => $pushtoken,
    'content' => 'record',
    'format' => 'json',
    'type' => 'flat',
    'overwriteBehavior' => 'normal',
    'forceAutoNumber' => 'false',
    'data' => $result,
    'returnContent' => 'count',
    'returnFormat' => 'json'
);

$ch = curl_init();
curl_setopt($ch, CURLOPT_URL, APP_PATH_WEBROOT_FULL.'api/');
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
curl_setopt($ch, CURLOPT_VERBOSE, 0);
curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true);
curl_setopt($ch, CURLOPT_AUTOREFERER, true);
curl_setopt($ch, CURLOPT_MAXREDIRS, 10);
curl_setopt($ch, CURLOPT_CUSTOMREQUEST, 'POST');
curl_setopt($ch, CURLOPT_FRESH_CONNECT, 1);
curl_setopt($ch, CURLOPT_POSTFIELDS, http_build_query($data, '', '&'));

$output = curl_exec($ch);

$message = str_replace("}", "", $output);

$message = substr ($message, 8);

$message = 'Current Number of Records in Linked Project is ' . $message ;


print $message;

curl_close($ch);



