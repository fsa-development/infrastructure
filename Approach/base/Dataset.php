<?php

/*
	Title: Dataset Class for Approach


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

/*

NOTICE THIS IS THE MYSQLI RELEASE CANDIDATE OF DATASET

IF YOU NEED PRODUCTION USAGE, USE MSSQL OR THOUROUGHLY TEST MYSQLI VERSION FOR NOW
MONGODB, REDIS and XML FILE CONNECTORS ON THE WAY - DESIGNING CONNECTOR ARCHITECTURE CURRENTLY

Request For Comments:
1. 	How can we make the search functionality stronger, easier to batch, more intuitive and/or more generic?

*/

global $db;
global $RuntimePath;
global $DataPath;

if(!isset($db))
{
	include_once(__DIR__.'/../__config_error.php');
	include_once(__DIR__.'/../__config_database.php');
	if(!isset($db))
	{
		include_once('__config_error.php');
		include_once('__config_database.php');
		if(!isset($db)) die('No database selected');
	}
}
if(!isset($RuntimePath)) $RuntimePath=$_SERVER['DOCUMENT_ROOT'];	//Included from core.php?

$tableName='NULL TABLE';
$currentTable;


//UpdateSchema();

class Dataset
{
    public $data,$options;

    function Dataset($t, $options=array())
    {
        global $tableName;
        global $currentTable;
        global $db;

        $this->table = get_class($this);

        $queryoverride = 'NULL';

        /* Default to selecting top 10 rows of the database */
        /* To Do: Default to all if !$ApproachDebugMode ? */

        $command='SELECT ';
        $range='* ';
        $target= isset($t)? $t : get_class($this);
        $method='';
        $condition='';

        /*  Override All Data Search Options If Available */

        if(isset($options['command'])) $command = $options['command'];
        if(isset($options['range'])) $range = $options['range'];
        if(isset($options['target'])) $target = $options['target'];
        if(isset($options['method'])) $method = $options['method'];
        if(isset($options['condition'])) $condition = $options['condition'];
        if(isset($options['queryoverride'])) $queryoverride = $options['queryoverride'];

        if($condition !== '' && $method === '') $method = 'WHERE';

        /* Set Options Explicitly To Dynamic Commands For Certain Use Cases If They Weren't There Before' */

        $options['command']         = $command ;
        $options['range']           = $range;
        $options['target']          = $target;
        $options['method']          = $method;
        $options['condition']       = $condition;
        $options['queryoverride']   = $queryoverride;

        /* Prepare  SQL Query And Ask The Database */

	//operator + properties FROM target + method + condition

        $buildQuery = $command .' '. $range .' FROM '. $target .' '. $method .' '. $condition;
        if($queryoverride != 'NULL') $buildQuery = $queryoverride;
        $options['queryoverride']=$buildQuery;
        if(isset($options['debug'])) print_r('<br>'.PHP_EOL.$buildQuery.PHP_EOL.'<br>');

        if($tableName!=$t || isset($options['new_query']) ) //Already on the right table? Don't restart the query! D:
        {
            $currentTable=$db->query($buildQuery);
            $tableName=$t;
            unset($options['new_query']);
        }

        $this->table = $t;

        /* Store Options For Context, To Do: Move all $table, $key and $options into $this->___context again */
        $this->options=$options;
    }

    function onLoad(){}
    function onSave(){}

