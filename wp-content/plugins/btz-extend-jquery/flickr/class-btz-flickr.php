<?php
/**
 * The class setup for post-content-shortcodes plugin
 * @version 0.3.2
 */
if (!class_exists('Btz_Flickr')) {
    class Btz_Flickr {

        protected $uid = "70783106@N08"; // aisartag
        protected $keySecret = "ec6377862a0e3aed";
        
        const METHOD_ALL_SETS = 'ajax_photosets';
        const METHOD_SINGLE_SET = 'ajax_photoset';
        
        function __construct($uid = '') {
            require_once 'phpFlickr.php';
            if($uid)$this->uid = $uid ;
            
            add_action( 'wp_enqueue_scripts', array(&$this, 'load_scripts'));

            add_action('wp_ajax_' . self::METHOD_ALL_SETS , array(&$this, self::METHOD_ALL_SETS));
            add_action('wp_ajax_nopriv_' . self::METHOD_ALL_SETS ,array(&$this, self::METHOD_ALL_SETS));
            
            add_action('wp_ajax_' . self::METHOD_SINGLE_SET , array(&$this, self::METHOD_SINGLE_SET));
            add_action('wp_ajax_nopriv_' . self::METHOD_SINGLE_SET ,array(&$this, self::METHOD_SINGLE_SET));
                
        }
        
        function load_scripts(){
            wp_register_script('btz-flickr', plugins_url('/js/btz-flickr.js', __FILE__),
                    array('jquery',  'fancybox', 'jquery-ui-progressbar'));
            
            wp_localize_script( 'btz-flickr', 'flickr_ajax_object',
                    array( 'ajax_url' => admin_url( 'admin-ajax.php' ),
                        'action_photosets' => self::METHOD_ALL_SETS , 'action_photoset' => self::METHOD_SINGLE_SET ) ); 
            wp_enqueue_script( 'btz-flickr' );

         }


        function ajax_photosets(){

                $fx = new phpFlickr('86dc97b665ab0f28078d49e6e981a080'); // key pubblica
                if($fx){
                    $psList = $fx->photosets_getList($this->uid);
                    
                    foreach ($psList['photoset'] as $set){

                         $id = $set['id'];
                         $title = $set['title'];
                         $farm = $set['farm'];
                         $server = $set['server'];
                         $primary = $set['primary'];
                         $secret = $set['secret'];
                         $photos = $set['photos'];
                         $urlThumb  = "http://farm{$farm}.static.flickr.com/{$server}/{$primary}_{$secret}_s.jpg"   ;
                         $urlSet = "http://www.flickr.com/photos/syrdon/sets/{$id}/";
                         $result[] = array('id' => $id, 'title' => $title, 'url_thumbnail' => $urlThumb, 'url_set' => $urlSet,
                             'photos' => $photos);

                    }
                    echo json_encode($result);
                }
                die();

        }

        function ajax_photoset(){
            if(isset($_POST['id_set'])){
                $psID = $_POST['id_set'];
                if(isset($_POST['photos'])){
                    $photos = $_POST['photos'];
                    $photos = !is_nan($photos) ? $photos : 10;

                    $size = isset($_POST['dim']) ? $_POST['dim'] : 'thumbnail';
                    $sizeZoom = isset($_POST['dimZoom']) ? $_POST['dimZoom'] : 'original';

                    $fx = new phpFlickr('86dc97b665ab0f28078d49e6e981a080'); // key pubblica
                    //$ps = $fx->photosets_getPhotos($psID, NULL, NULL, $photos);
                    $ps = $fx->photosets_getPhotos($psID, NULL, NULL, $photos);
                    foreach($ps['photoset']['photo'] as $image){
                        $result[] = array('title' => $image['title'], 'href' => $fx->buildPhotoURL($image, $sizeZoom),
                                        'src' => $fx->buildPhotoURL($image, $size) );
                        
                    }

                    echo json_encode($result);
                }
            }
            die();

        }
    }
}
?>
