<?php

namespace PhpAjaxFormDemo\Forms;

use PhpAjaxFormDemo\Data\MultiForeignRecord;
use PhpAjaxFormDemo\Data\SingleForeignRecord;
use PhpAjaxFormDemo\Forms\AjaxForm;
use PhpAjaxFormDemo\Data\Record;

/**
 * AJAX form example class: record create
 * 
 * @package ajax-form-demo
 * 
 * @author Juan Carrión
 * 
 * @version 0.0.1
 */

class RecordCreate extends AjaxForm
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
    private const FORM_ID = 'record-create';
    private const FORM_NAME = 'Create record';
    private const TARGET_OBJECT_NAME = 'Record';
    private const SUBMIT_URL = APP_URL . '/form-manager-record-create.php';
    private const EXPECTED_SUBMIT_METHOD = AjaxForm::HTTP_PATCH;
    private const ON_SUCCESS_EVENT_NAME = 'created.record';
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

    public function processSubmit(array $data = array()) : void
    {
        $name = $data['name'] ?? null;
        $surname = $data['surname'] ?? null;
        $nationality = $data['nationality'] ?? null;
        $hobbies = $data['hobbies'] ?? null;
        
        // Check all required fields were sent
        if (empty($name) || empty($surname) || empty($nationality)) {
            if (empty($name)) {
                $errors[] = 'Missing param "name".';
            }

            if (empty($surname)) {
                $errors[] = 'Missing param "surname".';
            }

            if (empty($nationality)) {
                $errors[] = 'Missing param "nationality".';
            }

            // Hobbies are optional

            $this->respondJsonError(400, $errors); // Bad request
        }

        // Check SingleForeignRecord (nationality)'s uniqueId is valid
        if (! SingleForeignRecord::existsById($nationality)) {
            $errors[] = 'Nationality not found.'; // with "uniqueId" "' . $uniqueId . '"

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
                    $errors[] = 'Hobbie not found.'; // with "uniqueId" "' . $hobbie . '"

                    $this->respondJsonError(404, $errors); // Not found
                }

                $hobbiesArray[] = MultiForeignRecord::getById($hobbie);
            }
        }
        
        // In real projects, data creation would be here.

        // Generate inserted id.
        do {
            $uniqueId = rand();
        } while (Record::existsById($uniqueId));
        
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