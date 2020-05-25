<?php

/**
 * Controller for record delete form loading
 * 
 * @package ajax-form-demo
 * 
 * @author Juan Carrión
 * 
 * @version 0.0.1
 */

require_once('classes/init.php');

use PhpAjaxFormDemo\Forms\RecordDelete;

$recordReadForm = new RecordDelete();

$recordReadForm->manage();

?>