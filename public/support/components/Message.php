<?php

class Message extends Component
{
	public static $ComponentName='Message';
	public $ChildTag='div';
	public $ContainerTag='div';
	public $RenderType = 'Smart';
	public $ContainerClasses = array('Messages');
	public $ChildClasses = array( array('Message') );	//One set of classes per <Render:Markup> in the template
	public $ChildAttributes = array( array('data-custom'=>'any value') );
	
	public $sources = ['messages'];
	
	function PreProcess(&$BuildData)
	{	
		if( !empty($BuildData) )try{
		foreach($BuildData as &$ConsolidatedRow)
		{
			$tmp = LoadObject('users', ['method' => 'WHERE `id` =' . $ConsolidatedRow['messages']['user']]);
			$ConsolidatedRow['users'] = $tmp->data;
		}}
		catch(Exception $e){ return false; }
	}

	function PostProcess(&$BuildData)
	{
		global $ApproachDebugConsole;
		$options=array();

		$options['template']=$this->context['template'];
		$options['Message']['users'] = array('condition' => 'LIMIT 0');
		$i=0;

		if( !empty($BuildData) )
		foreach($BuildData as &$ConsolidatedRow)
		{
			$options['Message']['messages'] = ['method' =>'WHERE `p_id` =' . $ConsolidatedRow['messages']['id']];//, 'condition' => 'GROUP_BY `post_date` ASC'];

			$MessageContainer=$this->ParentContainer->children[$i];
			//$MessageContainer
			$Responses = new Smart('div',null,$options);
			$Message = new Message();
			$Message->createContext($Responses,$Responses->id,$this->context['data'],$this->context['template'] );
			$Message->Load($options['Message']);
			foreach($Responses->children as &$child)
				$this->ParentContainer->children[]=$child;
			++$i;
		}
	}
}

/*
	Recursive version - For multi-user threads with nested replies (progresively more padded optional)
	foreach($BuildData as &$ConsolidatedRow)
		{
			$options['Message']['messages'] = ['method' =>'WHERE `p_id` =' . $ConsolidatedRow['messages']['id']];//, 'condition' => 'GROUP_BY `post_date` ASC'];

			$MessageContainer=$this->ParentContainer->children[$i];
			$MessageContainer->children[]=$Responses = new Smart('li',null,$options);
			$Message = new Message();
			$Message->createContext($Responses,$Responses->id,$this->context['data'],$this->context['template'] );
			$Message->Load($options['Message']);
			++$i;
		}
*/
?>
