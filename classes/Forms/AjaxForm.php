<?php

namespace PhpAjaxFormDemo\Forms;

use stdClass;

/**
 * AJAX form template class; other forms must extend this class
 * 
 * @package ajax-form-demo
 * 
 * @author Juan Carrión
 * 
 * @version 0.0.1
 */

abstract class AjaxForm
{

    /**
     * @var string $formId       Form identifier.
     * @var string FORM_ID_FIELD Form identifier field name (input and form id
     *                           attribute).
     * @var string $formName     Form name (modal heading).
     */
    private $formId = null;
    private const FORM_ID_FIELD = 'form-id';
    private $formName = null;

    /**
     * @var string $targetObjectName        Name of the object which the form is
     *                                      modifying.
     * @var string TARGET_OBJECT_NAME_FIELD Form field name.
     */
    private $targetObjectName = null;
    private const TARGET_OBJECT_NAME_FIELD = 'ajax-target-object-name';

    /**
     * @var string $readOnly       Locks the form with no submission expected.
     * @var string READ_ONLY_FIELD Form field name.
     */
    private $readOnly = false;
    private const READ_ONLY_FIELD = 'read-only';

    /**
     * @var string $submitUrl       Form submit URL.
     * @var string SUBMIT_URL_FIELD Form field name (data-* attribute).
     */
    private $submitUrl = null;
    private const SUBMIT_URL_FIELD = 'ajax-submit-url';

    /**
     * @var string $onSuccessEventName         Name of the event to be fired on
     *                                         AJAX success.
     * @var string ON_SUCCESS_EVENT_NAME_FIELD Form field name (data-* 
     *                                         attribute).
     * @var string $onSuccessTarget            Identifier of the element on 
     *                                         which to fire the event.
     * @var string ON_SUCCESS_TARGET_FIELD     Form field name (data-* 
     *                                         attribute).
     */
    private $onSuccessEventName = null;
    private const ON_SUCCESS_EVENT_NAME_FIELD = 'ajax-on-success-event-name';
    private $onSuccessEventTarget = null;
    private const ON_SUCCESS_EVENT_TARGET_FIELD = 'ajax-on-success-event-target';

    /**
     * @var string HTTP_GET              Supported HTTP method type: GET.
     * @var string HTTP_POST             Supported HTTP method type: POST.
     * @var string HTTP_PATCH            Supported HTTP method type: PATCH.
     * @var string HTTP_DELETE           Supported HTTP method type: DELETE.
     * @var array SUPPORTED_HTTP_METHODS All supported method types.
     * @var string $expectedSubmitMethod Expected form submit method.
     */
    public const HTTP_GET = 'GET';
    public const HTTP_POST = 'POST';
    public const HTTP_PATCH = 'PATCH';
    public const HTTP_DELETE = 'DELETE';
    public const SUPPORTED_HTTP_METHODS = [
        self::HTTP_GET,
        self::HTTP_POST,
        self::HTTP_PATCH,
        self::HTTP_DELETE
    ];
    private $expectedSubmitMethod = null;
    private const EXPECTED_SUBMIT_METHOD_FIELD = 'ajax-submit-method';

    /**
     * @var string CSRF_PREFIX           CSRF prefix for $_SESSION storing.
     * @var string CSRF_TOKEN_FIELD      CSRF token field name.
     */
    private const CSRF_PREFIX = 'csrf';
    private const CSRF_TOKEN_FIELD = 'csrf-token';

    /**
     * @var string JSON_ADMITTED_CONTENT_TYPE JSON admitted content type.
     */
    private const JSON_ADMITTED_CONTENT_TYPE = 'application/json; charset=utf-8';

    /**
     * Standard constructor.
     */
    public function __construct(
        string $formId,
        string $formName,
        string $targetObjectName,
        string $submitUrl,
        string $expectedSubmitMethod
    )
    {
        $this->formId = $formId;
        $this->targetObjectName = $targetObjectName;
        $this->formName = $formName;
        $this->submitUrl = $submitUrl;
        
        // Check submit method is valid
        if (! in_array($expectedSubmitMethod, self::SUPPORTED_HTTP_METHODS)) {
            throw new \Exception("Unsupported submit method \"$expectedSubmitMethod\".");
        }

        $this->expectedSubmitMethod = $expectedSubmitMethod;
    }

    /**
     * Getters and setters (only the necessary ones)
     */

