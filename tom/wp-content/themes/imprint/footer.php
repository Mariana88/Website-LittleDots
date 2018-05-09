<?php
/*
 * Template for displaying footer.
 * 
 * @package Imprint
 * @since Imprint 1.0
 */
?>

</div><!-- Main Container ends -->

<?php global $imprint_options ?>

<?php get_sidebar( 'footer' ); // Calls the footer sidebar box (sidebar-footer.php) containing 3/4 widget areas.  ?>


<div id="footer">
    <?php imprint_menu_show('bottom_menu'); //Menu Call ?>
    <div id="copyright"><?php _e( 'Copyright', 'imprint' ); ?> <?php echo date( 'Y' ); ?> <?php if( $imprint_options['footer_name'] ) { echo esc_html( $imprint_options['footer_name'] ); } ?> | <?php _e( 'Powered by', 'imprint' ); ?> <a href="http://www.wordpress.org"><?php _e( 'WordPress', 'imprint' ); ?></div>

</div>

</div><!-- Wrapper ends -->

<?php wp_footer(); ?>

</body>
</html>