    function load() //Individual MySQLset->load() will set that MySQLset to last result of current query when $newRow is replaced with $this
    {
        global $currentTable;
        global $tableName;

        if($currentTable && $currentTable !== true)
        {
			if($currentTable)
				$data = mysqli_fetch_assoc($currentTable);

            $this->options['debug']=null;
            if(class_exists($tableName)) $newRow = new $tableName($tableName, $this->options);
            else $newRow = new Dataset($tableName, $this->options);   //To Do: Move to Load Objects

            if(is_array($data))
            {
              $newRow->data = $data;
              $newRow->onLoad();
              return $newRow;
            } else{  return false;  }
        }else{ return false; }
    }
    function save($primaryValue=NULL)  //call this function after using the new update() function. it will save changes on the php object to database.
    {
        global $RuntimePath;
		global $db;

	    //TO DO: Refactor into disassemble() & save() --or-- save() & commit() --or-- something.
            /*
             *TO DO: Unify key associations to a standard mapping via connectors
             *Example: require_once('/.../support/datasets/mongodb3_01/geoDB_local/Countries.php');

            $SubsetName = $Aspects['DATABASE_TECH'].'/'.$Aspects['DATABASE_CONTAINER'].'/'. $Aspects['DATASET'];
            if(!in_array($SubsetName,$SubsetsSaved))	$SubsetsSaved[]=$SubsetName;
             */

        if(isset($this->profile['Accessor']['Reference'])) //PrimaryKey == '<Accessor="Inherited" />')
        {
            $SubsetsSaved=array();
            foreach($this->profile['header'] as $Proprety => &$Aspects)
            {
                $SubsetName =($Aspects['TABLE_SCHEMA']=='information_schema'? 'schema/':'').$Aspects['TABLE_NAME'];
                if(!in_array($SubsetName,$SubsetsSaved))	$SubsetsSaved[$SubsetName][]=$Aspects[''];
            }

            foreach($SubsetsSaved as $SubsetName)
            {
                    require_once($RuntimePath . '/support/datasets/'. $SubsetName . '.php');
                    $SubsetOrigin = new $Aspects['TABLE_NAME']($Aspects['TABLE_NAME']);

                    foreach($this->data as $Proprety => &$Value)
                            if($this->profile['header'][$Property]['TABLE_NAME'])	$Subset->data[$Property] = $this->data[$Property];
                    $SubsetOrigin->save($primaryValue); //Down the pipe you go
            }
        }
        else
        {
          $valuePairs ='';
          $SerializedProperties ='';
          $SerializedValues ='';

          $me = get_class($this);
          $primarykey=$me::$profile['Accessor']['Primary'];
          if(isset($primaryValue)) $this->data[$primarykey] = $primaryValue;
          foreach($this->data as $key => $value)
          {
            //if($key != $this::$profile['Accessor']['Primary'])
            if($value != '' && isset($value) && !is_array($value) )
            {
                $val=(is_string($value) ? '\'' . ms_escape_string($value) . '\', ' : $value.', ');

                $valuePairs .= ' `'. $key .'` = '.$val;
                $SerializedProperties .= '`'.$key .'`, ';
                $SerializedValues .= $val;
            }
          }
          $valuePairs=substr($valuePairs, 0, -2);
          $SerializedProperties=substr($SerializedProperties, 0, -2);
          $SerializedValues=substr($SerializedValues, 0, -2);
	      $query='INSERT INTO '. $this->table . ' ( ' . $SerializedProperties . ') VALUES( ' . $SerializedValues . ') ON DUPLICATE KEY UPDATE '. $valuePairs. ';' ;
          $result = mysqli_query($db,$query);
          if(isset($this->options['debug'])) print_r($query);
          if($result) $this->data[$this::$profile['Accessor']['Primary']]=mysqli_insert_id($db);
    	}
        $this->onSave();
        return $this->data;
    }
}

function DataclassError($errno, $errstr, $errfile, $errline, array $errcontext)
{
    if (0 === error_reporting()) {        return false;    }
    else throw new ErrorException($errstr, 0, $errno, $errfile, $errline);
}

function LoadObjects($table, $options=Array())
{
    global $DataPath;
    global $DatasetMissing;
    $Container=Array();
    $currentRow;

    //Look For Generated DataBase Object File, If Not There Try To Make One

    try
    {
        if(!include_once $DataPath.'/'. $table . '.php') throw new ErrorException('Data missing definition');
        else $currentRow = new $table($table, $options);
    }
    catch(ErrorException $e)
    {
	try
	{
	    UpdateSchema(); //TODO: Refactor Into Single Collection Updating
	    if(!include_once $DataPath.'/'. $table . '.php') throw new ErrorException('Data missing definition');
	    else $currentRow = new $table($table, $options);
	}
	catch(ErrorException $e){	exit('SCHEMA FAIL - '. $table . PHP_EOL . '<br />' . PHP_EOL . var_export($options,true) );    }
    }

    //Get That Data !! This Where 3/5 The Magic Happens! =D
    $newRow;

    while($newRow=$currentRow->load())
    {
        $Container[] = $newRow;
    }

    global $tableName;
    $tableName = 'NULL TABLE';
    return $Container;
}

