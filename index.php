<?php

// redirect to the respective php script if a .php file was entered.
if(preg_match("/(\.(php|js))$/i", $_SERVER['REDIRECT_URL'])) {
  // preg_match("/([a-z0-9_\-]+\.php)$/i", $_SERVER['SCRIPT_URL'], $matches);
  // $script = $matches[0];
  $script_url = $_SERVER['REDIRECT_URL'];
  $script = str_replace("/t/", "", $script_url);
  include($script);
  return;
}

Utils::$silentMode = true;

// Filter out based on the request type
$reqMethod = $_SERVER['REQUEST_METHOD'];
$data = null;

switch($reqMethod)
{
  case 'GET':
    selectObjects();
    break;
  case 'POST':
    $data = (object) $_POST;
    createObject();
    break;
  case 'PUT':
    parse_str(file_get_contents('php://input'), $update_vars);
    $data = (object) $update_vars;
    updateObject();
    break;
  case 'DELETE':
    parse_str(file_get_contents('php://input'), $delete_vars);
    $data = (object) $delete_vars;
    deleteObject();
    break;
}

function selectObjects()
{
  if($_GET['exec'])
  {
    include($_GET['exec']);
    return;
  }
  
  if($_GET['q'])
  {
    Utils::outputResponse(Tagz::selectFromQuery($_GET['q']));
    return;
  }
  // // Get the directory path
  // $dirPath = $_SERVER['SCRIPT_URL'];
// 
  // // trim off the '/t/' from the string
  // $tagPath = preg_replace("/^\/t\//",'',$dirPath);
// 
  // Utils::outputResponse(Tagz::selectFromPath($tagPath));
}

function createObject()
{
  global $data;
  // Get the object string
  $objString = $data->obj;
  Utils::outputResponse(Tagz::create($objString));
}

function deleteObject()
{
  global $data;
  Utils::outputResponse(Tagz::deleteById($data->id));
}

function updateObject()
{
  //Utils::$silentMode = false;
  global $data;
  $objString = $data->obj;
  Utils::printR($objString);
  Utils::outputResponse(Tagz::update($objString));
  //Utils::$silentMode = true;
}

/*
$_SERVER['SCRIPT_URL'] => gives the directory path without the query string (eg. /tagz/)
$_SERVER['QUERY_STRING'] => Gives the query string in the url (eg. name=abhilash&age=23)
$_SERVER['REQUEST_METHOD'] => Gives the request method (eg. GET)
*/
?>
