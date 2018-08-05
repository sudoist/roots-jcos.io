<?php
/**
 * The Beans Layout API controls what and how Beans main section elements are displayed.
 *
 * Layouts are:
 *      - "c" - content only
 *      - "c_sp" - content + sidebar primary
 *      - "sp_c" - sidebar primary + content
 *      - "c_ss" - content + sidebar secondary
 *      - "c_sp_ss" - content + sidebar primary + sidebar secondary
 *      - "ss_c" - sidebar secondary + content
 *      - "sp_ss_c" - sidebar primary + sidebar secondary + content
 *      - "sp_c_ss" - sidebar primary + content + sidebar secondary
 *
 * @package Beans\Framework\API\Layout
 *
 * @since   1.5.0
 */

/**
 * Get the default layout ID.
 *
 * @since 1.0.0
 *
 * @return string
 */
function beans_get_default_layout() {
	$default_layout = beans_has_widget_area( 'sidebar_primary' ) ? 'c_sp' : 'c';

	/**
	 * Filter the default layout ID.
	 *
	 * The default layout ID is set to "c_sp" (content + sidebar primary). If the sidebar primary is unregistered, then it is set to "c" (content only).
	 *
	 * @since 1.0.0
	 *
	 * @param string $layout The default layout ID.
	 */
	return apply_filters( 'beans_default_layout', $default_layout );
}

/**
 * Get the current web page's layout ID.
 *
 * @since 1.0.0
 *
 * @return string
 */
function beans_get_layout() {

	if ( is_singular() ) {
		$layout = beans_get_post_meta( 'beans_layout' );
	} elseif ( is_home() ) {
		$posts_page = (int) get_option( 'page_for_posts' );
		if ( 0 !== $posts_page ) {
			$layout = beans_get_post_meta( 'beans_layout', false, $posts_page );
		}
	} elseif ( is_category() || is_tag() || is_tax() ) {
		$layout = beans_get_term_meta( 'beans_layout' );
	}

	// When the layout is not found or is set to "default_fallback", use the theme's default layout.
	if ( ! isset( $layout ) || ! $layout || 'default_fallback' === $layout ) {
		$layout = get_theme_mod( 'beans_layout', beans_get_default_layout() );
	}

	/**
	 * Filter the web page's layout ID.
	 *
	 * @since 1.0.0
	 *
	 * @param string $layout The layout ID.
	 */
	return apply_filters( 'beans_layout', $layout );
}

/**
 * Get the current web page's layout class.
 *
 * This function generates the layout class(es) based on the current layout.
 *
 * @since 1.0.0
 *
 * @param string $id The searched layout section ID.
 *
 * @return bool Layout class, false if no layout class found.
 */
function beans_get_layout_class( $id ) {
	/**
	 * Filter the arguments used to define the layout grid.
	 *
	 * The content number of columns are automatically calculated based on the grid, sidebar primary and
	 * sidebar secondary columns.
	 *
	 * @since 1.0.0
	 *
	 * @param array $args              {
	 *                                 An array of arguments.
	 *
	 * @type int    $grid              Total number of columns the grid contains. Default 4.
	 * @type int    $sidebar_primary   The number of columns the sidebar primary takes. Default 1.
	 * @type int    $sidebar_secondary The number of columns the sidebar secondary takes. Default 1.
	 * @type string $breakpoint        The UIkit grid breakpoint which may be set to 'small', 'medium' or 'large'. Default 'medium'.
	 * }
	 */
	$args = apply_filters( 'beans_layout_grid_settings', array(
		'grid'              => 4,
		'sidebar_primary'   => 1,
		'sidebar_secondary' => 1,
		'breakpoint'        => 'medium',
	) );

	/**
	 * Filter the layout class.
	 *
	 * The dynamic portion of the hook name refers to the searched layout section ID.
	 *
	 * @since 1.0.0
	 *
	 * @param string $layout The layout class.
	 */
	return apply_filters( "beans_layout_class_{$id}", beans_get( $id, _beans_get_layout_classes( $args ) ) );
}

