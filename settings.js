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
	var dots = jQuery("input[name='pulsemaps_options[widget_dots]']:checked").val();
	var meta = jQuery("select[name='pulsemaps_options[widget_meta]']").val();

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
	scr.src = url + '&type=' + type + '&width=' + width + '&color=' + color + '&bgcolor=' + bgcolor
	    + '&meta=' + meta;
	if (!dots) {
		scr.src += '&nodots=1';
	}
	jQuery('#script-container').append(scr);
};

pulsemaps.install = function(event) {
	event.preventDefault();

	var btn = jQuery('#pulsemaps_install_button');
	var loader = jQuery('#pulsemaps_install_loading');
	var msg = jQuery('#pulsemaps_install_error');

	if (btn.hasClass('button-disabled')) {
		return;
	}

	btn.addClass('button-disabled');
	loader.show();
	msg.hide();

	var data = {'email': jQuery('#email').val(),
				'password': jQuery('#password').val(),
				'action': 'pulsemaps_register'};
	jQuery.ajax({
		type: 'POST',
		url: ajaxurl,
		data: data,
		dataType: 'json',
		success: function(data, status) {
			if (data.status != 'ok') {
				msg.html(data.errorMessage).show();
				btn.removeClass('button-disabled');
				loader.hide();
			} else {
				location.reload();
			}
		},
		error: function(request, status, error) {
			var html = 'An error occurred when connecting to your WordPress server.  Please try again later.<br/>';
			html += 'If the problem persists, contact <a href="mailto:support@pulsemaps.com">support@pulsemaps.com</a> and include the following information:<br/><br/>';
			html += 'Status: ' + status + '<br/>';
			html += 'Error: ' + error + '<br/>';
			msg.html(html).show();
			btn.removeClass('button-disabled');
			loader.hide();
		}
	});
};

pulsemaps.setHooks = function(url) {
	jQuery('input.widget-param, select.widget-param').change(function() {
		window.onbeforeunload = function() { return "Your changes haven't been saved, are you sure you want to leave the page?"; };
		pulsemaps.updatePreview(url);
	});
	jQuery("form").submit(function() { window.onbeforeunload = null; });
};

pulsemaps.loadInfo = function() {
	var admin_url = pulsemaps_data.adminUrl;
	var proxy_url = pulsemaps_data.proxyUrl;
	var id = pulsemaps_data.id;
	var key = pulsemaps_data.key;
	jQuery("#pulsemaps_plan_load").html('<img src="' + admin_url + '/images/loading.gif" alt="loading...">');
	jQuery("#pulsemaps_plan_load").load(proxy_url,
										{path: '/maps/' + id + '/wp-info.html',
										 key: key});
};


jQuery(document).ready(function() {
	if (typeof pulsemaps_data !== 'undefined') {
		var url = pulsemaps_data.url;
		var id = pulsemaps_data.id;
		url += '/widget.js?id=' + id + '&notrack=1&target=widget-preview';
		pulsemaps.setHooks(url);
		pulsemaps.updatePreview(url);
		pulsemaps.loadInfo();
	} else {
		jQuery('#pulsemaps_install_form').submit(pulsemaps.install);
	}
});
