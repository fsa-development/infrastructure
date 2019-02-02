<?php
require_once __DIR__.'/../../admin_layout.php';

global $RuntimePath;
global $DeployPath;
global $SupportPath;
global $StaticFiles;

$StaticMarkupPath = $RuntimePath.'/support/templates/static/';
$TemplatePath = $RuntimePath.'/support/templates/';

//$items = LoadObjects( 'leads',array( 'method' => ' WHERE parent = '.Composition::$Active->Context['data']['id'] ));
//$UserID = Composition::$Active->Context['authenticated_account']->data['id'];
array_pop($contentHeading->children);

$options=[];
$options['tag']='tbody';
$options['pageID']='LeadsLibrary';
$options['classes']=['Interface'];
$options['template']=$SupportPath .'/templates/LeadsList.xml';
$options['LeadsList']['leads'] = [
    'select'=>' * ',
    'condition'=>' ORDER BY `id` DESC LIMIT 20 ',
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
