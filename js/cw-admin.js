
jQuery(function($){
    var idx = jQuery('#cw_monedas_list .cw-moneda-row').length;
    $('#cw_add_moneda').on('click', function(e){
        e.preventDefault();
        var tmpl = $('#cw_moneda_template').html();
        tmpl = tmpl.replace(/__index__/g, idx);
        $('#cw_monedas_list').append(tmpl);
        idx++;
    });
    $(document).on('click', '.cw-delete-row', function(e){
        e.preventDefault();
        if(confirm(cwAdmin.confirm_delete)){
            $(this).closest('.cw-moneda-row').remove();
        }
    });
    $(document).on('click', '.cw-upload-flag', function(e){
        e.preventDefault();
        var btn = $(this);
        var input = btn.closest('td').find('.cw-flag-url');
        var preview = btn.closest('td').find('.cw-flag-preview');
        var frame = wp.media({ title: 'Seleccionar bandera', multiple:false, library:{ type: 'image' }});
        frame.on('select', function(){
            var att = frame.state().get('selection').first().toJSON();
            input.val(att.url);
            preview.html('<img src="'+att.url+'" style="height:20px;">');
        });
        frame.open();
    });
});
