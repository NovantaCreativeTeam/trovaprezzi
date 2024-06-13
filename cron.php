<?php

include(dirname(__FILE__).'/../../config/config.inc.php');
include(dirname(__FILE__).'/../../init.php');
include(dirname(__FILE__).'/trovaprezzi.php');

$module=new Trovaprezzi();
if($module->export()) {
    die ('OK, export completed successfully.');
}
else {
    die ('KO, Error during export.');
}
