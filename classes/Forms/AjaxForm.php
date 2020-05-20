<?php

namespace PhpAjaxFormDemo\Forms;

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
     * @var string $formId               Form identifier
     * @var string FORM_ID_FIELD         Form identifier field name
     */
    protected $formId = null;
    private const FORM_ID_FIELD = 'formId';

    /**
     * @var string HTTP_POST             Supported HTTP method type: POST
     * @var string $expectedSubmitMethod Expected form submit method
     */
    public const HTTP_POST = 'POST';
    protected $expectedSubmitMethod = null;

    /**
     * @var string CSRF_PREFIX           CSRF prefix for $_SESSION storing
     * @var string CSRF_TOKEN_FIELD      CSRF token field name
     */
    private const CSRF_PREFIX = 'csrf';
    private const CSRF_TOKEN_FIELD = 'csrfToken';

    /**
     * @var string JSON_ADMITTED_CONTENT_TYPE JSON admitted content type
     */
    private const JSON_ADMITTED_CONTENT_TYPE = 'application/json; charset=utf-8';

    /**
     * @var array $resultMessage         Form process result messages
     */
    private $resultMessages = array();

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

        } elseif ($httpMethod === $this->expectedSubmitMethod) {

            // If method is the one expected for submit, process data sent
            if ($this->expectedSubmitMethod === self::HTTP_POST) {
                $data = $_POST;
            } else {
                $data = array();
            }
            
            // Check form submit is valid
            if ($data[self::FORM_ID_FIELD] == $this->formId
                && $this->CsrfValidateToken($data[self::CSRF_TOKEN_FIELD])) {
                
                $this->processSubmit($data);

            } else {
                $errorMessages = array('Param "' . self::FORM_ID_FIELD . '" and/or "' . self::CSRF_TOKEN_FIELD . '" are not valid.');

                $this->respondJsonError(400, $errorMessages);
            }
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
    abstract public function processSubmit(array $data = array()) : void;

    /**
     * Generates specific form inputs as placeholders for AJAX preloading
     * 
     * @return string HTML containing the inputs
     */
    abstract public function generateFormInputs() : string;

    /**
     * Generates the default HTML Bootstrap modal
     *
     * @return string HTML containing the modal
     */
    public function generateModal() : string
    {
        $inputs = $this->generateFormInputs();

        $formId = $this->formId;
        $submitUrl = $this->submitUrl;
        $expectedSubmitMethod = $this->expectedSubmitMethod;
        $formIdField = self::FORM_ID_FIELD;
        $csrfTokenField = self::CSRF_TOKEN_FIELD;

        $html = <<< HTML
        <div class="modal fade ajax-modal" data-ajax-form-id="$formId" data-ajax-submit-url="$submitUrl" data-ajax-submit-method="$expectedSubmitMethod" tabindex="-1" role="dialog" aria-labelledby="register-update-modal-label" aria-hidden="true">
            <div class="modal-dialog modal-dialog-centered" role="document">
                <form class="modal-content">
                    <input type="hidden" name="$formIdField">
                    <input type="hidden" name="$csrfTokenField">
                    <div class="modal-header">
                        <h5 class="modal-title" id="register-update-modal-label">Editar registro</h5>
                        <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                            <span aria-hidden="true">&times;</span>
                        </button>
                    </div>
                    <div class="modal-body">
                        $inputs
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-dismiss="modal">Cancelar</button>
                        <button type="submit" class="btn btn-primary">Continuar</button>
                    </div>
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
}

?>