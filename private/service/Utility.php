<?php

/*************************************************************************

 APPROACH
 Organic, human driven software.


 COPYRIGHT NOTICE
 __________________

 (C) Copyright 2012 - Approach Foundation LLC, Garet Claborn
 All Rights Reserved.

 Notice: All information contained herein is, and remains
 the property of Approach Foundation LLC and the original author, Garet Claborn,
 herein referred to as "original author".

 The intellectual and technical concepts contained herein are
 proprietary to Approach Foundation LLC and the original author
 and may be covered by U.S. and Foreign Patents, patents in process,
 and are protected by trade secret or copyright law.

/*************************************************************************
*
*
* Approach by Garet Claborn is licensed under a
* Creative Commons Attribution-NonCommercial-ShareAlike 3.0 Unported License.
*
* Based on a work at https://github.com/stealthpaladin .
*
* Permissions beyond the scope of this license may be available at
* http://www.approachfoundation.org/now.
*
*
*
*************************************************************************/

require_once __DIR__.'/../core.php';
require_once($RuntimePath .'/service/Registrar.php');

global $APROACH_SERVICE_CALL;
global $RuntimePath;
global $InstallPath;
global $UserPath;
global $StaticFiles;
global $DeployPath;
global $RemoteBase;

$APROACH_SERVICE_CALL = true;
/*$url = parse_url($_SERVER['HTTP_ORIGIN']);
if( strrpos($url['host'],$RemoteBase) == strlen($url['host']) - strlen($RemoteBase) )
{
    header('Access-Control-Allow-Origin: '.$_SERVER['HTTP_ORIGIN']);
    header('Access-Control-Allow-Credentials: true');
}
*/
class Utility extends Service
{
    public $options=array();
    public $User;

    public function HandleXHR(){
        //allow subdomain access
        global $RemoteBase;
        $url = parse_url($_SERVER['HTTP_ORIGIN']);

        //var_export($_SERVER);
        if( strrpos($url['host'],$RemoteBase) == strlen($url['host']) - strlen($RemoteBase) )
        {
            header('Access-Control-Allow-Origin: '.$_SERVER['HTTP_ORIGIN']);
            header('Access-Control-Allow-Credentials: true');
        }
        else{ header('Access-Control-Allow-Origin: '.$_SERVER['HTTP_ORIGIN']);
          header('Access-Control-Allow-Credentials: true');

        }
    }

    public function ProcessJSON($activity)
    {
        global $ApproachRegisteredService;
        $success=false;
        $Component = false;
        $req=@$activity['incoming']['support'];
        $support = array();
        $arguments= array();
        $WorkingSet=array();
        $response = array();

//      if(isset($activity['incoming']['command']['ACTION'])) $arguments=$activity['incoming']['command']['ACTION'];
        if(isset($activity['incoming']['support'])) $support = $activity['incoming']['support'];

//        var_dump($activity['incoming']['command']);
        foreach($activity['incoming']['command'] as $Request => $Instructions)
        {
            $success=false;
            $arguments[$Request]=$Instructions;
            if($Request == 'TRIGGER') continue;
            foreach($Instructions as $Domain => $Command)
            {
                if($Domain== 'Composition')
                {
                    $support['context']=$activity['context'];
                    $support['LiveComponent']=$this->LiveComponent;
                }

                //{'$Request':[{'$Selector':'$Markup'}]}
                if(!is_array($Command))
                {
                    $WorkingSet[$Domain][$Command]= $ApproachRegisteredService[$Domain][$Command]($arguments, $support);
                    $selector = isset($WorkingSet[$Domain][$Command]['selector']) ? $WorkingSet[$Domain][$Command]['selector'] : '#Dynamics';
                    $response[$Request][$selector] = $WorkingSet[$Domain][$Command]['render'];
                }
                else
                foreach($Command as $action => $data)
                {
                    $WorkingSet[$Domain][$action]=$ApproachRegisteredService[$Domain][$action]($arguments, $support);
                    $response[$Request]['#ApproachDebugConsole'] .= $WorkingSet[$Domain][$action]['render'];
                }
                $success = true;
            }
        }
        $response['success'] = $success;

        $activity['outgoing']=$response;
        return $activity['outgoing'];   //Return value should be a nested array that will be json_encode into a JSON object
    }
}

$s = new Utility();
$s->Receive();
$s->Process();
$s->Respond();

?>
