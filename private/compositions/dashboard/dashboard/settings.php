<?php

require_once __DIR__.'/../../admin_layout.php';

global $RuntimePath;
global $DeployPath;
global $SupportPath;
global $StaticFiles;
global $Lockdown;

$StaticMarkupPath = $RuntimePath.'/support/templates/static/';
$TemplatePath = $RuntimePath.'/support/templates/';

$items = LoadObjects( 'compositions',array( 'method' => ' WHERE parent = '.Composition::$Active->Context['data']['id'] ));
$UserID = Composition::$Active->Context['authenticated_account']->data['id'];
$UserData = Composition::$Active->Context['authenticated_account']->data;

foreach($NewItemBtnWrap->classes as &$C) if($C=='controls') $C='';

if(!$Lockdown)
$NewItemBtnWrap->content='<button type="button" class="btn blueBtn" id="HeadingSaveBtn" onclick="$(\'#SaveBtnHelper\').click()"> Save </button>';
else $NewItemBtnWrap->content ='';
//style="margin-top: -4em; margin-left: -5em; position: fixed; z-index: 9999; "
$SaveBtn='<button class="control btn blueBtn" id="SaveBtnHelper"
    type="button"
    data-self="'.$UserID.'"
    data-role="autoform"
    data-context=\'{"_self_id":"'.$UserID.'", "_response_target":"notifier_0"}\'
    data-intent=\'{"REFRESH":{"Agent":"Save"}}\'
> Save </button>
';


$o=[];

//$AdminMain->children[]=$navWrap = new renderable(['tag'=>'li','classes'=>['col-sm-12']]);

$MainAdminTable->children[]=$tabWrap = new renderable(['tag'=>'li','classes'=>['col-sm-12']]);
$tabWrap->children[]= $Tabs = new renderable(['tag'=>'ul','classes'=>['nav','nav-tabs'],'pageID'=>'settingsTabs','attributes'=>['role'=>'tablist']]);
$tabWrap->children[]= $Panes = new renderable(['tag'=>'div','classes'=>['Interface','InterfaceContent','tab-content'],'pageID'=>'settingsTabPanes']);
if(!$Lockdown)
$Tabs->content='
<li role="presentation" class="nav-item active"><a class="nav-link" href="#account" aria-controls="account" role="tab" data-toggle="tab"  onclick="showSaveBtn()">Account</a></li>
<li role="presentation" class="nav-item"><a class="nav-link" href="#appearance" aria-controls="appearance" role="tab" data-toggle="tab" onclick="showSaveBtn()">Appearance</a></li>
<li role="presentation" class="nav-item"><a class="nav-link" href="#mls" aria-controls="mls" role="tab" data-toggle="tab" onclick="showSaveBtn()">MLS</a></li>
<li role="presentation" class="nav-item"><a class="nav-link" href="#password" aria-controls="mls" role="tab" data-toggle="tab" onclick="showSaveBtn()">Password</a></li>
<li role="presentation" class="nav-item"><a class="nav-link" href="#payment" aria-controls="payment" role="tab" data-toggle="tab" onclick="hideSaveBtn()">Payment</a></li>
<li role="presentation" class="nav-item"><a class="nav-link" href="#domains" aria-controls="domains" role="tab" data-toggle="tab">Domains</a></li>
<li class="pullRight controls pull-right" style="visibility: hidden;">'.$SaveBtn.'</li>
';

else $Tabs->content=
	'<li role="presentation" class="nav-item  active"><a class="nav-link" href="#payment" aria-controls="payment" role="tab" data-toggle="tab" onclick="hideSaveBtn()">Payment</a></li>';

/*
<li role="presentation" class="nav-item"><a class="nav-link" href="#domains" aria-controls="domains" role="tab" data-toggle="tab" onclick="hideSaveBtn()">Domains</a></li>
*/
//<li role="presentation" class="nav-item" ><a class="nav-link " href="#general" aria-controls="general" role="tab" data-toggle="tab">General</a></li>


