/*!
 * WebApp functions v0.9.9
 * Copyright 2014 Gabriele-V
 */

function test_html5 ()
    {
        if  (      !Modernizr.inputtypes.date  // Not working in Safari (Mac)
                || !Modernizr.inputtypes.number
                || !Modernizr.input.required  // No submit disable support in Safari (Mac)
                || !Modernizr.input.min
				//|| !Modernizr.input.placeholder   //Used but not prerequisite
				//|| !Modernizr.input.autofocus     //Used but not prerequisite
                //|| !Modernizr.input.step          //Used but not prerequisite
            )
            {
                alert("Seems that the browser doesn't fully supports HTML5" + '\n' + '\n' + "Please make attention because it doesn't validate fields!");
                //close();
            }
    }
    
var substringMatcher = function(strs)
    {
        return function findMatches(q, cb)
        {
            var matches, substringRegex;
            matches = [];
            substrRegex = new RegExp(q, 'i');
            $.each(strs, function(i, str)
                {
                    if (substrRegex.test(str) || q === '')
                        {matches.push({ value: str });}
                });
            if (matches.length == 1 && matches[0].value == q)
                {matches = [];}
        cb(matches);
        };
    };

function get_today ()
    {
        var now = new Date();
        var y1 = now.getFullYear();
        var m1 = now.getMonth()+1;
        if(m1<10)
            m1="0"+m1;
        var dt1 = now.getDate();
        if(dt1<10)
            dt1 = "0"+dt1;
        return y1+"-"+m1+"-"+dt1;
    }
 
function get_php_page_without_return (url)
    {
        $.get(url);
        return false;
    }

function get_php_page (url, callback)
    {
        $.ajax({
            url:    url,
            type:   "GET"
        }).done(function (data) {
            if (typeof callback === 'function')
            {
                callback(data);
            }
        }).fail(function () {
            if (typeof callback === 'function')
            {
                callback('<span style="color:red;">err</span>');
            }
        });
    }
    
function send_alert_and_redirect (alertmessage,newurl)
    {
        alert(alertmessage);
        window.location.href = newurl;
    }

function selectedTransactionType()
{
    var checked = document.querySelector('input[name="Type"]:checked');
    return checked ? checked.value : '';
}

function applyTransactionTypeUi()
{
    var type = selectedTransactionType();
    var isTransfer = type === 'Transfer';
    var toAccount = document.getElementById('ToAccount');
    var payee = document.getElementById('Payee');
    if (toAccount)
    {
        toAccount.disabled = !isTransfer;
        toAccount.parentElement.style.display = isTransfer ? '' : 'none';
        if (!isTransfer)
        {
            toAccount.value = 'None';
        }
    }
    if (payee)
    {
        payee.disabled = isTransfer;
        payee.required = !isTransfer;
        var payeeGroup = payee.closest ? payee.closest('.form-group') : payee.parentElement.parentElement;
        if (payeeGroup)
        {
            payeeGroup.style.display = isTransfer ? 'none' : '';
        }
        if (isTransfer)
        {
            payee.value = '';
        }
    }
}

function enable_element(element_to_enable,element_to_test,value_to_enable)
{
    applyTransactionTypeUi();
}
    
function disable_element(element_to_disable,element_to_test,value_to_disable)
{
    applyTransactionTypeUi();
}

function disable_confirm_password_if_empty()
    {
        if (document.getElementById("Set_Password").value == "")
            {document.getElementById("Set_Confirm_Password").disabled = true;}
        else
            {document.getElementById("Set_Confirm_Password").disabled = false;}
    }

function check_passwor_error()
    {
        if (document.getElementById("Set_Password").value != document.getElementById("Set_Confirm_Password").Value)
            {
                document.getElementById("Set_Confirm_Password").setAttribute("class", "form-control has-error");
                //document.getElementById("Set_Confirm_Password").ClassName = " has-error";
                console.log (document.getElementById("Set_Confirm_Password").ClassName);
            }
        else
            {
                //Normal
            }
    }

function check_password_match_and_submit (Password1,Password2,formid)
    {
        if (document.getElementById(Password1).value !== document.getElementById(Password2).value)
            {alert("Password doesn't match!");}
        else
            {document.getElementById(formid).submit();}
    }

function set_default_category ()
    {
        PayeeName = document.getElementById("Payee").value;
        $.getJSON("query.php?get_default_category="+PayeeName, function(json) {
            if (!jQuery.isEmptyObject(json))
            {
                // if merely changing payee and no category selected before
                // only then allow to change parent category and subcategory
                if (json.DefCateg != "None" && document.getElementById("Category").value == '')
                {
                    document.getElementById("Category").value = json.DefCateg;

                    // change subcategory only when parent category changed
                    // …with a child that belongs to new parent
                    if (json.DefSubCateg != "None")
                    {
                        document.getElementById("SubCategory").value = json.DefSubCateg;
                    }
                    // …otherwise reset child
                    else
                    {
                        document.getElementById("SubCategory").value = '';
                    }
                }
                populate_sub_category(false);
            }
        });
    }

function populate_sub_category (bCleanInput)
    {
        var categoryEl = document.getElementById("Category");
        var subEl = document.getElementById("SubCategory");
        if (!categoryEl || !subEl)
        {
            return;
        }
        var CategoryName = categoryEl.value;
        if(bCleanInput == true || typeof bCleanInput == "object")
            {subEl.value = "";}
        $('#SubCategory').typeahead('destroy');
        $.getJSON("query.php?get_subcategory="+encodeURIComponent(CategoryName), function(json) {
            var SubCategoryList = [];
            $.each(json,function(i,item){
                if (item.SubCategoryName && item.SubCategoryName !== 'None')
                {
                    SubCategoryList.push(item.SubCategoryName);
                }
            });
            $('#SubCategory').typeahead(
                {hint: true, highlight: true, minLength: 0},
                {name: 'SubCategoryList', limit:15, displayKey: 'value',source: substringMatcher(SubCategoryList)}
            );
        });
    }

function attachment_RefreshTable(TrID)
    {
        var table = document.getElementById('attachments_table');
        if (!table)
        {
            return;
        }
        get_php_page('attachments.php?AttachmentsTable='+TrID, function (html) {
            table.innerHTML = html;
        });
    }
 
function attachment_uploadFile(TrId)
    {
        var fd = new FormData();
        var count = document.getElementById('fileToUpload').files.length;
        for (var index = 0; index < count; index ++)
            {
                var file = document.getElementById('fileToUpload').files[index];
                fd.append('UploadedAttachments', file);
            }
        fd.append('Attachment_TrId',TrId);
        var xhr = new XMLHttpRequest();
        xhr.TrId = TrId;
        xhr.addEventListener("load", attachment_uploadComplete, false);
        xhr.addEventListener("error", attachment_uploadFailed, false);
        xhr.addEventListener("abort", attachment_uploadCanceled, false);
        xhr.open("POST", "attachments.php");
        xhr.send(fd);
    }
 
function attachment_uploadComplete(evt)
    {
        attachment_RefreshTable(evt.target.TrId);
    }
 
function attachment_uploadFailed(evt)
    {
        alert("There was an error attempting to upload the file.");
    }
 
function attachment_uploadCanceled(evt)
    {
        alert("The upload has been canceled by the user or the browser dropped the connection.");
    }

function attachment_delete(FileName,TrID)
    {
        get_php_page_without_return("attachments.php?DeleteAttach="+FileName);
        attachment_RefreshTable(TrID);
    }
