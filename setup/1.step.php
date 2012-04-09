<?php
class Step extends SetupStep {
  public function execute() {
    $request = (object) $_REQUEST;
    
    $appConfigPath = G87_DOCUMENT_ROOT."/".Setup::$app."/appConfig.json";
    $appConfigFile = fopen($appConfigPath, "w+");
    
    $config = new stdClass();
    $config->DB_HOST = $request->host;
    $config->DB_USERNAME = $request->username;
    $config->DB_PASSWORD = $request->password;
    $config->DB_DATABASE = $request->database;
    
    Logger::info("Going to write config: ", $config);
    Logger::info("$appConfigPath");
    fwrite($appConfigFile, json_encode($config));
    fclose($appConfigFile);
    Setup::nextStep(); 
  }
}
?>