//$General=new renderable(['tag'=>'div','classes'=>['tab-pane','active'],'attributes'=>['role'=>'tabpanel'],  'pageID'=>'general']);
$Account=new renderable(['tag'=>'div','classes'=>['tab-pane','active'],'attributes'=>['role'=>'tabpanel'],           'pageID'=>'account']);
$Appearance=new renderable(['tag'=>'div','classes'=>['tab-pane'],'attributes'=>['role'=>'tabpanel'],        'pageID'=>'appearance']);
$MLS=new renderable(['tag'=>'div','classes'=>['tab-pane'],'attributes'=>['role'=>'tabpanel'],               'pageID'=>'mls']);
$Password=new renderable(['tag'=>'div','classes'=>['tab-pane'],'attributes'=>['role'=>'tabpanel'],               'pageID'=>'password']);
$Payment=new renderable(['tag'=>'div','classes'=>['tab-pane'],'attributes'=>['role'=>'tabpanel'],               'pageID'=>'payment']);
$Domains=new renderable(['tag'=>'div','classes'=>['tab-pane'],'attributes'=>['role'=>'tabpanel'],               'pageID'=>'domains']);
//$Billing=new renderable(['tag'=>'div','classes'=>['tab-pane'],'attributes'=>['role'=>'tabpanel'],               'pageID'=>'billing']);

//$ContainerContent->children[] = (new BootstrapPanel('hello','mr','tseng'))->panel;

/****       SETTINGS        ***/
/*          General           */

$o['tag']='input';
$o['selfcontained']=true;
$o['attributes']=['name'=>'_setting_sitetitle', 'placeholder'=>'Site Title'];
/*
$General->content='
 <div class="form-group form-inline">
    <label class="sr-only" for="exampleInputEmail3">Email address</label>
    <input name="email" type="email" class="form-control" id="exampleInputEmail3" value="'.$UserData['email'].'" />
    <!-- label class="sr-only" for="exampleInputPassword3">Password</label>
    <input name="authcode" type="password" class="form-control" id="exampleInputPassword3" placeholder="Password" / -->
  </div>
  <div class="input-group">
      <input type="text" class="form-control" name="account" value="'.$UserData['username'].'" />
      <div class="input-group-addon">.suitespace.co</div>
  </div>
';
*/
$o=[];
/*          Account           */

/*
$o['attributes']=['name'=>'_setting_sitetitle', 'placeholder'=>'Site Title'];
$Account->children[] = new renderable($o);
*/
$Account->content='
  <div >
  <br/><br/>

    <fieldset class="form-group form-inline" style="width: 80%;" >
        <label>Profile Thumbnail</label><br/>
            <div class="fileUpload">
      				<img class="AdminSettings_AgentAvatar" src="https://www.'.$RemoteBase.'/__static/uploads'.$UserData['thumbnail'].'" />
      				<br />
              <input name="_thumb" value="'.$UserData['thumbnail'].'" type="hidden" />
      				<input type="file" name="files" class="AdminSettings_AgentAvatarInput pullLeft"
                style="margin-left: 4em; padding-left: 1em; "/>
                <br /><br />
              <a class="control btn-edit pullLeft"
                style="margin-left: 4em; padding-left: 1em; color: #58bcd3;"
  					 		data-toggle="modal"
  							href="#CropModal"><strong>Crop Profile</strong>
  						</a><br />
      			</div>
	</fieldset>
	<br />
    <label>Name</label><br/>
    <input type="text" class="form-control" name="fname" placeholder="First Name" value="'.$UserData['first_name'].'" />
    <input type="text" class="form-control" name="lname" placeholder="Last Name" value="'.$UserData['last_name'].'" /><br />

    <br/><br /><label>Email / Telephone</label><br/>
    <input type="text" class="form-control" name="email" placeholder="What is your mobile number?" value="'.$UserData['email'].'"/>
    <input type="text" class="form-control" name="mobile_num" placeholder="What is your mobile number?" value="'.$UserData['tele'].'"/><br />
    <br/><label>Firm Name / Address</label><br/>
    <input type="text" class="form-control" name="organization" placeholder="What company do you work for?" value="'.$UserData['firm_name'].'" />
    <input type="text" class="form-control" name="office_addr" placeholder="What is your office address?" value="'.$UserData['firm_addr_01'].'"/><br />
    <br/><label>Firm Telephone</label><br/>
    <!-- input type="email" class="form-control" name="office_email" placeholder="What is your work email?" value="'.$UserData['firm_email'].'"/ -->
    <input type="text" class="form-control" name="office_num" placeholder="What is your office number?" value="'.$UserData['firm_tele'].'"/>
    <!-- <br/>1
    <br/><label>About You</label><br/>
    <textarea class="form-control" name="bio" value="'.$UserData['profile0'].'" rows="8">Agent Bio</textarea> -->
  </div>';

