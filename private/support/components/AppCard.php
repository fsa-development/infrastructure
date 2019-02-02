<?php
class Postcard extends Component
{
	public static $ComponentName='Postcard';
	public $RenderType = 'Smart';
	public $ChildTag = 'ul';
	public $ChildClasses=array('round','Postcard');

	public $ContainerClasses = array('HorizontalList');
}

?>