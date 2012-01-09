<?php
class Setup {
  public $step = 1;
  public $hideNextButton = false;
  
  public function init() {
    $request = (object)$_REQUEST;
    
    $this->step = isset($request->step)?$request->step:1;
  }
  
  public function nextStep() {
    // $urlParts = parse_url(SCRIPT_URI);
    // $step = $this->step + 1;
    // $redirectUrl = "{$urlParts['scheme']}://{$urlParts['host']}{$urlParts['path']}?step=$step";
//     
    // echo "Please Wait...<script language=\"javascript\">window.location = '$redirectUrl';</script>";
    $this->goToStep($this->step + 1);
  }
  
  public function finish() {
    $this->goToStep("finish");
  }
  
  public function goToStep($step) {
    $urlParts = parse_url(SCRIPT_URI);
    $redirectUrl = "{$urlParts['scheme']}://{$urlParts['host']}{$urlParts['path']}?step=$step";
    
    echo "Please Wait...<script language=\"javascript\">window.location = '$redirectUrl';</script>";
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