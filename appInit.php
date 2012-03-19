<?php
$includeFolders = array(
  "$documentRoot/t",
  get_include_path());
set_include_path(implode(PATH_SEPARATOR, $includeFolders));
?>