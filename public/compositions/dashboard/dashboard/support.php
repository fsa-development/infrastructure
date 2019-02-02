<?php
require_once __DIR__.'/../../admin_layout.php';

global $RuntimePath;
global $DeployPath;
global $SupportPath;
global $StaticFiles;

$StaticMarkupPath = $RuntimePath.'/support/templates/static/';
$TemplatePath = $RuntimePath.'/support/templates/';




//$items = LoadObjects( 'tickets',array( 'method' => ' WHERE parent = '.Composition::$Active->Context['data']['id'] ));
//$UserID = Composition::$Active->Context['authenticated_account']->data['id'];

$NewItemBtnWrap->content=<<<STR
<a class="control btn blueBtn"
    data-self=""
    data-context='{"_self_id":"", "_response_target":"#componentSettingsStage"}'
    data-intent='{"REFRESH":{"TicketsList":"Edit"}}'
    data-toggle="modal"
    onclick="addTableRow('#TicketsLibrary');"
    href="#AdminModal"> <span class="MobileHide">Open New</span> Ticket</a>
STR;


$options=[];
$options['tag']='tbody';
$options['pageID']='TicketsLibrary';
$options['classes']=['Interface InterfaceContent'];
$options['template']=$SupportPath .'/templates/TicketsList.xml';
$options['TicketsList']['tickets'] = [
    'select'=>' * ',
    'condition'=>' ORDER BY `updated` ',
    'method'=>' WHERE `agent` = '.$_SESSION['user']['id'],
    'new_query'=>true
];
$TheList=new Smart($options);
$options=[];

$AdminContent = new renderable('table');
$AdminContent->classes=['table', 'table-hover', 'table-large'];


$LeftCol = new renderable(['tag'=>'div','classes'=>['col-sm-4','TicketsContainer']]);
$RightCol = new renderable(['tag'=>'div','classes'=>['col-sm-8','Interface','InterfaceContent'],'pageID'=>'TicketMessages']);
$RightCol->children[] = new renderable(['tag'=>'div','classes'=>['RefreshTarget']]);
$MainAdminTable->children[]=$LeftCol;
	$LeftCol->children[]=$AdminContent;
		$AdminContent->children[]=$TheList;
$MainAdminTable->children[]=$RightCol;

?>
