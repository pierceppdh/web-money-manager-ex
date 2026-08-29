<?php

$b_restricted_auth  = true;
$b_compact_header   = true;

$a_head_js_add[]      = '<script src="res/app/base-1.0.4.js" type="text/javascript"></script>';
$a_head_js_add[]      = '<script src="res/app/show-1.2.0.js" type="text/javascript"></script>';

include_once '_common.php';

$s_page_title = $lang["page.show-transactions"];
include_once '_header.php';

function show_h($value)
{
    return htmlspecialchars((string)$value, ENT_QUOTES, 'UTF-8');
}

function show_format_date($date)
{
    $ts = strtotime($date);
    if ($ts === false)
    {
        return show_h($date);
    }
    return show_h(date('D j M Y', $ts));
}

function show_type_meta($type)
{
    switch ($type)
    {
        case 'Deposit':
            return array('label' => costant::lang('trans.type.deposit'), 'icon' => 'plus', 'class' => 'show-amount-deposit');
        case 'Transfer':
            return array('label' => costant::lang('trans.type.transfer'), 'icon' => 'transfer', 'class' => 'show-amount-transfer');
        default:
            return array('label' => costant::lang('trans.type.withdrawal'), 'icon' => 'minus', 'class' => 'show-amount-withdrawal');
    }
}

$resultarray = db_function::transaction_select_all_order_by_date('DESC');
$count = is_array($resultarray) ? count($resultarray) : 0;

echo '<div class="container show-page">';

if ($count === 0)
{
    echo '<h3 class="text_align_center">' . show_h($lang['show.no_pending_trans']) . '</h3>';
    echo '<br />';
    echo '<a href="new_transaction.php" class="btn btn-lg btn-success btn-block">' . show_h($lang['show.add_new']) . '</a>';
    echo '</div>';
    include_once '_footer.php';
    return;
}

echo '<a href="new_transaction.php" class="btn btn-lg btn-success btn-block" id="btn_new">' . show_h($lang['show.new']) . '</a>';

$pending_label = ($count === 1) ? $lang['show.pending_one'] : sprintf($lang['show.pending_many'], $count);
echo '<p class="show-count">' . show_h($pending_label) . '</p>';

echo '<form id="Show_Function" class="form-show-function" method="post" action="show_function.php">';
echo '<input type="hidden" id="btn_action" name="btn_action" value="" />';
echo '<input type="hidden" id="TrEdit" name="TrEdit[]" value="" />';
echo '<button type="submit" id="TrDelete" name="btn_action" value="Delete" class="btn btn-lg btn-danger btn-block">' . show_h($lang['show.delete_all_selected']) . '</button>';

$current_date = null;
foreach ($resultarray as $row)
{
    if (!isset($row['ID']))
    {
        continue;
    }

    if ($current_date !== $row['Date'])
    {
        if ($current_date !== null)
        {
            echo '</div>';
        }
        $current_date = $row['Date'];
        echo '<div class="show-day">';
        echo '<div class="show-day-label">' . show_format_date($row['Date']) . '</div>';
    }

    $id = $row['ID'];
    $type = isset($row['Type']) ? $row['Type'] : 'Withdrawal';
    $meta = show_type_meta($type);
    $amount = number_format((float)$row['Amount'], 2, ',', ' ');
    $account = isset($row['Account']) ? $row['Account'] : '';
    $to_account = isset($row['ToAccount']) ? $row['ToAccount'] : '';
    $payee = isset($row['Payee']) ? $row['Payee'] : '';
    $category = isset($row['Category']) ? $row['Category'] : '';
    $sub = isset($row['SubCategory']) ? $row['SubCategory'] : '';
    $notes = isset($row['Notes']) ? $row['Notes'] : '';

    if ($type === 'Transfer' && $to_account !== '' && $to_account !== 'None')
    {
        $account_line = $account . ' → ' . $to_account;
    }
    else
    {
        $account_line = $account;
    }

    $details = array();
    if ($payee !== '' && $payee !== 'None' && costant::disable_payee() == False)
    {
        $details[] = $payee;
    }
    if ($category !== '' && $category !== 'None' && costant::disable_category() == False)
    {
        $cat_line = $category;
        if ($sub !== '' && $sub !== 'None')
        {
            $cat_line .= ' / ' . $sub;
        }
        $details[] = $cat_line;
    }

    echo '<div class="show-card">';
        echo '<label class="show-check">';
            echo '<input class="do-delete" type="checkbox" name="TrDelete[]" value="' . show_h($id) . '" />';
        echo '</label>';
        echo '<div class="show-main">';
            echo '<div class="show-line1">';
                echo '<span class="show-amount ' . $meta['class'] . '">' . show_h($amount) . '</span>';
                echo '<span class="show-type"><span class="glyphicon glyphicon-' . $meta['icon'] . '"></span> ' . show_h($meta['label']) . '</span>';
            echo '</div>';
            echo '<div class="show-line2">' . show_h($account_line) . '</div>';
            if (!empty($details))
            {
                echo '<div class="show-line3">' . show_h(implode(' · ', $details)) . '</div>';
            }
            if ($notes !== '' && $notes !== 'None' && $notes !== 'Empty')
            {
                echo '<div class="show-notes">' . show_h($notes) . '</div>';
            }
        echo '</div>';
        echo '<div class="show-actions">';
            echo '<button type="button" class="show-icon-btn do-edit TrModify" tr_id="' . show_h($id) . '" title="' . show_h($lang['trans.update.header']) . '"><span class="glyphicon glyphicon-edit"></span></button>';
            echo '<button type="button" class="show-icon-btn do-duplicate TrDuplicate" tr_id="' . show_h($id) . '" title="' . show_h($lang['trans.duplicate.header']) . '"><span class="glyphicon glyphicon-duplicate"></span></button>';
        echo '</div>';
    echo '</div>';
}

if ($current_date !== null)
{
    echo '</div>';
}

echo '</form>';
echo '</div>';

include_once '_footer.php';
