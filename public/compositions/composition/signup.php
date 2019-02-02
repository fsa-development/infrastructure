<?php

require_once __DIR__.'/../form_layout.php';

global $RuntimePath;
global $DeployPath;
global $SupportPath;
global $StaticFiles;

$StaticMarkupPath = $RuntimePath.'/support/templates/static/';
$TemplatePath = $RuntimePath.'/support/templates/';

$items = LoadObjects( 'compositions',array( 'method' => ' WHERE parent = '.Composition::$Active->Context['data']['id'] ));
$UserID = !empty(Composition::$Active->Context['authenticated_account']) ? Composition::$Active->Context['authenticated_account']->data['id'] : NULL;

$head->content.='
<script src="https://js.stripe.com/v3/"></script>
<script>

function UserCheckResponse(json,status,xhr){
	$("#UserCheck").replaceWith(json["REFRESH"]["#UserCheck"]);
}

function CheckAccount(e){
	var username = $("#accountInput").val();
	var re = /^(([a-zA-Z0-9]|[a-zA-Z0-9][a-zA-Z0-9-][a-zA-Z0-9]).)([A-Za-z0-9]|[A-Za-z0-9][A-Za-z0-9-]*[A-Za-z0-9])$/

	if( !re.exec(username) ){
		$("#UserCheck").html("<span class=\"errorStyle\">Website name cannot include spaces or special characters</span>");
		$("#UserCheck").addClass("alert-danger");
		$(e.target).data("valid","false");

		return -1;	// Do not even check if unique user w/ajax
	};

	var IntentJSON = {
		"support": {
			"_response_target": "#UserCheck",
			"dataset://users": {
				"username" : username
			}
		},
		"command": {
			"REFRESH": { "User": "Check"	}
		}
	};

	var ReqData = { json: JSON.stringify(IntentJSON) };
	var Approach ={};
	Approach.Utility = "//service.myrealestatesite.co/Utility.php";

	$.ajax({
		url: Approach.Utility,   //url of Utility.php
		type: "post",
		data: ReqData,  //the json data
		dataType: "json",
		xhrFields: {                    withCredentials: true                },
		crossDomain: true,
		success: UserCheckResponse
	});
	//Interface.call.Refresh
}


