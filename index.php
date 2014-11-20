<?php

// Initialize core
$f3=require("lib/base.php");
$f3->mset(array(
	"UI" => "app/view/",
	"LOGS" => "log/",
	"TEMP" => "tmp/",
	"CACHE" => true,
	"AUTOLOAD" => "app/",
	"PACKAGE" => "TinyStats",
	"microtime" => microtime(true),
	"site.url" => $f3->get("SCHEME") . "://" . $f3->get("HOST") . $f3->get("BASE") . "/",
	"menuitem" => false,
));

// Require configuration file
if(!is_file("config.ini")) {
	throw new Exception("App is not installed. Please create a config.ini file.");
}

// Load configuration
if(!is_file("config-base.ini")) {
	$f3->config("config-base.ini");
}
$f3->config("config.ini");

// Load routes
$f3->config("app/routes.ini");

// Set up error handling
$f3->set("ONERROR", function(Base $f3) {
	switch($f3->get("ERROR.code")) {
		case 404:
			$f3->set("title", "Not Found");
			$f3->set("ESCAPE", false);
			echo Template::instance()->render("error/404.html");
			break;
		case 403:
			echo "You do not have access to this page.";
			break;
		default:
			if(ob_get_level()) {
				include "app/view/error/inline.html";
			} else {
				include "app/view/error/500.html";
			}
	}
});

// Connect to database
$f3->set("db.instance", new DB\SQL(
	"mysql:host=" . $f3->get("db.host") . ";port=" . $f3->get("db.port") . ";dbname=" . $f3->get("db.name"),
	$f3->get("db.user"),
	$f3->get("db.pass")
));

// Minify static resources
$f3->route("GET /minify/@type/@files", function(Base $f3, $args) {
	$f3->set("UI", $args["type"] . "/");
	echo Web::instance()->minify($args["files"]);
}, $f3->get("cache_expire.minify") ?: 3600*24*7);

// Load user if session exists
$user = new Model\User();
$user->loadCurrent();

// Run the application
$f3->run();
