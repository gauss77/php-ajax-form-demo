<?php

/**
 * Controller for record read form loading
 * 
 * @package ajax-form-demo
 * 
 * @author Juan Carrión
 * 
 * @version 0.0.1
 */

require_once('classes/init.php');

use PhpAjaxFormDemo\Forms\RecordRead;

$recordReadForm = new RecordRead();

$recordReadForm->manage();

?>