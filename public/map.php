<?php

require_once( __DIR__.'/core.php');

if( !isset($_SERVER['HTTPS']) ){
	header("HTTP/1.1 301 Moved Permanently");
	header('Location: https://'.$_SERVER['HTTP_HOST'].$_SERVER['REQUEST_URI']);
	exit();
}

function Authorized()
{
    global $db;
    global $RemoteBase;
    $CompToAuth = end( Composition::$Active->Context['traversed'] );
    if(! isset($CompToAuth['auth_needed'])) $CompToAuth['auth_needed']=0;
    //if(! isset($CompToAuth['alias'])) $CompToAuth['alias'] =$CompToAuth['title'];

    //if(isset($_COOKIE['PHPSESSID'])) session_id($_COOKIE['PHPSESSID']);
    //else session_id();
    $didAuthenticate = false;
    if(isset($_POST['password']) && isset($_POST['email']) )
    {
        $user=LoadObject('users',[
            'target'=>'users',
            'method'=> 'WHERE `email` = \''.mysqli_real_escape_string($db, $_POST['email']) .'\''] );
        if(empty($user)) $user = new users('users');

        $didAuthenticate = ApproachUser::Authenticate($_POST['password'],$user);
        if( $didAuthenticate )
        {
            CheckSession();
            Composition::$Active->Context['authenticated_account'] = $user;
            $_SESSION['user']['id']=$user->data['id'];

            foreach($user->data as $key => $value)
            {
                if( !in_array($key, ['key_auth','snark','logpath','authority','case','profile_type','type','admin','join_date','datapath']))
                    $_SESSION['user'][$key] = $value;
            }
      			$_SESSION['user']['root'] = LoadObject('compositions',[
      				'range' => ' id, self, parent ',
      				'method' => 'WHERE `alias` = \''.$_SESSION['user']['username'].'\' AND `parent` = 1 '
      			])->data;
            header('Location: https://www.'.$RemoteBase.'/dashboard/listings');
            exit();
        }
    }
    if($CompToAuth['auth_needed'] == 0)
    {
        CheckSession();

        if( isset($_SESSION['user']) && isset($_SESSION['user']['id']) )
        {
            Composition::$Active->Context['authenticated_account']= LoadObject('users',['method'=>'WHERE `id` = '.$_SESSION['user']['id']] );
			if( empty($_SESSION['user']['root'])  ){
				try{
					$_SESSION['user']['root'] = LoadObject('compositions',[
						'range' => ' id, self, parent ',
						'method' => 'WHERE `alias` = \''.$_SESSION['user']['username'].'\' AND `parent` = 1 '
					])->data;
				}
				catch(Exception $e){
					session_destroy();
					$_SESSION = array();
				}
			}
            return true;
        }
        Composition::$Active->Context['authenticated_account'] = NULL;
        return true;
    }
    else if($CompToAuth['auth_needed'] == 1)
    {
        CheckSession();

        if($didAuthenticate) return true;
        else if( isset($_SESSION['user']) && isset($_SESSION['user']['id']) )
        {
            Composition::$Active->Context['authenticated_account']=
                LoadObject('users',['method'=>'WHERE `id` = '.$_SESSION['user']['id']] );

			if(empty($_SESSION['user']['root']))
				$_SESSION['user']['root'] = LoadObject('compositions',[
					'range' => ' id, self, parent ',
					'method' => 'WHERE `alias` = \''.$_SESSION['user']['username'].'\' AND `parent` = 1 '
				])->data;

            return true;
        }
    }

    return false;
}