    public function setOnSuccess(
        string $onSuccessEventName,
        string $onSuccessEventTarget
    )
    {
        $this->onSuccessEventName = $onSuccessEventName;
        $this->onSuccessEventTarget = $onSuccessEventTarget;
    }

    public function setReadOnlyTrue()
    {
        $this->readOnly = true;
    }

    public function isReadOnly()
    {
        return $this->readOnly;
    }

    /**
     * Handles HTTP requests, called by the controller
     */
    public function manage() : void
    {
        // Check content type
        $contentType = $_SERVER['CONTENT_TYPE'] ?? null;

        if ($contentType != self::JSON_ADMITTED_CONTENT_TYPE) {
            $this->respondJsonError(400, array( // Bad request
                'Content type not supported'
            ));
        }

        // Check request method
        $httpMethod = $_SERVER['REQUEST_METHOD'];

        if ($httpMethod === 'GET') {

            // If method is GET, generate default form data
            $this->processInitialData($_GET);

        // Check form is not read-only and method is the one expected
        } elseif (! $this->isReadOnly()
            && $httpMethod === $this->expectedSubmitMethod) {

            // Get request data as associative array
            $dataInput = file_get_contents('php://input');
            $data = json_decode($dataInput, true);

            // Check form submit is valid
            $submittedFormId = $data[self::FORM_ID_FIELD] ?? null;
            $submittedCsrfToken = $data[self::CSRF_TOKEN_FIELD] ?? null;

            if ($submittedFormId == $this->formId
                && $this->CsrfValidateToken($submittedCsrfToken)) {
                
                $this->processSubmit($data);

            } else {
                $errorMessages = array('Param "' . self::FORM_ID_FIELD . '" and/or "' . self::CSRF_TOKEN_FIELD . '" are not valid.');

                $this->respondJsonError(400, $errorMessages);
            }
        } else {
            $this->respondJsonError(400, // Bad request
                array('Method not supported')
            );
        }
    }

    /**
     * Generates an HTTP response with a JSON-encoded data and an HTTP status 
     * code, and stops script execution
     * 
     * @param array $data     Data to send in the response
     * @param int   $httpCode HTTP status code
     */
    public function respondJson(int $httpCode, array $data) : void
    {
        http_response_code($httpCode);

        header('Content-Type: application/json');

        // This should be the only echo in all the code
        echo json_encode($data);

        // Nothing else should be sent
        die();
    }

    /**
     * Responds with an HTTP 4XX error and message
     * 
     * @param int   $httpCode HTTP error code
     * @param array $messages Error messages
     */
    public function respondJsonError(int $httpErrorCode, array $messages) : void
    {
        $errorData = array(
            'status' => 'error',
            'error' => $httpErrorCode,
            'messages' => $messages
        );

        $this->respondJson($httpErrorCode, $errorData);
    }

    /**
     * Responds with an HTTP 200 OK and message
     * 
     * @param array $data Data to send
     */
    public function respondJsonOk(array $data) : void
    {
        $okData = array(
            'status' => 'ok',
        );

        $responseData = array_merge($okData, $data);

        $this->respondJson(200, $responseData);
    }

    /**
     * Loads the default form data (i. e. for reading, updating and deleting) 
     * and returns it; should be overriden if necessary
     * 
     * Defauld data keys must be mapped to form input names in
     * generateFormInputs()
     *
     * @param array $requestData Data sent in the request; may contain a 
     * uniqueId
     *
     * @return array Set of default data for the form, as "key" => "value"; must
     * include a "status" field with either "ok" or "error"
     */
    protected function getDefaultData(array $requestData) : array
    {
        return array();
    }

    /**
     * Sends a JSON response generated with the default form data to fill the
     * placeholders
     *
     * @param array $requestData Data sent in the initial request (i. e. 
     * $uniqueId)
     */
    public function processInitialData(array $requestData) : void
    {
        $defaultData = $this->getDefaultData($requestData);

        // Check that default data is OK
        if ($defaultData['status'] === 'error') {
            $this->respondJsonError(
                $defaultData['error'],
                $defaultData['messages']
            );
        } else {
            $csrfToken = $this->CsrfGenerateToken();

            $formHiddenData = array(
                self::FORM_ID_FIELD => $this->formId,
                self::CSRF_TOKEN_FIELD => $csrfToken
            );

            $all = array_merge($formHiddenData, $defaultData);

            $this->respondJsonOk($all);
        }
    }

