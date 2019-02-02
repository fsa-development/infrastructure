<?php

class Article extends Component
{
	public static $ComponentName='Article';
	public $ChildTag='div';
	public $ContainerTag='div';
	public $RenderType = 'Smart';
	public $ContainerClasses = array('Articles','Messages');
	public $ChildClasses = array( array('Article','Message') );	//One set of classes per <Render:Markup> in the template
	public $ChildAttributes = [];//[['data-custom'=>'any value']];
	
	public $sources = ['documents'];
}
?>
