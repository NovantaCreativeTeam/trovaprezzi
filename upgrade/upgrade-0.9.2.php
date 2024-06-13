<?php

if (!defined('_PS_VERSION_')) {
    exit;
}

function upgrade_module_0_9_2($module)
{
    if($module->registerHook('displayOrderConfirmation') == false) {
        return false;
    }

    return true;
}