<?php

/**
 * Class for any section view requested on the front end.
 *
 * @since 1.1
 */

class fdmViewSection extends fdmView {

	public $title = '';
	public $description = '';

	// Full menu object to capture the section's post data
	public $menu = null;

	/**
	 * Initialize the class
	 * @since 1.1
	 */
	public function __construct( $args ) {

		// Parse the values passed
		$this->parse_args( $args );
	}

	/**
	 * Render the view and enqueue required stylesheets
	 * @since 1.1
	 */
	public function render() {

		if ( !isset( $this->id ) ) {
			return;
		}

		// Gather data if it's not already set
		$this->load_section();

		// Define the classes for this section
		$this->set_classes();

		// Capture output
		ob_start();
		$template = fdm_find_template( 'menu-section', $this );
		if ( $template ) {
			include( $template );
		}
		$output = ob_get_clean();

		return apply_filters( 'fdm_menu_section_output', $output, $this );
	}

	/**
	 * Load section data
	 * @since 1.1
	 */
	public function load_section() {

		if ( !isset( $this->id ) ) {
			return;
		}

		// Make sure the section has posts before we load the data.
		$items = new WP_Query( array(
			'post_type'      	=> 'fdm-menu-item',
			'posts_per_page' 	=> -1,
			'order'				=> 'ASC',
			'orderby'			=> 'menu_order',
			'tax_query'     	=> array(
				array(
					'taxonomy' => 'fdm-menu-section',
					'field'    => 'id',
					'terms'    => $this->id,
				),
			),
		));
		if ( !count( $items->posts ) ) {
			return;
		}

		// We go ahead and store all the posts data now to save on db calls
		$this->items = array();
		foreach( $items->posts as $item ) {
			$this->items[] = new fdmViewItem(
				array(
					'id' => $item->ID,
					'post' => $item
				)
			);
		}

		if ( !$this->title ) {
			$section = get_term( $this->id, 'fdm-menu-section' );
			$this->title = $section->name;
			$this->description = $section->description;
		}

		do_action( 'fdm_load_section', $this );

	}

	/**
	 * Set the menu section css classes
	 * @since 1.1
	 */
	public function set_classes( $classes = array() ) {
		$classes = array_merge(
			$classes,
			array(
				'fdm-section',
				'fdm-sectionid-' . $this->id
			)
		);

		// Order of this section appearing on this menu
		if ( isset( $this->order ) ) {
			$classes[] = 'fdm-section-' . $this->order;
		}

		$this->classes = apply_filters( 'fdm_menu_section_classes', $classes, $this );
	}

}
