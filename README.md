# Form generation and processing using PHP and AJAX

This demo app's goal is to enable centralized form generation and processing using PHP and jQuery AJAX. It also integrates Bootstrap modals.

**Demo data types**

- `Record` is a representation of a data type (_person_ in this demo) we want to list, create, read, update and delete (CRUD) via AJAX.
- `SingleForeignRecord` is a representation of a data type (_nationality_ in this demo) linked as a single-valued (`1 to N`) foreign relationship to `Record`.
- `MultiForeignRecord` is a representation of a data type (_hobbies_ in this demo) linked as a multi-valued (`N to M`) foreign relationship to `Record`.

## Creating a form

### 1. AjaxForm class extension

Create a class that extends `AjaxForm`, for example, `RecordCreate`. `AjaxForm` class is in charge of managing form generation and processing and, as it is an abstract class, there are some methods that we have to implement/extend:

```php
public function __construct(
    string $formId,
    string $formName,
    string $targetObjectName,
    string $submitUrl,
    string $expectedSubmitMethod
)
```

Has to call `AjaxForm` (`super`) constructor with some params.

```php
protected function getDefaultData(array $requestData) : array`
```



```php
public function generateFormInputs() : string`
```



```php
public function processSubmit(array $data = array()) : void`
```

### 2. URL HTTP controller for submission creation

### 3. Modal placeholder generation