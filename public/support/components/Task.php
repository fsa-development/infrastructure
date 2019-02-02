<?php

class Task extends Component
{
	public static $ComponentName='Task';
    public static $PluralName='Tasks';
	public $ChildTag='tr';
	public $RenderType = 'Smart';
	public $ContainerClasses = array('Tasks');
	public $ChildClasses = [ ['Task', 'Interface'],['collapse'],[] ]; //One set of classes per <Render:Markup> in the template
	public $ChildAttributes = [['data-custom'=>'any value'], [],[]];
}
?>
