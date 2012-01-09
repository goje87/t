<?php
$request = (object) $_REQUEST;
$creatingTables = false;

if($request->createTables) {
  $creatingTables = true;
  createTagzTables();
  $setup->finish();
} else if($request->submit) {
  $setup->finish();
} else {
  $setup->hideNextButton();
}

function createTagzTables() {
  $query_objects = "CREATE TABLE `objects` (                                                                                
           `objectId` int(11) unsigned NOT NULL AUTO_INCREMENT COMMENT 'Autogenerated unique id for an object',  
           `object` text NOT NULL COMMENT 'the object representation',                                           
           `typeId` bigint(20) NOT NULL COMMENT 'Tag Id that indicates the type of the object',                  
           `createdOn` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,                                             
           `uid` varchar(20) NOT NULL COMMENT 'The associated user of the object',                               
           PRIMARY KEY (`objectId`)                                                                              
         ) ENGINE=MyISAM AUTO_INCREMENT=93 DEFAULT CHARSET=utf8 COMMENT='Table that holds all objects.' ;";
  
  $query_objectIndex = "CREATE TABLE `objectIndex` (                    
               `objectId` int(11) NOT NULL,                  
               `property` tinytext NOT NULL,                 
               `value` text NOT NULL,                        
               FULLTEXT KEY `property` (`property`,`value`)  
             ) ENGINE=MyISAM DEFAULT CHARSET=utf8;";
             
  $query = $query_objects.$query_objectIndex;
  DB::execQuery($query, true);
}
?>
<h1>
  Create Tables for Tagz
</h1>
<? if(!$creatingTables) : ?>
  <p>
    Click the button below if you would like to proceed with this step.
  </p>
  <input type="submit" name="createTables" value="Go ahead and create tables" />
<? else: ?>
  <p>
  	If the page does not automatically move to the next step, please click the next button below.
  </p>
<? endif; ?>

