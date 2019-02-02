<?php
/*
echo '<html>
<head> <title>Code Sandbox</title> </head>
<body>
<pre>
';
echo '
</pre>
</body>
</html>
';
*/
/*
	Title: Smart Templating Class for Approach

	Copyright 2002-2014 Garet Claborn

	Licensed under the Apache License, Version 2.0 (the "License");
	you may not use this file except in compliance with the License.
	You may obtain a copy of the License at

	http://www.apache.org/licenses/LICENSE-2.0

	Unless required by applicable law or agreed to in writing, software
	distributed under the License is distributed on an "AS IS" BASIS,
	WITHOUT WARRANTIES OR CONDITIONS OF ANY KIND, either express or implied.
	See the License for the specific language governing permissions and
	limitations under the License.
*/

require_once(__DIR__.'/Render.php');
require_once(__DIR__.'/Utility.php');
$Defaults['Renderable']='div';

class Smart extends renderable
{
	public  $data;
	public  $markup=array();
	public  $template=null;
	public  $context=Array();
	public  $tokens=Array();
	public  $TemplateBinding;
	public  $Scripts;
	public  $options=array();

	function Smart($t='div',$pageID='',$options=array())
	{
		$this->id=renderable::$renderObjectIndex;
		renderable::$renderObjectIndex++;                /*    Register New Renderable    */

		if( is_array($t) && !is_string($t) ){ $options = $t; $this->tag= isset($t['tag']) ? $t['tag'] : 'div';}
		else $this->tag = $t;

		if( is_array($pageID) ){ $options = $pageID; $this->pageID= isset($pageID['pageID']) ? $pageID['pageID'] : get_class($this) . $this->id;}
		else $this->pageID = $pageID;

		if(isset($options['pageID']) )  $this->pageID = $options['pageID'];
		if(isset($options['classes']) ) $this->classes = $options['classes'];
		if(isset($options['attributes'])) $this->attributes = $options['attributes'];
		if(isset($options['selfcontained'])) $this->selfContained = $options['selfcontained'];
		if(isset($options['content'])) $this->content = $options['content'] . $this->content;
        if(isset($options['template'])) $this->template = GetFile($options['template']);

		$this->options=$options;

		$this->ResolveTemplate();
		$this->BindContext();
	}

	function SplitTemplate($path,$override=false)
	{
		global $APPROACH_REGISTERED_FILES;

		$bound = false;
		if(isset($APPROACH_REGISTERED_FILES[$path.'#binding']) && !$override)
		{
			$this->binding = $APPROACH_REGISTERED_FILES[$path.'#binding'];
			$bound=true;
		}
		if(isset($APPROACH_REGISTERED_FILES[$path.'#template']) && !$override)
		{
			$this->template = $APPROACH_REGISTERED_FILES[$path.'#template'];
			if($bound) return false;    //template sections not loaded fresh from disk
		}

		$file = fopen($path,'r');

		$this->binding = stream_get_line($file,65536,'<Render:') . '</Template>';
		$this->template = '<Template xmlns:Render="Render://approach.im">'. PHP_EOL . '<Render:';

		while( ($line = fgets($file)) !== false ) $this->template .= $line;

		if(!isset($APPROACH_REGISTERED_FILES[$path.'#binding']) || $override) $APPROACH_REGISTERED_FILES[$path.'#binding'] = $this->binding;
		if(!isset($APPROACH_REGISTERED_FILES[$path.'#binding']) || $override) $APPROACH_REGISTERED_FILES[$path.'#template'] = $this->template;

		return true;    //template sections loaded fresh from disk and cahced
	}

	public function ResolveTemplate()
	{
		//var_export($this->options['template']);
    if(isset($this->options['binding']) && isset($this->options['template']) )
		{
			$this->binding = GetFile($options['binding']);
			$this->template = GetFile($options['template']);
		}
		else if(isset($this->options['template']) && !isset($this->options['binding']) ) $this->SplitTemplate( $this->options['template'] );

		if(isset($this->options['template']) && $this->options['template'] != null)
		{
			$markup=array();
			$dataSet=simplexml_load_string($this->template);

			$TemplateBindings=simplexml_load_string($this->binding);
			$TemplateBindings=$TemplateBindings->xpath('//Component:*');

			$markupHeaders=$dataSet->xpath('//Render:*');

            foreach($TemplateBindings as $binding){
                    $this->TemplateBinding[$binding->getName()] = json_decode((string)$binding,true);
                    if(json_decode((string)$binding,true)===NULL)
                    {
                        echo '<pre>'.PHP_EOL.var_export((string)$binding,true).PHP_EOL.'</pre>';
                        trigger_error('JSON Decode Error in '.$binding->getName().' Template.', E_USER_ERROR);
                    }
			}

			unset($TemplateBindings);
			foreach($markupHeaders as $mark)
			{
				$tmpStr=$mark->asXML();
				$markup[]=$tmpStr=substr($tmpStr,strpos($tmpStr,'>',15)+1,-16);
			}
			$this->markup = array_merge($this->markup,$markup);
		}
	}

	public function BindContext()
	{
	  $ActiveComponent='';
	  $i=0;   $IsComponent;

	  foreach($this->TemplateBinding as $ComponentName => $Component)
	  {
			//if(gettype(reset($Component)) === gettype('string'))	$i++;
			foreach($Component as $Dataclass => $Properties)
			{
				$context['data'][]=$Dataclass;
				if(!isset($this->options[$ComponentName][$Dataclass]) && isset($this->options[$ComponentName]['*']) ) //Propagate options to all
					$this->options[$ComponentName][$Dataclass]=$this->options[$ComponentName]['*'];
				if(!isset($this->options[$ComponentName][$Dataclass])){
					$this->options[$ComponentName][$Dataclass] = array();
			}
		}

		$context['self']=&$this;
		$context['render']=$this->id;
		$context['options']=$this->options[$ComponentName];
		$context['template']=$this->options['template'];

		$this->context[$ComponentName]=$context;
	  }
	}

	public function Tokenize()
	{
	  foreach($this->TemplateBinding as $ActiveComponent => $Dataclasses)
		foreach($Dataclasses as $ActiveDataclass => $PropertyList){
		  foreach($PropertyList as $ActiveProperty => $NewToken)
			$this->tokens[$NewToken]=$this->data[$ActiveComponent][$ActiveDataclass][$ActiveProperty];
        }
	}

	/* PERFORMANCE LOSS,  FIND OPTIMIZED ARRAY STRING REPLACE */
	function buildContent()
	{
		$this->Tokenize();
		$markupIndex=isset($this->options['markup']) ? $this->options['markup'] : 0;

		if(isset($this->data) && isset($this->markup))
		{
			foreach($this->tokens as $token => $value)
			{
			$this->markup[$markupIndex]=$this->parse( str_replace('[@ '.$token.' @]', $value, $this->markup[$markupIndex]) );
	//                $this->markup[$markupIndex]=$this->parse($this->markup[$markupIndex]);
			}
			$this->content .= $this->markup[$markupIndex];
		}
		parent::buildContent();    //Render Children Into Content with normal build content function
	}
}

?>
