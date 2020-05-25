<?php

namespace PhpAjaxFormDemo\Forms;

use PhpAjaxFormDemo\Data\MultiForeignRecord;
use PhpAjaxFormDemo\Data\SingleForeignRecord;
use PhpAjaxFormDemo\Forms\AjaxForm;
use PhpAjaxFormDemo\Data\Record;

/**
 * AJAX form example class: record delete
 * 
 * @package ajax-form-demo
 * 
 * @author Juan Carrión
 * 
 * @version 0.0.1
 */

class RecordDelete extends AjaxForm
{

    /**
     * Initialize specific form constants
     *
     * @var string FORM_ID
     * @var string FORM_NAME
     * @var string DATA_OBJECT_NAME
     * @var string SUBMIT_URL
     * @var string EXPECTED_SUBMIT_METHOD
     * @var string ON_SUCCESS_EVENT_NAME
     * @var string ON_SUCCESS_EVENT_TARGET
     */
    private const FORM_ID = 'record-delete';
    private const FORM_NAME = 'Delete record';
    private const TARGET_OBJECT_NAME = 'Record';
    private const SUBMIT_URL = APP_URL . '/form-manager-record-delete.php';
    private const EXPECTED_SUBMIT_METHOD = AjaxForm::HTTP_DELETE;
    private const ON_SUCCESS_EVENT_NAME = 'deleted.record';
    private const ON_SUCCESS_EVENT_TARGET = '#record-list-table';

    /**
     * Constructs the form object
     */
    public function __construct()
    {
        parent::__construct(
            self::FORM_ID,
            self::FORM_NAME,
            self::TARGET_OBJECT_NAME,
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
            // Link are not necessary in this case
            self::TARGET_OBJECT_NAME => $record
        );

        return $responseData;
    }

    public function generateFormInputs() : string
    {
        $html = <<< HTML
        <input type="hidden" name="uniqueId">
        <div class="form-group">
            <label>Name</label>
            <input name="name" type="text" class="form-control"  disabled="disabled">
        </div>
        <div class="form-group">
            <label>Surname</label>
            <input name="surname" type="text" class="form-control"  disabled="disabled">
        </div>
        <div class="form-group">
            <div class="custom-control custom-checkbox">
                <input type="checkbox" class="custom-control-input" name="checkbox" id="record-delete-checkbox" required="required">
                <label class="custom-control-label" for="record-delete-checkbox">Confirm record deletion.</label>
            </div>
        </div>
        HTML;

        return $html;
    }

    public function processSubmit(array $data = array()) : void
    {
        $uniqueId = $data['uniqueId'] ?? null;
        $checkbox = $data['checkbox'] ?? null;
        
        // Check all required fields were sent
        if (empty($uniqueId) || empty($checkbox)) {
            if (empty($uniqueId)) {
                $errors[] = 'Field "uniqueId" is required.';
            }

            if (empty($checkbox)) {
                $errors[] = 'Field "checkbox" is required.';
            }

            // Hobbies are optional

            $this->respondJsonError(400, $errors); // Bad request
        }

        // Check if confirmation checkbox is valid
        if ($uniqueId !== $checkbox) {
            $this->respondJsonError(400, array('Field "checkbox" is not valid.')); // Bad request
        }
        
        // Check Record's uniqueId is valid
        if (! Record::existsById($uniqueId)) {
            $errors[] = 'Record not found.'; // with "uniqueId" "' . $uniqueId . '" 

            $this->respondJsonError(404, $errors); // Not found
        }

        // Map data to match placeholder inputs' names
        $responseData = array(
            'status' => 'ok',
            'messages' => array(
                'Record deleted succesfully.'
            )
        );

        $this->respondJsonOk($responseData);
    }
}

?>