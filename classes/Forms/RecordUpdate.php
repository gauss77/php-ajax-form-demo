<?php

namespace PhpAjaxFormDemo\Forms;

use PhpAjaxFormDemo\Data\MultiForeignRecord;
use PhpAjaxFormDemo\Data\SingleForeignRecord;
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
     *
     * @var string FORM_ID
     * @var string FORM_NAME
     * @var string DATA_OBJECT_NAME
     * @var string SUBMIT_URL
     * @var string EXPECTED_SUBMIT_METHOD
     * @var string ON_SUCCESS_EVENT_NAME
     * @var string ON_SUCCESS_EVENT_TARGET
     */
    private const FORM_ID = 'record-update';
    private const FORM_NAME = 'Update record';
    private const TARGET_OBJECT_NAME = 'Record';
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
            self::TARGET_OBJECT_NAME,
            self::SUBMIT_URL,
            self::EXPECTED_SUBMIT_METHOD
        );

        $this->setOnSuccess(
            self::ON_SUCCESS_EVENT_NAME,
            self::ON_SUCCESS_EVENT_TARGET
        );
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
        <div class="form-group">
            <label for="control-nationality">Nationality</label>
            <select name="nationality" class="form-control" id="control-nationality">
            </select>
        </div>
        <div class="form-group">
            <label for="control-hobbies">Hobbies</label>
            <select name="hobbies" class="form-control" id="control-hobbies" multiple="multiple">
            </select>
        </div>
        HTML;

        return $html;
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

        // Nationality HATEOAS formalization
        $nationalityLink = AjaxForm::generateHateoasSelectLink(
            'nationality',
            'single',
            SingleForeignRecord::getAll()
        );

        // Hobbies HATEOAS formalization
        $hobbiesLink = AjaxForm::generateHateoasSelectLink(
            'hobbies',
            'multi',
            MultiForeignRecord::getAll()
        );

        // Map data to match placeholder inputs' names
        $responseData = array(
            'status' => 'ok',
            'links' => array(
                $nationalityLink,
                $hobbiesLink
            ),
            self::TARGET_OBJECT_NAME => $record
        );

        return $responseData;
    }

    public function processSubmit(array $data = array()) : void
    {
        $uniqueId = $data['uniqueId'] ?? null;
        $name = $data['name'] ?? null;
        $surname = $data['surname'] ?? null;
        $nationality = $data['nationality'] ?? null;
        $hobbies = $data['hobbies'] ?? null;
        
        // Check all required fields were sent
        if (empty($uniqueId) || empty($name) || empty($surname) || empty($nationality)) {
            if (empty($uniqueId)) {
                $errors[] = 'Field "uniqueId" is required.';
            }

            if (empty($name)) {
                $errors[] = 'Field "name" is required.';
            }

            if (empty($surname)) {
                $errors[] = 'Field "surname" is required.';
            }

            if (empty($nationality)) {
                $errors[] = 'Field "nationality" is required.';
            }

            // Hobbies are optional

            $this->respondJsonError(400, $errors); // Bad request
        }
        
        // Check Record's uniqueId is valid
        if (! Record::existsById($uniqueId)) {
            $errors[] = 'Record not found.'; // with "uniqueId" "' . $uniqueId . '" 

            $this->respondJsonError(404, $errors); // Not found
        }

        // Check SingleForeignRecord (nationality)'s uniqueId is valid
        if (! SingleForeignRecord::existsById($nationality)) {
            $errors[] = 'Nationality not found.'; // with "uniqueId" "' . $nationality . '" 

            $this->respondJsonError(404, $errors); // Not found
        }

        $nationalityObject = SingleForeignRecord::getById($nationality);

        $hobbiesArray = array();

        // Chech if any hobbies were sent
        if ($hobbies) {
            // Check if only one hobby was sent, and convert it
            if (! is_array($hobbies)) {
                $hobbies = array($hobbies);
            }

            // Check MultiForeignRecords (hobbies)' uniqueIds are valid
            foreach ($hobbies as $hobbie) {
                if (! MultiForeignRecord::existsById($hobbie)) {
                    $errors[] = 'Hobbie not found.'; // with "uniqueId" "' . $uniqueId . '" 

                    $this->respondJsonError(404, $errors); // Not found
                }

                $hobbiesArray[] = MultiForeignRecord::getById($hobbie);
            }
        }
        
        // In real projects, data update would be here.
        $record = new Record(
            $uniqueId,
            $name,
            $surname,
            $nationalityObject,
            $hobbiesArray
        );

        // Nationality HATEOAS formalization
        $nationalityLink = AjaxForm::generateHateoasSelectLink(
            'nationality',
            'single',
            $record->getNationality()
        );

        // Hobbies HATEOAS formalization
        $hobbiesLink = AjaxForm::generateHateoasSelectLink(
            'hobbies',
            'multi',
            $record->getHobbies()
        );

        // Map data to match placeholder inputs' names
        $responseData = array(
            'status' => 'ok',
            'messages' => array(
                'Register updated successfully.'
            ),
            'links' => array(
                $nationalityLink,
                $hobbiesLink
            ),
            self::TARGET_OBJECT_NAME => $record
        );

        $this->respondJsonOk($responseData);
    }
}

?>