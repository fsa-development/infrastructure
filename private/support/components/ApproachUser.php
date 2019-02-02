<?php

class ApproachUser extends Component
{
    static function SetAuthentication($auth_token,$user)
    {
        if(!$user) return false;

        if(!isset( $user->data['join_date'] ))
        {
            $user->save();
            $user = LoadObject('users',['condition'=>'WHERE `id` = '.$user->data['id'], 'new_query'=>true] );
        }
        $user->data['snark'] = substr(base64_encode(sha1( mt_rand(1,50000000).$_SERVER['REMOTE_ADDR'],true)), 0,12);
        $tkn=$auth_token.sprintf(crc32($user->data['email'].$user->data['join_date']),'%x') ;

        $user->data['key_auth'] = password_hash($tkn, PASSWORD_BCRYPT, ['cost' => 11]);
        $user->save($user->data['id']);
    }
    static function Logout()
    {
		global $SessionName;
        if (session_status() === PHP_SESSION_NONE){
            @session_name($SessionName);
            @session_start();
        }
        if(     !isset($_SESSION['user'])
            ||  !isset($_SESSION['user']['id'])
            ||  $_SESSION['user']['id'] + 0 <= 0
            )    exit('{"error":"Not Authenticated"}');
        $_SESSION=[];
            session_unset();
            session_destroy();
            unset($_SESSION);
    }
    static function Authenticate($auth_token, $user)
    {
        $tkn=$auth_token.sprintf(crc32($user->data['email'].$user->data['join_date']),'%x');
		    if( password_verify($tkn, $user->data['key_auth']) ) return true;
        else exit('{"REFRESH":{".passwordAlert":"<div class=\"passwordAlert alert alert-danger\">Password Error!<\/div>"},"success":true,"origin":{"json":"{\"support\":{\"_response_target\":\".passwordAlert\"},\"command\":{\"REFRESH\":{\"Agent\":\"UpdatePassword\"}}}"}}');
    }
}

?>
