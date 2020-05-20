<?php

/**
 * Application setup and initialization
 *
 * @package ajax-form-demo
 * 
 * @author Juan Carrión
 *
 * @version 0.0.1
 */

/**
 * Enable PHP strict typing
 */

declare(strict_types = 1);

/**
 * Declare constants
 */

define("APP_ROOT", "__DIR__");
define("APP_URL", "http://localhost/php-ajax-form-demo");
define("APP_PRODUCTION", false);

/**
 * Enable error display for debugging
 */

if (! APP_PRODUCTION) {
	ini_set('display_errors', 'On');
	error_reporting(E_ALL);
}

/**
 * Charset and timezone setup
 */

ini_set("default_charset", "UTF-8");
setLocale(LC_ALL, "es_ES.UTF.8");
setlocale(LC_TIME, "es_ES");
date_default_timezone_set("Europe/Madrid");

/**
 * Setup class autoload
 *
 * @see https://www.php.net/manual/en/function.spl-autoload-register.php
 * @see https://www.php-fig.org/psr/psr-4/
 */

spl_autoload_register(function ($class) {
	$prefix = "PhpAjaxFormDemo\\";

	$base_dir = __DIR__ . "/";

	$len = strlen($prefix);

	if (strncmp($prefix, $class, $len) !== 0) {
		return;
	}

	$relative_class = substr($class, $len);

	$file = $base_dir . str_replace("\\", "/", $relative_class) . ".php";

	if (file_exists($file)) {
		require $file;
	}
});

/**
 * Start PHP session
 */

session_start();

/**
 * Initialize record demo data
 */

\PhpAjaxFormDemo\Data\Record::initDemoData();

?>