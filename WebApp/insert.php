<?php

$b_restricted_auth = true;
include_once '_common.php';

if ($_SERVER['REQUEST_METHOD'] !== 'POST')
{
    header('Location: new_transaction.php');
    exit;
}

$TrEditedNr = -1;
if (isset($_POST['TrEditedNr']))
{
    $TrEditedNr = $_POST['TrEditedNr'];
}

$TrDate = isset($_POST['Date']) ? $_POST['Date'] : date('Y-m-d');
$TrStatus = isset($_POST['Status']) ? $_POST['Status'] : '';
$TrType = isset($_POST['Type']) ? $_POST['Type'] : 'Withdrawal';
$TrAccount = isset($_POST['Account']) ? $_POST['Account'] : 'None';
$TrToAccount = isset($_POST['ToAccount']) ? $_POST['ToAccount'] : 'None';
$TrPayee = isset($_POST['Payee']) ? $_POST['Payee'] : 'None';
$TrCategory = isset($_POST['Category']) ? $_POST['Category'] : 'None';
$TrSubCategory = isset($_POST['SubCategory']) ? $_POST['SubCategory'] : 'None';
$TrAmount = isset($_POST['Amount']) ? $_POST['Amount'] : '0';
$TrNotes = isset($_POST['Notes']) ? $_POST['Notes'] : '';

if ($TrType !== 'Transfer')
{
    $TrToAccount = 'None';
}
else
{
    $TrPayee = 'None';
}

db_function::category_insert_single($TrCategory, $TrSubCategory);
db_function::payee_insert_single($TrPayee, $TrCategory, $TrSubCategory);
db_function::payee_update_single($TrPayee, $TrCategory, $TrSubCategory);

if ($TrEditedNr > 0)
{
    db_function::transaction_update($TrEditedNr, $TrDate, $TrStatus, $TrType, $TrAccount, $TrToAccount, $TrPayee, $TrCategory, $TrSubCategory, $TrAmount, $TrNotes);
    attachments::rename_zero($TrEditedNr);
    various::last_account_set($TrAccount);
    header('Location: show.php');
    exit;
}

$newId = db_function::transaction_insert($TrDate, $TrStatus, $TrType, $TrAccount, $TrToAccount, $TrPayee, $TrCategory, $TrSubCategory, $TrAmount, $TrNotes);
attachments::rename_zero($newId);
various::last_account_set($TrAccount);
various::last_payees_remember($TrPayee);

$saved = ($TrEditedNr == 0) ? 'duplicated' : 'added';
header('Location: new_transaction.php?saved=' . $saved);
exit;
