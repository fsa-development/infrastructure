<?php
class TicketsList extends Component
{
	public static $ComponentName='TicketsList';
	public $RenderType = 'Smart';
	public $ChildTag = 'tr';
	public $ChildClasses=array('controls');
	public $ContainerClasses = array();
    
    public $sources = ['tickets'];
}
?>