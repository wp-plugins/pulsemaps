<?php

/*  Copyright 2011-2013 Aito Software Inc. (email : contact@aitosoftware.com)

    This program is free software; you can redistribute it and/or modify
    it under the terms of the GNU General Public License, version 2, as
    published by the Free Software Foundation.

    This program is distributed in the hope that it will be useful,
    but WITHOUT ANY WARRANTY; without even the implied warranty of
    MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
    GNU General Public License for more details.

    You should have received a copy of the GNU General Public License
    along with this program; if not, write to the Free Software
    Foundation, Inc., 51 Franklin St, Fifth Floor, Boston, MA  02110-1301  USA
*/

class PulseMapsWidget extends WP_Widget {

    function PulseMapsWidget() {
		parent::WP_Widget(false, $name = 'PulseMaps',
						  array('description' => "Show off your visitors on the world map."));
    }

    function widget($args, $instance) {
		global $wpdb;
		global $pulsemaps_url;
        extract( $args );
        $title = apply_filters('widget_title', $instance['title']);
		echo $before_widget;

		if ($title) {
			echo $before_title . $title . $after_title;
		}

		$opts = get_option('pulsemaps_options');
		$id = $opts['id'];
		$width = $opts['widget_width'];

		$url = "$pulsemaps_url/widget.js?id=$id&width=$width";
		if ($opts['widget_type'] == 'satellite') {
			$url .= "&type=satellite";
		} else {
			$color = $opts['widget_color'];
			$bgcolor = $opts['widget_bgcolor'];
			$url .= "&color=$color&bgcolor=$bgcolor$extra";
		}
		$url .= '&meta=' . $opts['widget_meta'];
		$url .= '&notrack=1';

		if (!$opts['widget_dots']) {
			$url .= '&nodots=1';
		}

		if ($opts['widget_new_window']) {
			$url .= '&wnd=1';
		}

?>
<div id="pulsemaps_widget"></div>
<script type="text/javascript">
	(function() {
     var pm=document.createElement('script');
	 pm.type = 'text/javascript';
	 pm.async = true;
	 pm.src = '<?php echo $url; ?>';
	 pm.id = 'pulsemaps_<?php echo $id; ?>';
	 var s = document.getElementById('pulsemaps_widget');
	 s.parentNode.appendChild(pm);
	})();
</script>
<?php
	    if (is_home()) { echo $opts['after_text']; }
		echo $after_widget;
	}

    function update($new_instance, $old_instance) {
		$instance = $old_instance;
		$instance['title'] = strip_tags($new_instance['title']);
        return $instance;
    }

    function form($instance) {
        $title = esc_attr($instance['title']);
        ?>
         <p>
			  <label for="<?php echo $this->get_field_id('title'); ?>"><?php _e('Title:'); ?></label>
          <input class="widefat" id="<?php echo $this->get_field_id('title'); ?>" name="<?php echo $this->get_field_name('title'); ?>" type="text" value="<?php echo $title; ?>" />
        </p>
        <?php
	}

}

add_action('widgets_init', create_function('', 'return register_widget("PulseMapsWidget");'));