    /**
     * Processes a submitted form and sends a JSON response if necessary
     * 
     * @param array $data Data sent in form submission
     */
    public function processSubmit(array $data = array()) : void
    {
    }

    /**
     * Generates specific form inputs as placeholders for AJAX preloading
     * 
     * @return string HTML containing the inputs
     */
    abstract protected function generateFormInputs() : string;

    /**
     * Generates the default HTML Bootstrap modal
     *
     * @return string HTML containing the modal
     */
    public function generateModal() : string
    {
        $inputs = $this->generateFormInputs();

        $formId = $this->formId;
        $formName = $this->formName;
        $formIdField = self::FORM_ID_FIELD;
        $csrfTokenField = self::CSRF_TOKEN_FIELD;

        $targetObjectNameData = 
            'data-' . self::TARGET_OBJECT_NAME_FIELD .
            '="' . $this->targetObjectName . '"';

        // Optional on success event name
        $onSuccessEventNameData = $this->onSuccessEventName ?
            'data-' . self::ON_SUCCESS_EVENT_NAME_FIELD .
            '="' . $this->onSuccessEventName . '"' : '';

        // Optional on success event target
        $onSuccessEventTargetData = $this->onSuccessEventTarget ?
            'data-' . self::ON_SUCCESS_EVENT_TARGET_FIELD .
            '="' . $this->onSuccessEventTarget . '"' : '';

        $submitUrlData =
            'data-' . self::SUBMIT_URL_FIELD .
            '="' . $this->submitUrl . '"';

        $expectedSubmitMethodData =
            'data-' . self::EXPECTED_SUBMIT_METHOD_FIELD .
            '="' . $this->expectedSubmitMethod . '"';

        if (! $this->isReadOnly()) {
            $footer = <<< HTML
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-dismiss="modal">Cancel</button>
                <button type="submit" class="btn btn-primary">Continue</button>
            </div>
            HTML;
        } else {
            $footer = '';
        }

        $html = <<< HTML
        <div class="modal fade ajax-modal" data-ajax-form-id="$formId" $onSuccessEventNameData $onSuccessEventTargetData $submitUrlData $expectedSubmitMethodData $targetObjectNameData tabindex="-1" role="dialog" aria-labelledby="register-update-modal-label" aria-hidden="true">
            <div class="modal-dialog modal-dialog-centered" role="document">
                <form class="modal-content" id="$formId">
                    <input type="hidden" name="$formIdField">
                    <input type="hidden" name="$csrfTokenField">
                    <div class="modal-header">
                        <h5 class="modal-title" id="register-update-modal-label">$formName</h5>
                        <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                            <span aria-hidden="true">&times;</span>
                        </button>
                    </div>
                    <div class="modal-body">
                        $inputs
                    </div>
                    $footer
                </form>
            </div>
        </div>
        HTML;

        return $html;
    }

    /**
     * Generates a CSRF token and stores it in $_SESSION
     * 
     * @return string Generated token
     */
    private function CsrfGenerateToken() : string
    {
        $token = hash('sha512', mt_rand(0, mt_getrandmax()));

        $_SESSION[self::CSRF_PREFIX . '_' . $this->formId] = $token;

        return $token;
    }

    /**
     * Validates a CSRF token
     * 
     * @param string $token Token to be validated
     * 
     * @return bool True if valid, else false
     */
    private function CsrfValidateToken(string $token) : bool
    {
        if (isset($_SESSION[self::CSRF_PREFIX . '_' . $this->formId])
            && $_SESSION[self::CSRF_PREFIX . '_' . $this->formId] === $token) {
            
            unset($_SESSION[self::CSRF_PREFIX . '_' . $this->formId]);

            return true;
        }

        return false;
    }

    /**
     * Generates a JSON link formalization based in HATEOAS link specification.
     * 
     * @param string $rel
     * @param string $selectType 'multi' for multiple select, 'single' for 
     *                           single select (interpreted in Bootstrap modal
     *                           handling).
     * @param array $data
     * 
     * @return stdClass Object ready for JSON serialization.
     */
    public static function generateHateoasSelectLink(string $rel, string $selectType, array $data) : stdClass
    {
        $link = new stdClass();

        $link->rel = $rel;
        $link->selectType = $selectType;
        $link->data = $data;

        return $link;
    }
}

?>