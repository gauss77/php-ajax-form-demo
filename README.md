# Form generation and processing using PHP and AJAX

This demo app's goal is to enable centralized form generation and processing using PHP and AJAX (via jQuery).

HTML form display and pre-loading is tackled with Bootstrap modals.

Form session tokens are used for protection against cross-site request forgery (CSRF) attacks.

**Demo data types**

- `Record` is a representation of a data type (_person_ in this demo) we want to list, create, read, update and delete (CRUD) via AJAX.
- `SingleForeignRecord` is a representation of a data type (_nationality_ in this demo) linked as a single-valued (`1 to N`) foreign relationship to `Record`.
- `MultiForeignRecord` is a representation of a data type (_hobbies_ in this demo) linked as a multi-valued (`N to M`) foreign relationship to `Record`.

## Creating a form

### 1. AjaxForm class extension

Create a class that extends `AjaxForm`, for example, `RecordUpdate`. `AjaxForm` is in charge of managing form generation and processing and, as it is an abstract class, there are some methods that we have to implement/extend in `RecordUpdate`:

#### 1.1. Form object construction

```php
public function __construct(
    string $formId,
    string $formName,
    string $targetObjectName,
    string $submitUrl,
    string $expectedSubmitMethod
)
```

This method is the standard `RecordUpdate` constructor. It has to call `AjaxForm` (`super`) constructor with some params:

- `string $formId` Unique form identifier. Used for modal generation, submission validity verification, CSRF token storage in `$_SESSION` and client/server JSON communication.
- `string $formName` Form visible name. Used as modal's header title.
- `string $targetObjectName` Name of the object that the form modifies, for example, `Record`. Used for client/server JSON communication and data exchange. 
- `string $submitUrl` URL to which the form will be submitted.
- `string $expectedSubmitMethod` HTTP request method with which the form will be submitted. Supported methods must be defined as constants in `AjaxForm` and added to `AjaxForm::SUPPORTED_HTTP_METHODS`.

Within the constructor, an **_on-success_ event and target** can be set using `AjaxForm::setOnSuccess()`. The event will be fired to the target when a successful response has been sent after a form submission. This allows for, e. g., data lists update.

```php
...
public function setOnSuccess(
    string $onSuccessEventName,
    string $onSuccessEventTarget
) : void
...
```

#### 1.2. Form input and data plceholders generation

```php
public function generateFormInputs() : string
```

This method should generate an HTML string containing the placeholder inputs for the default form data, and return it. Placeholder inputs `name` attributes should match the ones in data type's `jsonSerialize()` method (`JsonSerializable` interface implementation), as placeholder filling with default data is done using these.

#### 1.3. Placeholder filling with default data

```php
protected function getDefaultData(array $requestData) : array
```

This method is called when a `GET` request is received to get form's default data, including identifier, CSRF token, data type (`Record`) initial data and other information.

This method should return an array containing the data to be sent as a response. The returned array must follow a specific structure. Here's an OK default data response:

```json
{
    "status":"ok",
    "form-id":"record-read",
    "csrf-token":"93157bab9b2a11...",
    "Record":{
        "uniqueId":23,
        "selectName":"Pedro Martínez Fernández",
        "checkbox":23,
        "name":"Pedro",
        "surname":"Martínez Fernández"
    }
}
```

If this initial data needs any information, validation should be done here, e. g.:

```php
// Check that uniqueId was provided.
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

// Check that uniqueId is valid.
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
```

In this cases, an error structure is sent as a response:

```json
{
    "status":"error",
    "error":404,
    "messages":[
        "Invalid param \"uniqueId\"."
    ]
}
```

In all cases, a `messages` array attribute can be sent to inform the user about what happened.

If there are any foreign attributes attached to the main data type, then a HATEOAS-based _link_ should be sent too in the same response. This is done using the `AjaxForm::generateHateoasSelectLink()` method which generates a `link` object or array containing the foreign data. In these cases `jsonSerialize()` method should only return foreign attributes' unique identifiers:

```php
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
```

A response with foreign attributes would be similar to:

