<?php
global $RuntimePath;
global $DeployPath;
global $SupportPath;
global $StaticFiles;

require_once $RuntimePath .'/composition/pagebuild_layout.php';

$StaticMarkupPath = $RuntimePath.'/support/templates/static/';
$TemplatePath = $RuntimePath.'/support/templates/';

$ComposerData = Composition::$Active->Context['data'];
$LayoutStructure = json_decode($ComposerData['layout_structure'],true);
$UserID = Composition::$Active->Context['authenticated_account'] ? Composition::$Active->Context['authenticated_account']->data['id'] : null;

$rowCount=0;
$colCount=0;
if(is_array($LayoutStructure))
foreach($LayoutStructure as $layoutrow){
    //if(empty($layoutrow['children'][0]['children'])) continue;
    $row = $layoutrow['self'];
    if(!isset($row['type'])) $row['col-schema']='row';
    if(!isset($row['col-schema'])) $row['col-schema']='12';
    if(!isset($row['options'])) $row['options']='[]';

    $LayoutCanvas->children[]=
    $FrontEndRow = new renderable([ 'tag'=>'li',
                                    'classes'=>['col-sm-12','layoutRow', 'panel panel-default'],
                                    'attributes'=>[
                                        'data-colschema' =>$row['col-schema'],
                                        'data-support' => htmlspecialchars( '{ "RowSettings" : '.json_encode($row['options'], true).' }',ENT_QUOTES )
                                    ]
                                 ]);
    $row['col-schema'] = explode(',' , $row['col-schema']); //convert comma-delimited to array
    $colIndex=0;

    foreach($layoutrow['children'] as $col){
        //if(empty($col['children'])) continue;
        if(!isset($col['options'])) $col['options']='null';

        $ColWidth = $row['col-schema'][$colIndex].'';

        $FrontEndRow->children[]=
        $FrontEndCol=new renderable([   'tag'=>'div',
                                        'classes'=>['col-sm-'.$ColWidth,'layoutColumn', 'layoutRowStage'],
                                        'attributes'=>[
                                            'data-colindex' =>$colIndex,
                                            'data-options' =>$col['options']
                                        ]
                                   ]);
        foreach($col['children'] as $ComponentList){

            $ComponentName = $ComponentList['self']['type'];

            $cinstance = $ComponentList['self']['instance'];
            $cid = isset($ComponentList['self'])? (isset($ComponentList['self']['self']) ? $ComponentList['self']['self'] : null) : null;

            switch($ComponentName){
              case 'LeadGeneration':
              case 'SearchBar': $ComponentList['self']['self'] = $cid = 1; break;
              case 'ListingMLS':$ComponentList['self']['self'] = $cid = !empty($ComponentList['self']['self']) ? $ComponentList['self']['self'].'' : '1'; break;
              default: break;
            }

            //var_dump($cid);
            $Interface = generic_get_plus_interface(NULL,NULL,$cid,$ComponentName);
            $FrontEndCol->children[]=$Interface;
        }
        $colIndex++;
        $colCount++;

        $FrontEndCol->lastFilter = '
        <div class="col-xs-12 col-sm-12 col-md-12 col-lg-12">
            <a href="#" class="addComponentBtn btn layoutBtn" data-toggle="modal" data-target="#ComponentLibrary" > <i class="fa fa-plus"></i> </a>
        </div>';
    }
    $FrontEndRow->lastFilter = '
    <div class="layoutRowController controls selectable">
            <span class="moveRow"><i class="fa fa-arrows"></i></span>
            <span class="editRow" data-toggle="modal" data-target="#rowSettingsModal" ><i class="fa fa-pencil"></i></span>
            <span class="deleteRow"><i class="fa fa-trash"></i></span>
    </div>';
    $rowCount++;
}

if( !function_exists('getPlural') ){

function getPlural($noun)
{
    switch(substr($noun,-2))
    {
        case 'ty':                      return substr($noun,0,-2).'ties'; break;
        case 'us':                      return substr($noun,0,-2).'i'; break;
        case (substr($noun,-1)=='s'):   return $noun.'es'; break;
        default:                        return $noun .'s'; break;
    }
    return $noun.'s';
}
}


// NOTICE: function generic_get_plus_interface is from /service/Registrar.php ~!!
// Will be using the Component interface in the future
// Duplication is temporary to avoid loading the entire Registrar script

