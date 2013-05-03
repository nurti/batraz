<?php

require_once(STYLESHEETPATH . '/classes/constants.php');

class BTZ_Option_element {
    
    public $name;
    public $label;
    public $type = 'text';
    public $usemedia;
    public $class ="regular-text";
    public $values = array();
    public $tab =  BTZ_Options_Helper::DEFAULT_TAB;
    
    function __construct($parameters = array()) {
        foreach($parameters as $key => $value) {
            $this->$key = $value;
        }
    }
    

}
class BTZ_Options_Helper {

    protected $jquery_ui_themes = array( 'base', 'black-tie', 'blitzer', 'cupertino', 'dark-hive', 'dot-luv',
                                    'eggplant', 'excite-bike', 'flick', 'hot-sneaks', 'humanity', 'le-frog',
                                    'mint-choc', 'overcast', 'pepper-grinder', 'redmond', 'smoothness', 'south-street',
                                    'start', 'sunny', 'swanky-purse', 'trontastic', 'ui-darkness', 'ui-lightness', 'vader'
                                );
    protected $mSBthemes = array(
                'ui-darkness' => 'light',
                'back-tie' => 'light',
                'btz-sunny' => 'dark',
        );
    
    public $elements = array();
    
    protected  $option_name_slug = 'batraz';
    protected  $option_menu_slug;
    protected  $option_group;
    protected  $option_tab = array( self::DEFAULT_TAB);
    
    const TABS_ID = 'btz-options-tabs';
    const DEFAULT_TAB = 'Generale';
    
     public function __construct($option_slug = '') {
        
        
        
        if(!empty($option_slug)){
            $this->option_name_slug = $option_slug;
        }
        
        $this->option_group = $this->option_name_slug . '-settings-group';
        $this->option_menu_slug = $this->option_name_slug . '-menu-slug';
        
        $this->assets();

    }
    
    /*
                HOOKS
     */
    public function assets(){
        
        add_action('admin_menu', array(&$this, 'setting_menu'));
        add_action('admin_init', array(&$this, 'register_settings'));
        
        add_action( 'admin_enqueue_scripts', array(&$this, 'load_admin_script') );
        
        add_filter( 'wp_nav_menu_items', array(&$this,'add_home_link') , 10, 2 );
        
        add_filter('single_template', array(&$this, 'set_single_styles'), 10, 1);
        
        add_action( 'wp_enqueue_scripts', array(&$this,'add_custom_style_sheet'), 100 );
   
    }

    
    function add_elements($elements){
        foreach($elements as $element){
            if(isset($element['tab']) && !in_array($element['tab'], $this->option_tab)){
                $this->option_tab[] = $element['tab'];
            }
            $this->add(new BTZ_Option_element($element));
                       
        }

           
       
    }
    
    public function load_admin_script($hook){
        
        //var_dump($hook);
        if('settings_page_' . $this->option_menu_slug != $hook)
            return ;
        
         $tmp = array();
         foreach ($this->elements as $element){
            if(isset($element->usemedia) ){
                if(!in_array($element->usemedia, $tmp)){
                    $tmp[] = $element->usemedia;
                }
            }
         }
         
         if(count($tmp) > 0 ){
            
             // This function loads in the required media files for the media manager.
             wp_enqueue_media();
             foreach($tmp as $usemedia){
                $handle = 'btz-nmp-' . basename($usemedia, '.js');
                $_handle = 'btz_nmp_' . basename($usemedia, '.js');
                wp_register_script($handle, get_stylesheet_directory_uri() . $usemedia, array( 'jquery' ), '1.0.0', true);
                wp_localize_script( $handle, $_handle,
                    array(
                       'title'     => __( 'Carica o scegli file' ), // This will be used as the default title
                       'button'    => __( 'Inserisci immagine nel campo' )            // This will be used as the default button text
                    )
                );
                wp_enqueue_script( $handle );
             }
             
             
             wp_register_style('btz-admin', get_stylesheet_directory_uri() . '/css/btz_admin.css' );
             wp_enqueue_style('btz-admin');
         }
    }
    
    
    
    
  
    
    public function add($element){
        
        $this->elements[] = &$element;
    }
    
    function register_settings() {
        foreach($this->elements as $element){
            register_setting($this->option_group, $element->name);
        }
    }
    
  
    function setting_menu() {
        $elements = apply_filters('adding_elements_options', $this);
        $this->add_elements($elements);
        
        $page =  add_options_page(ucfirst($this->option_name_slug), ucfirst($this->option_name_slug),
                'manage_options', $this->option_menu_slug , array(&$this, 'options_page'));

    }
    

    function options_page() {
        if (!current_user_can('manage_options')) {
            wp_die(__('You do not have sufficient permissions to access this page.'));
           
        }
       
        ?>

        <div class="wrap">
            <div  class="icon32"><br></div>
            <h2>Setting <?php echo ucfirst($this->option_name_slug); ?></h2>
            <br>
                <form method="post" action="options.php">

                <?php settings_fields($this->option_group);
                    $this->create_table();
                    submit_button();
                ?>
                </form>
            </div>
            <script type="text/javascript">
                jQuery(document).ready(function() {
                    jQuery( "#<?php echo self::TABS_ID; ?>" ).tabs();
                });
            </script>
        <?php
    }
    
