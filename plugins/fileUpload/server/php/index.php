<?php
/*
 * jQuery File Upload Plugin PHP Example 5.14
 * https://github.com/blueimp/jQuery-File-Upload
 *
 * Copyright 2010, Sebastian Tschan
 * https://blueimp.net
 *
 * Licensed under the MIT license:
 * http://www.opensource.org/licenses/MIT
 */
require_once("../../../../systemclasses/Class.mainconfig.php");
require_once("../../../../systemclasses/Class.dbconnect.php");
require_once("../../../../systemclasses/Class.url.php");
require_once("../../../../systemclasses/Class.login.php");
session_start();
if(!login::check_login())
{
	exit();
}
error_reporting(E_ALL | E_STRICT);
require('UploadHandler.php');
$upload_handler = new UploadHandler();