```json
{
    "status":"ok",
    "form-id":"record-update",
    "csrf-token":"8a71c5ea1ff406...",
    "links":[
        {
            "rel":"nationality",
            "selectType":"single",
            "data":[
                {
                    "uniqueId":1,
                    "selectName":"Spain",
                    "name":"Spain"
                },
                "...",
                {
                    "uniqueId":6,
                    "selectName":"Belgium",
                    "name":"Belgium"
                }
            ]
        },
        {
            "rel":"hobbies",
            "selectType":"multi",
            "data":[
                {
                    "uniqueId":1,
                    "selectName":"Sports",
                    "name":"Sports"
                },
                "...",
                {
                    "uniqueId":10,
                    "selectName":"Writing",
                    "name":"Writing"
                }
            ]
        }
    ],
    "Record":{
        "uniqueId":23,
        "selectName":"Pedro Martínez Fernández",
        "checkbox":23,
        "name":"Pedro",
        "surname":"Martínez Fernández",
        "nationality":2,
        "hobbies":[
            1,
            4,
            5,
            6,
            7,
            8,
            9
        ]
    }
}
```

In case of read-only forms or update responses, sending all available foreign records is not recommended. On the contrary, it is recommended to send only the foreign registers that the main data type uses.

#### 1.4. Submission processing

```php
public function processSubmit(array $data = array()) : void
```

The last method is in charge of processing a form submission. This method is called when a request matching the expected HTTP method is received and the form is not read-only.

Server-side input validation should be done here, e. g.: 

```php
$uniqueId = $data['uniqueId'] ?? null;
$name = $data['name'] ?? null;
$surname = $data['surname'] ?? null;
$nationality = $data['nationality'] ?? null;
$hobbies = $data['hobbies'] ?? null;

// Check all required fields were sent.
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

    // Hobbies are optional.

    $this->respondJsonError(400, $errors); // Bad request.
}

// Check Record's uniqueId is valid.
if (! Record::existsById($uniqueId)) {
    $errors[] = 'Record not found.';

    $this->respondJsonError(404, $errors); // Not found.
}

// Check SingleForeignRecord (nationality)'s uniqueId is valid.
if (! SingleForeignRecord::existsById($nationality)) {
    $errors[] = 'Nationality not found.';

    $this->respondJsonError(404, $errors); // Not found.
}

$nationalityObject = SingleForeignRecord::getById($nationality);

$hobbiesArray = array();

// Chech if any hobbies were sent.
if ($hobbies) {
    // Check if only one hobby was sent, and convert it.
    if (! is_array($hobbies)) {
        $hobbies = array($hobbies);
    }

    // Check MultiForeignRecords (hobbies)' uniqueIds are valid.
    foreach ($hobbies as $hobbie) {
        if (! MultiForeignRecord::existsById($hobbie)) {
            $errors[] = 'Hobbie not found.';

            $this->respondJsonError(404, $errors); // Not found.
        }

        $hobbiesArray[] = MultiForeignRecord::getById($hobbie);
    }
}
```

`AjaxForm` implements some methods used to generate a JSON response and then terminate the script execution. For example, `AjaxForm::respondJsonError()` takes an HTTP status and some error messages and returns the specific error structure to the client.

Once validation is done, it is time for the data update. In this demo, we simply create a new non-persistent `Record`:

```php
// In real projects, data update would be here.
$record = new Record(
    $uniqueId,
    $name,
    $surname,
    $nationalityObject,
    $hobbiesArray
);
```

Lastly, an OK response should be sent to the client, e. g.:

```php
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
```

This will generate the following response:

```json
{
    "status":"ok",
    "messages":[
        "Register updated successfully."
    ],
    "links":[
        {
            "rel":"nationality",
            "selectType":"single",
            "data":[
                {
                    "uniqueId":2,
                    "selectName":"France",
                    "name":"France"
                }
            ]
        },
        {
            "rel":"hobbies",
            "selectType":"multi",
            "data":[
                {
                    "uniqueId":1,
                    "selectName":"Sports",
                    "name":"Sports"
                },
                "...",
                {
                    "uniqueId":9,
                    "selectName":"Reading",
                    "name":"Reading"
                }
            ]
        }
    ],
    "Record":{
        "uniqueId":23,
        "selectName":"Pedro Martínez Fernández",
        "checkbox":23,
        "name":"Pedro",
        "surname":"Martínez Fernández",
        "nationality":2,
        "hobbies":[
            1,
            4,
            5,
            6,
            7,
            8,
            9
        ]
    }
}
```

### 2. HTTP controller for submission creation

An HTTP controller should be created for the URL to which the form will be submitted. It only needs to instantiate the class and call the `AjaxForm::manage()` method, e. g.:

