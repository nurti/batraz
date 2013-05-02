<?php

class BTZ_Cross_Post {

    const CROSS_POST_META_FIELD_KEY = 'btz_cross_post_meta_field_key';
    const CROSS_POST_NAME_FIELD = 'btz_cross_post_id';
    const CROSS_POST_SEARCH_FIELD = 'btz_cross_post_search';
    const META_FIELD_CROSS_POST_COLUMN = 'btz_cross_post_meta_column';
    const NONCE = 'btz_cross_post_noncename';
    const VALIDATION_CROSS_POST_MESSAGE = 'btz_validation_cross_post_message';

    protected $post_types = array();

    function __construct() {



        add_action('admin_init', array(&$this, 'post_types_assets'));


        add_action('admin_enqueue_scripts', array(&$this, 'enqueue_script'));
        
        add_action('wp_ajax_query_posts', 'Btz_Content_Repository::ajax_query_posts');
    }

    function post_types_assets() {
        $this->post_types = get_post_types(array('_builtin' => false, 'public' => true), 'names');
        array_push($this->post_types, 'post');

        add_action('add_meta_boxes', array(&$this, 'btz_cross_post_meta_box_add'));
        add_action('pre_post_update', array(&$this, 'btz_save_cross_post_postdata'));
        add_action('admin_notices', array(&$this, 'validation_cross_post_message'));

        if (is_admin()) {
            foreach ($this->post_types as $type) {
                add_filter("manage_{$type}_posts_columns", array(&$this, 'meta_cross_post_columns_id'));
                add_action("manage_{$type}_posts_custom_column", array(&$this, 'meta_cross_post_columns_value'), 10, 2);
            }
        }
    }

    function enqueue_script($hook) {
        if (function_exists('jquiw_enqueue_scripts')) {
            jquiw_enqueue_scripts();
        }
        
        wp_register_script('btz-content-common', plugins_url('/js/btz-content-common.js', __FILE__),
                   array('jquery', 'jquery-ui-progressbar'));
        wp_enqueue_script( 'btz-content-common' );
    }

    /*
     *  ADD COLUMN CROSS POST ADMIN SCREEN PAGE
     */

    function meta_cross_post_columns_id($column) {
        $column[self::META_FIELD_CROSS_POST_COLUMN] = 'Cross Post';
        return $column;
    }

    function meta_cross_post_columns_value($column_name, $post_id) {

        if ($column_name == self::META_FIELD_CROSS_POST_COLUMN) {
            $cross_post_id = get_post_meta($post_id, self::CROSS_POST_META_FIELD_KEY, true);
            
            
            if ($cross_post_id) {
                $listPosts = Btz_Content_Repository::get_post_titles_from_ids($cross_post_id);
                $displayList = "";
                if ($listPosts) {
                    $displayList = $this->format_list_posts($listPosts);
                }
                echo $displayList;
            }
        }
    }

    /*
     *  GESTIONE META BOX
     */

    function btz_cross_post_meta_box_add() {

        foreach ($this->post_types as $type) {
            add_meta_box('cross_post_meta_box_id', 'Cross Post', array(&$this, 'btz_inner_cross_post_meta_box'), $type, 'normal', 'high');
        }
    }

    function btz_inner_cross_post_meta_box($post) {
       
        $message = '';
        // Use nonce for verification
        wp_nonce_field(plugin_basename(__FILE__), self::NONCE);


        $cross_post_id = get_post_meta($post->ID, self::CROSS_POST_META_FIELD_KEY, true);
        $displayList = "";
        if ($cross_post_id) {
            $listPosts = Btz_Content_Repository::get_post_titles_from_ids($cross_post_id);
           
            if ($listPosts) {
                $displayList = $this->format_list_posts($listPosts);
            }
        }
        ?>  
        <table class="widefat">
               <tr>
                <td>
                    <label for="cross_post_id">Cross Post ID :
                        <input style="width:300px" type="text" name="<?php echo self::CROSS_POST_NAME_FIELD; ?>" id="<?php echo self::CROSS_POST_NAME_FIELD; ?>"  value="<?php echo $cross_post_id; ?>" />
                    </label>  
                </td>
                <td>
                    <span><?php echo $displayList; ?></span>
                </td>
                <td>
                    <a href="javascript:;" id="<?php echo self::CROSS_POST_SEARCH_FIELD ; ?>" class="btz-cross-post button button-primary">Scegli post</a>
                </td>
                <td>
            </tr> 
        </table>
        
        <script type="text/javascript">
            jQuery(document).ready(function(){
                jQuery("<?php echo "#" . self::CROSS_POST_SEARCH_FIELD ?>" ).postChoice({
                    target : jQuery("<?php echo "#" . self::CROSS_POST_NAME_FIELD ?>"),
                    currentPost : <?php echo $post->ID; ?>
                });
            });
        </script>

        <?php
    }

    function btz_save_cross_post_postdata($post_id) {
        // Bail if we're doing an auto save  
        if (defined('DOING_AUTOSAVE') && DOING_AUTOSAVE)
            return;

        if (in_array($_POST['post_type'], $this->post_types)) {
            if (!current_user_can('edit_page', $post_id))
                return;
        } else {
            if (!current_user_can('edit_post', $post_id))
                return;
        }


        // Secondly we need to check if the user intended to change this value.
        if (!isset($_POST[self::NONCE]) || !wp_verify_nonce($_POST[self::NONCE], plugin_basename(__FILE__)))
            return;


        if (isset($_POST[self::CROSS_POST_NAME_FIELD])) {
            $cross_post_id = $_POST[self::CROSS_POST_NAME_FIELD];
            if (!$cross_post_id) {
                delete_post_meta($post_id, self::CROSS_POST_META_FIELD_KEY);
            } else {
                $listPosts = Btz_Content_Repository::get_post_titles_from_ids($cross_post_id);
                $message = $this->validate_list_posts($cross_post_id, $listPosts);
                if ($message){
                    update_option(self::VALIDATION_CROSS_POST_MESSAGE, $message);
                    return;
                }
                
                update_post_meta($post_id, self::CROSS_POST_META_FIELD_KEY, $cross_post_id);
            }

        }
    }

    protected function format_list_posts($listPosts) {
        $result = "";
        foreach ($listPosts as $row) {
            $value = "({$row['ID']}) {$row['post_title']}";
            $result .= ($result <> "") ? ", " . $value : $value;
        }
        return $result;
    }

    protected function validate_list_posts($cross_post_id, $listPosts) {
        $message = false;
        $values = explode(',', $cross_post_id);
        $valid = true;

        foreach ($values as $value) {
            if (!ctype_digit($value)) {
                $valid = false;
                break;
            }
        }

        if (!$valid) {
            $message = "Formato input ( IDs post ) {$cross_post_id} incorretta.";
            return $message;
        }

        $ids = array();
        foreach ($listPosts as $post) {
            array_push($ids, $post['ID']);
        }

        foreach ($values as $value) {
            if (!in_array($value, $ids)) {
                $message ="ID post {$value} non trovato.";
                break;
            }
        }

        return $message;
    }

    function validation_cross_post_message() {

        $notice = get_option(self::VALIDATION_CROSS_POST_MESSAGE);
        if ($notice) {
            echo "<div class='updated fade'><p>$notice</p></div>";
            delete_option(self::VALIDATION_CROSS_POST_MESSAGE);
        }
    }
    
    

}
?>
