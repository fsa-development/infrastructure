<?php

global $RuntimePath;
global $DeployPath;
global $SupportPath;
global $StaticFiles;

require_once $RuntimePath .'/composition/dynamic_layout.php';

$mls_mode = $suite_mode = false;
$LID = '';
$LastPath  = '';
foreach( Composition::$Active->Context['entry'] as $k => $v)
    $LastPath  = $k;

if($LastPath[0] == 'm') $mls_mode = true;
else if($LastPath[0] == 's') $suite_mode = true;
else if($LastPath[0] == 'M') $mls_mode = true;
else if($LastPath[0] == 'S') $suite_mode = true;

$LID = substr($LastPath,1);
//  LID now has the numeric ID for either the mls_listings or the listings table

$Template = $mls_mode ? 'ListingDetailMLS' : 'ListingDetail';
$Table = $mls_mode ? 'mls_listings' : 'listings';
$PrimaryKey = $mls_mode ? 'Matrix_Unique_ID' : 'id';
$PrimaryValue = end( Composition::$Active->Context['traversed'] )[$PrimaryKey];
$img_base =	$mls_mode ?
			  'https://storage.bhs3.cloud.ovh.net/v1/AUTH_89e628be5d744bb2acc8f0613a73f75d/ImageStore/ImageStore' :
			  'https://static.myrealestatesite.co/uploads';

// Template, Table and PrimaryKey match form for either SuiteSpace Listings or MLS Listings

$Main->children[] = $BannerRow =
    new renderable(['tag'=>'li','classes'=>['wideFit','layoutRow']]);
$Main->children[] = $FrontEndRow =
    new renderable(['tag'=>'li','classes'=>['wideFit','layoutRow']]);

$BannerRow->content .='
<div data-instance="0"  class="Banner ComponentLayout Gallery Banner1">
<ul  class="tallFit sheer" >
<li  class="BackBtn" ><i class="fa fa-chevron-left " ></i></li>';

$runOnce = $runOnce2 = false;
$thumnail[0] ='<div class="sheer evens" >';
$thumnail[1] ='<div class="sheer odds" >';
$i=0;

$images = json_decode( end( Composition::$Active->Context['traversed'] )['jsondata'], true );
if(empty($images)) $images=[];

if($mls_mode && empty($images)){
  $images['img']                = [];
  $images['img']['Photo']       =
  $images['img']['LargePhoto']  =
  $images['img']['HighRes']     = [];
  for($n=0,$L=end( Composition::$Active->Context['traversed'] )['PhotoCount'];
      $n<$L;
      $n++){
        $images['Photo']        []= '/'.$PrimaryValue.'/Photo/'.$n.'.jpg';
        $images['LargePhoto']   []= '/'.$PrimaryValue.'/LargePhoto/'.$n.'.jpg';
        $images['HighRes']      []= '/'.$PrimaryValue.'/HighRes/'.$n.'.jpg';
      }
}


foreach($images as $all => $dir ){
    foreach($dir as $quality => $list){

        $ii=0;
          $L = empty( end( Composition::$Active->Context['traversed'] )['PhotoCount'] ) ?
                count($list) :
                end( Composition::$Active->Context['traversed'] )['PhotoCount'] ;
          for(  $n=0,$L; $n < $L; $n++  ){
            $img = isset($list[$n]) ? $list[$n] : '/'.$PrimaryValue.'/'.$quality.'/'.$n.'.jpg';
            if($quality =='HighRes'){
                $BannerRow->content.='
                <li class="col-sm-12 tab-pane tallFit ImageRow wideFit sheer '.(!$runOnce? 'active':'').'">
                    <div data-self="11" class="ImageRow">
                        <div class="layoutImg">';
                if( $mls_mode )
                            $BannerRow->content.='<a target="1" style="display: inline-block; background: url(\''.$img_base.'/'.$PrimaryValue.'/'.$quality.'/'.$ii.'.jpg\') no-repeat 50% 50%;   -webkit-background-size: auto 100%;   -moz-background-size: auto 100%;   -o-background-size: auto 100%;   background-size: auto 100%;"></a></div></div></li>';
                else        $BannerRow->content.='<a target="1" style="display: inline-block; background: url(\''.$img_base.$img.'\') no-repeat 50% 50%;   -webkit-background-size: auto 100%;   -moz-background-size: auto 100%;   -o-background-size: auto 100%;   background-size: auto 100%;"></a></div></div></li>';
                $runOnce=true;
            }
            else if($quality =='Photo'){
                $thumnail[$i%2] .= '<a class="'.( !$runOnce2 ? 'active' : '' ).'" style="border: 1px solid #fff; display: inline-block; float:left; background: url(\''.$img_base.$img.'\') no-repeat 50% 50%;   -webkit-background-size: cover;   -moz-background-size: cover;   -o-background-size: cover;   background-size: cover;"></a>';
				        $runOnce2=true;
            }
            ++$i;
            ++$ii;
        }
    }
}

$thumnail[0].='</div>';
$thumnail[1].='</div>';
$BannerRow->content.='<li  class="NextBtn" ><i class="fa fa-chevron-right"></i></li></ul>'.
	'<div class="slider_nav" id="slider_nav">'.$thumnail[0].$thumnail[1].'</div>
</div>';



//$OwnerID = Composition::$Active->Context['traversed'][0]['owner'];
$CurrentURLNode = end(Composition::$Active->Context['traversed']);
if(count(Composition::$Active->Context['traversed']) > 2)
	$ParentURLNode = Composition::$Active->Context['traversed'][ count(Composition::$Active->Context['traversed']) -2 ];
else
	$ParentURLNode = $CurrentURLNode;
$OwnerID = $ParentURLNode['owner'];


$o[$Template]['users'] = ['method' => 'WHERE `id` =' . $OwnerID  ];
$o[$Template][$Table]['condition']='LIMIT 0, 1';
$o[$Template][$Table]['method']= $mls_mode ?
    'WHERE `'.$PrimaryKey.'` = ' . $LID  :
    'WHERE `'.$PrimaryKey.'` = ' . $LID . ' AND `agent` = '.$OwnerID.' ';

$o['tag']= 'div';
$o['template']=$SupportPath .'/templates/'.$Template.'.xml';
$FrontEndRow->children[] = new Smart($o);


?>
