<?php

class BTZ_Otp_Options {
    
    const OPTIONS_OTP_EXCLUDE = 'btz-otp-order-tax-exclude';
    
    const LEADER_META_FIELD_KEY = 'btz_leader_meta_field_key';
    const SELECT_NAME_FIELD = 'btz_tax_leader_select';
    
    const LEADER_META_REDIRECT_KEY = 'btz_leader_redirect_key';
    const REDIRECT_NAME_FIELD = 'btz_tax_leader_redirect';
    
    const LEADER_META_SHOW_DESC_KEY = 'btz_leader_show_desc_key';
    const SHOW_DESC_NAME_FIELD = 'btz_tax_leader_show_desc';
    
    const META_FIELD_LEADER_COLUMN = 'btz_leader_meta_column';
    const NONCE = 'btz_otp_noncename';
    const NULL_LEADER = '0';
    
    const VALIDATION_LEADER_MESSAGE = 'btz_validation_leader_message';
    
    const TABS_ID = 'btz-tabs';
    
    
    
    function __construct() {
        add_action('admin_menu', array(&$this, 'setting_menu'));
        add_action( 'admin_enqueue_scripts', array(&$this, 'enqueue_script' ));
        
        add_action( 'add_meta_boxes', array(&$this, 'btz_leader_select_meta_box_add' )); 
        add_action( 'pre_post_update', array(&$this, 'btz_save_leader_postdata' )); 
        add_action('admin_notices', array(&$this, 'validation_leader_message'));
        
        if(is_admin()){
            add_filter('manage_pages_columns', array(&$this, 'meta_leader_columns_id'));
            add_action('manage_pages_custom_column', array(&$this,'meta_leader_columns_value'), 10, 2);
        }
    }
    
    function setting_menu(){
        add_plugins_page( 'OTP Order setting', 'OTP Order setting', 'manage_options', 'otp-setting', array(&$this, 'setting_page'));

    }
    
    function  enqueue_script($hook){
        if( function_exists('jquiw_enqueue_scripts')){
            jquiw_enqueue_scripts();
        }
    }
    
    function setting_page(){
        $taxonomies = $this->get_public_taxonomies();
       
        if(isset($_POST['checktax'])){
            $list_unordered = $this->unordered_tax_list();
        }
        
       
        if(isset($_POST['submit'])){
           unset($_POST['submit']);
           
           $slugs = array();
           foreach ($taxonomies as $tax){
                $slug = ($tax->rewrite['slug'] == 'tag' ) ? 'post_' . $tax->rewrite['slug'] : $tax->rewrite['slug'];
                $slugs[] = $slug;
           }
           
            $excluded = array();
            $message = "";
            foreach($_POST as $key=> $value){
                if( in_array($key, $slugs)){
                    if(intval($value) > 0){
                        if($this->taxonomy_busy($key)){
                            $message = "Tassonomia '" . $key . "' impegnata come leader. Non può essere esclusa.";
                            break;
                        }
                        $excluded[] = $key;
                        
                    }
                }
               
            }
            if($message == ""){
                update_option(self::OPTIONS_OTP_EXCLUDE , implode(',', $excluded));
                $message = "Options saved.";
            }
            ?>
                 <div id="message" class="updated fade"><p><strong><?php _e($message) ?></strong></p></div>
            <?php
        }
        
        $tax_excluded = $this->get_excluded();
        $taxs = array();
        foreach ($taxonomies as $slug=> $tax){
            if(in_array($slug, $tax_excluded)){
               $checked = '1';
            }
            else {
                $checked = '0';
            }
            $taxs[] = array('slug' => $slug, 'name' => $tax->labels->name, 'checked' => $checked);
            
        }
        
        ?>
       
        <div id="icon-plugins" class="icon32"><br></div>
           <h2>Impostazioni OTP Order</h2>
               
        <br/>          
        <form id="taxonomies-settings" method="post" >
            <div class="wrap">
              
                <table class="widefat">
                    <tr><th>Selezione Tassonomie</th></tr>
                    <tr>
                        <td>
                            <div class="description">Tassonomie che saranno escluse da OTP Order</div>
                            <?php foreach($taxs as $tax) : ?>
                               <input type="checkbox" name="<?php echo $tax['slug'] ?>" value="1" <?php checked('1', $tax['checked']); ?> />
                               <label for="<?php echo $tax['slug'] ?>" ><?php echo $tax['name'] ?></label>
                               &nbsp;&nbsp;&nbsp;
                            <?php endforeach; ?>
                        </td>
                    </tr>
                </table>
                <?php submit_button(); ?>
            </div> 
        </form>  

       
         <form id="taxonomies-verify" method="post" >
             <table class="widefat">
                    <tr><th>Check Order</th></tr>
                    <tr>
                        <td>
                            <div class="description">Tassonomie con ordinamento pendente</div>
                            <?php if(isset($list_unordered)) : ?>
                                <div id="<?php echo self::TABS_ID; ?>" >
                                    <?php echo $list_unordered; ?>
                                </div>    
                            <?php endif; ?>
                        </td>
                    </tr>
             </table>
             <br>
             <input type="submit" name="checktax" id="checktax" class="button button-primary" value="Verifica Order">
                
         </form>   
        
          
    <script type="text/javascript">
       jQuery(document).ready(function() {
             jQuery( "#btz-tabs" ).tabs();
       });
    </script>
        <?php    
    }
    
