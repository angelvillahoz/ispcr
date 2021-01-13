<?php
// Bootstrap with the new bootstrap.php file.
require_once(__DIR__ . "/../lib/bootstrap.php");
// Set up the include path
$include_path  = ini_get("include_path");
$include_path .= ":" . $GLOBALS["options"]->general->site_base_dir . "/lib";
ini_alter("include_path", $include_path);
// Set up the session.  This must happen after the autoloader is defined in case
// any objects are stored in the session.
if ( php_sapi_name() !== "cli" ) {
   // HTTP/1.1
  header("Cache-Control: no-cache, must-revalidate");
   // Date in the past
  header("Expires: Sat, 26 Jul 1997 05:00:00 GMT");
}
?>
