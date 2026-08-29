var show = {
    init: function(){
        $(function() {
            show.monitorEditTransaction();
            show.monitorDeleteCheckboxes();
            show.monitorDuplicateTransaction();
            show.monitorDeleteSubmit();
        });
    },

    monitorDuplicateTransaction: function(){
        $('.TrDuplicate').on('click', function(){
            $('#TrEdit').val($(this).attr('tr_id'));
            $('#btn_action').val('Duplicate');
            $('#Show_Function').trigger('submit');
        });
    },

    monitorEditTransaction: function(){
        $('.TrModify').on('click', function(){
            $('#TrEdit').val($(this).attr('tr_id'));
            $('#btn_action').val('Edit');
            $('#Show_Function').trigger('submit');
        });
    },

    monitorDeleteCheckboxes: function(){
        var btn_delete = $('#TrDelete');
        btn_delete.hide();

        $('.do-delete').on('change', function(){
            if ($('.do-delete:checked').length > 0) {
                btn_delete.show();
                $('#btn_new').hide();
            } else {
                btn_delete.hide();
                $('#btn_new').show();
            }
        });
    },

    monitorDeleteSubmit: function(){
        $('#Show_Function').on('submit', function(){
            if ($('#btn_action').val() === 'Delete' || $('#TrDelete').is(':focus')) {
                var n = $('.do-delete:checked').length;
                if (n === 0) {
                    return true;
                }
                return confirm(n === 1
                    ? 'Delete this pending transaction?'
                    : 'Delete ' + n + ' pending transactions?');
            }
            return true;
        });
    }
};

show.init();
