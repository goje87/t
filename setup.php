<?php
class Setup {
  public $step = 1;
  public $hideNextButton = false;
  
  public function init() {
    $request = (object)$_REQUEST;
    
    $this->step = isset($request->step)?$request->step:1;
    $this->app = isset($request->app)?$request->app:'G87';
  }
  
  public function nextStep() {
    $this->goToStep($this->step + 1);
  }
  
  public function finish() {
    $this->goToStep("finish");
  }
  
  public function goToStep($step) {
    // $urlParts = parse_url(SCRIPT_URI);
    // $redirectUrl = "{$urlParts['scheme']}://{$urlParts['host']}{$urlParts['path']}?step=$step";
    
    $redirectUrl = $this->getRedirectUrl(SCRIPT_URI, array("step" => $step));
    
    echo "Please Wait...<script language=\"javascript\">window.location = '$redirectUrl';</script>";
  }
  
  protected function getRedirectUrl($url, $params) {
    // $pairs = array();
    // foreach($params as $key => $value) {
      // $pairs[] = "$key=".urlencode($value);
    // }
    // $queryString = implode("&", $pairs);
    
    $queryString = http_build_query($params);
    
    $urlParts = parse_url($url);
    $redirectUrl = "{$urlParts['scheme']}://{$urlParts['host']}{$urlParts['path']}?$queryString";
    return $redirectUrl;
  }
  
  public function hideNextButton() {
  	$this->hideNextButton = true;
  }
}

$setup = new Setup();
$setup->init();
?>
<html>
  <head>
    <title>Tagz Setup</title>
    <link rel="stylesheet" href="/G87/js/basic.css" />
  </head>
  <body>
    <form method="post">
      <div>
        <? include("setup/$setup->step.step.php"); ?>
      </div>
      <? if(!$setup->hideNextButton && $setup->step != "finish") : ?>
      	<input type="submit" name="submit" value="Next &raquo;" />
      <? endif; ?>
    </form>
  </body>
</html>