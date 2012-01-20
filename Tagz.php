<?php
 
class Tagz
{
  protected static $uid = null;
  protected static $regs = array();
  protected static $units = array();
  protected static $filters = array();
  protected static $selectColumns = array("o.`objectId`", "o.`object`"); 
  protected static $selectTable = "`objects`";
  
  public static function init()
  {
  	// Set the default database server.
  	// TODO: Remove the following lines of codes. DB will automatically be set by G87.
  	// DB::setServer((object)array(
  	  // "host" => DB_HOST,
  	  // "username" => DB_USERNAME,
  	  // "password" => DB_PASSWORD,
  	  // "database" => DB_DATABASE));
  	
    $regs = & self::$regs;
    $regs[':key'] = "([\w$]([\w\d$])*)";
    $regs[':value'] = "(\'(([^\']|(?<=\\\\)\')*)\')";
    $regs[':operator'] = "(&|\|)";
    $regs[':bracket'] = "(\(|\))";
    $regs[':pair'] = "(({$regs[':key']}\s*:)?\s*{$regs[':value']})";
    $regs[':filterKey'] = "([a-zA-Z0-9]+)";
    $regs[':filter'] = "(\-\-{$regs[':filterKey']}\s*=\s*{$regs[':value']})";
    
    $filters = & self::$filters;
    
    $filters['selectCol'] = (object) array(
      "enabled" => false,
      "col" => null);

    $filters['orderBy'] = (object) array(
      "enabled" => false,
      "col" => null);
      
    $filters['orderDir'] = (object) array(
      "enabled" => false,
      "dir" => 'ASC');
  }

  public static function selectFromPath($path)
  {    
    // convert the '\' to '/'
    $path = str_replace('\\','/',$path);

    // Extract tagz out of path
    $tags = explode("/",$path);

    // Convert the tagnames from xxxxx to 'xxxxx'
    for($i=0; $i<count($tags); $i++)
    {
      $tags[$i] = "'".$tags[$i]."'";
    }

    // Form the query to be executed
    //$uid = self::$uid;
    $query = "SELECT DISTINCT o.`objectId`, o.`object` FROM `objects` o, `tagMap` m, `tags` t WHERE m.objectId = o.objectId AND m.tagId = t.tagId";
    $query .= " AND t.tagName IN (".implode($tags,',').");";

    // Execute the query
    $result = DB::execQuery($query);
    print_r($result);
    $objects = array();

    // Populate the objects array extracting objects from result
    for($i=0; $i<count($result); $i++)
    {
      $currRow = $result[$i];
      if($i == 0)
        
      // Extract object from result
      $object = json_decode($currRow->object);
      if($i == 0)
       
        
      // Add the objectId to object
      $object->objectId = $currRow->objectId;

      $objects[] = $object;
    }
    
    Utils::printR($objects);

    //Utils::$silentMode = true;
    return $objects;
  }
  
  public static function selectFromQuery($query)
  {
  	$units = self::convertToUnits($query);
  	$sql = self::convertToSql($units);
  	$objects = self::processSql($sql);

  	return $objects;
  }
  
  protected static function convertToUnits($query)
  {
    $units = array();
  	$regs = & self::$regs;
  	
  	$toMatch = array(
  	  ':pair',
  	  ':operator',
  	  ':bracket',
      ':filter');
  	
  	while(ltrim($query))
  	{
  		foreach($toMatch as $matchKey)
  		{
  			$matches = array();
  			if(preg_match("@^\s*{$regs[$matchKey]}@", ltrim($query), $matches))
  			{
  			  $units[] = self::processTokenForMatchKey($matches[0], $matchKey);
          
          $query = preg_replace("@^\s*{$regs[$matchKey]}@", "", ltrim($query));
          break;
  			}
  			
  		}
  	}
    
    return $units;
  }
  
  protected static function processTokenForMatchKey($token, $matchKey)
  {
    $unit = new stdClass();
    $regs = self::$regs;
    
    $unit->token = $token;
    $unit->matchKey = $matchKey;
    
    if($matchKey == ':pair')
    {
      $keyMatches = array();
      if(preg_match("@^{$regs[':key']}@", $token, $keyMatches))
      {
        $token = preg_replace("@^{$regs[':key']}\s*:\s*@", "", $token);
      }
      
      if(!$keyMatches) $key = 'tags';
      else $key = $keyMatches[0];

      $valueMatches = array();
      if(preg_match("@^{$regs[':value']}@", $token, $valueMatches))
      {
        //$value = preg_replace(array("@^\'@", "@\'$@"), "", $valueMatches[0]);
        $value = $valueMatches[0];
      }
      
      $unit->key = $key;
      $unit->value = $value; 
    }
    
    if($matchKey == ':filter')
    {
      $matches = array();
      if(preg_match("@^\-\-{$regs[':filterKey']}\s*=\s*@", $token, $matches))
      {
        $key = $matches[1];
        $token = preg_replace("@^\-\-{$regs[':filterKey']}\s*=\s*@", "", $token);
      }
      
      if(preg_match("@^{$regs[':value']}@", $token, $matches))
      {
        $value = $matches[0];
      }
      
      $unit->key = $key;
      $unit->value = $value; 
    }
    
     return $unit;
  }
  
