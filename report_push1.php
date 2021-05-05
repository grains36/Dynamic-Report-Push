<?php

namespace DynamicReportPush\DynamicReportPushExternalModule;

use ExternalModules\AbstractExternalModule;
use ExternalModules\ExternalModules;

require_once ExternalModules::getProjectHeaderPath();

# requires a GET['pid'], a configuration for the current project, and a GET['location'] (this [project] or all)
# for repeating forms/instruments/events, the first instance on the earliest event is used


$pushvars = new DynamicReportPushExternalModule();

$settings = ExternalModules::getProjectSettingsAsArray($pushvars->PREFIX, $_GET['pid']);
$reports = $settings['report_id']['value'];
$reportCount = count($reports);
//print_r ($reportCount);

$ProjectID = $settings['project-id']['value'];
if (!is_array($ProjectID)) {
	$ProjectID = array($ProjectID);
}
// create an array with values of each instance of a dynamic report
for($i=0; $i <$reportCount; ++$i) {

$ProjectID = $settings['project-id']['value'];
$ReportID = $settings['report_id']['value'];
$curtoken = $settings['token1']['value'];
$pushtoken = $settings['token2']['value'];
$eventname = $settings['event_name']['value'];
$longtoclassic = $settings['long_to_classic']['value'];
$repeattoclassic = $settings['repeat_to_classic']['value'];
$onetime = $settings['onetime1']['value'];

			$data = array(
			    'token' => $curtoken[$i],
			    'content' => 'report',
			    'format' => 'json',
			    'report_id' => $ReportID[$i],
			    'redcap_event_name' => $eventname[$i],
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

			//print_r ($data);
			$result = curl_exec($ch);

			curl_close($ch);

			$shark = json_decode($result, true);  //convert json file to a php array


            // add event name to data file or destroy the event/repeating instrument fields if necessary
            foreach ($shark as &$record) {
            if (isset($eventname[$i])) {
			 $record ['redcap_event_name'] = $eventname[$i];
			}
			if ($longtoclassic[$i] == 1) {
				unset($record[redcap_event_name]);
			}

			if ($repeattoclassic[$i] == 1) {
			    unset($record[redcap_repeat_instrument]);
			    unset($record[redcap_repeat_instance]);
			}
			}

			$result2 = json_encode($shark, JSON_PRETTY_PRINT); //convert data to json

            //print_r ($result2);

			$data = array(
			    'token' => $pushtoken[$i],
			    'content' => 'record',
			    'format' => 'json',
			    'type' => 'flat',
			    'redcap_event_name' => $eventname[$i],
			    'overwriteBehavior' => 'normal',
			    'forceAutoNumber' => 'false',
			    'data' => $result2,
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

			$message = ' Current Number of Records in Project ' .$ProjectID[$i]. ' is ' . $message ;

			print $message;
			echo "<br>";

			curl_close($ch);

		if (($output < '1') or ($onetime[$i] <'1')){
		    goto drpendnow;
		    }



			 // add onetime flag data to data file
			            foreach ($shark as &$record) {
			            if (isset($shark[$i])) {
						 $record ['drp_onetime1'] = 1;
			}
			}

						$result3 = json_encode($shark, JSON_PRETTY_PRINT); //convert data to json

						//print_r ($result3);


						$data = array(
									    'token' => $curtoken[$i],
									    'content' => 'record',
									    'format' => 'json',
									    'type' => 'flat',
									    'redcap_event_name' => $eventname[$i],
									    'overwriteBehavior' => 'normal',
									    'forceAutoNumber' => 'false',
									    'data' => $result3,
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
		   			$outflag = curl_exec($ch);

			        curl_close($ch);


					drpendnow:

						$result = null;
						$shark = null;
						$result2 = null;
            			$data = null;
            			$result3 = null;

}