/*          Appearance        */

$Appearance->content = '
    <!-- label>How would you like the header to show?</label>
    <select name="headerOpts" class="form-control" id="exampleSelect1">
      <option value="1">White header</option>
      <option value="0">Flush background</option>
    </select><br />
  <div class="form-group" style="padding: 2em; background-color: #ccd;">
    <img style="min-height:8em; background-color: white; width:100%; " src=""/>
    <br /> Preview
  </div>

  <br / -->
<label>  Upload a logo </label><br />

<div class="fileUpload pull-left">
	<input type="file" name="files[]" style="float: none;"><br><br>
	<input class="upThumb" name="_logo_thumb" value="'.$UserData['logo_thumbnail'].'" type="hidden">
	<input class="upImg" name="_logo_main" value="'.$UserData['logo'].'" type="hidden">
	<img style="height: 42px; width: auto; " src="https://static.'.$RemoteBase.'/uploads'.$UserData['logo_thumbnail'].'">
</div>
<br /><br />
For best results we recommend uploading your logo with
a transparent background (example .png .gif)

<br /><br />
<input type="hidden" class="form-control" name="logoname" value="'.$UserData['textlogo'].'" />
  <!-- div class="form-group form-inline">
    If you do not have a logo, no worries, just type in the name you want to show.<br />
    <input type="text" class="form-control" name="logoname" value="'.$UserData['textlogo'].'" />
  </div -->
';
/*$o=[];
$o=['tag'=>'div','content'=>'How would you like the header to show?'];

//$o['attributes']=['name'=>'_setting_sitetitle', 'placeholder'=>'Site Title'];
$Appearance->children[]= new renderable($o);
$o['tag']='label';
$o['content']='<input type="radio" name="setting_heading" value="255" /><img src="http://rockstartemplate.com/blogheaders/bannerdesign2.jpg" />';
$Appearance->children[] = new renderable($o);
$o['content']='<input type="radio" name="setting_heading" value="0" /><img src="http://templatelite.com/uploads/2008/11/set5-header-original.jpg" />';
$Appearance->children[] = new renderable($o);
*/


/*          MLS/IDX           */


$MLS->content='
<br />
  <label>
      <input name="idxon" type="checkbox" value='.($UserData['mls_choice'] > 0 ? '"true" checked' : '"false" ').' />
      Enable MLS?
  </label>
  Only the IDX for Miami Realtor Association is currently available.<br/><br/>
    Agent License #
  <fieldset class="form-group">
    <input type="text" class="form-control" name="agent_license" value="'.$UserData['agent_license'].'" />
  </fieldset>
    Broker Name
  <div class="form-group form-inline">
    <input type="text" class="form-control" name="broker_fname" value="'.$UserData['broker_first_name'].'" />
    <input type="text" class="form-control" name="broker_lname" value="'.$UserData['broker_last_name'].'" />
  </div>
    Broker License #
  <fieldset class="form-group">
    <input type="text" class="form-control" name="broker_license" value="'.$UserData['broker_license'].'" />
 </fieldset>
';

$Password->content='
  <div>
    <fieldset class="form-group">
	Current Password
    <input type="password" class="form-control" name="current_password" value="" /><br />
	New Password
    <input type="password" class="form-control" name="new_password" value="" /><br />
	Confirm New Password
	<input type="password" class="form-control" name="confirm_new_password" value="" /><br />
  </fieldset>
  <div class="passwordAlert">&#160;</div>
  <button type="button" value="Reset Password" class="SubmitBtn btn btn-default control" onclick="ResetPassword(this);">
	Reset Password
  </button>

  </div>