    function unordered_tax_list(){
        
        global $wpdb;
        
        $statement = "select tr.term_taxonomy_id, tt.taxonomy, t.name, t.slug from ("
                     ." select tr.term_taxonomy_id, tr.otp_order from wp_term_relationships tr"
                     ." where tr.otp_order = 0"
                     ." group by  tr.term_taxonomy_id, tr.otp_order"
                     ." having count(*) > 1 ) as tr"
                     ." INNER JOIN wp_term_taxonomy AS tt ON tt.term_taxonomy_id = tr.term_taxonomy_id"
                     ." INNER JOIN wp_terms AS t ON t.term_id = tt.term_id"
                     ." WHERE tt.taxonomy <> 'nav_menu'";
        
        $results = $wpdb->get_results($statement);
        
        if( !empty($results)){
            
            $list = '<ul>';
             
            
            $tax_excluded = $this->get_excluded();
           
            $tree = array();
            foreach ($results as $result){
                if(in_array($result->taxonomy, $tax_excluded))
                        continue;
                $key = get_taxonomy($result->taxonomy)->label;
                if(!isset($key)){
                    $tree[$key] = array($result->name);
                }else{
                    $tree[$key][] = $result->name;
                }
                
            }
            
            $i=0;
            foreach($tree as $key=>$values){
              $i++;
              $list .= '<li><a href="#' . self::TABS_ID . '-' . $i . '">';
              $list .= $key;
              $list .= '</a></li>';
            }
            
            $list .= '</ul>';
             
            $tabs = '';
            $i=0;
            foreach($tree as $key=>$values){
              $i++;
              $tabs .= '<div id="' . self::TABS_ID . '-' . $i . '">' ;
              foreach($values as $value){
                  $tabs .= '<p>' . $value . '</p>';
              }
              $tabs .= '</div>';
            }

        }
        
        return $list . $tabs;
        
    }
    
  
    function get_excluded(){
        
        $tax_excluded = array();
        $options = get_option(self::OPTIONS_OTP_EXCLUDE);
        if (!empty($options)) {
            $tax_excluded = explode(',', $options);
        }
        
        return $tax_excluded;
    }
    
    function get_public_taxonomies(){
        $taxonomies = get_taxonomies(array('public' => true,), 'objects');
        unset($taxonomies['post_format']);
        
        return $taxonomies;
     }
     
     function get_taxonomies_slugs(){
        $taxonomies = $this->get_public_taxonomies();
       
        foreach ($taxonomies as $tax){
            $slug = ($tax->rewrite['slug'] == 'tag' ) ? 'post_' . $tax->rewrite['slug'] : $tax->rewrite['slug'];
            $slugs[$slug] =  $tax->labels->singular_name;
        }
        
        return $slugs;
     }
    
    /*
     *  ADD COLUMN LEADER ADMIN SCREEN PAGE
     */
     function meta_leader_columns_id($column){
          $column[self::META_FIELD_LEADER_COLUMN] = 'Leader';
          return $column;
     }
     
     function meta_leader_columns_value($column_name, $post_id){
       
         if($column_name == self::META_FIELD_LEADER_COLUMN){
            $slug = get_post_meta( $post_id, self::LEADER_META_FIELD_KEY, true );
            
            if($slug){
                $redirect = get_post_meta( $post_id, self::LEADER_META_REDIRECT_KEY, true );
                $r = ($redirect) ? "<strong>(R)</strong>" : "";
                echo get_taxonomy($slug)->labels->singular_name . $r;
            }

         }
     }
         
    /*
     *  GESTIONE META BOX
     */
    function btz_leader_select_meta_box_add(){  
        
        add_meta_box( 'leader_select_meta_box_id', 'Tassonomia Leader', array(&$this, 'btz_inner_select_leader_meta_box'), 'page', 'normal', 'high' ); 
       

    } 
    
