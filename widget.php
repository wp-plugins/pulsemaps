<?php

/*  Copyright 2011 Aito Software Inc. (email : contact@aitosoftware.com)

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
		global $pulsemaps_api;
        extract( $args );
        $title = apply_filters('widget_title', $instance['title']);
		echo $before_widget;

		if ($title) {
			echo $before_title . $title . $after_title;
		}

		$opts = get_option('pulsemaps_options');
		$id = $opts['id'];
		$style = get_option('pulsemaps_widget');
		echo "<script type=\"text/javascript\" id=\"pulsemaps_$id\" src=\"$pulsemaps_api/widget.js?id=$id&style=$style\"></script>";
		echo "<a style=\"color: rgba(40,40,40,0.5);\" href=\"http://pulsemaps.com/\">Visitor tracking by PulseMaps.com</a>";

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
