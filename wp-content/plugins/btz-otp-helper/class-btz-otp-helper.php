<?php
/**
 * Plugin Name: BTZ  Order Taxonomies-Posts
 * Description: A plugins for coppol'e cazze
 * Version: 0.1
 * Author: Aisartag
 * Author URI: http://aisartag.worpress.com
 */



define('OTPPATH',   plugin_dir_path(__FILE__));
define('OTPURL',    plugins_url('', __FILE__));

load_plugin_textdomain('to', FALSE, OTPPATH . "/lang/");


/*
 *  attivazione / disattivazione del plugin
 *  Aggiunta della colonna otp_order in wp_term_relationships se non esiste
 */
register_deactivation_hook(__FILE__, 'TO_deactivated');
register_activation_hook(__FILE__, 'TO_activated');

function TO_activated(){
    global $wpdb;

    //check if the otp_order column exists;
    $query = "SHOW COLUMNS FROM $wpdb->term_relationships 
                LIKE 'otp_order'";
    $result = $wpdb->query($query);

    if ($result == 0){
            $query = "ALTER TABLE $wpdb->term_relationships  ADD `otp_order` INT( 4 ) NULL DEFAULT '0'";
            $result = $wpdb->query($query); 
    }
    
   
}
    
function TO_deactivated(){
        
}
    
require_once( OTPPATH . '/include/class-btz-otp-options.php'); 
require_once( OTPPATH . '/repository/class-btz-otp-repository.php'); 
    
/*
 *  filtro  sulle liste da taxonomies con uso otp_order
 */
if(!is_admin()){
    
    
//    add_filter('btz_entry_meta', 'btz_entry_meta_func', 10, 2);
//    function btz_entry_meta_func($a, $b) {
//        return  $a . $b;
//    }
    
    
    add_filter('posts_orderby_request', 'edit_posts_orderby', 99, 2);

    function edit_posts_orderby($orderby, $query) {
        
        if ((!$query->is_category()) && (!$query->is_tag()) && (!$query->is_tax())) {
          //  var_dump('fore = ' . $caz  . ' ' . $orderby);
            return $orderby;
        }
        
        $tax_excluded = array();
        $options = get_option(BTZ_Otp_Options::OPTIONS_OTP_EXCLUDE);
        if (!empty($options)) {
            $tax_excluded = explode(',', $options);
        }
        
        foreach($tax_excluded as $tax){
            if($tax == 'category'){
                if(isset($query->query[$tax . '_name'])){
                    return;
                }
            }
            if(isset($query->query[$tax])){
                return;
            }
        }
        

        $orderby = 'wp_term_relationships.otp_order ASC';


        return $orderby;
    }

}



/*
 * class helper gestione order tax posts
 */
class OTP_Helper {

    const OTP_PAGE = 'order-tax-posts-page';
    const OPTIONS_CURRENT_TAX_ID = 'otp_current_tax_id';

    function __construct() {
        add_action('admin_init', array(&$this, 'registerFiles'), 11);
        add_action('admin_menu', array(&$this, 'admin_menu_func'));
        add_action( 'wp_ajax_update-order-tax-posts', array(&$this, 'saveAjaxOrder') );
        
        
    }
    
    

    

    /*
     *  registra scripts e style
     */
    function registerFiles() {
    
        wp_enqueue_script('jQuery');
        wp_enqueue_script('jquery-ui-sortable');
    

        wp_register_style('OTPStyleSheets', OTPURL . '/css/otp.css');
        wp_enqueue_style('OTPStyleSheets');
    }

    /*
     * hooks per aggiunta colonna order sulle liste tax
     * aggiunge submenu necessario
     */
    function admin_menu_func() {
        $taxonomies = get_taxonomies(array('public' => true,), 'names');
        unset($taxonomies['post_format']);
        
        
        $tax_excluded = array();
        $options = get_option(BTZ_Otp_Options::OPTIONS_OTP_EXCLUDE);
        if (!empty($options)) {
            $tax_excluded = explode(',', $options);
        }
        
        foreach($tax_excluded as $tax){
            if(isset($taxonomies[$tax])){
                unset($taxonomies[$tax]);
            }
        }

        foreach ($taxonomies as $taxonomy) {
            add_filter("manage_edit-{$taxonomy}_columns", array(&$this, 'add_otp_column')); // 'add_custom_columns');
            add_action("manage_{$taxonomy}_custom_column", array(&$this, 'populate_otp_column'), 10, 4);
        }

        $tax_ids = array();
        $options = get_option(self::OPTIONS_CURRENT_TAX_ID);
        if (!empty($options)) {
            $tax_ids = explode(',', $options);
        }

        foreach ($tax_ids as $tax_id) {

            add_submenu_page(
                    'options.php'
                    , 'Order Tax Posts'
                    , 'Order Tax Posts'
                    , 'manage_options'
                    , self::OTP_PAGE . '-' . $tax_id
                    , array(&$this, 'order_tax_page')
            );
        }
    }

