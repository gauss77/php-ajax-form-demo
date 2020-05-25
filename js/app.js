/**
 * Using name convention about $ for jQuery object variables
 */

/**
 * Toasts
 */
toast = {};

/**
 * Creates a toast
 */
toast.create = (type, text) =>
{
    var autohide = autoconf.APP_PRODUCTION;
    var toastHtml = '<div class="toast" role="alert" aria-live="assertive" aria-atomic="true" data-autohide="' + autohide + '" data-delay="3000"><div class="toast-header"><span class="type-indicator ' + type + '"></span><strong class="mr-auto">Gesi</strong><button type="button" class="ml-2 mb-1 close" data-dismiss="toast" aria-label="Close"><span aria-hidden="true">&times;</span></button></div><div class="toast-body">' + text + '</div></div>';

    const $toast = $(toastHtml);

    return $toast;
}

/**
 * Creates and shows a success toast
 */
toast.success = (text) =>
{
    var $toast = toast.create('success', text);
    $('#toasts-container').prepend($toast);
    $toast.toast('show');
}

/**
 * Creates and shows an error toast
 */
toast.error = (text) =>
{
    var $toast = toast.create('error', text);
    $('#toasts-container').prepend($toast);
    $toast.toast('show');
}

/**
 * Aux functions
 */

/**
 * Aux object used for namespace
 */
var aux = {};

/**
 * Retrieves a form's data and converts it to a JSON string
 */
aux.jQueryFormToJsonString = ($form) =>
{
    var resultObject = {};

    // Obtener objeto DOM del formulario
    const form = $form[0];

    // Obtener datos del formulario
    var formData = new FormData(form);

    // Recoger todos los campos
    for (const [key, value] of formData.entries()) {
        // Permitir que haya varios valores para campos con el mismo atributo 
        // 'name' (y convertir estos a arrays)
        if (resultObject[key]) {
            if (Array.isArray(resultObject[key])) {
                resultObject[key].push(value);
            } else {
                const prev = resultObject[key];
                resultObject[key] = [ prev, value ];
            }
        } else {
            resultObject[key] = value;
        }
    }

    return JSON.stringify(resultObject);
}

/**
 * Finds an object with a specific attribute in an array.
 * 
 * @param {array} array
 * @param {string} attributeName
 * @param {string} attributeValue
 */
aux.findObjectInArray = (array, attributeName, attributeValue) =>
{
    for (var i = 0; i < array.length; i++) {
        if (array[i][attributeName] === attributeValue) {
            return array[i];
        }
    }
    
    return null;
}

/**
 * jQuery on document ready
 */
