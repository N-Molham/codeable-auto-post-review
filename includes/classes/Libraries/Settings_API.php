<?php namespace Codeable_AutoPost_Review\Libraries;

use Codeable_AutoPost_Review\Helpers;

/**
 * WordPress Settings API wrapper class
 *
 * @package Master_Key_Experience_Content_Submission
 */
class Settings_API {

	/**
	 * settings sections array
	 *
	 * @var array
	 */
	protected $settings_sections = [];

	/**
	 * Settings fields array
	 *
	 * @var array
	 */
	protected $settings_fields = [];

	/**
	 * Set settings sections
	 *
	 * @param array $sections setting sections array
	 *
	 * @return self
	 */
	public function set_sections( $sections ) {
		$this->settings_sections = $sections;

		return $this;
	}

	/**
	 * Add a single section
	 *
	 * @param array $section
	 *
	 * @return self
	 */
	public function add_section( $section ) {
		$this->settings_sections[] = $section;

		return $this;
	}

	/**
	 * Set settings fields
	 *
	 * @param array $fields settings fields array
	 *
	 * @return self
	 */
	public function set_fields( $fields ) {
		$this->settings_fields = $fields;

		return $this;
	}

	/**
	 * Get settings fields
	 *
	 * @return array
	 */
	public function get_fields() {
		return $this->settings_fields;
	}

	/**
	 * Add new field to section
	 *
	 * @param string $section
	 * @param array  $field
	 *
	 * @return self
	 */
	public function add_field( $section, $field ) {
		$this->settings_fields[ $section ][] = wp_parse_args( $field, $this->field_default_args() );

		return $this;
	}

	/**
	 * Initialize and registers the settings sections and fileds to WordPress
	 *
	 * Usually this should be called at `admin_init` hook.
	 *
	 * This function gets the initiated settings sections and fields. Then
	 * registers them to WordPress and ready for use.
	 *
	 * @return void
	 */
	public function admin_init() {
		//register settings sections
		foreach ( $this->settings_sections as $section ) {
			if ( false === get_option( $section['id'] ) ) {
				add_option( $section['id'] );
			}

			$callback = null;
			if ( isset( $section['desc'] ) && ! empty( $section['desc'] ) ) {
				$section['desc'] = '<div class="inside">' . $section['desc'] . '</div>';
				$callback        = create_function( '', 'echo "' . str_replace( '"', '\"', $section['desc'] ) . '";' );
			} else if ( isset( $section['callback'] ) ) {
				$callback = $section['callback'];
			}

			add_settings_section( $section['id'], $section['title'], $callback, $section['id'] );
		}

		//register settings fields
		foreach ( $this->settings_fields as $section => $field ) {
			foreach ( $field as $option ) {

				$name     = $option['name'];
				$type     = isset( $option['type'] ) ? $option['type'] : 'text';
				$label    = isset( $option['label'] ) ? $option['label'] : '';
				$callback = isset( $option['callback'] ) ? $option['callback'] : [ &$this, 'callback_' . $type ];
				if ( ! is_callable( $callback ) ) {
					// skip field without a valid type
					continue;
				}

				$args = [
					'id'                => $name,
					'class'             => isset( $option['class'] ) ? $option['class'] : $name,
					'label_for'         => "{$section}[{$name}]",
					'desc'              => isset( $option['desc'] ) ? $option['desc'] : '',
					'name'              => $label,
					'section'           => $section,
					'size'              => isset( $option['size'] ) ? $option['size'] : null,
					'options'           => isset( $option['options'] ) ? $option['options'] : '',
					'std'               => isset( $option['default'] ) ? $option['default'] : '',
					'sanitize_callback' => isset( $option['sanitize_callback'] ) ? $option['sanitize_callback'] : '',
					'type'              => $type,
					'placeholder'       => isset( $option['placeholder'] ) ? $option['placeholder'] : '',
					'min'               => isset( $option['min'] ) ? $option['min'] : '',
					'max'               => isset( $option['max'] ) ? $option['max'] : '',
					'step'              => isset( $option['step'] ) ? $option['step'] : '',
				];

				add_settings_field( "{$section}[{$name}]", $label, $callback, $section, $section, $args );
			}
		}

		// creates our settings in the options table
		foreach ( $this->settings_sections as $section ) {
			register_setting( $section['id'], $section['id'], [ &$this, 'sanitize_options' ] );
		}
	}

