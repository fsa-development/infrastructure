<?php
if(isset($_REQUEST['interface'])) require_once 'LayoutBuilder.php';
else{

global $RuntimePath;
global $DeployPath;
global $SupportPath;
global $StaticFiles;

require_once $RuntimePath .'/composition/dynamic_layout.php';


if($CurrentURLNode['alias'] == 'blog'){
    $options['method']= 'WHERE `type` = 576 AND `agent` = '.$OwnerID;
    $options['condition']= 'ORDER BY `id`';
    $blog_posts = LoadObjects('text_embeds',$options);
    $Main->children[]=$FrontEndRow =
        new renderable(['tag'=>'li','classes'=>['col-md-12','layoutRow']]);

    foreach($blog_posts as $p){
        $FrontEndRow->content.=PHP_EOL.'<h2><a href="/blog/'.$p->data['title'].'" >'.$p->data['title'].'</a><br /><h2>'.
            substr(strip_tags($p->data['embed']),0,80).'...<br /><br />'.PHP_EOL;
    }
}
if($CurrentURLNode['alias'] == 'listings'){
    $options['method']= 'WHERE `agent` = '.$OwnerID;
    $options['condition']= 'ORDER BY `id`';
    $list_post = LoadObjects('listings',$options);
    $rows=[];
    $cols=[];
    $cRow=0;$cCol=0;$pi=0;
    foreach($list_post as $p){
        $rows[$cRow]['self']['col-schema']='4,4,4';
        $rows[$cRow]['children'][$cCol]['children'][]=[
                'self'=>[ 'type'=>'Listing', 'instance'=>0,'self'=>$p->data['id'] ]
        ];
        $pi++;
        $cCol++;
        if($pi%3==0){$cRow++; $cCol=0;}

    }
    $LayoutStructure = $rows;
}
if(is_array($LayoutStructure) && $CurrentURLNode['alias'] != 'blog')
foreach($LayoutStructure as $layoutrow){
    $row = $layoutrow['self'];
    $row['col-schema'] = explode(',' , $row['col-schema']); //convert comma-delimited to array

    $colIndex=0;

    if(!empty($layoutrow['children'][0]['children'])){
        $Main->children[]=
            $FrontEndRow = new renderable(['tag'=>'li','classes'=>['col-md-12','layoutRow']]);
			$FrontEndRow->attributes['style']='';
			if( !empty( $row['options']['_enable']) )
			if($row['options']['_enable']===1 || $row['options']['_enable']==='1'){
				$FrontEndRow->attributes['style'].= 'background-color: '.$row['options']['_bgcolor'] .'; ';
			}

			if( !empty($row['options']['_large_bg'])){

				$FrontEndRow->children[]=
					$RowBackdrop=new renderable([
						'tag'=>'div',
						'classes'=>['tallFit','wideFit','sheer','rowBackdrop'],
						'attributes'=>['style'=> 'background: url(\'https://static.'.$RemoteBase.'/uploads'.$row['options']['_large_bg'] .'\'); ']
					]);

					if( !empty( $row['options']['_overlaytransparency']) )
					if(($row['options']['_overlaytransparency']+0)< 10 ){
						$alpha = ($row['options']['_overlaytransparency'] * 10);
						$RowBackdrop->attributes['style'].='opacity: 0.'.$alpha.'; -ms-filter: \'progid:DXImageTransform.Microsoft.Alpha(Opacity='.$alpha.')\';'
							.'filter: alpha(opacity='.$alpha.'); -moz-opacity: 0.'.$alpha.'; -khtml-opacity: 0.'.$alpha.';  ';
						//= 'background-color: '.$row['options']['_bgcolor'] .'; ';
					}
			}


	}
    foreach($layoutrow['children'] as $col){
        $ColWidth = $row['col-schema'][$colIndex].'';
        //if(!empty($col['children']))
            $FrontEndRow->children[]=$FrontEndCol=new renderable(['tag'=>'div','classes'=>['col-md-'.$ColWidth,'layoutColumn']]);
        foreach($col['children'] as $ComponentList){
            $ComponentName = $ComponentList['self']['type'];
            $cinstance = $ComponentList['self']['instance'];
            $cid = isset($ComponentList['self'])? (isset($ComponentList['self']['self']) ? $ComponentList['self']['self'] : null) : null;
            if($ComponentName == 'LeadGeneration')
            {
                $ComponentList['self']['self'] = $cid = $OwnerID;
            }

            $options=[];

            global $SupportPath;
            $Component = new $ComponentName();
            $datasources = !empty($Component->sources) ? $Component->sources : [ str_to_lower(getPlural($ComponentName))];

            $options['template']=$SupportPath .'/templates/'.$ComponentName.'.xml';
            $PrimaryID = $i=0;

            $containerTag = isset($Component->ContainerTag) ? $Component->ContainerTag : 'div';

            if(empty($cid) && $cid !== 0 )
            {
                $Component = new $ComponentName();
                //$options['pageID']=$ComponentName.'Editor';
                $options['classes']=[$ComponentName,'Component'];
                $options['template']=$SupportPath .'/templates/'.$ComponentName.'.xml';
                $options['tag']=$containerTag;
                $options['attributes']=[//'data-self'=>'',
                                        'data-instance'=>$cinstance,
                                        'data-role'=>'Service',
                                        'data-self'=>$cid,
                                        'data-component'=>$ComponentName,
                                        'data-persist'=>'[&quot;data-persist&quot;,&quot;id&quot;,&quot;,&quot;data-instance&quot;,&quot;data-layoutnid&quot;]',
                                        //'data-context'=>'{&quot;_self_id&quot;:null, &quot;_response_target&quot;:&quot;getSelectedStage()&quot;}',
                                        //'data-intent'=>'{&quot;REFRESH&quot;:{&quot;'.$ComponentName.'&quot;:&quot;Save&quot;}}',
                                        'onclick'=>'setActiveComponent(this);'
                                        ];
                if(!isset($Component->sources))
                    $options[$ComponentName][strtolower( getPlural($ComponentName) )] = ['condition'=>'LIMIT 0, 1' ];
                else foreach($Component->sources as $src)
                    $options[$ComponentName][$src] = ['condition'=>'LIMIT 0, 1' ];

                $FrontEndCol->children[]= $tmp=new Smart($options);
                foreach($tmp->TemplateBinding[$ComponentName] as $dataset => $row2)  foreach($row2 as $key => $value){
                    $tmp->data[$ComponentName][$dataset][$key]=$value;
                }
            }
            else{
                if(!isset($Component->sources)){$options[$ComponentName][strtolower( getPlural($ComponentName))] = [ 'method'=>'WHERE `id` = '.$cid, 'condition' => ' LIMIT 0, 1' ];}
                else foreach($Component->sources as $src)
                {
					$p_key = $src::$profile['Accessor']['Primary'];
                    if(is_array($cid) && count($cid) == count($Component->sources)){
                        $options[$ComponentName][$src] = [ 'target'=>$src, 'new_query'=>true, 'method'=>'WHERE `'.$p_key.'` = '.$cid[$i] .' LIMIT 1'];
                        $PrimaryID = $cid[0];
                        ++$i;
                       }
                    elseif(is_array($cid)){ $options[$ComponentName][$src] = [ 'target'=>$src, 'new_query'=>true, 'method'=>'WHERE `'.$p_key.'` = '.$cid[0].' LIMIT 1' ]; $PrimaryID = $cid[0];}
                    elseif(!empty($cid) || $cid === 0){ $options[$ComponentName][$src] = [ 'target'=>$src, 'new_query'=>true, 'method'=>'WHERE `'.$p_key.'` = '.$cid .' LIMIT 1']; $PrimaryID = $cid;}
                    else{ $options[$ComponentName][$src] = [ 'target'=>$src, 'new_query'=>true, 'method'=>'WHERE `'.$p_key.'` = NULL LIMIT 1']; $PrimaryID = null; }
                }

                $FrontEndCol->children[]= $tmp = new Smart($options);
                $tump->classes[]='Component';
                $tmp->classes[]=$ComponentName;
                $tmp->attributes['data-instance']=$cinstance;
            }
        }
        $colIndex++;
    }
}

//$ContentArea->content='<div class="col-md-12"><pre>'.var_export(,true).'</pre></div>';

/*
$Main->children[]=new renderable(['tag'=>'li','classes'=>['col-md-4']]);
$Main->children[]=$ContentArea=new renderable(['tag'=>'li','classes'=>['col-md-8']]);
$ContentArea->content='<div class="col-md-12"><pre>'.var_export(,true).'</pre></div>';
*/

}
?>
