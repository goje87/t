<?php
$request = (object) $_REQUEST;
if($request->submit) {
  $appConfigPath = APP_ROOT."/appConfig.json";
  $appConfigFile = fopen($appConfigPath, "w");
  
  $config = new stdClass();
  $config->DB_HOST = $request->host;
  $config->DB_USERNAME = $request->username;
  $config->DB_PASSWORD = $request->password;
  $config->DB_DATABASE = $request->database;
  
  Logger::info("Going to write config: ", $config);
  Logger::info("$appConfigPath");
  fwrite($appConfigFile, json_encode($config));
  fclose($appConfigFile);
  $setup->nextStep();
  return;
}
?>
<h1>
  Set Database for Tagz.
</h1>
<table border="0">
  <tr>
    <td>Host:</td>
    <td><input type="text" name="host" value="<?= DB_HOST ?>"</td>
  </tr>
  <tr>
    <td>Username:</td>
    <td><input type="text" name="username" value="<?= DB_USERNAME ?>"</td>
  </tr>
  <tr>
    <td>Password:</td>
    <td><input type="password" name="password" value="<?= DB_PASSWORD ?>"</td>
  </tr>
  <tr>
    <td>Database:</td>
    <td><input type="text" name="database" value="<?= DB_DATABASE ?>"</td>
  </tr>
</table>