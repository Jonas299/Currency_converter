
jQuery(function ($) {
    $(document).on('click', '#cw_convert_btn', function (e) {
        e.preventDefault();
        var amount = $('#cw_amount').val();
        var from = $('#cw_from').val();
        var to = $('#cw_to').val();
        $('#cw_result').text('Obteniendo tipo de cambio...');
        $.post(cw_ajax_obj.ajax_url, { action: 'cw_convert', nonce: cw_ajax_obj.nonce, amount: amount, from: from, to: to }, function (resp) {
            if (resp.success) {
                $('#cw_result').text(amount + ' ' + resp.data.from + ' = ' + resp.data.converted + ' ' + resp.data.to + '');
            } else {
                $('#cw_result').text('Error: ' + resp.data);
            }
        }).fail(function () { $('#cw_result').text('Error en la petición'); });
    });
});
