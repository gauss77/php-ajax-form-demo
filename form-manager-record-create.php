<?php

/**
 * Controller for record create form loading and submitting
 * 
 * @package ajax-form-demo
 * 
 * @author Juan Carrión
 * 
 * @version 0.0.1
 */

require_once('classes/init.php');

use PhpAjaxFormDemo\Forms\RecordCreate;

$recordUpdateForm = new RecordCreate();

$recordUpdateForm->manage();

?>