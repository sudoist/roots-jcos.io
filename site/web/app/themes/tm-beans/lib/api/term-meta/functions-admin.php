<?php
/**
 * The Beans Term Meta component extends the Beans Fields API and makes it easy add fields to any Taxonomy.
 *
 * @package Beans\Framework\API\Term_Meta
 *
 * @since 1.0.0
 */

/**
 * Register Term Meta.
 *
 * This function should only be invoked through the 'admin_init' action.
 *
 * @since 1.0.0
 *
 * @param array        $fields {
 *            Array of fields to register.
 *
 *      @type string $id          A unique id used for the field. This id will also be used to save the value in
 *                                the database.
 *      @type string $type        The type of field to use. Please refer to the Beans core field types for more
 *                                information. Custom field types are accepted here.
 *      @type string $label       The field label. Default false.
 *      @type string $description The field description. The description can be truncated using <!--more-->
 *                                as a delimiter. Default false.
 *      @type array  $attributes  An array of attributes to add to the field. The array key defines the
 *                                attribute name and the array value defines the attribute value. Default array.
 *      @type mixed  $default     The default field value. Default false.
 *      @type array  $fields      Must only be used for the 'group' field type. The array arguments are similar to the
 *                                {@see beans_register_fields()} $fields arguments.
 *      @type bool   $db_group    Must only be used for 'group' field type. Defines whether the group of fields
 *                                registered should be saved as a group in the database or as individual
 *                                entries. Default false.
 * }
 * @param string|array $taxonomies Array of taxonomies for which the term meta should be registered.
 * @param string       $section    A section id to define the group of fields.
 *
 * @return bool True on success, false on failure.
 */
function beans_register_term_meta( array $fields, $taxonomies, $section ) {

	/**
	 * Filter the term meta fields.
	 *
	 * The dynamic portion of the hook name, $section, refers to the section id which defines the group of fields.
	 *
	 * @since 1.0.0
	 *
	 * @param array $fields An array of term meta fields.
	 */
	$fields = apply_filters( "beans_term_meta_fields_{$section}", _beans_pre_standardize_fields( $fields ) );

	/**
	 * Filter the taxonomies used to define whether the fields set should be displayed or not.
	 *
	 * The dynamic portion of the hook name, $section, refers to the section id which defines the group of fields.
	 *
	 * @since 1.0.0
	 *
	 * @param string|array $taxonomies Taxonomies used to define whether the fields set should be displayed or not.
	 */
	$taxonomies = apply_filters( "beans_term_meta_taxonomies_{$section}", (array) $taxonomies );

	// Stop here if the current page isn't concerned.
	if ( ! _beans_is_admin_term( $taxonomies ) || ! is_admin() ) {
		return false;
	}

	// Stop here if the field can't be registered.
	if ( ! beans_register_fields( $fields, 'term_meta', $section ) ) {
		return false;
	}

	// Load the class only if this function is called to prevent unnecessary memory usage.
	require_once BEANS_API_PATH . 'term-meta/class-beans-term-meta.php';

	new _Beans_Term_Meta( $section );

	return true;
}

/**
 * Check if the current screen is a given term.
 *
 * @since 1.0.0
 * @ignore
 * @access private
 *
 * @param array|bool $taxonomies Array of taxonomies or true for all taxonomies.
 *
 * @return bool
 */
function _beans_is_admin_term( $taxonomies ) {
	if ( true === $taxonomies ) {
		return true;
	}

	$taxonomy = beans_get_or_post( 'taxonomy' );

	if ( empty( $taxonomy ) ) {
		return false;
	}

	return in_array( $taxonomy, (array) $taxonomies, true );
}