function RouteFromURL($url, $silent=false, $RootComposition=1, $DoPublish=true, $root_host=NULL)
{
    global $RuntimePath;
	global $RemoteBase;
    global $ApproachConfig;
	global $db;

	$platform_roots = [$RemoteBase];
	$url= empty($url) ? $_SERVER['REQUEST_URI'] : $url;
    $url= strtok($url,'?&');
    $qs = strtok('?&');
    $uriCursor = ArrayFromURI($url);
	$RootOwner = $root_composition = NULL;
	$uriStart = 1;
	$RootSearch=new compositions('compositions');

	// Check for NGINX $_SERVER param
	if(!empty($_SERVER['ORIGIN_DOMAIN']) && $root_host===NULL)
		$root_host = $_SERVER['ORIGIN_DOMAIN'];

	// NGINX Supplied Domain Root
	if($root_host !== NULL){
		$r=LoadObject('compositions',[
			'range' => 'id, parent, type, alias, owner',
			'method' =>	'where `alias` = "'.$root_host.'" AND parent = id',
			'new_query' => true,
//			'debug' => true,
		]);
		$RootComposition = $r->data['id'];
		$RootOwner = $r->data['owner'];
		$RootSearch = $r;
	}
	// User Supplied Domain Root
	else{
		$h = mysqli_real_escape_string($db, $_SERVER['HTTP_HOST']);
		$r=LoadObject('compositions',[
			'range' => 'id, alias, owner',
			'method' =>	'where "'.$h.'" LIKE CONCAT(\'%\', alias) AND parent = id',
			'new_query' => true
		]);
		$root_host = $r->data['alias'];
		$RootComposition = $r->data['id'];
		$RootOwner = $r->data['owner'];
		$RootSearch = $r;
	}

	$subscope = str_replace($root_host, '', $_SERVER['HTTP_HOST']);
	if(empty($subscope) || $subscope == 'www.')
		$tsubs = [];
	else
		$tsubs = array_reverse( explode('.',$subscope) );

	$subs=[];
	foreach($tsubs as $s)
		if($s != 'www' && $s != '')
			$subs[$s] = [];

	$RootSearch->data['id']=$RootComposition;

	$AppPath=array_merge( [ $root_host => [] ], $subs, $uriCursor ) ;
	unset($tsubs);
	unset($subs);


	/*  Root Level & Type Detection */


	Composition::$Active = new Composition();
	$keys = array_keys($uriCursor);
	$LastKey = end($keys);

	$RemoteBase =
	$ApproachConfig['Core']['DynamicSiteBaseURL'] =
	$ApproachConfig['Core']['SafeDynamicSiteBaseURL'] = $root_host;

    if(is_int($LastKey))    Composition::$Active->Context['path']=ResolveCompositionByID($LastKey);
    else    Composition::$Active->Context['path']=ResolveComposition($RootSearch,array_keys($AppPath));

    Composition::$Active->Context['data']=end(Composition::$Active->Context['traversed']);
    //array_shift(Composition::$Active->Context['traversed']);
    Composition::$Active->Context['entry'] = $AppPath;
    Composition::$Active->Context['path'] .= '/compose.php';
    Composition::$Active->Context['self'] = $RootSearch;  //Database Values for this node

    if(!Authorized($RootSearch->data)) exit('L'.__LINE__.' = Authentication failed, halting execution.');


	$ApproachConfig['remote']['base']=$subscope.$root_host;
	$ApproachConfig['remote']['static']='static.'.$root_host;
	$ApproachConfig['remote']['path'] =$url;

	if( !empty(Composition::$Active->Context['data']['owner']) )
		$owner = LoadObject('users',['method'=>'WHERE `id` = '.Composition::$Active->Context['data']['owner'] ]);
	else
		$owner = LoadObject('users',['method'=>'WHERE `id` = '.$RootOwner ]);

	if(!empty($owner))
		$ApproachConfig['path.owner'] = $owner->data;

	//echo $RuntimePath . '/'.Composition::$Active->Context['path'];
	$compbase = in_array($root_host,$platform_roots) ? '/' : '/composition/';
    require_once($RuntimePath . $compbase.Composition::$Active->Context['path']);

	if($DoPublish)	Composition::$Active->publish($silent);
	else 	Composition::$Active->prepublish($silent);
    Composition::$Active->Context['self'] = &Composition::$Active;  //Application instantiated by running node values throw chained scopes

    return Composition::$Active;
}

