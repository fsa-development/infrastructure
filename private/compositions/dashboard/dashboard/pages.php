<?php
require_once __DIR__.'/../../admin_layout.php';

global $RuntimePath;
global $DeployPath;
global $SupportPath;
global $StaticFiles;

$StaticMarkupPath = $RuntimePath.'/support/templates/static/';
$TemplatePath = $RuntimePath.'/support/templates/';

//$items = LoadObjects( 'compositions',array( 'method' => ' WHERE parent = '.Composition::$Active->Context['data']['id'] ));
$UserID = Composition::$Active->Context['authenticated_account']->data['id'];

$NewItemBtnWrap->content=<<<STR
<a class="control btn blueBtn MobileHide"
    data-self=""
    data-context='{"_self_id":"", "_response_target":"#componentSettingsStage"}'
    data-intent='{"REFRESH":{"PagesList":"Edit"}}'
    data-toggle="modal"
    onclick="addTableRow('#PagesLibrary');"
    href="#AdminModal"> New Page </a>
STR;

$AdminMain->children[] = $ListStage = new renderable(['tag'=>'li', 'classes'=>['col-sm-10']]);
//$AdminMain->children[]=

//$opt['tag']='table';
//$opt['Component']['DashTable']['condition'] = 'WHERE `agent` = '.$UserID;

$options=[];
$options['tag']='tbody';
$options['pageID']='PagesLibrary';
$options['classes']=['Interface'];
$options['template']=$SupportPath .'/templates/PagesList.xml';
$options['PagesList']['compositions'] = [
    'select'=>' * ',
    'method'=>'WHERE `owner` = '.$UserID,//.' AND `alias` != "listings" AND `alias` != "blog" ',
    //'target'=>'pages',
    'condition'=>' ORDER BY `self`,`parent`, `id` ASC LIMIT 20 ',
    'new_query'=>true,    //re-using previous product object from save step by re-initializing the query. else LoadObjects is confused
    //'debug'=>true
];
$TheList=new Smart('tbody','PagesList',$options);
$options=[];
$AdminContent = new renderable('table');
$AdminContent->classes=['table', 'table-hover', 'table-large'];

$MainAdminTable->children[]=$AdminContent;
$AdminContent->children[]=$TheList;
//$ListStage->children[]=$AdminContent;
//$items = LoadObjects( 'pages',array( 'method' => ' WHERE `agent` = '.$UserID.' ') );
//var_dump($items);
//$AdminMain->children[]= $TheList; //new renderable(['classes'=>['col-sm-2']]);

?>
