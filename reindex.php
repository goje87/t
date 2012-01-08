<?php
require_once '../common/php/Utils.php';
require_once 'Tagz.php';
require_once('../common/php/DB.php');
require_once('./dbConfig.php');

// select all objects from the `objects` table and run the update function of Tagz class on it.

$selectQuery = "SELECT `objectId`, `object` FROM `objects`;";

$result = DB::execQuery($selectQuery);

Utils::$silentMode = false;
Utils::printLine('Updating objects...');

for($i=0; $i<count($result); $i++)
{
  $currRow = $result[$i];
  $object = json_decode($currRow->object);
  $object->objectId = $currRow->objectId;
  Utils::printLine('Object Id: '.$object->objectId);
  Tagz::update(json_encode($object));
}

Utils::printLine('Update process completed.');