```php
$recordUpdateForm = new RecordUpdate();

$recordUpdateForm->manage();
```

### 3. Modal placeholder generation

Bootstrap modals are used as hidden placeholders. To pre-load a modal in a view, just instantiate the class and echo the `AjaxForm::generateModal()` method, e. g.:

```php
$recordUpdateForm = new RecordUpdate();

$recordUpdateForm->generateModal();
```

Elements that fire modals need these basic attributes:

```html
<button class="btn-ajax-modal-fire btn btn-sm btn-primary mb-1" data-ajax-form-id="record-update" data-ajax-unique-id="$uniqueId">Update</button>
```

- `btn-ajax-modal-fire` Class that identifies the element as modal fire button.
- `data-ajax-form-id` Attribute that identifies the form of which modal should be shown.
- `data-ajax-unique-id` Attribute that identifies the unique identifier of the object to modify, if needed.

### 4. On-success event handling

As said in section _1.1. Form object construction_, an _on-success_ event and target can be set in an `AjaxForm` object using `AjaxForm::setOnSuccess()`. The event will be fired to the target when a successful response has been sent after a form submission. This allows for, e. g., data lists update. Having said that, a handling function should be defined for the event and the target, e. g.:

```javascript
/**
 * Handle record update success (ON_SUCCESS_EVENT_*)
 */
$('#record-list-table').on('updated.record', (e, params) => {
    const $modalData = params.modalData;
    const result = params.result;

    const targetObjectName = $modalData.data('ajax-target-object-name');
    
    // Get response data.
    const uniqueId = result[targetObjectName].uniqueId;
    const name = result[targetObjectName].name;
    const surname = result[targetObjectName].surname;

    // Get nationality name (first find the link, then the object).
    const nationalityLinkData = aux.findObjectInArray(result.links, 'rel', 'nationality').data;

    const nationalityName = aux.findObjectInArray(nationalityLinkData, 'uniqueId', result[targetObjectName].nationality).name;

    // Get hobbies names.
    const hobbiesLinkData = aux.findObjectInArray(result.links, 'rel', 'hobbies').data;

    const hobbies = result[targetObjectName].hobbies;
    var hobbiesNames = '';

    for (var i = 0; i < hobbies.length; i++) {
        hobbiesNames += aux.findObjectInArray(hobbiesLinkData, 'uniqueId', hobbies[i]).name + ' ';
    }

    // Update list row

    const $list = $(e.currentTarget);
    const $row = $list.find('tr[data-unique-id="' + uniqueId + '"]');

    $row.find('td[data-col-name="name"]').text(name);
    $row.find('td[data-col-name="surname"]').text(surname);
    $row.find('td[data-col-name="nationality"]').text(nationalityName);
    $row.find('td[data-col-name="hobbies"]').text(hobbiesNames);
});
```

## Implementation details

### CSRF protection

When loading default form data, a CSRF token is provided. This token is stored in PHP's `$_SESSION` after generation.

The provided token is sent at the same time that the form is submitted. Then, it is verified.

### Field names

Input field and `data-*` attribute names are defined in constants in `AjaxForm` (`*_FIELD`) and in the JavaScript script (`app.js`).

### PHP JSON serialization

PHP data type classes should implement the `JsonJsonSerializable` interface and its `JsonSerializable::jsonSerialize()` method, as shown in the examples, to match input field names to attribute names, and to include only identifiers of foreign attributes, not the entire objects.

### JavaScript-jQuery form serialization

Form data JSON serialization is done using `jQuery.serializeArray()` and then adding each field as an attribute to a JSON object.

Multi-valued controls (multiple selects, same-name checkboxes...) are supported and represented as arrays.

### JavaScript-jQuery form input placeholders filling

When a default data response has been received in the client, the first process done is to fill the `<selec>` elements whose `name` attribute matches a `rel` attribute of a received `link`. E. g., `<select name="hobbies" multiple>` will be filled with `<option>` elements corresponding to data in the `link` with  `rel == 'hobbies'`.

Next, if a target object with initial data was sent along with the default data, e. g., a `Record`, then we use this data to mark the correspondent select option as `selected`.

Then, form identifier and CSRF token hidden inputs are filled manually.

Lastly, also if a target object with initial data was sent along with the default data, then common `<input>`s and `<textarea>`s are filled.