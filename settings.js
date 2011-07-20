var pulsemaps = pulsemaps || {};

pulsemaps.updatePreview = function(url) {
    // Get the selected color.
    var color = 'F2EFE8';
    var csel = jQuery("input[name='widget_color']:checked").val();
    if (csel == 'custom') {
	color = jQuery('#custom-color').val();
    }
    jQuery('input[name="pulsemaps_options[widget_color]"]').val(color);

    // Get the selected background color.
    var bgcolor = '99B2CF';
    var csel = jQuery("input[name='widget_bgcolor']:checked").val();
    if (csel == 'custom') {
	bgcolor = jQuery('#custom-bgcolor').val();
    } else if (csel == 'dark') {
	bgcolor = '3b3b3b';
    } else if (csel == 'transparent') {
	bgcolor = 'transparent';
    }
    jQuery('input[name="pulsemaps_options[widget_bgcolor]"]').val(bgcolor);

    // Get other params.
    var type = jQuery('input:radio[name="pulsemaps_options[widget_type]"]:checked').val();
    var width = jQuery('#widget-width').val();

    if (type == 'satellite') {
	jQuery('.widget-plain').attr('disabled', 'disabled');
    } else {
	jQuery('.widget-plain').removeAttr('disabled');
    }
    // Remove current preview.
    jQuery('#widget-preview').empty();
    jQuery('#script-container').empty();

    // Load new preview.
    var scr = document.createElement('script');
    scr.type = 'text/javascript';
    scr.src = url + '&type=' + type + '&width=' + width + '&color=' + color + '&bgcolor=' + bgcolor;
    jQuery('#script-container').append(scr);
}

pulsemaps.setHooks = function(url) {
    jQuery('input.widget-param').change(function() {
	window.onbeforeunload = function() { return "Your changes haven't been saved, are you sure you want to leave the page?"; };
	pulsemaps.updatePreview(url);
    });
    jQuery("form").submit(function() { window.onbeforeunload = null; });
}