/**
 * Get the layout's class attribute values.
 *
 * @since  1.5.0
 * @ignore
 * @access private
 *
 * @param array $args Grid configuration.
 *
 * @return array
 */
function _beans_get_layout_classes( array $args ) {
	$grid   = beans_get( 'grid', $args );
	$c      = $grid; // $c stands for "content".
	$sp     = beans_get( 'sidebar_primary', $args );
	$ss     = beans_get( 'sidebar_secondary', $args );
	$prefix = 'uk-width-' . beans_get( 'breakpoint', $args, 'medium' );

	$classes = array(
		'content' => "{$prefix}-{$c}-{$grid}",
	);

	if ( ! beans_has_widget_area( 'sidebar_primary' ) ) {
		return $classes;
	}

	$layout        = beans_get_layout();
	$has_secondary = beans_has_widget_area( 'sidebar_secondary' );
	$c             = $has_secondary && strlen( trim( $layout ) ) > 4 ? $grid - ( $sp + $ss ) : $grid - $sp;

	switch ( $layout ) {

		case 'c_sp':
		case 'c_sp_ss':
			$classes['content']         = "{$prefix}-{$c}-{$grid}";
			$classes['sidebar_primary'] = "{$prefix}-{$sp}-{$grid}";

			if ( $has_secondary && 'c_sp_ss' === $layout ) {
				$classes['sidebar_secondary'] = "{$prefix}-{$ss}-{$grid}";
			}
			break;

		case 'sp_c':
		case 'sp_c_ss':
			$classes['content']         = "{$prefix}-{$c}-{$grid} uk-push-{$sp}-{$grid}";
			$classes['sidebar_primary'] = "{$prefix}-{$sp}-{$grid} uk-pull-{$c}-{$grid}";

			if ( $has_secondary && 'sp_c_ss' === $layout ) {
				$classes['sidebar_secondary'] = "{$prefix}-{$ss}-{$grid}";
			}
			break;

		case 'c_ss':
			// If we don't have a secondary sidebar, bail out.
			if ( ! $has_secondary ) {
				return $classes;
			}

			$classes['content']           = "{$prefix}-{$c}-{$grid}";
			$classes['sidebar_secondary'] = "{$prefix}-{$ss}-{$grid}";
			break;

		case 'ss_c':
			// If we don't have a secondary sidebar, bail out.
			if ( ! $has_secondary ) {
				return $classes;
			}

			$classes['content']           = "{$prefix}-{$c}-{$grid} uk-push-{$ss}-{$grid}";
			$classes['sidebar_secondary'] = "{$prefix}-{$ss}-{$grid} uk-pull-{$c}-{$grid}";
			break;

		case 'sp_ss_c':
			$push_content               = $has_secondary ? $sp + $ss : $sp;
			$classes['content']         = "{$prefix}-{$c}-{$grid} uk-push-{$push_content}-{$grid}";
			$classes['sidebar_primary'] = "{$prefix}-{$sp}-{$grid} uk-pull-{$c}-{$grid}";

			if ( $has_secondary ) {
				$classes['sidebar_secondary'] = "{$prefix}-{$ss}-{$grid} uk-pull-{$c}-{$grid}";
			}

			break;
	}

	return $classes;
}

/**
 * Generate layout elements used by Beans 'imageradio' option type.
 *
 * Added layout should contain a unique ID as the array key and a URL path to its related image
 * as the array value.
 *
 * @since 1.0.0
 *
 * @param bool $add_default Optional. Whether the 'default_fallback' element is added or not.
 *
 * @return array Layouts ready for Beans 'imageradio' option type.
 */
