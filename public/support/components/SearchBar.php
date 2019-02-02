<?php
class SearchBar extends Component
{
	public static $ComponentName='SearchBar';
	public $RenderType = 'Smart';
	public $ChildTag = 'div';
	public $ChildClasses=array('');

	public $ContainerClasses = array('search','col-sm-12','Interface', 'InterfaceContent');
  public $sources = ['text_embeds'];
}

?>