$().ready(function(){

	$("#accountInput").change( CheckAccount );

	var a = $(".Interface").each(function(instance, Markup){ Markup.Interface=new Interface(Markup);	});
	console.log( $(".Interface") );

	$("#BackBtn2").hide();
	$("#NextBtn2").hide();

	var CurrentTabIndex =0;
	$("#NextBtn,#NextBtn2").click(function(){

		if( $("#UserCheck").hasClass("alert-danger") ) return false;

		if(CurrentTabIndex < 2){
			var curTab = $(\'#payment-form li.tab-pane.active\');
			curTab.removeClass(\'active\');
			curTab.next(\'li\').addClass(\'active\');
			CurrentTabIndex++;
		}
		switch(CurrentTabIndex){
			case 0:  $("#NextBtn").show(); $("#NextBtn").html("Next"); $("#BackBtn").html("Back to Site"); $("#BackBtn2").hide();$("#NextBtn2").hide(); break;
			case 1:  $("#NextBtn").hide(); $("#NextBtn2").hide();  $("#BackBtn").html("Back"); $("#BackBtn2").html("Back");break;
			//case 1:   $("#NextBtn").show(); $("#NextBtn").html("Next"); $("#NextBtn2").html("Next"); $("#BackBtn").html("Back"); $("#BackBtn2").show(); $("#NextBtn2").show(); $("#BackBtn2").html("Back"); break;
			case 2:  $("#NextBtn").hide(); $("#BackBtn2").hide();$("#NextBtn2").hide(); break;
			default: break;
		}
    });

    $("#BackBtn,#BackBtn2").click(function(){
	  if (CurrentTabIndex == 0){
		window.location = "https://suiteux.com";
		return true;
	  }
      if(CurrentTabIndex > 0){
        var curTab = $(\'#payment-form li.tab-pane.active\');
        curTab.removeClass(\'active\');
        curTab.prev(\'li\').addClass(\'active\');
        CurrentTabIndex--;
	  }
    switch(CurrentTabIndex){
      case 0:  $("#NextBtn").show(); $("#NextBtn").html("Next"); $("#BackBtn").html("Back to Site"); $("#BackBtn2").hide();$("#NextBtn2").hide(); break;
      case 1:  $("#NextBtn").hide(); $("#NextBtn2").hide();  $("#BackBtn").html("Back"); $("#BackBtn2").html("Back"); $("#BackBtn2").show();  break;
      case 2:  $("#NextBtn").hide(); $("#BackBtn2").hide();$("#NextBtn2").hide(); break;
      default: break;
    }
    });
	return true;
});

</script>
';

$AdminMain->children[]=new renderable(['tag'=>'li','classes'=>['col-sm-1']]);
$AdminMain->children[]=$ContentArea=new renderable(['tag'=>'li','classes'=>['col-sm-10', 'verticalMarg', 'Interface', 'InterfaceContent']]);
$AdminMain->children[]=new renderable(['tag'=>'li','classes'=>['col-sm-1']]);

$ContentArea->content = <<<BLOB
<button type="button" id="BackBtn" class="btn btn-default btn-lg backBtn">Back to site</button>
<button type="button" id="NextBtn" class="btn btn-info btn-lg nextBtn">Next</button>
<button type="button" id="BackBtn2" class="btn btn-default btn-lg backBtn">Back to site</button>
<button type="button" id="NextBtn2" class="btn btn-info btn-lg nextBtn">Build my site</button>

  <hr noshade="" class="breakline">

  <form id="payment-form" method="post" action="" data-action="CHECKOUT">
    <ul class="col-sm-12 wideFit " >

      <li class="col-sm-12 tab1 tab-pane active"> <!-- start TAB1 -->
        <div class="row verticalMarg">
          <div class="col-sm-6">
            <input name="email" type="email" class="form-control inputArea" placeholder="Enter email">
          </div>
          <div class="col-sm-6">
            <input name="authcode" type="password" class="form-control inputArea" placeholder="Password">
            <!-- a class="greyTxt horizonMarg" href="">Forgot your password?</a -->
          </div>
        </div>
        <div class="col-sm-12 input-group verticalMarg">
            <input id="accountInput" type="text" class="form-control inputArea" name="account" placeholder="Choose your website name">
            <div class="input-group-addon noBorder">.myrealestatesite.co</div>
        </div>
        <p>A custom domain may be added once your account is created (e.g.: www.yoursitename.com)</p>
        <div id="UserCheck" class="hidden"></div>
        <a class="greyTxt horizonMarg" href="https://myrealestatesite.co/login">Already have an account? Login.</a>
          <!-- button type="button" data-intent='{"SERVICE":{"Slider":"Next"} }' class="btn btn-primary">Sign Up</button -->
      </li> <!-- end tab1 -->



      <li class="col-sm-12 tab2 tab-pane"> <!--start tab2 -->
        <div class="row verticalMarg1">
          <div class="col-sm-6">
            <input type="text" class="form-control inputArea" name="fname" placeholder="First Name">
          </div>
          <div class="col-sm-6">
            <input type="text" class="form-control inputArea" name="lname" placeholder="Last Name">
          </div>
        </div>
        <fieldset class="form-group sheer">
		  Information entered will show on your website's <strong>contact page</strong>.<br/>
          <input type="text" class="form-control inputArea verticalMarg1" name="organization" placeholder="What company do you work for?">
          <input type="text" class="form-control inputArea verticalMarg1" name="office_addr" placeholder="What is your office address?">
          <input type="email" class="form-control inputArea verticalMarg1" name="office_email" placeholder="What is your work email?">
       </fieldset>

        <div class="row">
          <div class="col-sm-6 verticalMarg1">
            <input type="text" class="form-control inputArea" name="office_num" placeholder="What is your office number?">
          </div>
          <div class="col-sm-6 verticalMarg1">
            <input type="text" class="form-control inputArea" name="mobile_num" placeholder="What is your mobile number?">
          </div>
        </div>

        <fieldset class="form-group">
		Information entered will show on your website's <strong>about page</strong>.<br/>
          <textarea class="form-control inputAreaLg verticalMarg1" name="bio" rows="8" placeholder="Agent Bio"></textarea>
        </fieldset>

        <!-- fieldset class="form-group">
        <p class="verticalMarg">Upload your Logo</p>
        </br>
          <label class="file">
          <input type="file" name="file">
          <span class="file-custom"></span>
          </label>
        <br />
        <p class="greyTxt">
          For best results we recommend uploading your logo with </br>
      a transparent background (example .png .gif)
        </p>

        </fieldset >

        <div class="form-group">
          If you do not have a logo, no worries, just type in the name you want to show.
          <input type="text" class="form-control inputArea" name="logoname" placeholder="">
        </div -->
<!--     <a name="Appearance"></a>
        <fieldset class="form-group">
        <h2> Appearance</h2>
          <label for="exampleSelect1">How would you like the header to show?</label>
          <select name="headerOpts" class="form-control" id="exampleSelect1">
            <option value="1">White header</option>
            <option value="0">Flush background</option>
          </select>
        </fieldset>
        <div class="form-group" style="padding: 2em; background-color: #ccd;">
          <img style="min-height:8em; background-color: white; width:100%; " src=""/>
          <br /> Preview
        </div>


        <fieldset class="form-group">
          <label for="exampleSelect2">Choose the banner style you want on the front of your site.</label>
          <select class="form-control" name="bannerType">
            <option value="3">Banner image with text</option>
            <option value="2">Listing info</option>
            <option value="1">Agent info</option>
            <option value="0">No banner</option>
          </select>
        </fieldset>
        <fieldset class="form-group">
          <label for="exampleTextarea">Main text</label>
          <textarea class="form-control" name="mainText" rows="3"></textarea>
        </fieldset>
        <fieldset class="form-group">
          <label for="exampleTextarea">Secondary text</label>
          <textarea class="form-control" name="secondText" rows="3">Martin is a top realtor/investor with over 15 years of experience. Martin is a Miami native and SELLING HOMES FAST is his specialty.</textarea>
        </fieldset>

        <a name="Body"></a>
        <h2>Body Text</h2>
        <fieldset class="form-group">
          <label for="exampleTextarea">Give the title text for your experience <a>Example</a></label>
          <textarea class="form-control" name="titleExp" rows="3"></textarea>
        </fieldset>
        <fieldset class="form-group">
          <label for="exampleTextarea">Give your clients more detail about your experience <a>Example</a></label>
          <textarea class="form-control" name="infoExp" rows="3"></textarea>
        </fieldset> -->

        <a name="MLS"></a>
        <h2>MLS/IDX (Free with annual plan)</h2>
        Only the IDX for Miami Realtor Association is currently available.
        </br>
        <label>
            <input name="idxon" type="checkbox"> Enable MLS?
        </label>

            Agent License #
          <fieldset class="form-group">
            <input type="text" class="form-control inputArea" name="agent_license" placeholder="">
         </fieldset>
         <div class="greyBox">
            <p class="horizonMarg">Broker Name</p>
          <div class="row ">
            <div class="col-sm-6">
              <input type="text" class="form-control inputArea" name="broker_fname" placeholder="First Name">
            </div>
            <div class="col-sm-6">
              <input type="text" class="form-control inputArea" name="broker_lname" placeholder="Last Name">
            </div>
          </div>
            <p class="horizonMarg">Broker License #</p>
          <fieldset class="form-group">
            <input type="text" class="form-control inputArea" name="broker_license" placeholder="">
          </fieldset>
        </div>

		<div class="col-sm-12 paymentCard controls" >
            <!--div id="card-element"></div>
            <div id="card-errors"></div-->
            <button type="button" class="control btn btn-lg btn-info purchaseBtn" data-role="autoform" id="paymentSubmit"
             data-context="{}" data-intent='{"REDIRECT":{"Checkout":"Stripe"}}' >Build my site</button>
            <div class="LoadingContain wideFite"><img src="https://static.myrealestatesite.co/img/suiteloading.gif" /></div>
          </div>
      </li> <!-- End Tab2 -->
      <!-- li class="col-sm-12 tab3 tab-pane">
        <div class="row pad1rem">
          <div class="col-sm-6 paymentSelector">
            <fieldset>
            <legend>Payment Options</legend>
              <label class="btn btn-info paymentInput ">
                <span class="paymentText">Annually</br></br>$420.00/year</br><span class="paymentTextS">Save $120</span></span>
                <input type="radio" name="subscriptionType" value="1" class="hideMe"/>
              </label>
              <label class="btn btn-info paymentInput floatyRighty">
                <span class="paymentText">Monthly</br></br>$45.00/month</br><span class="paymentTextS">$540/yr</span></span>
                <input type="radio" name="subscriptionType" value="2" class="hideMe"/>
              </label>
            </fieldset>
            <hr noshade="" class="breakline">
            <fieldset>Total: <h2 id="totalUnit" style="float:right">$420/year</h2></fieldset>
          </div>
        </div>
      </li> <!--end tab3 -->
    </ul>
  </form>
BLOB;

?>
