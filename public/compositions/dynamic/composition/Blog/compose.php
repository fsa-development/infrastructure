<?php

global $RuntimePath;
global $DeployPath;
global $SupportPath;
global $StaticFiles;

require_once $RuntimePath .'/composition/dynamic_layout.php';

    $Main->children[]=$FrontEndRow =
        new renderable(['tag'=>'li','classes'=>['col-md-12','layoutRow']]);
    
    	$o['PostDetail']['text_embeds']=  [
            'method' => 'WHERE title LIKE "' . $CurrentURLNode['alias'] .'" AND `agent` = '.$OwnerID,
            'condition'=>'LIMIT 0, 1'
        ];
    	$o['template']=$SupportPath .'/templates/'.'PostDetail'.'.xml';
        $FrontEndRow->children[] = new Smart($o);

?>