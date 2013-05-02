<?php
/**
 * BATRAZ child di Twenty Twelve scripts.
 *
 * 
 * vengono gestiti gli scripts
 */


/*
 *  gestisce tooltip se jquery-ui tools caricato
 */
add_action('wp_footer', 'tooltip_script', 99);

function tooltip_script() {
    if (wp_script_is('jquery-ui-widget', 'done')) {
        ?>
        <script type="text/javascript">
            jQuery(document).ready(function() {
                jQuery(document).tooltip({
                    position: {
                        my: "center bottom-20",
                        at: "center top",
                        using: function( position, feedback ) {
                            jQuery( this ).css( position );
                            jQuery( "<div>" )
                            .addClass( "arrow" )
                            .addClass( feedback.vertical )
                            .addClass( feedback.horizontal )
                            .appendTo( this );
                        }

                    }

                });
                
            });
        </script>
        <?php

    }
}
?>
