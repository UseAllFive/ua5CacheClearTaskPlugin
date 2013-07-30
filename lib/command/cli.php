<?php
//-- 100% ripoff of vendor/symfony/symfony1/command/cli.php
try
{
  $dispatcher = new sfEventDispatcher();
  $logger = new sfCommandLogger($dispatcher);

  $application = new ua5CommandApplication($dispatcher, null, array('symfony_lib_dir' => './'));
  $statusCode = $application->run();
}
catch (Exception $e)
{
  if (!isset($application))
  {
    throw $e;
  }

  $application->renderException($e);
  $statusCode = $e->getCode();

  exit(is_numeric($statusCode) && $statusCode ? $statusCode : 1);
}

exit(is_numeric($statusCode) ? $statusCode : 0);
