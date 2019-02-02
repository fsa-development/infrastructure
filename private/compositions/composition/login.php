<?php

require_once __DIR__.'/../form_layout.php';

global $RuntimePath;
global $DeployPath;
global $SupportPath;
global $StaticFiles;


if($_SERVER['HTTP_HOST'] == $RemoteBase){
		header('HTTP/1.1 301 Moved Permanently');
		header('Location: https://www.'.$RemoteBase.$_SERVER['REQUEST_URI']);
		exit();
}

$StaticMarkupPath = $RuntimePath.'/support/templates/static/';
$TemplatePath = $RuntimePath.'/support/templates/';

$items = LoadObjects( 'compositions',array( 'method' => ' WHERE parent = '.Composition::$Active->Context['data']['id'] ));
//$UserID = !empty(Composition::$Active->Context['authenticated_account']) ? Composition::$Active->Context['authenticated_account']->data['id'] : 0;

$head->content.='
<script>

function ResetPasswordEmail(e){
    var IntentJSON = {
    	"support": {
    		"_response_target": "#NotifyPasswordReset",
    		"dataset://users": {
    			"email":$(\'#ForgotPassModal input[name="p_email"]\').val(),
    		},
    		"page_query": [ "https: //www.'.$RemoteBase.'/login" ]
    	},
    	"command": {
    		"REFRESH": { "Agent": "Password.Reset"	}
    	}
    };

    CallIntent( IntentJSON );
}

function OnReady(){
    $("#ForgotPassSubmit").click(ResetPasswordEmail);
}

$(document).ready(OnReady);
</script>

';


$AdminMain->children[]=new renderable(['tag'=>'li','classes'=>['col-sm-1']]);
$AdminMain->children[]=$ContentArea=new renderable(['tag'=>'li','classes'=>['col-sm-10','verticalMarg']]);
$AdminMain->children[]=new renderable(['tag'=>'li','classes'=>['col-sm-1']]);

$ContentArea->children[]=
$AdminContent = new renderable('ul');
$AdminContent->classes=['col-sm-12', 'row'];
//$AdminContent->attributes['style']='height: 90%; position: relative; top: 0px; left: 0px;';
$AdminContent->content='
<form method="post">
<div id="FModal" class="modal-body col-sm-12">
    <div class="form-group">
      <label class="sr-only" for="exampleInputEmail2">Email address</label>
      <input type="email" name="email" class="form-control inputArea" placeholder="Enter email"><br><br>
    </div>
    <div class="form-group">
      <label class="sr-only" for="exampleInputPassword2">Password</label>
      <input type="password" name="password" class="form-control inputArea" placeholder="Password">
    </div>
    <!-- div id="RememberMe" class="checkbox"><label>
        <input type="checkbox"> Remember me
    </label></div -->
    <span class="pull-right"><a href="#ForgotPassModal" data-toggle="modal" data-target="#ForgotPassModal">Forgot your password?</a></span>
</div>
<div class="modal-footer col-sm-12" style="background: none;">
    <a href="signup"><button type="button" id="ModalRegisterButton" class="btn pull-left btn-default btn-lg backBtn" style="color: #373a3c;" >Signup</button></a>
    <input id="ModalLoginButton" style="margin-right: 1em; display: inline-block; " class="btn btn-info btn-primary btn-lg nextBtn" data-loading-text="Loading..." type="submit" value="Login">
</div>
</form>
';



//$ContainerContent->children[] = (new BootstrapPanel('hello','mr','tseng'))->panel;

?>