	/**
	 * Get field description for display
	 *
	 * @param array $args settings field args
	 *
	 * @return string
	 */
	public function get_field_description( $args ) {
		return empty( $args['desc'] ) ? '' : sprintf( '<p class="description">%s</p>', $args['desc'] );
	}

	/**
	 * Displays a text field for a settings field
	 *
	 * @param array $args settings field args
	 *
	 * @return void
	 */
	public function callback_text( $args ) {

		$value       = esc_attr( $this->get_option( $args['id'], $args['section'], $args['std'] ) );
		$size        = isset( $args['size'] ) && null !== $args['size'] ? $args['size'] : 'regular';
		$type        = isset( $args['type'] ) ? $args['type'] : 'text';
		$placeholder = empty( $args['placeholder'] ) ? '' : ' placeholder="' . $args['placeholder'] . '"';

		$html = sprintf( '<input type="%1$s" class="%2$s-text" id="%3$s[%4$s]" name="%3$s[%4$s]" value="%5$s"%6$s/>', $type, $size, $args['section'], $args['id'], $value, $placeholder );
		$html .= $this->get_field_description( $args );

		echo $html;
	}

	/**
	 * Displays a url field for a settings field
	 *
	 * @param array $args settings field args
	 *
	 * @return void
	 */
	public function callback_url( $args ) {
		$this->callback_text( $args );
	}

	/**
	 * Displays a number field for a settings field
	 *
	 * @param array $args settings field args
	 *
	 * @return void
	 */
	public function callback_number( $args ) {
		$value       = esc_attr( $this->get_option( $args['id'], $args['section'], $args['std'] ) );
		$size        = isset( $args['size'] ) && null !== $args['size'] ? $args['size'] : 'regular';
		$type        = isset( $args['type'] ) ? $args['type'] : 'number';
		$placeholder = empty( $args['placeholder'] ) ? '' : ' placeholder="' . $args['placeholder'] . '"';
		$min         = empty( $args['min'] ) ? '' : ' min="' . $args['min'] . '"';
		$max         = empty( $args['max'] ) ? '' : ' max="' . $args['max'] . '"';
		$step        = empty( $args['max'] ) ? '' : ' step="' . $args['step'] . '"';

		$html = sprintf( '<input type="%1$s" class="%2$s-number" id="%3$s[%4$s]" name="%3$s[%4$s]" value="%5$s"%6$s%7$s%8$s%9$s/>', $type, $size, $args['section'], $args['id'], $value, $placeholder, $min, $max, $step );
		$html .= $this->get_field_description( $args );

		echo $html;
	}

	/**
	 * Displays a checkbox for a settings field
	 *
	 * @param array $args settings field args
	 *
	 * @return void
	 */
	public function callback_checkbox( $args ) {

		$value = esc_attr( $this->get_option( $args['id'], $args['section'], $args['std'] ) );

		$html = '<fieldset>';
		$html .= sprintf( '<label for="wpuf-%1$s[%2$s]">', $args['section'], $args['id'] );
		$html .= sprintf( '<input type="hidden" name="%1$s[%2$s]" value="off" />', $args['section'], $args['id'] );
		$html .= sprintf( '<input type="checkbox" class="checkbox" id="wpuf-%1$s[%2$s]" name="%1$s[%2$s]" value="on" %3$s />', $args['section'], $args['id'], checked( $value, 'on', false ) );
		$html .= sprintf( '%1$s</label>', $args['desc'] );
		$html .= '</fieldset>';

		echo $html;
	}

	/**
	 * Displays a multicheckbox for a settings field
	 *
	 * @param array $args settings field args
	 *
	 * @return void
	 */
	public function callback_multicheck( $args ) {

		$value = $this->get_option( $args['id'], $args['section'], $args['std'] );
		$html  = '<fieldset>';
		$html  .= sprintf( '<input type="hidden" name="%1$s[%2$s]" value="" />', $args['section'], $args['id'] );
		foreach ( $args['options'] as $key => $label ) {
			$checked = isset( $value[ $key ] ) ? $value[ $key ] : '0';
			$html    .= sprintf( '<label for="wpuf-%1$s[%2$s][%3$s]">', $args['section'], $args['id'], $key );
			$html    .= sprintf( '<input type="checkbox" class="checkbox" id="wpuf-%1$s[%2$s][%3$s]" name="%1$s[%2$s][%3$s]" value="%3$s" %4$s />', $args['section'], $args['id'], $key, checked( $checked, $key, false ) );
			$html    .= sprintf( '%1$s</label><br>', $label );
		}

		$html .= $this->get_field_description( $args );
		$html .= '</fieldset>';

		echo $html;
	}

