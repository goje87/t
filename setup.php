<?php
class Setup {
  public $step = 1;
  
  public function init() {
    $request = (object)$_REQUEST;
    
    $this->step = isset($request->step)?$request->step:1;
  }
  
  public function nextStep() {
    $urlParts = parse_url(SCRIPT_URI);
    $step = $this->step + 1;
    $redirectUrl = "{$urlParts['scheme']}://{$urlParts['host']}/{$urlParts['path']}?step=$step";
    
    echo "Redirecting...<script language=\"javascript\">window.location = '$redirectUrl';</script>";
  }
}

$setup = new Setup();
$setup->init();
?>
<html>
  <head>
    <title>Tagz Setup</title>
  </head>
  <body>
    <form method="post">
      <div>
        <? include("setup/$setup->step.step.php"); ?>
      </div>
      <input type="submit" name="submit" value="Next &raquo;" />
    </form>
  </body>
</html>