function LoadObject($table, $options=Array())
{
    global $DataPath;
    global $DatasetMissing;
    $Container=Array();
    $currentRow;

    $originalHandler=set_error_handler('DataclassError');

    //Look For Generated DataBase Object File, If Not There Try To Make One
    try
    {
        if(!include_once $DataPath.'/'. $table . '.php') throw new ErrorException('Data missing definition');
        else $currentRow = new $table($table, $options);
    }
    catch(ErrorException $e)
    {
	try
	{
	    UpdateSchema();
	    if(!include_once $DataPath.'/' . $table . '.php') throw new ErrorException('Data missing definition');
	    else $currentRow = new $table($table, $options);
	}
	catch(ErrorException $e){	exit('SCHEMA FAIL - '. $table . PHP_EOL . '<br />' . PHP_EOL . var_export($options,true) );    }
    }

    //Get That Data !! This Where 3/5 The Magic Happens! =D
    $newRow;

    if($newRow=$currentRow->load())
    {
        $Container = $newRow;
    }

    global $tableName;
    $tableName = 'NULL TABLE';
    return $Container;
}










function fileSave($file, $data)
{
    $handle =fopen($file, 'w+');
    fwrite($handle,$data);
    fclose($handle);
}

function SavePHP($dbo,$classpath='')
{
  global $RuntimePath;
    /*
     *	To Do: Move Variables into a public static Dataset::profile map
     */
  $tn = $dbo->table;
  if( class_exists($tn) && isset( $tn::$profile['directives'] ) )
    if(in_array('no_auto_modify', $tn::$profile['directives'] ) )
      return -2;

  $RefersExist=isset($dbo->ForeignKey);

  $LinePrefix="\n\t";
  $theOutput = '<?php '.PHP_EOL.'require_once(__DIR__.\'/../../../../core.php\');'.PHP_EOL.'class '.$dbo->table . ' extends Dataset'.PHP_EOL.'{';

  //TO DO: In C++ this would be public static const, but in PHP we will need to make it protected
  //First will need to make read-only accessor/get function in Dataset and ensure other classes are using it

  $theOutput .= $LinePrefix.'public static $profile= array(' ;
  $theOutput .= $LinePrefix."\t'target' =>'".$dbo->table.'\',';
  if( isset($dbo->PrimaryKey))
  {
	$theOutput .= $LinePrefix."\t'Accessor'=>array( ".($RefersExist?$LinePrefix."\t\t":'').'\'Primary\' => \''.$dbo->PrimaryKey.'\'';
	if($RefersExist)
	{
		$theOutput .= ','.$LinePrefix."\t\t'Reference'=>array( ";//implode('\', \'',$dbo->ForeignKey).'\')';
		foreach($dbo->ForeignKey as $k => $v)
		{
			$theOutput .=' array(\'';
			foreach($v as $v2)
			{
				$theOutput .=implode('\',\'',$v2);
			}
			$theOutput .='\'),';//\''.$k.'\' => array(\''.$a.'\')';
		}
		rtrim($theOutput,',');
		$theOutput .= ')';
	}
	$theOutput .= '),';
  }
  elseif($RefersExist)
  	{
    	$theOutput .= $LinePrefix."\t'Accessor'=>array( ";
        $theOutput .= ','.$LinePrefix."\t\t'Reference'=>array(  ";//implode('\', \'',$dbo->ForeignKey).'\')';
		foreach($dbo->ForeignKey as $k => $v)
		{
			$theOutput .=' array(\'';
			foreach($v as $v2)
			{
				$theOutput .=implode('\',\'',$v2);
			}
			$theOutput .='\'),';//\''.$k.'\' => array(\''.$a.'\')';
		}
		rtrim($theOutput,',');
		$theOutput .= ') )';
	}



  $theOutput .= $LinePrefix."\t'header'=>array( ";
  foreach($dbo->Columns as $col => $aspect)
  {
	$theOutput.=$LinePrefix."\t\t'".$col.'\' => array( ';
	foreach($aspect as $k => $v)
	{
		$theOutput.=' \''.$k.'\' => \''.str_replace('\'','\\\'',$v).'\',';
	}
	rtrim($theOutput,',');
	$theOutput.='),';
  }
  rtrim($theOutput,',');
  $theOutput.=$LinePrefix."\t".')'.$LinePrefix.');';

  $theOutput .= $LinePrefix.'public $data;';
  $theOutput .= PHP_EOL.'}'.PHP_EOL.'?>';

//  print_r($theOutput);	$RuntimePath . '/support/datasets/'

  if (!file_exists($RuntimePath .'/support/datasets/' .$classpath))    mkdir($RuntimePath .'/support/datasets/' .$classpath, 1770, true);
  fileSave($RuntimePath . '/support/datasets/' .$classpath.'/'. $dbo->table . '.php', $theOutput);
}