';

$Payment->classes[]='';
$Payment->content='
<br/><br/>

<!-- div style="inline-block; float:right; width:20%;" >
<br/><br/>

<form action="" id="payment-form" method="POST">
  <script
  src="https://checkout.stripe.com/checkout.js" class="stripe-button"
  data-key="pk_test_T0WLn7KnQ7tbTflF8zHbjqeL"
  data-image="https://static.'.$RemoteBase.'/img/suiteux_logo.png"
  data-name="Suite UX"
  data-panel-label="Update Card Details"
  data-label="Update Card Details"
  data-allow-remember-me=false
  data-locale="auto">
  </script>
</form>
</div -->

  <div class="row pad1rem">
    <div class="col-sm-6 paymentSelector" style="padding-left: 0px">
      <fieldset>
      <h3 style="color: #333;font-size: 18px;font-weight: bold;">Choose a Plan</h3>
        <label class="btn btn-info paymentInput ">
          <span class="paymentText">Annually</br></br>$420.00/year</br><span class="paymentTextS">Save $120</span></span>
          <input type="radio" name="subscriptionType" value="1" class="hideMe"/>
        </label>
        <label class="btn btn-info paymentInput floatyRighty">
          <span class="paymentText">Monthly</br></br>$45.00/month</br><span class="paymentTextS">$540/yr</span></span>
          <input type="radio" name="subscriptionType" value="2" class="hideMe"/>
        </label>
      </fieldset>
      <br/><br/>
    </div>
    <div class="col-sm-6" style="background-color: #E8E8E8; border-right: 4em solid white;">
      <h3 style="font-size: 18px;font-weight: bold;">Contact Info On File</h3>
      <span>Name: </span><span>'.$UserData['first_name'].' '.$UserData['last_name'].'</span><br/>
      <span>Phone: </span><span>'.$UserData['tele'].'</span><br/>
      <span>Email: </span><span>'.$UserData['email'].'</span><br/>
      <br /><br />
    </div>
    <br/><br/>
    <div class="HalfWidth">
      <h3 style="color: #333;font-size: 18px;font-weight: bold;">Enter Credit or Debit Card</h3>
      <div id="card-element"></div>
      <div id="card-errors"></div>
      <br/><br/>
    </div>
    <br/>
  </div>
  <div class="col-sm-12" style="margin-left: 0px; padding-left: 0px;">
    <hr noshade="" class="breakline">
    <fieldset>Total: <h2 id="totalUnit" >$420 / yr</h2></fieldset>
    <br /><br />
    <div id="billingSuccess"></div>
  </div>


  <div id="payment-form">
  <button
      type="button"
      class=" btn btn-lg btn-info purchaseBtn"
      id="payment-submit"
      data-role="autoform"
      data-context=\'{ "_self_id":'.$UserID.', "_response_target": "#billingSuccess" }\'
      data-intent=\'{"REFRESH":{"Agent":"Billing"}}\'>
    Update Billing Info
  </button>
  </div>
';



$domain_list = [];
$roots = LoadObjects('compositions',[
	'range' => '`id`, `pointer`, `alias`',
	'method' => 'WHERE `id` = `parent` AND `owner` = '.$UserID
]);
//var_dump($roots);
foreach($roots	as	$r){
	$d=[];
	$d['name'] = $r->data['alias'];
	$d['mask'] = $r->data['pointer'];
	$d['id'] = $r->data['id'];
	$domain_list[]=$d;
}

//$domain_list = json_decode( $UserData['broker_first_name'], true);

$domain_select = new renderable('select');

$i=0;

foreach($domain_list as $domain){
 $domain_select->children[] = new renderable([
   'tag'=>'option',
   'attributes'=>[
     'value' =>  $domain['name']
   ],
   'content'=>  $domain['name']
 ]);
 ++$i;
}

