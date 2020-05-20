<?php

namespace PhpAjaxFormDemo\Forms;

use PhpAjaxFormDemo\Forms\AjaxForm;
use PhpAjaxFormDemo\Data\Record;

/**
 * AJAX form example class: record update
 * 
 * @package ajax-form-demo
 * 
 * @author Juan Carrión
 * 
 * @version 0.0.1
 */

class RecordUpdate extends AjaxForm
{

    /**
     * Initialize form constants
     */
    private const FORM_ID = 'record-update';
    private const SUBMIT_URL = 'form-manager-record-update.php';
    private const EXPECTED_SUBMIT_METHOD = AjaxForm::HTTP_PATCH;

    /**
     * Constructs the form object
     */
    public function __construct()
    {
        $this->formId = self::FORM_ID;
        $this->submitUrl = self::SUBMIT_URL;
        $this->expectedSubmitMethod = self::EXPECTED_SUBMIT_METHOD;
    }

    protected function getDefaultData(array $requestData) : array
    {
        // Check that uniqueId was provided
        if (! isset($requestData['uniqueId'])) {
            $responseData = array(
                'status' => 'error',
                'error' => 400, // Bad request
                'messages' => array(
                    'Missing param "uniqueId".'
                )
            );

            return $responseData;
        }

        $uniqueId = $requestData['uniqueId'];

        // Check that uniqueId is valid
        if (! Record::existsById($uniqueId)) {
            $responseData = array(
                'status' => 'error',
                'error' => 404, // Not found
                'messages' => array(
                    'Invalid param "uniqueId".'
                )
            );

            return $responseData;
        }

        $record = Record::getById($uniqueId);

        // Map data to match placeholder inputs' names
        $responseData = array(
            'status' => 'ok',
            'uniqueId' => $record->getUniqueId(),
            'name' => $record->getName(),
            'surname' => $record->getSurname()
        );

        return $responseData;
    }

    public function generateFormInputs() : string
    {
        $html = <<< HTML
        <input type="hidden" name="uniqueId">
        <div class="form-group">
            <label for="control-name">Nombre</label>
            <input name="name" type="text" class="form-control" id="control-name" aria-describedby="control-name-help" placeholder="Nombre">
            <small id="control-name-help" class="form-text text-muted">Introduce tu nombre.</small>
        </div>
        <div class="form-group">
            <label for="control-surname">Apellidos</label>
            <input name="surname" type="text" class="form-control" id="control-surname" aria-describedby="control-surname-help" placeholder="Apellidos">
            <small id="control-surname-help" class="form-text text-muted">Introduce tus apellidos.</small>
        </div>
        <input type="checkbox" name="hellok" value="asdjkkl" checked>
        <input type="checkbox" name="hellok" value="jkklasf" checked>
        <select id="cars" name="hellotres" multiple>
            <option value="volvo" selected>Volvo</option>
            <option value="saab">Saab</option>
            <option value="opel" selected>Opel</option>
            <option value="audi">Audi</option>
        </select>
        HTML;

        return $html;
    }

    public function processSubmit(array $data = array()) : void
    {
        $uniqueId = $data['uniqueId'] ?? null;
        $name = $data['name'] ?? null;
        $surname = $data['surname'] ?? null;

        // Check all required fields were sent
        if (empty($uniqueId) || empty($name) || empty($surname)) {
            if (empty($uniqueId)) {
                $errors[] = 'Missing param "uniqueId"';
            }

            if (empty($name)) {
                $errors[] = 'Missing param "name"';
            }

            if (empty($surname)) {
                $errors[] = 'Missing param "surname"';
            }

            $this->respondJsonError(400, $errors); // Bad request
        }
        
        // Check uniqueId is valid
        if (! Record::existsById($uniqueId)) {
            $errors[] = 'Record with "uniqueId" "' . $uniqueId . '" not found.';

            $this->respondJsonError(404, $errors); // Not found
        }

        $responseData = array(
            'messages' => array('Record updated successfully'),
            'uniqueId' => $uniqueId,
            'name' => $name,
            'surname' => $surname
        );

        $this->respondJsonOk($responseData);
    }
}

?>