  protected static function convertToSql($units)
  {
    $joins = array();
    $conditions = array();
    
    //foreach($units as $unit)
    for($i=0; $i<count($units); $i++)
    {
      $unit = $units[$i];
      //Utils::$silentMode = false;
      //Utils::printLine($unit->matchKey);
      
      switch($unit->matchKey)
      {
        case ':bracket':
          $conditions[] = $unit->token;
          break;
          
        case ':operator':
          if($unit->token == '&')
          {
            $conditions[] = "AND";
          }
          else if($unit->token == '|')
          {
            $conditions[] = "OR";
          }
          break;
          
        case ':pair':
          if($unit->key == 'objectId')
          {
            $conditions[] = "o.`objectId` = {$unit->value}";
          }
          else
          {
            $joins[] = "LEFT OUTER JOIN `objectIndex` c$i ON o.`objectId` = c$i.`objectId` AND c$i.`property`='{$unit->key}'";
            $conditions[] = "c$i.`value` = {$unit->value}";
          }
          break;
          
        case ':filter':
          if($unit->key == 'selectCol')
          {
            $filter = self::$filters['selectCol'];
            $filter->enabled = true;
            $filter->col = $unit->value;
            
            self::$selectColumns = array("o.`value`");
            self::$selectTable = "`objectIndex`";
            
            if(count($conditions) > 0)
            {
              $conditions[] = "AND";
            }
            
            $conditions[] = "o.`property` = {$unit->value}";
          }

          if($unit->key == 'orderBy')
          {
            $filter = self::$filters['orderBy'];
            $filter->enabled = true;
            $filter->col = "f$i.`value`";

            $joins[] = "LEFT OUTER JOIN `objectIndex` f$i ON o.`objectId` = f$i.`objectId` AND f$i.`property`={$unit->value}";
          }
          
          if($unit->key == 'orderDir')
          {
            $filter = self::$filters['orderDir'];
            $filter->enabled = true;
            
            // $unit->value is of the form 'xxx'. We need to remove the single quotes from beginning and end.
            $value = substr($unit->value, 1); // removes the single quote from beginning.
            $value = substr($value, 0, -1); // removes the single quote from end.
            
            switch(strtolower($value))
            {
              case 'a':
              case 'asc':
              case 'ascending':
                $dir = 'ASC';
                break;
              
              case 'd':
              case 'desc':
              case 'descending':
                $dir = 'DESC';
                break;
                
              default: 
                $dir = 'ASC';
            }
            
            $filter->dir = $dir;
          }
          break;
          
        default:
          break;
      }
    }
    
    $columnString = implode(", ", self::$selectColumns);
    $table = self::$selectTable;
    $joinString = implode(" ", $joins);
    $conditionString = implode(" ", $conditions);
    //$sqlQuery = "SELECT DISTINCT o.`objectId`, o.`object` FROM `objects` o $joinString WHERE $conditionString ;";
    $sqlQuery = "SELECT DISTINCT $columnString FROM $table o $joinString WHERE $conditionString ";
    $orderBy = self::$filters['orderBy'];
    if($orderBy->enabled == true)
    {
      $sqlQuery .= " ORDER BY {$orderBy->col}";
      
      $orderDir = self::$filters['orderDir'];
      if($orderDir->enabled == true)
      {
        $sqlQuery .= " {$orderDir->dir}";
      }
    }
    //Utils::$silentMode = false;
    //Utils::printLine($sqlQuery);
     
    
    return $sqlQuery;
    
  }

  protected static function processSql($sql)
  {
    $result = DB::execQuery($sql);
    
    if($result->error)
    {
      return $result;
    }
    
    $objects = array();
    
    foreach($result as $row)
    {
      $object = json_decode($row->object);
      $selectCol = self::$filters['selectCol'];
      if(!$selectCol->enabled)
      {
        $object->objectId = $row->objectId;
        $objects[] = $object;
      }
      else
      {
        $objects[] = $row->value;
      }
    }
    
    return $objects;
  }

