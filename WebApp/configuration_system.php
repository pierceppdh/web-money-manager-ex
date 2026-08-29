<?php
######################################
##  System configuration constants  ##
##         don't touch              ##
######################################

/**
 * Read a Docker/Apache environment variable, falling back to a default.
 * Empty string is treated as unset so compose can omit unused overrides.
 */
function mmex_env($name, $default)
{
    $value = getenv($name);
    if ($value === false || $value === '')
    {
        if (isset($_SERVER[$name]) && $_SERVER[$name] !== '')
        {
            $value = $_SERVER[$name];
        }
        else
        {
            $value = $default;
        }
    }
    return $value;
}

$dbpath = mmex_env('MMEX_DB_PATH', 'MMEX_New_Transaction.db');
$app_name = 'Money Manager EX';
$app_version = '1.2.3';
$api_version = '1.0.1';
$tr_default_status = '';
$tr_default_type = 'Withdrawal';
$attachments_folder = mmex_env('MMEX_ATTACHMENTS_DIR', 'attachments');
$configuration_user_path = mmex_env('MMEX_CONFIG_PATH', 'configuration_user.php');