$(() => {

    /**
     * Side menu
     */
    $('#btn-side-menu-show').on('click', (e) => {
        e.preventDefault();
        $('#side-menu-wrapper').addClass('active');
    });

    $('#btn-side-menu-hide, #toasts-container, #main-container, #main-footer').on('click', (e) => {
        e.preventDefault();
        $('#side-menu-wrapper').removeClass('active');
    });

    $(document).on('keyup', (e) => {
        if (e.key === "Escape") {
            $('#side-menu-wrapper').removeClass('active');
        }
    });

    /**
     * Loading progress bar
     */
    var $loadingProgressBar = $('#loading-progress-bar');

    /**
     * AJAX list initialization
     */

    // TODO

    /**
     * AJAX form initialization
     */
    $('.btn-ajax-modal-fire').on('click', (e) => {
        var $btn = $(e.currentTarget);

        // Get formId
        var formId = $btn.data('ajax-form-id');

        var $modal = $('.ajax-modal[data-ajax-form-id="' + formId + '"]');
        var url = $modal.data('ajax-submit-url');

        // Check that modal and URL exist
        if (url) {
            $loadingProgressBar.fadeIn();

            // Get formId
            var data = {
                formId: formId,
            };

            /**
             * uniqueId represents the id of the record to read, update or
             * delete; it is optional (i. e. in creation forms)
             */
            var uniqueId = $btn.data('ajax-unique-id');
            if (uniqueId) {
                data.uniqueId = uniqueId;
            }

            // Load default data and token
            $.ajax({
                url: url,
                type: 'GET',
                contentType: 'application/json; charset=utf-8',
                dataType: 'json',
                data: data,
                success: (result) => {
                    if (! autoconf.APP_PRODUCTION) {
                        console.log('#btn-ajax-modal-fire click AJAX success');
                        console.log(result);
                    }

                    // Array for late prevention
                    var linkRels = [];

                    // Get data payload (named with the target object's name)
                    var resultData = result[$modal.data('ajax-target-object-name')];

                    // Check if foreign attribute links were provided
                    if (result.links) {
                        result.links.forEach(link => {
                            linkRels.push(link.rel);

                            // First populate the select
                            var $select = $modal.find('select[name="' + link.rel + '"]');

                            link.data.forEach(value => {
                                const option = '<option value="' + value.uniqueId + '">' + value.selectName + '</option>';
                                $select.append(option);
                            });

                            // Then select the option by id
                            if (link.selectType === 'single') {
                                $modal.find('select[name="' + link.rel + '"]').val(resultData[link.rel]);
                            } else if (link.selectType === 'multi') {
                                // .val(['1', '2']);
                            }
                        });
                    }

                    // Fill form id and CSRF token
                    $modal.find('input[name="form-id"]').val(result['form-id']);
                    $modal.find('input[name="csrf-token"]').val(result['csrf-token']);
                    
                    // Fill form placeholder inputs
                    for (const name in resultData) {
                        // Prevent from filling linked inputs (selects)
                        if ($.inArray(name, linkRels) === -1) {
                            $modal.find('input[name="' + name + '"]').val(resultData[name]);
                        }
                    }
                    
                    // Hide loader and show modal
                    $loadingProgressBar.fadeOut();
                    $modal.modal('show');
                },
                error: (result) => {
                    // Hide loader and log error
                    $loadingProgressBar.fadeOut();

                    if (! autoconf.APP_PRODUCTION) {
                        console.log('#btn-ajax-modal-fire click AJAX error');
                        console.error(result);
                    }
                }
            });
        }
    });

    /**
     * AJAX form submit processing
     */
    $('.ajax-modal form').on('submit', (e) => {
        e.preventDefault();
        
        $loadingProgressBar.fadeIn();

        var $form = $(e.currentTarget);
        var $modal = $form.closest('.ajax-modal');
        
        var onSuccessEventName = $modal.data('ajax-on-success-event-name');
        var onSuccessEventTarget = $modal.data('ajax-on-success-event-target');
        var submitUrl = $modal.data('ajax-submit-url');
        var submitMethod = $modal.data('ajax-submit-method');

        $.ajax({
            url: submitUrl,
            type: submitMethod,
            contentType: 'application/json; charset=utf-8',
            dataType: 'json',
            data: aux.jQueryFormToJsonString($form),
            success: (result) => {
                if (! autoconf.APP_PRODUCTION) {
                    console.log('.ajax-modal form submit AJAX success');
                    console.log(result);
                }

                // Copy the modal so data is not emptied on hidden.bs.modal
                const $modalData = $modal;

                // Trigger success event on the target
                if (onSuccessEventName && onSuccessEventTarget) {
                    $(onSuccessEventTarget).trigger(onSuccessEventName, {
                        modalData: $modalData,
                        result: result
                    });
                }

                $modal.modal('hide');

                $loadingProgressBar.fadeOut();

                if (result.messages) {
                    result.messages.forEach(m => toast.success(m));
                }
            },
            error: (result) => {
                $loadingProgressBar.fadeOut();
                toast.error('There was an error processing the form.');

                if (! autoconf.APP_PRODUCTION) {
                    console.log('.ajax-modal form submit AJAX error');
                    console.error(result);
                }
            }
        });
    });

    /**
     * AJAX form modal empty on hide
     */
    $('.ajax-modal').on('hidden.bs.modal', (e) => {
        $(e.currentTarget).find('input, select, textarea').val();
    });

    /**
     * Handle record update success (ON_SUCCESS_EVENT_*)
     */
    $('#record-list-table').on('updated.record', (e, params) => {
        const $modalData = params.modalData;
        const result = params.result;

        const targetObjectName = $modalData.data('ajax-target-object-name');
        
        const uniqueId = result[targetObjectName].uniqueId;
        const name = result[targetObjectName].name;
        const surname = result[targetObjectName].surname;

        // Get nationality name (first find the link, then the object)
        const nationalityLinkData = aux.findObjectInArray(result.links, 'rel', 'nationality').data;

        const nationalityName = aux.findObjectInArray(nationalityLinkData, 'uniqueId', result[targetObjectName].nationality).name;

        const $list = $(e.currentTarget);
        const $row = $list.find('tr[data-unique-id="' + uniqueId + '"]');

        $row.find('td[data-col-name="name"]').text(name);
        $row.find('td[data-col-name="surname"]').text(surname);
        $row.find('td[data-col-name="nationality"]').text(nationalityName);
        console.log(nationalityName);
    });
});