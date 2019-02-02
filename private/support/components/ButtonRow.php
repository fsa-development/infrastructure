<?php
class ButtonRow extends Component
{
	public static $ComponentName='ButtonRow';
	public $RenderType = 'Smart';
    public $ContainerTag = 'div';
	public $ChildTag = 'div';
	public $ChildClasses=array('');
    public $ChildAttributes=array('style'=>'width:100%;');
	public $ContainerClasses = array();
	public $sources = ['links'];
}

?>