<?php
require_once __DIR__.'/../../admin_layout.php';

global $RuntimePath;
global $DeployPath;
global $SupportPath;
global $StaticFiles;

$StaticMarkupPath = $RuntimePath.'/support/templates/static/';
$TemplatePath = $RuntimePath.'/support/templates/';

$items = LoadObjects( 'compositions',array( 'method' => ' WHERE parent = '.Composition::$Active->Context['data']['id'] ));
$UserID = Composition::$Active->Context['authenticated_account']->data['id'];

$NewItemBtnWrap->content=<<<STR
<a class="control btn blueBtn"
    data-self=""
    data-context='{"_self_id":"", "_response_target":"#componentSettingsStage"}'
    data-intent='{"REFRESH":{"PostsList":"Edit"}}'
    data-toggle="modal"
    onclick="addTableRow('#PostsLibrary');"
    href="#AdminModal"> New Post </a>
STR;

$options=[];
$options['tag']='tbody';
$options['pageID']='PostsLibrary';
$options['classes']=['Interface'];
$options['template']=$SupportPath .'/templates/PostsList.xml';
$options['PostsList']['text_embeds'] = [
    'select'=>' * ',
    'condition'=>'ORDER BY `id`,`title` ',
    'method'=>' WHERE `agent` = '.$_SESSION['user']['id'] . ' AND `type` = 576',
    'new_query'=>true
];

$TheList=new Smart($options);
$options=[];

$AdminContent = new renderable('table');
$AdminContent->classes=['table', 'table-hover', 'table-large'];
$AdminContent->children[]=$TheList;

$MainAdminTable->children[]=$AdminContent;

?>