    function btz_inner_select_leader_meta_box($post)  
    {  
        $message = '';
        // Use nonce for verification
        wp_nonce_field( plugin_basename( __FILE__ ), self::NONCE );

      
        $selected = get_post_meta( $post->ID, self::LEADER_META_FIELD_KEY, true );
        $taxonomies_slugs = $this->get_taxonomies_slugs();
        
        $redirect = get_post_meta( $post->ID, self::LEADER_META_REDIRECT_KEY, true );
        
        $show_desc = get_post_meta( $post->ID, self::LEADER_META_SHOW_DESC_KEY, true );
       
        ?>  
            <table class=""widefat">
                <tr>
                    <td>
                        <label for="tax_leader_select">Scegli Tassonomia : </label>  
                        <select name="<?php echo self::SELECT_NAME_FIELD; ?>" id="<?php echo self::SELECT_NAME_FIELD; ?>">  
                            <option value="0" <?php selected( $selected, self::NULL_LEADER ); ?>>[Nessuna tassonomia]</option>  
                            <?php foreach ($taxonomies_slugs as $slug => $name) : ?>
                                  <option value="<?php echo $slug; ?>" <?php selected( $selected, $slug ); ?>><?php echo $name; ?></option>  
                            <?php endforeach; ?>
                        </select>
                    </td>    

                    <td> 
                        <label for="tax_leader_redirect">&nbsp;&nbsp;Redirect : &nbsp;
                            <input type="checkbox" name="<?php echo self::REDIRECT_NAME_FIELD; ?>" id="<?php echo self::REDIRECT_NAME_FIELD; ?>"  value="1" <?php checked('1', $redirect); ?> />
                        </label>  

                    </td>
                    <td> 
                        <label for="tax_leader_show_desc">&nbsp;&nbsp;Mostra descrizione : &nbsp;
                            <input type="checkbox" name="<?php echo self::SHOW_DESC_NAME_FIELD; ?>" id="<?php echo self::SHOW_DESC_NAME_FIELD; ?>"  value="1" <?php checked('1', $show_desc); ?> />
                        </label>  

                    </td>
                </tr> 
            </table>
            
 
        <?php  

    } 
    
     
     
    function btz_save_leader_postdata( $post_id){  
        // Bail if we're doing an auto save  
        if( defined( 'DOING_AUTOSAVE' ) && DOING_AUTOSAVE ) return; 
        
        if ( 'page' == $_POST['post_type'] ) {
           if ( ! current_user_can( 'edit_page', $post_id ) )
               return;
         } else {
           if ( ! current_user_can( 'edit_post', $post_id ) )
               return;
         }

        
        // Secondly we need to check if the user intended to change this value.
        if ( ! isset( $_POST[self::NONCE] ) || ! wp_verify_nonce( $_POST[self::NONCE], plugin_basename( __FILE__ ) ) )
                return;
        
        //if saving in a custom table, get post_ID
        //$post_ID = $_POST['post_ID'];
        
        if(isset($_POST[self::SELECT_NAME_FIELD])){
            $selected = $_POST[self::SELECT_NAME_FIELD] ;
            if($selected == self::NULL_LEADER){
                delete_post_meta($post_id, self::LEADER_META_FIELD_KEY);
                delete_post_meta($post_id, self::LEADER_META_REDIRECT_KEY); 
                return;
            }
            
            
            $excluded = $this->get_excluded();
            if(in_array($selected, $excluded )){
                $notice= "La tassonomia '" . $selected . "' è stata esclusa da OTP order.";
                update_option(self::VALIDATION_LEADER_MESSAGE, $notice);
                return;
            }
            
            if($this->taxonomy_busy( $selected, $post_id)){
                $notice= "La tassonomia '" . $selected . "' è occupata su altra pagina.";
                update_option(self::VALIDATION_LEADER_MESSAGE, $notice);
                return;    
            }
            
            update_post_meta( $post_id, self::LEADER_META_FIELD_KEY, $selected );  
        }
        
        if(isset($_POST[self::REDIRECT_NAME_FIELD])){
            update_post_meta( $post_id, self::LEADER_META_REDIRECT_KEY, $_POST[self::REDIRECT_NAME_FIELD] );
        }else{
            delete_post_meta($post_id, self::LEADER_META_REDIRECT_KEY); 
        }
        
        if(isset($_POST[self::SHOW_DESC_NAME_FIELD])){
            update_post_meta( $post_id, self::LEADER_META_SHOW_DESC_KEY, $_POST[self::SHOW_DESC_NAME_FIELD] );
        }else{
            delete_post_meta($post_id, self::LEADER_META_SHOW_DESC_KEY); 
        }
    }
    
    function taxonomy_busy($taxonomy, $post_id = false){
        global $wpdb;
        
        if($post_id){
            $query = "SELECT COUNT(*) FROM wp_postmeta WHERE `meta_key` = '" . self::LEADER_META_FIELD_KEY . "' AND meta_value = %s
                AND post_id <> %d
            ";
            $count = $wpdb->get_var($wpdb->prepare($query, $taxonomy, $post_id)); 
        }else{
            $query = "SELECT COUNT(*) FROM wp_postmeta WHERE `meta_key` = '" . self::LEADER_META_FIELD_KEY . "' AND meta_value = %s";
            $count = $wpdb->get_var($wpdb->prepare($query, $taxonomy)); 
        }
        
        return ($count > 0 );
            
    }
    
    function validation_leader_message(){

        $notice= get_option(self::VALIDATION_LEADER_MESSAGE);
        if($notice){
            echo "<div class='updated fade'><p>$notice</p></div>";
            delete_option(self::VALIDATION_LEADER_MESSAGE);
        }

    }

}
?>
