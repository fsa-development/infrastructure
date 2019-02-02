<?php
require_once __DIR__.'/../../admin_layout.php';

global $RuntimePath;
global $DeployPath;
global $SupportPath;
global $StaticFiles;

$StaticMarkupPath = $RuntimePath.'/support/templates/static/';
$TemplatePath = $RuntimePath.'/support/templates/';

//$items = LoadObjects( 'compositions',array( 'method' => ' WHERE parent = '.Composition::$Active->Context['data']['id'] ));
//$UserID = Composition::$Active->Context['authenticated_account']->data['id'];

$NewItemBtnWrap->content=<<<STR
<a class="control btn blueBtn"
    data-self=""
    data-context='{"_self_id":"", "_response_target":"#componentSettingsStage"}'
    data-intent='{"REFRESH":{"ListingsList":"Edit"}}'
    data-toggle="modal"
    onclick="addTableRow('#ListingsLibrary');"
    href="#AdminModal"> New Listing</a>
STR;

$options=[];
$options['tag']='tbody';
$options['pageID']='ListingsLibrary';
$options['classes']=['Interface'];
$options['template']=$SupportPath .'/templates/ListingsList.xml';
$options['ListingsList']['listings'] = [
    'select'=>' * ',
    'condition'=>'ORDER BY `active`,`private`,`price`,`title`,`id`',
    'method'=>' WHERE `agent` = '.$_SESSION['user']['id'],
    'new_query'=>true
];
$TheList=new Smart($options);
$options=[];

$AdminContent = new renderable('table');
$AdminContent->classes=['table', 'table-hover', 'table-large'];

$MainAdminTable->children[]=$AdminContent;
$AdminContent->children[]=$TheList;

?>