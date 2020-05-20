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
     * Initialize specific form constants
     */
    private const FORM_ID = 'record-update';
    private const FORM_NAME = 'Update record';
    private const SUBMIT_URL = APP_URL . '/form-manager-record-update.php';
    private const EXPECTED_SUBMIT_METHOD = AjaxForm::HTTP_PATCH;
    private const ON_SUCCESS_EVENT_NAME = 'updated.record';
    private const ON_SUCCESS_EVENT_TARGET = '#record-list-table';

    /**
     * Constructs the form object
     */
    public function __construct()
    {
        parent::__construct(
            self::FORM_ID,
            self::FORM_NAME,
            self::SUBMIT_URL,
            self::EXPECTED_SUBMIT_METHOD
        );

        $this->setOnSuccess(
            self::ON_SUCCESS_EVENT_NAME,
            self::ON_SUCCESS_EVENT_TARGET
        );
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
            <label for="control-name">Name</label>
            <input name="name" type="text" class="form-control" id="control-name" aria-describedby="control-name-help" placeholder="Name">
            <small id="control-name-help" class="form-text text-muted">Please fill the name.</small>
        </div>
        <div class="form-group">
            <label for="control-surname">Surname</label>
            <input name="surname" type="text" class="form-control" id="control-surname" aria-describedby="control-surname-help" placeholder="Surname">
            <small id="control-surname-help" class="form-text text-muted">Please fill the surname.</small>
        </div>
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