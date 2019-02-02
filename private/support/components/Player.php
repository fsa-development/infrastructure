<?php

class Player extends Component
{
	public static $ComponentName='Player';
	function Player()
	{	 global $ApproachHTML5;
		if($ApproachHTML5) // isHTML5 call in root.php sets this var. this var also goes to client side.
		{
			$this->ChildTag = array( 'video' , 'ul');
			$this->ChildAttributes = array
			(
				 'VideoMarkup' =>array
				 (
						 'width'=>'640',
						 'height'=>'480',
						 'align'=>'middle'

				 ),
				 'ControlMarkup' => array()
			);
		}
		else
		{
			$this->ChildTag = array( 'object', 'ul');
			$this->ChildAttributes = array
			(
				 'VideoMarkup' =>array
				 (
						 'width'=>'640',
						 'height'=>'480',
						 'align'=>'middle',
						 'classid'=>'clsid:d27cdb6e-ae6d-11cf-96b8-444553540000'

				 ),
				 'ControlMarkup' => array()
			);
		}
	}
	public $RenderType = 'Smart';
	public $ChildClasses = array
			(
				'VideoMarkup' => array
				(
					'video'
				),
				'ControlMarkup' => array
				(
					'controls'
				)
			);
	public $ContainerClasses = array('Player');
	public $ScriptPlacement = true;

	function PostProcess(&$BuildData){	}
}


?>
