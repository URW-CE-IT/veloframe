<?php
/**
 * index.php
 * 
 * WebFramework Starting File - Code Execution begins here.
 * All Web Requests are redirected to this file using the .htaccess in project root.
 * 
 * @author Patrick Matthias Garske <patrick@garske.link>
 * @since 0.1
 */

include_once("WebFramework/autoload.php");

use WebFramework as WF;

$server = new WF\Server();
$server->attachRoutingHandler(new WF\RoutingHandler());
$server->serve();