<?php

/**
 * Autoconfigure JavaScript based in PHP application configuration
 *
 * @package php-ajax-form-demo
 *
 * @author Juan Carrión
 *
 * @version 0.0.1
 */

require_once('../classes/init.php');

$appUrl = APP_URL;
$appProduction = APP_PRODUCTION ? 'true' : 'false';

$js = <<< JS

/**
 * Autoconfigure JavaScript based in PHP application configuration
 *
 * @package php-ajax-form-demo
 *
 * @author Juan Carrión
 *
 * @version 0.0.1
 */

var autoconf = {
    APP_URL : "$appUrl",
    APP_PRODUCTION : $appProduction
}

JS;

header('Content-Type: application/javascript; charset=utf-8');

echo $js;

die();

?>