$domain_select_string = $domain_select->render();

$Domains->content='
<br/><br/>

<div class="DomainEditor">
	Domains<br>

	<div class="form-row">
		<form data-action="dataset://domains">
			<div class="col-sm-11" style="padding-left: 0px;">
				<input name="_domain" type="text" class="form-control" placeholder="Enter your domain" id="new_domain" />
			</div>
			<div class="col-sm-1">
				<button type="button" class="sheer btn addButton" id="AddDomainBtn">
					Add
				</button>
			</div>
		</form>
	</div>

	<ul class="sheer">';

foreach($domain_list as $domain){
  $Domains->content.='
    <li>
      <span class="top">'.$domain['name'].'</span>
      <span class="bottom">
        <a href="#">Click here to get step-by-step directions for your hosting company, you tell me Sean</a> <br /><br /><br />
        <div class="row">
          <div class="col-sm-4">
            <span><strong>Domain Routing</strong></span><br />
            <input class="domainRadio" type="radio" value="0" name="maskDomain'.$i.'" '. ( !$domain['mask'] ? ' checked="checked" ' : '') .'/>Show on pages <br />
            <input class="domainRadio" type="radio" value="1" name="maskDomain'.$i.'" '. (  $domain['mask'] ? ' checked="checked" ' : '') .'/>Mask domain <br />
            '.$domain_select_string.'
          </div>
          <div class="col-sm-8">
            <span><strong>Nameservers</strong></span><br />
            <span>ns1.suiteux.com</span><br />
            <span>ns1.suiteux.com</span><br /><br />
            <a href="#">Click here if you have a GoDaddy account</a> <br />
          </div>
        </div>
      </span>
      <button class="btn removeListItem"><em class="fa fa-trash "> </em></button>
    </li>';
    ++$i;
}

$Domains->content.='
  </ul>
</div>';







/*
$Billing->content='
<div >
	<br/><br/>
	<form action="" method="POST">
	  <script
		src="https://checkout.stripe.com/checkout.js" class="stripe-button"
		data-key="pk_test_T0WLn7KnQ7tbTflF8zHbjqeL"
		data-image="https://static.'.$RemoteBase.'/img/suiteux_logo.png"
		data-name="Suite UX"
		data-panel-label="Update Card Details"
		data-label="Update Card Details"
		data-allow-remember-me=false
		data-locale="auto">
	  </script>
	</form>
</div>';
*/


$MainAdminTable->classes[]='Interface InterfaceContent';
$MainAdminTable->content='<form data-action="AGENT" class="autoform" >' . $MainAdminTable->content;
//$Panes->children[]=$General;
if(!$Lockdown){
$Panes->children[]=$Account;
$Panes->children[]=$Appearance;
$Panes->children[]=$MLS;
$Panes->children[]=$Password;
}
else $Payment->classes[]='active';

$Panes->children[]=$Payment;
if(!$Lockdown)
	$Panes->children[]=$Domains;
//$Panes->children[]=$Domains;
//$Panes->children[]=$Billing;

$MainAdminTable->lastFilter.='</form>';
$MainAdminTable->classes[]='Interface InterfaceContent';

if ($_SERVER['REQUEST_METHOD'] == 'POST'){
	CheckSession();
	require_once('vendor/autoload.php');
	// Use your test API key (switch to the live key later)
	\Stripe\Stripe::setApiKey('sk_test_BQokikJOvBiI2HlWgH4olfQ2');

	if (isset($_POST['stripeToken'])){
		try
		{
			$cu = \Stripe\Customer::retrieve($_SESSION['User']['stripe_id']); // stored in your application
			$cu->source = $_POST['stripeToken']; // obtained with Checkout
			$cu->save();
			$success = "Your card details have been updated!";
			$msg = $success;
		}
		catch(\Stripe\Error\Card $e) {
			// Use the variable $error to save any errors to be displayed to the customer later in the page
			$body = $e->getJsonBody();
			$err  = $body['error'];
			$error = $err['message'];
			$msg = $error;
		}
	}
}

?>