    /*
     *  funzione per la gestione della pagina tax post order
     */
    function order_tax_page() {
        $tax_id = '';
        if (isset($_GET['page'])) {
            $tax_id = end($temp = explode('-', $_GET['page']));
        }

        global $wpdb;
        if (is_numeric($tax_id)) {

            $term = $wpdb->get_row($wpdb->prepare("SELECT t.*, tt.* FROM $wpdb->terms AS t INNER JOIN $wpdb->term_taxonomy AS tt ON t.term_id = tt.term_id WHERE tt.term_taxonomy_id = %s LIMIT 1", $tax_id));
            $nome_termine = $term->name;
            
            $nome_tax = get_taxonomy($term->taxonomy)->labels->singular_name;

            $list = $this->listPosts($tax_id);
        }
        ?>
        <div class="wrap">
            <div class="icon32" id="icon-edit"><br></div>
        <?php if (!$tax_id || !is_numeric($tax_id)) : ?>
                <br><br>
                <em><strong>Tassonomia indefinita</strong></em>     
            </div>
                <?php return; ?> 
        <?php endif; ?>

        <h2><?php echo $nome_tax . ' / ' . $nome_termine; ?></h2>
        <br>
        <?php if (empty($list)) : ?>
            <div>Tassonomia vuota!</div>
        <?php else : ?>
            <div id="ajax-response"></div>

            <noscript>
            <div class="error message">
                <p><?php _e('This plugin can\'t work without javascript, because it\'s use drag and drop and AJAX.', 'cpt') ?></p>
            </div>
            </noscript>

            <div id="order-post-tax">
                <ul id="sortable">
                    <?php echo $list ?>	
                </ul>
                
                <p class="submit">
		      <a href="#" id="save-order" class="button-primary">Aggiorna</a>
	        </p>
                
                 <script type="text/javascript">
                        jQuery(document).ready(function() {
                                jQuery("#sortable").sortable({
                                        'tolerance':'intersect',
                                        'cursor':'pointer',
                                        'items':'li',
                                        'placeholder':'placeholder',
                                        'nested': 'ul'
                                });

                                jQuery("#sortable").disableSelection();
                                jQuery("#save-order").bind( "click", function() {
                                        jQuery.post( ajaxurl, { action:'update-order-tax-posts', order:jQuery("#sortable").sortable("serialize"), tax:<?php echo $tax_id ?> }, function() {
                                                jQuery("#ajax-response").html('<div class="message updated fade"><p><?php _e('Ordinamento effettuato', 'otp') ?></p></div>');
                                                jQuery("#ajax-response div").delay(3000).hide("slow");
                                        });
                                });
                        });
                </script>
                                
            </div>    
                <?php endif; ?>


        <?php
    }

    /*
     *  lista posts del tax
     */
    function listPosts($tax_id) {
        global $wpdb;
        $list = '';
        $posts = $wpdb->get_results($wpdb->prepare("SELECT p.ID, p.post_title  FROM $wpdb->posts AS p INNER JOIN $wpdb->term_relationships AS tr ON p.ID = tr.object_id WHERE (p.post_status = 'publish' OR p.post_status = 'private' ) AND tr.term_taxonomy_id = %s ORDER BY tr.otp_order", $tax_id));
        if (empty($posts)) {
            return $list;
        }

        foreach ($posts as $item) {
            $list .= "<li id=\"item_{$item->ID}\" ><span>";
            $list .=  $item->post_title;
            $list .='</span></li>';
        }

        return $list;
    }
    
    /*
     *  call ajax per aggiornamento ordine
     */
    function saveAjaxOrder(){
         
         global $wpdb;
         
         $tax_id = $_POST['tax'];
         
         parse_str($_POST['order'], $data);
         
         if (is_array($data)){
            
            foreach($data as $key => $postIds ) {
                  if ( $key == 'item' ) {
                       foreach( $postIds as $position => $postId ){
	                    $wpdb->update( $wpdb->term_relationships, array('otp_order' => $position), array('object_id' => $postId, 'term_taxonomy_id' => $tax_id) );
                       } 
                  } 
                      

             }
             
         }
     }

     /*
      *  aggiunta colonna sulla llsta del tax
      */
    function add_otp_column($columns) {
        update_option(self::OPTIONS_CURRENT_TAX_ID, '');
        $columns['order_link'] = 'Ordina Posts';
        $columns['my_tax_id'] = 'ID';
        return $columns;
    }

    /*
     *  populate colonna sulla llsta del tax
     */
    function populate_otp_column($a, $column, $term_id, $tax_id) {
        
        if ($column == 'my_tax_id'){
	     echo  $tax_id; 
	}
        
        $tax_ids = array();
        if ($column == 'order_link' && is_numeric($tax_id)) {
            $options = get_option(self::OPTIONS_CURRENT_TAX_ID);
            if (!empty($options)) {
                $tax_ids = explode(',', $options);
            }
            if (!in_array($tax_id, $tax_ids)) {
                $tax_ids[] = $tax_id;
            }

            update_option(self::OPTIONS_CURRENT_TAX_ID, implode(',', $tax_ids));

            $href = admin_url('options.php?page=' . self::OTP_PAGE . '-' . $tax_id);
            $link = "<a href={$href} >Go</a>";
            echo $link;
        }
    }

}

new OTP_Helper();
new BTZ_Otp_Options();


?>
