jQuery(document).ready(function ($) {
    $('.boomerang-color-picker').wpColorPicker({
        change: function(event, ui){
            var rgb = ui.color.toCSS( 'rgb' ).replace(')', ', 0.17)').replace('rgb', 'rgba');;
            $('#background-color').val(rgb);
        },
        // a callback to fire when the input is emptied or an invalid color
        clear: function() {},
    });
});