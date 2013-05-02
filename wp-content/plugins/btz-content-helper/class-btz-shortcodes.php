<?php

/**
 * The class setup for post-content-shortcodes plugin
 * @version 0.3.2
 */
if (!class_exists('Btz_Shortcodes')) {

    /**
     * Classe e metodi per implementazione del cloning content
     */
    class Btz_Shortcodes {

        protected $tabCount = 0;
        protected $tabs = array();
        protected $sectionCount = 0;
        protected $sections = array();

        function __construct() {
            
            require_once 'repository/class-btz-content-repository.php';

            add_action('init', array(&$this, 'btz_shortcodes_init'));

            add_shortcode("tabs", array(&$this, "tabs_shortcode"));
            add_shortcode("tab", array(&$this, "single_tab_function"));

            add_shortcode("accordion", array(&$this, "accordion_shortcode"));
            add_shortcode("section", array(&$this, "accordion_section_function"));

            add_shortcode("marker", array(&$this, "mark_box"));


            add_action('wp_ajax_shortcodes', array(&$this, 'json_query_post'));
        }

        function btz_shortcodes_init() {

            //Abort early if the user will never see TinyMCE
            if (!current_user_can('edit_posts') && !current_user_can('edit_pages') && get_user_option('rich_editing') == 'true')
                return;

            //Add a callback to regiser our tinymce plugin   
            add_filter("mce_external_plugins", array(&$this, "btz_register_tinymce_plugin"));

            // Add a callback to add our button to the TinyMCE toolbar
            add_filter('mce_buttons_3', array(&$this, 'btz_add_tinymce_buttons'));
        }

        //This callback registers our plug-in
        function btz_register_tinymce_plugin($plugin_array) {
            $plugin_array['btz_tinymce_plugin'] = plugins_url('/js/shortcodes.js', __FILE__);
            return $plugin_array;
        }

        //This callback adds our button to the toolbar
        function btz_add_tinymce_buttons($buttons) {
            //Add the button ID to the $button array
            array_push($buttons, "wpse72394_button", "btz-marker_button", "btz-list_button", "btz-content_button", "btz-popup_button",
                    "btz-tab_button", "btz-tabs_button", "btz-section_button", "btz-accordion_button", 
                    "btz-tabs-delete_button", "btz-accordion-delete_button");
            return $buttons;
        }

        function json_query_post() {

            if (isset($_POST['tiny_request'])) {
                $request = $_POST['tiny_request'];
                switch ($request) {
                    case 'taxonomies':
                        echo json_encode(Btz_Content_Repository::get_taxonomies_with_ids());
                        break;
                    case 'post_list':
                        if (isset($_POST['pageNum'])) {
                            echo json_encode(Btz_Content_Repository::get_post_titles_ids_paged($_POST['pageNum']));
                        }
                        break;
                }
            }

            die();
        }

        function get_taxonomies_with_ids() {

            global $wpdb;
            $taxonomies = get_taxonomies(array('public' => true,), 'objects');
            unset($taxonomies['post_format']);

            $array_tax = array();
            foreach ($taxonomies as $tax) {
                $prefix = '';
                if ($tax->rewrite['slug'] == 'tag') {
                    $prefix = 'post_';
                }
                $array_slug[] = "'" . $prefix . $tax->rewrite['slug'] . "'";
                $array_tax[$tax->rewrite['slug']] = array('name' => $tax->labels->singular_name, 'data' => array());
            }


            $slugs = '( ' . implode(', ', $array_slug) . ' )';
            $statement = 'SELECT tt.term_taxonomy_id, tt.taxonomy, t.name, t.slug FROM wp_term_taxonomy tt  INNER JOIN wp_terms t ON tt.term_id = t.term_id WHERE tt.taxonomy IN ' . $slugs;


            $results = $wpdb->get_results($statement);
            foreach ($results as $row) {
                $key = ( $row->taxonomy === 'post_tag') ? 'tag' : $row->taxonomy;
                array_push($array_tax[$key]['data'], array('id' => $row->term_taxonomy_id, 'slug' => $row->slug, 'name' => $row->name));
            }


            return $array_tax;
        }

        function get_post_titles_ids($pageNum) {

            global $wpdb;
            $row_per_page = 5;

            if (!is_numeric($pageNum) || $pageNum <= 1) {
                $pageNum = 1;
            }



            $types = get_post_types(array('_builtin' => false, 'public' => true), 'names');
            $types[] = 'post';
            $types[] = 'page';

            foreach ($types as $t) {
                $array_types[] = "'" . $t . "'";
            }


            $where_status = " ( p.post_status = 'publish' OR p.post_status = 'private' ) ";
            $in = '( ' . implode(', ', $array_types) . ' )';
            $where = " WHERE p.post_title <> '' AND " . $where_status . "  AND post_type IN " . $in;
            $order_by = " ORDER BY p.post_title ";


            $total_rows = $wpdb->get_var("SELECT COUNT(*) FROM wp_posts p " . $where);
            
            $first = ($pageNum - 1 ) * $row_per_page;
            

            if ($total_rows === 0 || $first > $total_rows) {
                return array(
                    'pageNum' => 0,
                    'pageTotal' => 0,
                    'data' => array()
                );
            }

            $limit = " LIMIT $first, $row_per_page";
            $statement = "SELECT p.ID, p.post_title FROM wp_posts p  " . $where . $order_by . $limit;
            
            $results = $wpdb->get_results($statement);
            foreach ($results as $row) {
                $array_results[] = array('ID' => $row->ID, "title" => $row->post_title);
            }

            $return = array(
                'pageNum' => $pageNum,
                'pageTotal' => ceil($total_rows / $row_per_page),
                'data' => $array_results
            );

            return $return;
        }

        function tabs_shortcode($atts, $content = null) {
            $this->tabCount = 0;
            do_shortcode($content);

            if (is_array($this->tabs)) {
                foreach ($this->tabs as $tab) {
                    $tabs[] = '<li><a href="#' . str_replace(" ", "-", strtolower($tab["name"])) . '">' . $tab["name"] . '</a></li>';
                    $panes[] = '<div id="' . str_replace(" ", "-", strtolower($tab["name"])) . '"><p>' . do_shortcode($tab["content"]) . '</p></div>';
                }
            }

            if (is_array($panes)) {
                $output = '
                                   <script type="text/javascript">
                                            jQuery(document).ready(function($){
                                                    $(".tabs").tabs();
                                            });
                                    </script>
                                    <div class="tabs"><ul>' . implode("", $tabs) . '</ul>' . implode("", $panes) . '</div>
                                 ';
                return $output;
            }
        }

        function single_tab_function($atts, $content = null) {
            extract(shortcode_atts(array(
                        "name" => "Tab name"
                            ), $atts));

            $x = $this->tabCount;
            $this->tabs[$x] = array(
                "name" => sprintf($name, $this->tabCount),
                "content" => $content
            );

            $this->tabCount += 1;
        }

        function accordion_shortcode($atts, $content = null) {
            extract(shortcode_atts(array(
                        "collapsible" => "false",
                        "heightstyle" => "auto",
                        "active" => "true",
                            ), $atts));


            $options = "";
            //      Check if it's collapsible
            if ($collapsible == "true") {
                $options = "collapsible: true";
            }

            if ($active == "false") {
                if ($options <> "") {
                    $options .= ",";
                }
                $options .= "active: false";
            }

            //      Check if it's collapsible
            if ($heightstyle != "auto") {
                if ($options <> "") {
                    $options .= ",";
                }
                $options .="heightStyle: " . $heightstyle;
            }

            if ($options != "") {
                $options = "{" . $options . "}";
            }

            $this->sectionCount = 0;

            //      Get the content
            do_shortcode($content);
                    

            if (is_array($this->sections)) {
                foreach ($this->sections as $section) {
                    $panes[] = '<h3><a href="#' . str_replace(" ", "-", strtolower($section["name"])) . '">' . $section["name"] . '</a></h3>
                                    <div id="' . str_replace(" ", "-", strtolower($section["name"])) . '">
                                            <p>' . do_shortcode($section["content"]) . '</p>
                                    </div>';
                }
            }

            if (is_array($panes)) {
                $output = '
                                <script type="text/javascript">
                                        jQuery(document).ready(function($){
                                                $(".accordion").accordion(' . $options . ');
                                        });
                                </script>
                                <div class="accordion">' . implode("", $panes) . '</div>
                        ';
                return $output;
            }
        }

        function accordion_section_function($atts, $content = null) {
            extract(shortcode_atts(array(
                        "name" => "Section name"
                            ), $atts));

            $x = $this->sectionCount;
            $this->sections[$x] = array(
                "name" => sprintf($name, $this->sectionCount),
                "content" => $content
            );
            $this->sectionCount += 1;
        }

        function mark_box($atts, $content = null) {
            extract(shortcode_atts(array(
                        "class" => ""
                            ), $atts));

            if (empty($class)) {
                $class = "marker";
            }
            $content = str_ireplace("<br />", "", $content);
            return '<div class="' . $class . '">' . $content . '</div>';
        }

    }

}
?>