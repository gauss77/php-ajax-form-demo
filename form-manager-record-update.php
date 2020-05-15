<?php

/**
 * Controller for record update form submit
 * 
 * @package ajax-form-demo
 * 
 * @author Juan Carrión
 * 
 * @version 0.0.1
 */

require_once('classes/init.php');

use PhpAjaxFormDemo\Forms\RecordUpdate;

$recordUpdateForm = new RecordUpdate();

$recordUpdateForm->manage();

?>