	/**
	 * Displays a radio button for a settings field
	 *
	 * @param array $args settings field args
	 *
	 * @return void
	 */
	public function callback_radio( $args ) {

		$value = $this->get_option( $args['id'], $args['section'], $args['std'] );
		$html  = '<fieldset>';

		foreach ( $args['options'] as $key => $label ) {
			$html .= sprintf( '<label for="wpuf-%1$s[%2$s][%3$s]">', $args['section'], $args['id'], $key );
			$html .= sprintf( '<input type="radio" class="radio" id="wpuf-%1$s[%2$s][%3$s]" name="%1$s[%2$s]" value="%3$s" %4$s />', $args['section'], $args['id'], $key, checked( $value, $key, false ) );
			$html .= sprintf( '%1$s</label><br>', $label );
		}

		$html .= $this->get_field_description( $args );
		$html .= '</fieldset>';

		echo $html;
	}

	/**
	 * Displays a selectbox for a settings field
	 *
	 * @param array $args settings field args
	 *
	 * @return void
	 */
	public function callback_select( $args ) {

		$value = esc_attr( $this->get_option( $args['id'], $args['section'], $args['std'] ) );
		$size  = isset( $args['size'] ) && null !== $args['size'] ? $args['size'] : 'regular';
		$html  = sprintf( '<select class="%1$s" name="%2$s[%3$s]" id="%2$s[%3$s]">', $size, $args['section'], $args['id'] );

		foreach ( $args['options'] as $key => $label ) {
			$html .= sprintf( '<option value="%s"%s>%s</option>', $key, selected( $value, $key, false ), $label );
		}

		$html .= sprintf( '</select>' );
		$html .= $this->get_field_description( $args );

		echo $html;
	}

	/**
	 * Displays a textarea for a settings field
	 *
	 * @param array $args settings field args
	 *
	 * @return void
	 */
	public function callback_textarea( $args ) {
		$value       = esc_textarea( $this->get_option( $args['id'], $args['section'], $args['std'] ) );
		$size        = isset( $args['size'] ) && null !== $args['size'] ? $args['size'] : 'regular';
		$placeholder = empty( $args['placeholder'] ) ? '' : ' placeholder="' . $args['placeholder'] . '"';
		$rows        = isset( $args['rows'] ) ? $args['rows'] : '8';
		$columns     = isset( $args['cols'] ) ? $args['cols'] : '55';

		$html = sprintf( '<textarea rows="%6$s" cols="%7$s" class="%1$s-text" id="%2$s[%3$s]" name="%2$s[%3$s]"%4$s>%5$s</textarea>', $size, $args['section'], $args['id'], $placeholder, $value, $rows, $columns );
		$html .= $this->get_field_description( $args );

		echo $html;
	}

	/**
	 * Displays a wp editor for a settings field
	 *
	 * @param array $args settings field args
	 *
	 * @return void
	 */
	public function callback_editor( $args ) {
		$value = $this->get_option( $args['id'], $args['section'], $args['std'] );

		ob_start();
		wp_editor( $value, $args['id'], [
			'teeny'         => true,
			'textarea_rows' => '8',
			'textarea_name' => sprintf( '%s[%s]', $args['section'], $args['id'] ),
		] );
		$html = ob_get_clean();
		$html .= $this->get_field_description( $args );

		echo $html;
	}

	/**
	 * Displays the html for a settings field
	 *
	 * @param array $args settings field args
	 *
	 * @return void
	 */
	public function callback_html( $args ) {
		echo $this->get_field_description( $args );
	}