    public function create_table(){
        ?>
        
                
                <?php ob_start(); ?>
                <div id="<?php echo self::TABS_ID; ?>">
                    <ul>
                        <?php foreach ($this->option_tab as $tab) : ?>
                        <li><a href="#<?php echo self::TABS_ID . sanitize_title_with_dashes($tab); ?>" ><?php echo $tab; ?></a></li>
                        <?php endforeach;  ?>
                    </ul>
                <?php foreach ($this->option_tab as $tab) : ?>
                    <div id="<?php echo self::TABS_ID . sanitize_title_with_dashes($tab); ?>">
                        <table class="form-table">
                            <tbody>
                                
                                <?php foreach ($this->elements as $element) : ?>
                                     <?php if ($element->tab == $tab) : ?>
                                        <tr valign="top">
                                              <th scope="row"><label for="<?php echo $element->name ?>" ><?php echo $element->label ?></label></th>
                                              <td><?php  $this->create_input_element($element); ?></td>
                                        </tr>
                                     <?php endif; ?>
                                <?php endforeach;  ?>
                                 
                             </tbody>
                        </table>  
                    </div>        
                <?php endforeach;  ?>
                </div>    

        <?php
        
    }
    
    protected function create_input_element($element){
        switch($element->type){
            case 'checkbox':
                $this->create_checkbox_element($element);
                break;
            
            case 'select':
                $this->create_select_element($element);
                break;
            
            case 'text':    
                $this->create_text_element($element);
                break;
            
            default :
                $this->create_text_element($element);
                break;
        }
    }
    
    protected function create_text_element($element){
        ?>
            <input type="text" name="<?php echo $element->name ?>" value="<?php echo get_option($element->name); ?>" class="<?php echo $element->class; ?>" />
            <?php if(!empty($element->usemedia)) : ?>
                 <a href="#" id="<?php echo $element->name; ?>" class="btz-media-load button button-primary">Scegli immagine</a>
            <?php endif; ?>
        <?php
    }
    
    protected function create_checkbox_element($element){
        ?>
            <input type="checkbox" name="<?php echo $element->name ?>" value="1" <?php checked('1', get_option($element->name)); ?> />
        <?php
    }
    
    protected function create_select_element($element){
        ?>
            <select class="<?php echo $element->class; ?>" id="<?php echo $element->name; ?>" name="<?php echo $element->name ?>" size="1">
                <?php foreach($element->values as $key => $option) : ?>
                         <option value="<?php echo $option ?>" <?php echo (get_option($element->name) == $option ? 'selected' : ''); ?> ><?php echo self::get_name_from_dir($option) ?></option>
                <?php endforeach;  ?>
            </select>
        <?php
    }
    
    
    
    function add_home_link($items, $args) {
      
        $home_before = '';
        $blogs_after = '';

        if (is_front_page())
            $class = 'class="current_page_item"';
        else
            $class = '';

        if( isset($args->theme_location)){
            $loc_theme = $args->theme_location;
            if($loc_theme === 'topright' ){
                if(get_option(OPTION_TOPRIGHT_HH) > 0){
                     $home_before = $this->get_home_menu_item($args, $class);;
                }

            }


            if($loc_theme === 'primary' ){
                if(get_option(OPTION_PRIMARY_HH) > 0){
                     $home_before = $this->get_home_menu_item($args, $class);;
                }
            }
        }
        $items = $home_before . $items . $blogs_after;
        return $items;
    }
    
    
    function get_home_menu_item($args, $class){
   
        $item = 
        '<li ' . $class . '>' .
        $args->before .
        '<a href="' . home_url( '/' ) . '" title="Home">' .    
        $args->link_before . 'Home' . $args->link_after .
        '</a>' .
        $args->after .
        '</li>';


        return $item;
    }
    
    /*
     *  set style per singolo post
     */
    protected $single_style;
    function set_single_styles($single_template){
        
        global $post;
       
        if(!empty($post->post_type)){
            $this->single_style = get_option(OPTION_COLOR_STYLE_SINGLE . '_' . $post->post_type);
        }
         
        return $single_template;
    }
    
