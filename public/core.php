<?php

/*************************************************************************

 APPROACH
 Organic, human driven software.

 garet.claborn@gmail.com
 
 *************************************************************************/

global $RuntimePath;
global $InstallPath;
global $StaticFiles;
global $DeployPath;
global $SupportPath;
global $ServicePath;
global $ServicePathAlias;
global $DataPath;
global $UserPath;
global $APROACH_SERVICE_CALL;

global $ClusterName;
global $RemoteBase;
global $MultiEOL;
global $SessionName;

$ClusterName='fsa.east.edge';
$SessionName='fsacook';
$MultiEOL =PHP_EOL.'<br />';

$RuntimePath	=__DIR__;
$InstallPath	=__DIR__.'/../Approach';
$SupportPath	=__DIR__.'/support';
$UserPath	=$SupportPath.'/components';
$DataPath	=$SupportPath.'/datasets/schema/SellerSumo';
$RemoteBase	= 'freespeechaction.com';
$StaticPath	='//static.'.$RemoteBase;
$DeployPath	='//'.$RemoteBase;
$ServicePath = '//service.'.$RemoteBase;
$ServicePathAlias = '//'.$RemoteBase.'/service';

/*
$ApproachConfig['Core']['DynamicSiteBaseURL']='';
$ApproachConfig['Core']['SafeDynamicSiteBaseURL'] ='';
$ApproachConfig['Core']['SafeCurrentURL'] = '';
*/
$ApproachConfig['remote']['base']=$RemoteBase;
$ApproachConfig['remote']['path'] ='/';
$ApproachConfig['user']['id'] = 100;
$ApproachConfig['user']['logo'] = '404.png';


ini_set('session.cookie_domain', '.'.$RemoteBase);

require_once($RuntimePath.'/support/_config.php');
require_once($InstallPath.'/base/Renderables/DisplayUnits.php');
require_once($InstallPath.'/base/Dataset.php');
require_once($InstallPath.'/base/Smart.php');
require_once($InstallPath.'/base/ClientEvents.php');
require_once($InstallPath.'/core/Component.php');
require_once($InstallPath.'/core/Composition.php');
require_once($InstallPath.'/core/Service.php');

foreach (glob($DataPath .'/*.php') as $filename) require_once $filename;
foreach (glob($UserPath .'/*.php') as $filename) require_once $filename;

//if(defined('IncludeDNS')) require_once($RuntimePath.'/service/tools/dns_config/dns_config.php');


function CheckSession(){
	global $SessionName;
	if (session_status() === PHP_SESSION_NONE){
		@$sesname = session_name($SessionName);
		@session_start();
	}
}
?>
