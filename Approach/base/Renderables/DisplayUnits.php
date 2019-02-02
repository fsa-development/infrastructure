<?php

//require_once('../Service.php');
require_once(__DIR__ . '/../Render.php');
//require_once(__DIR__ . '/../DataObject.php');

$ApproachDisplayUnit = array();

class UserInterface extends renderable
{
  public $Layout;
  public $Header;   //in layout
  public $Titlebar; //in header
  public $Content;	//in layout
  public $Footer;	//in layout
  public $title='';

  function UserInterface()
  {
    $this->tag	        = 'ul';
    $this->classes[]	= 'Interface';
    $this->children[]	= $this->Layout = new renderable('li','',	array('classes'=>'InterfaceLayout') );

    $this->Layout->children[]	= $this->Header = new renderable('ul','',	array('classes'=>array('Header','controls')));
    $this->Layout->children[]	= $this->Content	= new renderable('ul','',	array('classes'=>array('Content','controls')));
    $this->Layout->children[]	= $this->Footer	= new renderable('ul','',	array('classes'=>array('Footer','controls')));

    $this->Header->children[]	= $this->Titlebar	= new renderable('li','',	array('classes'=>array('Titlebar'),'content'=>($this->title | 'Command Console')));
  }
}

class Wizard extends UserInterface
{
  public $Slides;
  public $CancelButton;
  public $BackButton;
  public $NextButton;
  public $FinishButton;

  function Wizard()
  {
    $this->title = 'Complete actions using the following steps';
    $this->classes[]	= 'Wizard';
    
    $Footer->children[]	= $CancelButton	= new renderable('li','',	array('classes'=>array('Cancel',	'DarkRed',	'Button'),'content'=>'Cancel'));
    $Footer->children[]	= $BackButton	= new renderable('li','',	array('classes'=>array('Back',	'DarkGreen',	'Button'),'content'=>'Back'));
    $Footer->children[]	= $NextButton	= new renderable('li','',	array('classes'=>array('Next',	'DarkGreen',	'Button'),'content'=>'Next'));
    $Footer->children[]	= $FinishButton	= new renderable('li','',	array('classes'=>array('Finish',	'DarkBlue',	'Button'),'content'=>'Finish'));

    $FinishButton->attributes['data-intent']='Autoform Insert ACTION';
  }
}

class BootstrapPanel {
    private static $panelID=0;
    public $panel;
    public $titlebar;
    public $body;
    public $footer;
    
    function BootstrapPanel($title='', $body='', $footer='')
    {
        $arg=[];
        $arg['tag'] = 'div';
        $arg['classes'] = ['col-sm-6'];
        $arg['pageID'] = 'BootPanel__' . BootstrapPanel::$panelID;
        $this->panel = new renderable($arg);    //Set $this->panel
        BootstrapPanel::$panelID++;        
        
        $arg['classes'] = ['panel-group'];
        $panel_group= new renderable($arg);
        
        $arg['classes'] = ['panel panel-default'];
        $panel_default = new renderable($arg);
        
        $arg['classes'] = ['panel-heading'];
        $panel_heading = new renderable($arg);
        
        $arg['classes'] = ['panel-collapse collapse'];
        $panel_collapse = new renderable($arg);
        
        if(gettype($title) == 'string')
        {
            $arg['tag'] = 'h4';
            $arg['classes'] = ['panel-title'];
            $arg['content'] = $title;
            $this->titlebar =  new renderable($arg);
        }
        else $this->titlebar = $title;

        if(gettype($body) == 'string')
        {
            $arg['tag'] = 'div';
            $arg['classes'] = ['panel-body'];            
            $arg['content'] = $body;
            $this->body = new renderable($arg);
        }
        else $this->body = $body;

        if(gettype($footer) == 'string')
        {
            $arg['tag'] = 'div';
            $arg['classes'] = ['panel-footer'];
            $arg['content'] = $footer;
            $this->footer = new renderable($arg);
        }
        else $this->footer = $body;

        $this->panel->children[] = $panel_group;
        $panel_group->children[] = $panel_default;
        $panel_default->children[] = $panel_heading;
        $panel_heading->children[] = $this->titlebar;
        
        $panel_default->children[]= $panel_collapse;
        $panel_collapse->children[]= $this->body;
        $panel_collapse->children[]= $this->footer;
    }
}


$ApproachDisplayUnit['Composition']['NewWizard'] = new Wizard();
$ApproachDisplayUnit['User']['Browser'] = new renderable('ul');
$ApproachDisplayUnit['Bootstrap']['Panel'] = new BootstrapPanel('My Panel', 'Content', 'Footer');




?>