	/**
	 * Displays a rich text textarea for a settings field
	 *
	 * @param array $args settings field args
	 *
	 * @return void
	 */
	public function callback_wysiwyg( $args ) {

		$value = $this->get_option( $args['id'], $args['section'], $args['std'] );
		$size  = isset( $args['size'] ) && null !== $args['size'] ? $args['size'] : '500px';

		echo '<div style="max-width: ' . $size . ';">';

		$editor_settings = [
			'teeny'         => true,
			'textarea_name' => $args['section'] . '[' . $args['id'] . ']',
			'textarea_rows' => 10,
		];

		if ( isset( $args['options'] ) && is_array( $args['options'] ) ) {
			$editor_settings = array_merge( $editor_settings, $args['options'] );
		}

		wp_editor( $value, $args['section'] . '-' . $args['id'], $editor_settings );

		echo '</div>';

		echo $this->get_field_description( $args );
	}

	/**
	 * Displays a file upload field for a settings field
	 *
	 * @param array $args settings field args
	 *
	 * @return void
	 */
	public function callback_file( $args ) {

		$value = esc_attr( $this->get_option( $args['id'], $args['section'], $args['std'] ) );
		$size  = isset( $args['size'] ) && null !== $args['size'] ? $args['size'] : 'regular';
		$id    = $args['section'] . '[' . $args['id'] . ']';
		$label = isset( $args['options']['button_label'] ) ? $args['options']['button_label'] : __( 'Choose File' );

		$html = sprintf( '<input type="text" class="%1$s-text mkecs-url" id="%2$s[%3$s]" name="%2$s[%3$s]" value="%4$s"/>', $size, $args['section'], $args['id'], $value );
		$html .= '<input type="button" class="button mkecs-browse" value="' . $label . '" />';
		$html .= $this->get_field_description( $args );

		echo $html;
	}

	/**
	 * Displays a password field for a settings field
	 *
	 * @param array $args settings field args
	 *
	 * @return void
	 */
	public function callback_password( $args ) {

		$value = esc_attr( $this->get_option( $args['id'], $args['section'], $args['std'] ) );
		$size  = isset( $args['size'] ) && null !== $args['size'] ? $args['size'] : 'regular';

		$html = sprintf( '<input type="password" class="%1$s-text" id="%2$s[%3$s]" name="%2$s[%3$s]" value="%4$s"/>', $size, $args['section'], $args['id'], $value );
		$html .= $this->get_field_description( $args );

		echo $html;
	}

	/**
	 * Displays a color picker field for a settings field
	 *
	 * @param array $args settings field args
	 *
	 * @return void
	 */
	public function callback_color( $args ) {

		$value = esc_attr( $this->get_option( $args['id'], $args['section'], $args['std'] ) );
		$size  = isset( $args['size'] ) && null !== $args['size'] ? $args['size'] : 'regular';

		$html = sprintf( '<input type="text" class="%1$s-text wp-color-picker-field" id="%2$s[%3$s]" name="%2$s[%3$s]" value="%4$s" data-default-color="%5$s" />', $size, $args['section'], $args['id'], $value, $args['std'] );
		$html .= $this->get_field_description( $args );

		echo $html;
	}

	/**
	 * Displays a select box for creating the pages select box
	 *
	 * @param array $args settings field args
	 *
	 * @return void
	 */
	public function callback_pages( $args ) {
		echo wp_dropdown_pages( [
			'selected' => esc_attr( $this->get_option( $args['id'], $args['section'], $args['std'] ) ),
			'name'     => $args['section'] . '[' . $args['id'] . ']',
			'id'       => $args['section'] . '[' . $args['id'] . ']',
			'echo'     => 0,
		] );
	}

	/**
	 * Displays a select box for creating the roles select box
	 *
	 * @param array $args settings field args
	 *
	 * @return void
	 */
	public function callback_roles( $args ) {
		echo '<select name="', $args['section'] . '[' . $args['id'] . ']', '" id="', $args['section'] . '[' . $args['id'] . ']', '">';

		$selected_role  = $this->get_option( $args['id'], $args['section'], $args['std'] );
		$editable_roles = array_reverse( get_editable_roles() );

		foreach ( $editable_roles as $role => $details ) {
			$name = translate_user_role( $details['name'] );
			// preselect specified role
			if ( $selected_role == $role ) {
				echo '<option selected="selected" value="' . esc_attr( $role ) . '"">', $name, ' ( ', $role, ' )</option>';
			} else {
				echo '<option value="' . esc_attr( $role ) . '"">', $name, ' ( ', $role, ' )</option>';
			}
		}

		echo '</select>';
	}

