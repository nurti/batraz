<?php
/**
 * sidebar header
 *
 *
 * If none of the sidebars have widgets, then let's bail early.
 */
if ( is_active_sidebar( 'sidebar-h1' ))
    dynamic_sidebar( 'sidebar-h1' );
?>