function generic_get_plus_interface($arguments, $support, $cid, $ComponentName='Component')
{
    global $SupportPath;
    $Component = new $ComponentName();
    $datasources = !empty($Component->sources) ? $Component->sources : [ strtolower(getPlural($ComponentName))];

    $options['template']=$SupportPath .'/templates/'.($ComponentName == 'Banner' ? 'BannerPageBuilder' : $ComponentName ).'.xml';
    $PrimaryID = $i=0;
	if($ComponentName=='LeadGeneration')	$cid=76;
    if(!isset($Component->sources)){$options[$ComponentName][strtolower( getPlural($ComponentName))] = [ 'method'=>'WHERE `id` = '.$cid, 'condition' => ' LIMIT 0, 1' ];}
    else foreach($Component->sources as $src)
    {
		    $pk = $src::$profile['Accessor']['Primary'] ?: 'id';
        if(is_array($cid) && count($cid) == count($Component->sources)){
            $options[$ComponentName][$src] = [ 'target'=>$src, 'new_query'=>true, 'method'=>'WHERE `'.$pk.'` = '.$cid[$i] .' LIMIT 1'];
            $PrimaryID = $cid[0];
            ++$i;
           }
        elseif(is_array($cid)){ $options[$ComponentName][$src] = [ 'target'=>$src, 'new_query'=>true, 'method'=>'WHERE `'.$pk.'` = '.$cid[0].' LIMIT 1' ]; $PrimaryID = $cid[0];}
        elseif(!empty($cid) || $cid === 0){ $options[$ComponentName][$src] = [ 'target'=>$src, 'new_query'=>true, 'method'=>'WHERE `'.$pk.'` = '.$cid .' LIMIT 1']; $PrimaryID = $cid;}
        else{ $options[$ComponentName][$src] = [ 'target'=>$src, 'new_query'=>true, 'method'=>'WHERE `'.$pk.'` = NULL LIMIT 1']; $PrimaryID = null; }
    }

    $ComponentContainer=new Smart($options);
    $ComponentContainer->classes[]='Interface layoutComponent '.$ComponentName;
    //$ComponentContainer->attributes['onclick']='setActiveComponent(this);';
    $ComponentContainer->attributes['data-component']=$ComponentName;
    $ComponentContainer->attributes['data-self']=is_array($cid) ? '[ '. implode(',',$cid) .' ]': $cid;
    $ComponentContainer->attributes['data-persist']='[&quot;data-persist&quot;,&quot;data-instance&quot;,&quot;id&quot;,&quot;data-layoutnid&quot;]';

    //$ComponentContainer->BindContext();

//    $Context=$ComponentContainer->context[$ComponentName];
//    $Component->createContext($Context['self'], $Context['render'], $Context['data'], $Context['template']);
//    $Component->Load($options[$ComponentName]);

    $options2=[];
    $options2['attributes']=['data-self'=>$PrimaryID,
                            'data-instance'=>'',
                            'data-role'=>'Service',
							'onclick'=>'setActiveComponent(this);',
                            'data-component'=>$ComponentName,
                            'data-context'=>'{&quot;_self_id&quot;:&quot;null&quot;, &quot;_response_target&quot;:&quot;getSelectedStage()&quot;}',
                            'data-persist'=>'[&quot;data-persist&quot;,&quot;data-instance&quot;,&quot;id&quot;]',
                            'data-intent'=>'{&quot;REFRESH&quot;:{&quot;'.$ComponentName.'&quot;:&quot;Edit&quot;}}'];
    $componentController = new renderable($options2);
    $componentController->classes = ['layoutComponentController','controls','selectable', 'instance'];
    $componentController->content = '<span class="moveComponent control"><i class="fa fa-arrows"></i></span>'.
                                    '<span class="editComponent control" data-intent=\'{"REFRESH":{"'.$ComponentName.'":"Edit"}}\' data-context=\'{"_self_id":"'.$PrimaryID.'", "_response_target":"#componentSettingsStage"}\' data-toggle="modal" data-target="#componentSettingsModal"><i class="fa fa-pencil"></i></span>'.
                                    '<span class="deleteComponent control" data-intent=\'{"REMOVE":{"'.$ComponentName.'":"Delete"}}\' data-context=\'{"_self_id":"'.$PrimaryID.'"}\' data-toggle="modal" data-target="#removeComponentModal"><i class="fa fa-trash"></i></span>';
    $ComponentContainer->children[]= $componentController;
    return $ComponentContainer;
};


?>
