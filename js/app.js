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
toast.create = function (type, text)
{
    var toastHtml = '<div class="toast" role="alert" aria-live="assertive" aria-atomic="true" data-autohide="false" data-delay="3000"><div class="toast-header"><span class="type-indicator ' + type + '"></span><strong class="mr-auto">Gesi</strong><button type="button" class="ml-2 mb-1 close" data-dismiss="toast" aria-label="Close"><span aria-hidden="true">&times;</span></button></div><div class="toast-body">' + text + '</div></div>';

    const $toast = $(toastHtml);

    return $toast;
}

/**
 * Creates and shows a success toast
 */
toast.success = function (text)
{
    var $toast = toast.create('success', text);
    $('#toasts-container').prepend($toast);
    $toast.toast('show');
}

/**
 * Creates and shows an error toast
 */
toast.error = function (text)
{
    var $toast = toast.create('error', text);
    $('#toasts-container').prepend($toast);
    $toast.toast('show');
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
                    console.log('#btn-ajax-modal-fire click AJAX success');
                    console.log(result);

                    // Fill form placeholder inputs
                    for (const name in result) {
                        $modal.find('input[name="' + name + '"]').val(result[name]);
                    }
                    
                    // Hide loader and show modal
                    $loadingProgressBar.fadeOut();
                    $modal.modal('show');
                },
                error: (result) => {
                    // Hide loader and log error
                    $loadingProgressBar.fadeOut();
                    console.log('#btn-ajax-modal-fire click AJAX error');
                    console.error(result);
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
        
        var submitUrl = $modal.data('ajax-submit-url');
        var submitMethod = $modal.data('ajax-submit-method');

        $.ajax({
            url: submitUrl,
            type: submitMethod,
            contentType: 'application/json; charset=utf-8',
            dataType: 'json',
            data: $form.serialize(),
            success: (result) => {
                console.log('.ajax-modal form submit AJAX success');
                console.log(result);

                // TODO: update list

                $modal.modal('hide');

                // TODO arreglar que entre dos veces a este listener
                $modal.on('hidden.bs.modal', () => {
                    $loadingProgressBar.fadeOut();

                    if (result.messages) {
                        result.messages.forEach(m => toast.success(m));
                    }
                });
            },
            error: (result) => {
                $loadingProgressBar.fadeOut();
                toast.error('There was an error processing the form.');

                console.log('.ajax-modal form submit AJAX error');
                console.error(result);
            }
        });
    });

    /**
     * AJAX form modal empty on hide
     */
    $('.ajax-modal').on('hidden.bs.modal', (e) => {
        $(e.currentTarget).find('input, select, textarea').val();
    });

});