    /*
     *  set styles dalle opzioni del tema
     */
    function add_custom_style_sheet() {
	global $wp_styles, $btz_sub_theme;
        
        
        $btz_sub_theme = get_option(OPTION_COLOR_STYLE);
	if(!empty($this->single_style) && $btz_sub_theme != $this->single_style){
             $btz_sub_theme = $this->single_style;
        }
       
        if(wp_style_is('jquery-ui-standard-css')){
           // if(substr(strtolower($btz_sub_theme), 0, 3) != 'btz'){
            if(in_array($btz_sub_theme, $this->jquery_ui_themes)){    
                $wp_styles->registered['jquery-ui-standard-css']->src = 'http://ajax.googleapis.com/ajax/libs/jqueryui/1.10.2/themes/'.$btz_sub_theme.'/jquery-ui.css';
            }elseif(file_exists( STYLESHEETPATH . '/css/' . $btz_sub_theme . '/jquery-custom.min.css')){
                $wp_styles->registered['jquery-ui-standard-css']->src = get_stylesheet_directory_uri() . '/css/' . $btz_sub_theme . '/jquery-custom.min.css';
            }else{
                $wp_styles->registered['jquery-ui-standard-css']->src = 'http://ajax.googleapis.com/ajax/libs/jqueryui/1.10.1/themes/base/jquery-ui.css';
            }
            
        }
        
        wp_enqueue_style( 'child-custom-' . $btz_sub_theme, get_stylesheet_directory_uri() . '/css/' . $btz_sub_theme . '/custom.css', array(), '1.0' );
        $wp_styles->add_data( 'child-custom-' . $btz_sub_theme , 'title', __( $btz_sub_theme, 'child-theme-textdomain' ) );

        if(isset ( $this->mSBthemes[$btz_sub_theme])){
            $GLOBALS['mSBtheme'] =  $this->mSBthemes[$btz_sub_theme];
        }else{
            $GLOBALS['mSBtheme'] =  'light';
        }
        
    }
    
    
    
    
    
    
    
    
    
    /*
     *  
     *   AREA METODI STATICI
     */
    public static function hide_topmenu(){
        return ( get_option(OPTION_TOPRIGHT_HIDE) > 0 );
    }
    
    public static function hide_mainmenu(){
        return ( get_option(OPTION_PRIMARY_HIDE) > 0 );
    }
    
    public static function search_on_header(){
        return ( get_option(OPTION_SEARCH_ON_HEADER) > 0 );
    }
    
    public static function search_on_footer(){
        return ( get_option(OPTION_SEARCH_ON_FOOTER) > 0 );
    }
    
    public static function get_logo(){
    
        $logo_url = get_option(OPTION_LOGO_URL);
        if(self::is_image($logo_url)){
            $tag = '<img src="' . $logo_url . '"' . ' />';
        }else{
            $tag = get_bloginfo('name');
        }
       
        return $tag;
    }
    
    public static function logo(){
        echo self::get_logo();
    }
    
    public static function get_copyright(){
        return get_option(OPTION_COPYRIGHT);
    }

    public static function copyright(){
        echo self::get_copyright();
    }
    
    public static function get_annotation(){
       return get_option(OPTION_ANNOTATION);
    }
    
    public static function annotation(){
        echo self::get_annotation();
    }
    
    public static function is_image($img){
    
       $ext = preg_match('/\.([^.]+)$/', $img, $matches) ? strtolower($matches[1]) : false;
    
        $image_exts = array( 'jpg', 'jpeg', 'jpe', 'gif', 'png' );
    
        if ( in_array($ext, $image_exts) )
            return true;
        return false;
    }
    
    public static function get_styles_path(){
        $styles = glob(STYLESHEETPATH . '/css/*', GLOB_ONLYDIR);
        $result = array();
        foreach($styles as $style){
            $result[] = basename($style);
        }
        
        return $result;
    }
    
    public static function get_name_from_dir($style){
        
        $parts = explode('-', $style);
        $result = array();
        foreach($parts as $part){
            $result[]  = (strtolower($part) == 'ui') ? ucwords($part) :  ucfirst($part);
        }
        return implode(' ', $result);
                
    }
    
    /*
     *  thumbnail indicator 
     */
    public static function get_thumbnail_indicator($postId, $class = ''){
        //global $post;
        $block = '';
        $img = get_post_meta($postId, BTZ_THUMB_INDICATOR, true); 
        if(!empty($img) && self::is_image($img)){
            $block = '<img width="115" height="115" src="' . $img . '" class="attachment-115x115 wp-post-image ' . $class . '" alt="' . BTZ_THUMB_INDICATOR . '" />';
        }else{
  
            $block = get_the_post_thumbnail( $postId , array(115,115, 'class' => ' ' . $class));
        
            if(empty($block)){
                $img = self::get_theme_image_rand();
                if(empty($img)){
                    $img = get_bloginfo('wpurl') .'/wp-includes/images/icon-pointer-flag-2x.png';
                }
                $block = '<img width="115" height="115" src="' . $img . '" class="attachment-115x115 wp-post-image ' . $class . '" alt="' . 'thumbnail" />';

            }
        }
   
        return $block;
    }
    
    /*
     * 
     *  immagine random newlla cartella left-images del teme
     */
    protected static function get_theme_image_rand(){
    
        if(!isset($_GLOBALS['theme_images'])){
            $imgs = glob(STYLESHEETPATH . '/left-images/*.{png,jpg}', GLOB_BRACE);
            $_GLOBALS['theme_images'] = str_replace(STYLESHEETPATH, get_stylesheet_directory_uri() ,$imgs);
        }
        
        $theme_images = $_GLOBALS['theme_images'];

        if(count($theme_images) > 0){
            $image_rand = $theme_images[array_rand($theme_images)];
        }

        return  $image_rand;

    }
    
   
}
?>
