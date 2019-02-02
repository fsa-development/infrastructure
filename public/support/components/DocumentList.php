<?php
class DocumentList extends Component
{
	public static $ComponentName='DocumentList';
	public $RenderType = 'Smart';
	public $ChildTag = 'tr';
	public $ChildClasses=array('controls');
	public $ContainerClasses = array();
    
    public $sources = ['documents'];
}
?>