function ResolveComposition($RootSearch,$PathList)
{
    $options['method']= 'WHERE `alias` LIKE \'' . $PathList[0] . '\' AND `parent` = '.$RootSearch->data['id'].' ';
    $options['condition']= 'ORDER BY self LIMIT 1';
	//$options['debug']=true;

    $parent=$RootSearch->data['id'];
    $parent_data=$RootSearch->data;
		$RootSearch = LoadObject('compositions', $options);

    if(!isset($RootSearch->data['type']) )
    {
        //var_dump($RootSearch);
        $options['method']= 'WHERE `title` LIKE \'' . $PathList[0].'\' AND `parent` = '.$parent.' ';
        $options['condition']= 'LIMIT 1';

        $RootSearch = LoadObject('compositions', $options);

        if(!isset($RootSearch->data['type']) )
				exit(	'No matching object in database! <br/>'.PHP_EOL.
					'Failed To Route Composition: TYPECAST FAILURE. '.PHP_EOL.'<br />'.PHP_EOL.
					var_export($PathList,true)
			);
    }

    $options['method']= 'WHERE id='.$RootSearch->data['type'];
    $options['condition']= 'ORDER BY id LIMIT 1';
    $Type = LoadObject('types', $options);

    Composition::$Active->Context['id'][]=$RootSearch->data['id'];
    Composition::$Active->Context['type'][]=$Type->data['name'];
    Composition::$Active->Context['typeid'][]= $Type->data['id'];
    Composition::$Active->Context['traversed'][]=$RootSearch->data;

    if(count($PathList)<=1) return $Type->data['name'];
    else return $Type->data['name'] .'/'.ResolveComposition($RootSearch,array_slice($PathList, 1));

    /*
    if($RootSearch->data['restricted']==true)
    {
        $options = array();
        $options['method']= 'WHERE [owner_id]='.Composition::$Active->Context['authenticated_account'];
        $options['condition']= 'ORDER BY id';
        $permissions = LoadObjects('permissions',$options);
        $active_perms = GetUserPermissions( Composition::$Active->Context['authenticated_account'] );
        if ( !isPermitted( $active_perms, $permissions, $traversed ) ) exit(0);
    }
    */
}

function ResolveCompositionByID($cID){
    $toCompose = LoadObject('compositions',['target'=>'compositions','condition'=>'`id` = '.$cID,'new_query'=>true]);
    $toComposeType = LoadObject('types',['target'=>'types','condition'=>'`id` = '.$toCompose->data['type'],'new_query'=>true]);
    if(!isset(Composition::$Active->Context)) Composition::$Active->Context = array();
    $PathList = $toComposeType->data['name'];

    //build parent nodes first
    if($toCompose->data['parent'] !== null && $toCompose->data['parent'] !== $toCompose->data['id'] && $toCompose->data['parent'] != 0){
        $PathList = ResolveCompositionByID($toCompose->data['parent']) .'/'.$PathList = $toComposeType->data['name'];
    }
	if($toCompose->data['parent'] == $toCompose->data['id'] && $toCompose->data['id'] != 1)
		$PathList = 'composition/'.$PathList = $toComposeType->data['name'];

    Composition::$Active->Context['id'][]=$toCompose->data['id'];
    Composition::$Active->Context['type'][]=$toComposeType->data['name'];
    Composition::$Active->Context['typeid'][]= $toComposeType->data['id'];
    Composition::$Active->Context['traversed'][]=$toCompose->data;

    return $PathList;
}


global $APROACH_SERVICE_CALL;
if(!$APROACH_SERVICE_CALL)       RouteFromURL($_SERVER['REQUEST_URI']);

?>
