<?php

$b_restricted_auth  = true;
$transaction_name   = '';

require_once 'functions.php';

$TrEditNr = 0;
$TransactionHeaderText = $lang["trans.new.header"];
$TransactionSubmit = $lang["trans.new.submit"];
$FlagNew = true;
$saved_action = isset($_GET['saved']) ? $_GET['saved'] : '';

if (isset($_GET['TrEditNr']))
{
    $TrEditNr = $_GET['TrEditNr'];
    $TransactionHeaderText = $lang["trans.update.header"];
    $TransactionSubmit = $lang["trans.update.submit"];
    $FlagNew = false;
}
elseif (isset($_GET['TrDuplicateNr']))
{
    $TrEditNr = $_GET['TrDuplicateNr'];
    $TransactionHeaderText = $lang["trans.duplicate.header"];
    $TransactionSubmit = $lang["trans.duplicate.submit"];
}

$s_page_title           = $TransactionHeaderText;
$b_compact_header       = true;
$a_head_css_add[]       = '<link rel="stylesheet" type="text/css" href="res/typeahead-bootstrap-0.11.1.css" />';
$a_head_js_add[]        = '<script src="res/app/base-1.0.4.js" type="text/javascript"></script>';
$a_head_js_add[]        = '<script src="res/typeahead.bundle-0.11.1.min.js" type="text/javascript"></script>';
$a_head_js_add[]        = '<script src="res/modernizr-3.2.0.js" type="text/javascript"></script>';
$a_head_js_add[]        = '<script src="res/app/new_transaction-1.3.0.js" type="text/javascript"></script>';

include_once '_common.php';
include_once '_header.php';

    attachments::delete_zero();

    if ($TrEditNr == 0)
        {
            $resultarray = array();
            $TransactionDate = date('Y-m-d');
            $TransactionStatus = costant::transaction_default_status();
            $TransactionType = costant::transaction_default_type();
            $TransactionAccount = various::last_account_get();
            $TransactionToAccount = 'None';
            $TransactionPayee = '';
            $TransactionCategory = '';
            $TransactionSubCategory = '';
            $TransactionAmount = '0';
            $TransactionNotes = 'Empty';
        }
        else
        {
            $resultarray = db_function::transaction_select_one($TrEditNr);
            $TransactionDate = $resultarray['Date'];
            $TransactionStatus = $resultarray['Status'];
            $TransactionType = $resultarray['Type'];
            $TransactionAccount = $resultarray['Account'];
            $TransactionToAccount = $resultarray['ToAccount'];
            $TransactionPayee = $resultarray['Payee'];
            $TransactionCategory = $resultarray['Category'];
            $TransactionSubCategory = $resultarray['SubCategory'];
            $TransactionAmount = $resultarray['Amount'];
            $TransactionNotes = $resultarray['Notes'];
        }

    if (sizeof($resultarray) > 0 || $FlagNew)
        {
            if ($saved_action === 'added' || $saved_action === 'duplicated')
            {
                $toast_key = 'trans.msg.action-' . $saved_action . '.successfully';
                echo '<div class="capture-toast" id="capture_toast">' . htmlspecialchars($lang[$toast_key]) . '</div>';
            }

            echo "<div class='container'>";
                echo "<form id='Transaction' class='form-transaction' method='post' action='insert.php'>";

                    design::input_amount($TransactionAmount);
                    if (costant::disable_payee() !== True)
                        {
                            design::input_payee($TransactionPayee);
                        }
                    else
                        {
                            design::input_hidden("Payee","None");
                        }
                    if (costant::disable_category() !== True)
                        {
                            design::input_category($TransactionCategory);
                            design::input_subcategory($TransactionSubCategory);
                        }
                    else
                        {
                            design::input_hidden("Category","None");
                            design::input_hidden("SubCategory","None");
                        }
                    design::input_account($TransactionAccount);
                    design::input_type($TransactionType);
                    design::input_toaccount($TransactionToAccount);
                    design::input_date($TransactionDate);

                    echo '<details class="capture-more">';
                    echo '<summary>' . htmlspecialchars($lang["trans.more"]) . '</summary>';
                    design::input_status($TransactionStatus);
                    design::input_notes($TransactionNotes);

                    echo "<div class='form-group'>";
                        echo "<label class='width100' for='fileToUpload'>{$lang["trans.upload.label"]}</label><br />";
                        echo "<input type='file' name='fileToUpload' id='fileToUpload' onchange='attachment_uploadFile({$TrEditNr});' />";
                        echo "<span class='help-block'></span>";
                    echo "</div>\n";

                    echo "<div class='table-responsive' id='attachments_table'>";
                    echo "</div>\n";
                    echo '</details>';

                    if (isset($_GET['TrDuplicateNr']))
                    {
                        design::input_hidden("TrEditedNr", 0);
                    }
                    elseif (isset($_GET['TrEditNr']))
                    {
                        design::input_hidden("TrEditedNr",$TrEditNr);
                    }

                    echo "<button type='submit' id='SubmitButton' name='SubmitButton' class='btn btn-lg btn-success btn-block'>{$TransactionSubmit}</button>";

                echo "</form>";
            echo "</div>\n";

            echo "<script type='text/javascript'>";
                if ($FlagNew)
                {
                    echo "document.getElementById('Amount').focus();\n";
                }
                else
                {
                    echo "populate_sub_category(false);\n";
                }
                echo "applyTransactionTypeUi();\n";
                echo "attachment_RefreshTable({$TrEditNr});\n";
                echo "$('#Payee').bind('input', set_default_category);\n";
                echo "$('#Payee').bind('typeahead:selected', set_default_category);\n";
                echo "$('#Category').bind('input', populate_sub_category);\n";
                echo "$('#Category').bind('typeahead:selected', populate_sub_category);\n";
                echo "$('input[name=Type]').on('change', applyTransactionTypeUi);\n";
            echo "</script>";
        }
    else
        {
            echo "<div class='container'>";
                echo "<br />";
                echo "<h3 class='text_align_center'>Wrong transaction ID</h3>";
                echo "<br />";
                echo "<a href='new_transaction.php' class='btn btn-lg btn-success btn-block'>{$lang["show.add_new"]}</a>";
                echo "<br />";
            echo "</div>\n";
        }

include_once '_footer.php';
