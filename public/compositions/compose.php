<?php
$blank=false;
switch(Composition::$Active->Context['data']['alias']){
    case 'login': require_once 'login.php'; break;
    case 'signup': require_once 'signup.php'; break;
    case 'new_password': require_once 'new_password.php'; break;
    case 'checkout': require_once 'checkout.php'; break;
    default: $blank = true; break;
}

if($blank){

require_once __DIR__.'/../form_layout.php';

global $RuntimePath;
global $DeployPath;
global $SupportPath;
global $StaticFiles;

$StaticMarkupPath = $RuntimePath.'/support/templates/static/';
$TemplatePath = $RuntimePath.'/support/templates/';

$items = LoadObjects( 'compositions',array( 'method' => ' WHERE parent = '.Composition::$Active->Context['data']['id'] ));
$UserID = isset(Composition::$Active->Context['authenticated_account']) ? Composition::$Active->Context['authenticated_account']->data['id'] : null;

$AdminMain->children[]=new renderable(['tag'=>'li','classes'=>['col-sm-4']]);
$AdminMain->children[]=$ContentArea=new renderable(['tag'=>'li','classes'=>['col-sm-8']]);
$ContentArea->content='<div class="modal-body col-sm-12">';

}

?>
