<?php
/*
 *
 * File based on Approach Registrar release candidate available on GitHub.com/Approach (Toolkit Release Candidates)
 * This version is considered a derrived work and property of the current project
 * except for domain functions and functions which exist on the public Approach github account
 */

xdebug_disable();

require_once(__DIR__ . '/../core.php');
global $InstallPath;
require_once($InstallPath.'/base/Renderables/DisplayUnits.php');

//$sesname = session_name('ctdash');
//session_start();
//require_once('Datasets/composistions.php');
//require_once('Datasets/components.php');

$some_function = function(){
  return $WorkData[
    'render'    =>  '<div>HTML Markup</div>',
    'selector'  =>  '#SomePageID'
  ]
}

$register['Action']['Scope']= $function;

$ApproachRegisteredService = $register;

?>
    
