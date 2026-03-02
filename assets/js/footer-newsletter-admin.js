(function () {
    'use strict';

    var $ = window.jQuery;
    if ( ! $ ) return;

    function populateFields( select, fields, selected ) {
        select.empty();
        select.append( $('<option>').attr('value', '').text('— Selecciona un campo —') );
        fields.forEach(function(f){
            var label = f.label || f.name || f;
            var opt = $('<option>').attr('value', f.name).text(label + ' (' + f.name + ')');
            if ( selected && selected === f.name ) opt.prop('selected', true);
            select.append(opt);
        });
    }

    $(document).ready(function(){
        var formSelect = $('#jaspi_footer_forminator_form_id');
        var fieldSelect = $('#jaspi_footer_forminator_field_name');
        var currentField = fieldSelect.val();

        function fetchFields(formId, cb) {
            if (!formId) {
                populateFields(fieldSelect, []);
                return;
            }
            $.post(
                jaspiFooterNewsletterAdmin.ajax_url,
                {
                    action: 'jaspi_get_forminator_fields',
                    nonce: jaspiFooterNewsletterAdmin.nonce,
                    form_id: formId
                },
                function (resp) {
                    if (resp && resp.success && resp.data && resp.data.fields) {
                        cb(null, resp.data.fields);
                    } else {
                        cb(new Error('No fields'));
                    }
                }
            ).fail(function(){ cb(new Error('AJAX fail')); });
        }

        formSelect.on('change', function(){
            var fid = $(this).val();
            fetchFields(fid, function(err, fields){
                if (err) {
                    populateFields(fieldSelect, []);
                    return;
                }
                populateFields(fieldSelect, fields, currentField);
            });
        });

        // initial load if form preselected
        if (formSelect.val()) {
            fetchFields(formSelect.val(), function(err, fields){
                if (!err) populateFields(fieldSelect, fields, currentField);
            });
        }
    });
})();
