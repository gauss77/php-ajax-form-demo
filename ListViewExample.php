<?php

/**
 * Record list view example
 * 
 * @package php-ajax-form-demo
 * 
 * @author Juan Carrión
 * 
 * @version 0.0.1
 */

require_once('classes/init.php');

use PhpAjaxFormDemo\Data\Record;
use PhpAjaxFormDemo\Forms\RecordRead;
use PhpAjaxFormDemo\Forms\RecordUpdate;

$recordReadForm = new RecordRead();
$recordUpdateForm = new RecordUpdate();

$v = APP_PRODUCTION ? '' : '?v=0.0.0' . time();

?>
<!DOCTYPE html>
<html lang="es">
    <head>
        <!-- Meta -->
        <meta charset="utf-8">
        <meta name="robots" content="noindex,nofollow">

        <title>Record list - PHP AJAX form demo</title>
        
        <meta name="viewport" content="width=device-width, minimum-scale=1.0, initial-scale=1">
        <meta name="theme-color" content="#333333">

        <!-- Styles and fonts -->
        <link rel="stylesheet" href="https://fonts.googleapis.com/icon?family=Material+Icons">
        <link rel="stylesheet" href="https://fonts.googleapis.com/css?family=Ubuntu:400,700&display=swap">

        <link rel="stylesheet" href="css/app.css<?php echo $v; ?>">

        <!-- Bootstrap -->
        <link rel="stylesheet" href="css/bootstrap-4.5.0.min.css">
    </head>
    <body>

        <div id="loading-progress-bar"><div></div></div>

        <div id="side-menu-wrapper">
            <div class="m-3">
                <button type="button" id="btn-side-menu-hide" class="btn btn-sm btn-outline-secondary mr-4">Cerrar</button>
            </div>

            <nav>
                <ul class="list-group list-group-flush">
                    <li class="list-group-item mt-3"><strong>Funciones</strong></li>
                    <li class="list-group-item list-group-item-action active">Lista de ejemplo</li>
                    <li class="list-group-item list-group-item-action">Dapibus ac facilisis in</li>
                    <li class="list-group-item list-group-item-action">Morbi leo risus</li>

                    <li class="list-group-item mt-3"><strong>Otros</strong></li>
                    <li class="list-group-item list-group-item-action">Porta ac consectetur ac</li>
                    <li class="list-group-item list-group-item-action">Vestibulum at eros</li>
                </ul>
            </nav>
        </div>
            
        <nav id="main-header" class="navbar navbar-expand-lg navbar-light bg-light">
            <button type="button" id="btn-side-menu-show" class="btn btn-sm btn-outline-secondary mr-4">Menu</button>

            <a class=" navbar-brand" href="#">
                <img src="img/logo.svg" height="30" alt="" class="d-block mr-3">
            </a>

            <div class="ml-auto">
                <ul class="navbar-nav">
                    <li class="nav-item ">
                        <a class="nav-link" href="#">Login</a>
                    </li>
                </ul>
            </div>
        </nav>

        <div id="toasts-container" aria-live="polite" aria-atomic="true"></div>

        <div id="main-container" class="container mt-4 mb-4">
            <div class="row">
                <div class="col"></div>
                <div class="col-10">
                    <button id="btn-record-create-modal-open" class="btn btn-primary">Create record</button>
                    
                    <div class="card mt-3">
                        <table id="record-list-table" class="table table-borderless table-striped">
                            <thead>
                                <tr>
                                    <th scope="col">Id</th>
                                    <th scope="col">Name</th>
                                    <th scope="col">Surname</th>
                                    <th scope="col">Nationality</th>
                                    <th scope="col">Hobbies</th>
                                    <th scope="col">Actions</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php

foreach (Record::getAll() as $record) {
    $uniqueId = $record->getUniqueId();
    $name = $record->getName();
    $surname = $record->getSurname();
    $nationality = $record->getNationality()->getName();

    $hobbies = '';
    foreach ($record->getHobbies() as $hobbie) {
        $hobbies .= $hobbie->getName() . ' ';
    }

    echo <<< HTML
    <tr data-unique-id="$uniqueId">
        <td scope="row">$uniqueId</td>
        <td data-col-name="name">$name</td>
        <td data-col-name="surname">$surname</td>
        <td data-col-name="nationality">$nationality</td>
        <td data-col-name="hobbies">$hobbies</td>
        <td>
            <button class="btn-ajax-modal-fire btn btn-sm btn-primary mb-1" data-ajax-form-id="record-read" data-ajax-unique-id="$uniqueId">Read</button>
            <button class="btn-ajax-modal-fire btn btn-sm btn-primary mb-1" data-ajax-form-id="record-update" data-ajax-unique-id="$uniqueId">Update</button>
            <button class="btn-ajax-modal-fire btn btn-sm btn-primary mb-1" data-ajax-form-id="record-delete" data-ajax-unique-id="$uniqueId">Delete</button>
        </td>
    </tr>
    HTML;
}

                                ?>
                            </tbody>
                        </table>
                    </div>

                </div>
                <div class="col"></div>
            </div>
        </div>

        <!-- Record read modal -->
        <?php echo $recordReadForm->generateModal(); ?>
        
        <!-- Record update modal -->
        <?php echo $recordUpdateForm->generateModal(); ?>

        <footer id="main-footer" class="page-footer font-small bg-light pt-4">
            <div class="footer-copyright text-center p-3">
                <p>© 2020 Gesi</p>
            </div>
        </footer>

        <!-- App autoconfiguration -->
        <script src="js/autoconf.php<?php echo $v; ?>"></script>

        <!-- jQuery -->
        <script src="js/jquery-3.5.1.min.js"></script>

        <!-- Popper -->
        <script src="js/popper-1.16.0.min.js"></script>

        <!-- Bootstrap -->
        <script src="js/bootstrap-4.5.0.js"></script>

        <!-- App scripts -->
        <script src="js/app.js<?php echo $v; ?>"></script>

    </body>
</html>