	/**
	 * Sanitize callback for Settings API
	 *
	 * @param array $options
	 *
	 * @return mixed
	 */
	public function sanitize_options( $options ) {
		if ( ! $options ) {
			return $options;
		}

		foreach ( $options as $option_slug => $option_value ) {
			$sanitize_callback = $this->get_sanitize_callback( $option_slug );

			// If callback is set, call it
			if ( $sanitize_callback && is_callable( $sanitize_callback ) ) {
				$options[ $option_slug ] = call_user_func( $sanitize_callback, $option_value );
				continue;
			}
		}

		return $options;
	}

	/**
	 * Get sanitization callback for given option slug
	 *
	 * @param string $slug option slug
	 *
	 * @return mixed string or bool false
	 */
	public function get_sanitize_callback( $slug = '' ) {
		if ( empty( $slug ) ) {
			return false;
		}

		// Iterate over registered fields and see if we can find proper callback
		foreach ( $this->settings_fields as $section => $options ) {
			foreach ( $options as $option ) {
				if ( $option['name'] != $slug ) {
					continue;
				}

				// Return the callback name
				return isset( $option['sanitize_callback'] ) && is_callable( $option['sanitize_callback'] ) ? $option['sanitize_callback'] : false;
			}
		}

		return false;
	}

	/**
	 * Get the value of a settings field
	 *
	 * @param string $option settings field name
	 * @param string $section the section name this field belongs to
	 * @param string $default default text if it's not found
	 *
	 * @return mixed
	 */
	public function get_option( $option, $section, $default = null ) {
		if ( ! isset( $this->settings_fields[ $section ] ) || ! isset( $this->settings_fields[ $section ][ $option ] ) ) {
			// invalid field
			return null;
		}

		// use which default
		$default = null === $default ? $this->settings_fields[ $section ][ $option ]['default'] : $default;

		$options = get_option( $section );

		return isset( $options[ $option ] ) ? $options[ $option ] : $default;
	}

	/**
	 * Show navigations as tab
	 *
	 * Shows all the settings section labels as tab
	 *
	 * @return void
	 */
	public function show_navigation() {
		$html = '<h2 class="nav-tab-wrapper">';

		$count = count( $this->settings_sections );

		// don't show the navigation if only one section exists
		if ( $count === 1 ) {
			return;
		}

		foreach ( $this->settings_sections as $tab ) {
			$html .= sprintf( '<a href="#%1$s" class="nav-tab" id="%1$s-tab">%2$s</a>', $tab['id'], $tab['title'] );
		}

		$html .= '</h2>';

		echo $html;
	}

	/**
	 * Show the section settings forms
	 *
	 * This function displays every sections in a different form
	 *
	 * @return void
	 */
	public function show_forms() {
		// vars
		$enqueue_path   = Helpers::enqueue_path();
		$assets_version = Helpers::assets_version();

		wp_enqueue_media();
		wp_enqueue_style( 'wp-color-picker' );
		wp_enqueue_style( 'mkecs-style', $enqueue_path . 'css/settings_api.css', null, $assets_version );
		wp_enqueue_script( 'mkecs-admin', $enqueue_path . 'js/settings_api.js', [
			'jquery',
			'wp-color-picker',
		], $assets_version, true );

		?>
		<div class="metabox-holder">
			<?php foreach ( $this->settings_sections as $form ) : ?>
				<div id="<?php echo $form['id']; ?>" class="group" style="display: none;">
					<form method="post" action="options.php">
						<?php
						do_action( 'mkecs_form_top_' . $form['id'], $form );

						settings_fields( $form['id'] );

						do_settings_sections( $form['id'] );

						do_action( 'mkecs_form_bottom_' . $form['id'], $form );

						if ( isset( $this->settings_fields[ $form['id'] ] ) ) :
							?>
							<div style="padding-left: 10px">
								<?php submit_button(); ?>
							</div>
						<?php endif; ?>
					</form>
				</div>
			<?php endforeach; ?>
		</div>
		<?php
	}

	/**
	 * Field default args
	 *
	 * @return array
	 */
	public function field_default_args() {
		return [
			'name'    => '',
			'label'   => '',
			'desc'    => '',
			'type'    => 'text',
			'default' => '',
		];
	}
}