function ms_escape_string($data)
{
    if ( !isset($data) or empty($data) ) return '';
    if ( is_numeric($data) ) return $data;

    $non_displayables = array(
	'/%0[0-8bcef]/',            // url encoded 00-08, 11, 12, 14, 15
	'/%1[0-9a-f]/',             // url encoded 16-31
	'/[\x00-\x08]/',            // 00-08
	'/\x0b/',                   // 11
	'/\x0c/',                   // 12
	'/[\x0e-\x1f]/'             // 14-31
    );
    foreach ( $non_displayables as $regex )
	$data = preg_replace( $regex, '', $data );
    $data = str_replace("'", "''", $data );
    return $data;
}


function LoadDirect($query,$t='information_schema')
{
    $connection=new Dataset($t,array('queryoverride'=>$query));
    $newRow; $Container=array();

    while($newRow=$connection->load()){	$Container[] = $newRow;    }

    return $Container;
}

function UpdateSchema()
{
  //need switch() case: for database type [MySQL, MSSQL, Mongo, Redis, Parsyph, Hadoop, Cassandra]
  $InfoSchemaDatabaseColumn='TABLE_SCHEMA';

  $sql='SELECT TABLE_CATALOG, TABLE_SCHEMA, TABLE_NAME, COLUMN_NAME, ORDINAL_POSITION, COLUMN_DEFAULT, IS_NULLABLE, DATA_TYPE FROM INFORMATION_SCHEMA.COLUMNS';

  $spread=array();
  $DataObjects=array();
  $schemainfo=LoadDirect($sql,'INFORMATION_SCHEMA.COLUMNS');

  foreach($schemainfo as $SchemaRow)
  {
    $schemas[$SchemaRow->data['TABLE_SCHEMA']][$SchemaRow->data['TABLE_NAME']][$SchemaRow->data['COLUMN_NAME']]=$SchemaRow->data;
  }

  foreach($schemas as $current_db => $spread)
  foreach($spread as $table => $columns)
  {
    //Cross-Database Discrepency : MySQL uses quotes, MSSQL uses N
    $sql='SELECT * FROM INFORMATION_SCHEMA.KEY_COLUMN_USAGE WHERE `TABLE_NAME` = "'.$table.'";';
    $findKeys=LoadDirect($sql,'INFORMATION_SCHEMA.KEY_COLUMN_USAGE');

    $sql='SELECT * FROM INFORMATION_SCHEMA.VIEW_COLUMN_USAGE WHERE `VIEW_NAME` = "'.$table.'";';
    $keyProperties=LoadDirect($sql,'INFORMATION_SCHEMA.VIEW_COLUMN_USAGE');

    $dObj = new stdClass();

//	var_dump($keyProperties);
    foreach($findKeys as $row)
    {
        $str = explode('_',$row->data['CONSTRAINT_NAME']);
//		if($table == 'compositions'){ var_export($row); }
        if($str[0] == 'PRIMARY')
            $dObj->PrimaryKey = $row->data['COLUMN_NAME'];
        else
            $dObj->ForeignKey[]=array($row->data['COLUMN_NAME']=>array($row->data['REFERENCED_TABLE_SCHEMA'],$row->data['REFERENCED_TABLE_NAME'],$row->data['REFERENCED_COLUMN_NAME']));
    }

    $t=array();
    foreach($keyProperties as $View)
    {
      if($View === reset($keyProperties) )
      {
        $t = $spread[$table];
        $spread[$table]=array();
      }
      $spread[$table][$View->data['TABLE_NAME']][$View->data['COLUMN_NAME']] = array_merge($spread[$View->data['TABLE_NAME']][$View->data['COLUMN_NAME']], $View->data);
    }

    $dObj->Columns = $spread[$table];
    $dObj->table = $table;

    $classpath='';
    foreach($spread[$table] as $column )
    {
        $classpath='schema/'.$current_db;   //wth?
        break;
    }

    SavePHP($dObj,  $classpath);
  }
}



