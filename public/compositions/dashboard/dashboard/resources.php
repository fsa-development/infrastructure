<?php
require_once __DIR__.'/../../admin_layout.php';

global $RuntimePath;
global $DeployPath;
global $SupportPath;
global $StaticFiles;

$StaticMarkupPath = $RuntimePath.'/support/templates/static/';
$TemplatePath = $RuntimePath.'/support/templates/';

//$items = LoadObjects( 'documents',array( 'method' => ' WHERE parent = '.Composition::$Active->Context['data']['id'] ));
//$UserID = Composition::$Active->Context['authenticated_account']->data['id'];
$isOperator=false;

if($isOperator)
$NewItemBtnWrap->content=<<<STR
<a class="control btn blueBtn"
    data-self=""
    data-context='{"_self_id":"", "_response_target":"#componentSettingsStage"}'
    data-intent='{"REFRESH":{"DocumentList":"Edit"}}'
    data-toggle="modal"
    onclick="addTableRow('#DocumentLibrary');"
    href="#AdminModal"> Open New Document</a>
STR;

else $NewItemBtnWrap->content='';

$options=[];
$options['tag']='tbody';
$options['pageID']='DocumentLibrary';
$options['classes']=['Interface'];
$options['template']=$SupportPath .'/templates/DocumentList.xml';
$options['DocumentList']['documents'] = [
    'select'=>' * ',
    //'condition'=>' ORDER BY `updated` ',
    //'method'=>' WHERE `agent` = '.$_SESSION['user']['id'],
    'new_query'=>true
];
$TheList=new Smart($options);
$options=[];

$AdminContent = new renderable('table');
$AdminContent->classes=['table', 'table-hover', 'table-large'];


$LeftCol = new renderable(['tag'=>'div','classes'=>['col-sm-4','DocumentContainer']]);
$RightCol = new renderable(['tag'=>'div','classes'=>['col-sm-8'],'pageID'=>'DocumentArticles']);
$RightCol->children[] = new renderable(['tag'=>'div','classes'=>['RefreshTarget']]);
$MainAdminTable->children[]=$LeftCol;
	$LeftCol->children[]=$AdminContent;
		$AdminContent->children[]=$TheList;
$MainAdminTable->children[]=$RightCol;

?>