function beans_get_layouts_for_options( $add_default = false ) {
	$base    = BEANS_ADMIN_ASSETS_URL . 'images/layouts/';
	$layouts = array(
		'c' => array(
			'src'                => $base . 'c.png',
			'alt'                => __( 'Full-Width Content Layout', 'tm-beans' ),
			'screen_reader_text' => __( 'Option for the Full-Width Content Layout.', 'tm-beans' ),
		),
	);

	// Add sidebar primary layouts if the primary widget area is registered.
	$has_primary = beans_has_widget_area( 'sidebar_primary' );

	if ( $has_primary ) {
		$layouts['c_sp']['src']                = $base . 'cs.png';
		$layouts['c_sp']['alt']                = __( 'Content and Primary Sidebar Layout', 'tm-beans' );
		$layouts['c_sp']['screen_reader_text'] = __( 'Option for the Content and Primary Sidebar Layout.', 'tm-beans' );

		$layouts['sp_c']['src']                = $base . 'sc.png';
		$layouts['sp_c']['alt']                = __( 'Primary Sidebar and Content Layout', 'tm-beans' );
		$layouts['sp_c']['screen_reader_text'] = __( 'Option for the Primary Sidebar and Content Layout.', 'tm-beans' );
	}

	// Add sidebar secondary layouts if the primary and secondary widget area are registered.
	if ( $has_primary && beans_has_widget_area( 'sidebar_secondary' ) ) {
		$layouts['c_sp_ss']['src']                = $base . 'css.png';
		$layouts['c_sp_ss']['alt']                = __( 'Content, Primary Sidebar and Secondary Sidebar Layout', 'tm-beans' );
		$layouts['c_sp_ss']['screen_reader_text'] = __( 'Option for the Content, Primary Sidebar and Secondary Sidebar Layout.', 'tm-beans' );

		$layouts['sp_ss_c']['src']                = $base . 'ssc.png';
		$layouts['sp_ss_c']['alt']                = __( 'Primary Sidebar, Secondary Sidebar and Content Layout', 'tm-beans' );
		$layouts['sp_ss_c']['screen_reader_text'] = __( 'Option for the Primary Sidebar, Secondary Sidebar and Content Layout.', 'tm-beans' );

		$layouts['sp_c_ss']['src']                = $base . 'scs.png';
		$layouts['sp_c_ss']['alt']                = __( 'Primary Sidebar, Content and Secondary Sidebar Layout', 'tm-beans' );
		$layouts['sp_c_ss']['screen_reader_text'] = __( 'Option for the Primary Sidebar, Content and Secondary Sidebar Layout.', 'tm-beans' );
	}

	/**
	 * Filter the layouts.
	 *
	 * - $c stands for content.
	 * - $sp stands for sidebar primary.
	 * - $ss stands for 'sidebar secondary.
	 *
	 * @since 1.0.0
	 *
	 * @param array $args An array of layouts.
	 */
	$layouts = apply_filters( 'beans_layouts', $layouts );

	if ( ! $add_default ) {
		return $layouts;
	}

	$layouts = array_merge( array(
		'default_fallback' => sprintf(
			// translators: The (%s) placeholder is for the "Modify" hyperlink.
			__( 'Use Default Layout (%s)', 'tm-beans' ),
			'<a href="' . admin_url( 'customize.php?autofocus[control]=beans_layout' ) . '">' . _x( 'Modify', 'Default layout', 'tm-beans' ) . '</a>'
		),
	), $layouts );

	return $layouts;
}

/**
 * Check if the given layout has a primary sidebar.
 *
 * @since 1.5.0
 *
 * @param string $layout The layout to check.
 *
 * @return bool
 */
function beans_has_primary_sidebar( $layout ) {

	if ( ! in_array( $layout, array( 'c_sp', 'sp_c', 'c_sp_ss', 'sp_c_ss', 'sp_ss_c' ), true ) ) {
		return false;
	}

	return beans_is_active_widget_area( 'sidebar_primary' );
}

/**
 * Check if the given layout has a secondary sidebar.
 *
 * @since 1.5.0
 *
 * @param string $layout The layout to check.
 *
 * @return bool
 */
function beans_has_secondary_sidebar( $layout ) {

	if ( ! in_array( $layout, array( 'c_ss', 'ss_c', 'c_sp_ss', 'sp_c_ss', 'sp_ss_c' ), true ) ) {
		return false;
	}

	return beans_is_active_widget_area( 'sidebar_secondary' );
}