/* Type checking ugly draft


    $proto=[];

    foreach($reports as &$report){
        $ContainerContent->children[] = $bullet_list = new renderable('ul');
        foreach($report as $k=>&$v){
            $v = substr($v,0,72);
            $bullet_list->children[] = new renderable(['tag'=>'li','content'=>$k.': '.$v]);

            if(!empty(trim($v))){
                if(isset($proto[$k])){
                    if(is_numeric($proto[$k])){
                        if($proto[$k] < 0){
                            if($proto[$k] > $v){ $proto[$k]=$v; }
                        }
                        else if($v > $proto[$k]){ $proto[$k] = $v; }
                    }
                    else if(!isDateTime($v,'Y-m-d H-m-s e',	'America/Chicago') && is_string($v))
                        if(count( $proto[$k] ) < count($v) )
                            $proto[$k] = $v;
                }
                else $proto[$k] = $v;
            }
        }
    }        //$ContainerContent->children[] = new renderable(['tag'=>'div','content'=>var_export($report,true)]);

    $query = 'CREATE TABLE `products` ( '.
            '`id` BIGINT(20) UNSIGNED NOT NULL AUTO_INCREMENT, '.
            '`amazon-order-id` VARCHAR(24) NOT NULL DEFAULT \'0\' COLLATE \'utf8_bin\', '.
            '`merchant-order-id` VARCHAR(24) NOT NULL DEFAULT \'0\' COLLATE \'utf8_bin\', '.
            '`purchase-date` TIMESTAMP NULL DEFAULT NULL, '.
            '`last-update-date` TIMESTAMP NULL DEFAULT NULL'.PHP_EOL;

    foreach($proto as $k => $v)
    {
        $type=$unsign=$nullable=$default=$comment=$collate=$expression=$virtual='';
        $default = 'DEFAULT 0';
        if(is_numeric($v)){
            if(is_int($v)){
                $type = 'BIGINT(20)';
                $default = 'DEFAULT 0';
                if($v >= 0) $unsigned='UNSIGNED';   //else signed
            }
            else if(is_float($v)){
                if( isDecimal($v) ) // 1 or 2 character decimals
                    $type = 'DECIMAL(10)';
                else                // various float types
                    $type = 'FLOAT';
                $default = 'DEFAULT 0.00';
                if($v >= 0) $unsigned='UNSIGNED';
            }
        }
        else if(isDateTime($v,'Y-m-d H-m-s e',	'America/Chicago')){
            $type = 'TIMESTAMP';
            $default='DEFAULT NULL';
        }
        else if(is_string($v)){

            $type = 'VARCHAR';
            $collate='COLLATE \'utf8_bin\'';
        }

        $q = '`'.$k.'` '.$type.' '.$unsign.' '.$nullable.' '.$default.' '.$comment.' '.$collate.' '.$expression.' '.$virtual.', '.PHP_EOL;
        $query.=$q;
    }

    $query .= '	PRIMARY KEY (`id`) ) COLLATE=\'utf8_bin\' ENGINE=MyISAM;';

*/


?>
