<?php
/**
 * View file for the image field type.
 *
 * @package Beans\Framework\API\Fields\Types
 *
 * @since   1.0.0
 * @since   1.5.0 Moved to view file.
 */

// phpcs:disable WordPress.NamingConventions.PrefixAllGlobals.NonPrefixedVariableFound -- Variables are used within a function's scope.
// phpcs:disable Generic.WhiteSpace.ScopeIndent.Incorrect, Generic.WhiteSpace.ScopeIndent.IncorrectExact -- View file is indented for HTML structure.
?>

<button class="bs-add-image button button-small" type="button" <?php echo isset( $hide_add_link ) && $hide_add_link ? 'style="display: none"' : ''; ?>><?php echo esc_html( $link_text ); ?></button>
<input id="<?php echo esc_attr( $field['id'] ); ?>" type="hidden" name="<?php echo esc_attr( $field['name'] ); ?>" value="">
<div class="bs-images-wrap" data-multiple="<?php echo esc_attr( $is_multiple ); ?>">
<?php

foreach ( $images as $image_id ) :

	// Skip this one if the ID is not set.
	if ( ! $image_id ) {
		continue;
	}

	$attributes = _beans_get_image_id_attributes( $image_id, $field, $is_multiple );
	$image_url  = _beans_get_image_url( $image_id );
	$image_alt  = $image_url ? _beans_get_image_alt( $image_id ) : '';
?>
	<div class="bs-image-wrap<?php echo 'placeholder' === $image_id ? ' bs-image-template' : ''; ?>">
		<input <?php echo beans_esc_attributes( $attributes ); ?> /><?php // phpcs:ignore WordPress.XSS.EscapeOutput.OutputNotEscaped -- Escaping is handled in the function. ?>
		<img src="<?php echo $image_url ? esc_url( $image_url ) : ''; ?>" alt="<?php echo $image_alt ? esc_attr( $image_alt ) : ''; ?>">
		<div class="bs-toolbar">
			<?php
			/**
			 * The image toolbar's dashicons' class attributes are deprecated in Beans 1.5.  Instead, the .bs-button-{icon}
			 * class attributes are used and the icon is defined in the CSS via .bs-button-{icon}:before pseudo-element.
			 *
			 * The dashicons' class attributes remain in Beans 1.x for backwards compatibility for customs scripts
			 * or styling. However, these will be removed in Beans 2.0.
			 *
			 * @since 1.5.0
			 * @deprecated
			 */
			?>
		<?php if ( $is_multiple ) : ?>
			<button aria-label="<?php esc_attr_e( 'Manage Images', 'tm-beans' ); ?>" type="button" class="button bs-button-menu dashicons dashicons-menu"></button>
		<?php endif; ?>
			<button aria-label="<?php esc_attr_e( 'Edit Image', 'tm-beans' ); ?>" type="button" class="button bs-button-edit dashicons dashicons-edit"></button>
			<button aria-label="<?php esc_attr_e( 'Delete Image', 'tm-beans' ); ?>" type="button" class="button bs-button-trash dashicons dashicons-post-trash"></button>
		</div>
	</div>
<?php endforeach; ?>
</div>
