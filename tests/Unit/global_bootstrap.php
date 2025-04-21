<?php

if (! function_exists('current_user_can')) {
    function current_user_can($capability) {
        return (bool) ($GLOBALS['test_can_edit'] ?? false);
    }
}