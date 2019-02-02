<?php

require_once __DIR__.'/../form_layout.php';

global $RuntimePath;
global $DeployPath;
global $SupportPath;
global $StaticFiles;
global $ServicePath;

$StaticMarkupPath = $RuntimePath.'/support/templates/static/';
$TemplatePath = $RuntimePath.'/support/templates/';

$items = LoadObjects( 'compositions',array( 'method' => ' WHERE parent = '.Composition::$Active->Context['data']['id'] ));
//$UserID = !empty(Composition::$Active->Context['authenticated_account']) ? Composition::$Active->Context['authenticated_account']->data['id'] : 0;

$head->content.='
<script>
var Approach={};
Approach.Reflection = "'.$ServicePath.'/console.php";
Approach.Utility = "'.$ServicePath.'/Utility.php";

function ResetPassword(t){
	var c = $(t).parent();
	var map = {};
	$("#FModal input").each(function() {
		map[$(this).attr("name")] = $(this).val();
	});

	console.log(map);

	var IntentJSON = {
		"support": {
			"_response_target": ".passwordAlert",
			"dataset://users": {
				"confirm_new_password":map["confirm_new_password"],
				"new_password":map["new_password"]
			},
			"page_query": [ window.location ]
		},
		"command": {
			"REFRESH": { "Agent": "UpdateForgottenPassword"	}
		}
	};

	console.log(IntentJSON);
	var ReqData = { json: JSON.stringify(IntentJSON) }; //Switch to JSON3 ?


	$.ajax({
		url: Approach.Utility,   //url of Utility.php
		type: "post",
		data: ReqData,  //the json data
		dataType: "json",
		xhrFields: {                    withCredentials: true                },
		crossDomain: true,
		success: NotifyPassword
	});
}

function NotifyPassword(json, status, xhr){
	$(".passwordAlert").html( json.REFRESH[".passwordAlert"] );
}

</script>
';

$AdminMain->children[]=new renderable(['tag'=>'li','classes'=>['col-sm-1']]);
$AdminMain->children[]=$ContentArea=new renderable(['tag'=>'li','classes'=>['col-sm-10','verticalMarg']]);
$AdminMain->children[]=new renderable(['tag'=>'li','classes'=>['col-sm-1']]);

$ContentArea->children[]=
$AdminContent = new renderable('ul');
$AdminContent->classes=['col-sm-12', 'row', 'Interface','InterfaceContent'];
//$AdminContent->attributes['style']='height: 90%; position: relative; top: 0px; left: 0px;';
$AdminContent->content='
<form data-action="dataset://users">
<div id="FModal" class="modal-body col-sm-12">
    <div>
        <fieldset class="form-group">
        New Password
        <input type="password" class="form-control inputArea" name="new_password" value="" /><br />
        Confirm New Password
        <input type="password" class="form-control inputArea" name="confirm_new_password" value="" /><br />
        </fieldset>
        <div class="passwordAlert">&#160;</div>
    </div>
</div>
<div class="modal-footer col-sm-12" style="background: none;">
    <button type="button" value="Reset Password" class="nextBtn submitBtn btn btn-info btn-lg control" onclick="ResetPassword(this);">
    Reset Password
    </button>
</div>
</form>
';



//$ContainerContent->children[] = (new BootstrapPanel('hello','mr','tseng'))->panel;

?>