  public static function create($objJson)
  {
    $uid = FB::getUserId();
    
    // User needs to be signed in to create an object. 
    if(!$uid) return self::userNotLoggedInError();
    
    // Decode object Json
    $object = json_decode($objJson);
    $object->user = $uid;

    // TODO: Remove self::insertTagsQuery()
    /*// Query for `tags` table
    if($object->tags)
    {
      $tagsQuery = self::insertTagsQuery($object->tags);
    }*/
    
    // Prepare query for `objects` table
    $objectsQuery = "";
    $object->createdOn = time();
    $objectJson = DB::escapeString(json_encode($object));
    $type = $object->type;
    
    $objectsQuery = "INSERT INTO `objects` (`object`, `uid`) VALUES ('$objectJson', '$uid');";

    $indexStmnt = self::AddToIndexQuery($object, 'LAST_INSERT_ID()');
    
    /*
    // Prepare query for adding the tag mappings to `tagMap` table
    $tabMapQuery = "";

    //Converting the tags from xxxxx to 'xxxxx'
    $tags = $object->tags;
    for($i=0; $i<count($tags); $i++)
    {
      $tags[$i] = "'".$tags[$i]."'";
    }

    // Forming the query
    $tagsJoin = implode($tags, ',');
    $tagMapQuery = "INSERT INTO `tagMap` (`tagId`, `objectId`) SELECT `tagId`, LAST_INSERT_ID() FROM `tags` WHERE `tagName` IN ($tagsJoin);";
    */
    // Execute all the queries 
    $finalQuery = $objectsQuery."\n".$indexStmnt;
    $response = DB::execQuery($finalQuery, true);
    
    return $response;
  }
  
  public static function deleteById($id)
  {
    $uid = FB::getUserId();
    
    // User needs to be signed in to delete an object. 
    if(!$uid) return self::userNotLoggedInError();
    
    
    // Query to delete the rows from objects table
    // for the passed $id
    $deleteObjectQuery = 
      "DELETE FROM `objects`" .
      "  WHERE `objects`.`objectId` = $id" .
      "  AND `objects`.`uid` = '$uid';";
      
    $removeFromIndexQuery = self::removeFromIndexQuery($id);
      
    $response = DB::execQuery($deleteObjectQuery.$removeFromIndexQuery, true); 
    return $response;
  }
  
  public static function update($objJson)
  {
    // TODO: user needs to own the object that he's updating.
    // TODO: Create Error class.
    // TODO: Have a TagzError (extending Error) class to define errors related to tagz.
    
    $uid = FB::getUserId();
    
    // User needs to be signed in to update an object. 
    if(!$uid) return self::userNotLoggedInError();
    
    // Decode object Json
    $object = json_decode($objJson);

    $objectId = $object->objectId;
    
    // update `objects` table
    $type = $object->type;
    $objectJson = DB::escapeString(json_encode($object));
    $objectsQuery = 
      "UPDATE `objects`" .
      "  SET `object` = '$objectJson'," .
      "    `uid` = $uid" .
      "  WHERE `objects`.`objectId` = $objectId;";
      
    $removeFromIndexQuery = self::removeFromIndexQuery($objectId);
    $addToIndexQuery = self::addToIndexQuery($object, $objectId);
      
    // Form the final query and execute it
    $finalQuery = $objectsQuery.$removeFromIndexQuery.$addToIndexQuery;
    return DB::execQuery($finalQuery, true);
  }
  
  private static function insertTagsQuery($tags)
  {
    // Conert the tags to lower case and also from format xxxxx to ('xxxxx') for its usage in query
    for($i=0; $i<count($tags); $i++)
    {
      $tags[$i] = "('".strtolower($tags[$i])."')";
    }

    // Form the query for `tags` table
    $tagsQuery = "INSERT INTO `tags` (`tagName`) VALUES ".implode($tags,',')." ON DUPLICATE KEY UPDATE `tagName` = `tagName`;";
    
    return $tagsQuery;
  }
  
  private static function userNotLoggedInError()
  {    
    if(!FB::getUserId())
    {
      $response = new stdClass();
      $response->error = true;
      $response->message = "User needs to be logged in.";
      
      return $response;
    }
  }
  
  private static function AddToIndexQuery($object, $objectId)
  {
    $indexStmnts = Array();
    $indexStmnt = "";
    
    foreach($object as $property => $value)
    {
      if($property == 'objectId')
        continue;
      
      $property = DB::escapeString($property);
      $value = DB::escapeString($value);
      if(is_string($value))
      {
        $indexStmnts[] = 
          "INSERT INTO `objectIndex` (`objectId`, `property`, `value`)".
          " VALUES ($objectId, '$property', '$value');";
      }
      else if(is_array($value))
      {
        foreach($value as $currValue)
        {
          $indexStmnts[] = 
            "INSERT INTO `objectIndex` (`objectId`, `property`, `value`)".
            " VALUES ($objectId, '$property', '$currValue');";
        }
      }
    }
    
    if(count($indexStmnts) > 0)
    {
      $indexStmnt = implode("", $indexStmnts);
      return $indexStmnt;
    }
    //Utils::$silentMode = true;
    return "";
    
  }
  
  private static function removeFromIndexQuery($objectId)
  {
    $query = "DELETE FROM `objectIndex` WHERE `objectId` = $objectId;";
    return $query;
  }
  
  public static function getUserId() {
    FB::getUserId();
  }
}

Tagz::init();
?>
