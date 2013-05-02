<?php

/**
 * The class setup for repository custom content
 * @version 0.3.2
 */
if (!class_exists('Btz_Content_Repository')) {

    /**
     * Classe e metodi per implementazione repository
     */
    class Btz_Content_Repository {

        public static function get_taxonomies_with_ids() {

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

       
         public static function get_post_titles_ids_paged($pageNum) {

            global $wpdb;
            $row_per_page = 5;

            if (!is_numeric($pageNum) || $pageNum <= 1) {
                $pageNum = 1;
            }



            $types = get_post_types(array('_builtin' => false, 'public' => true), 'names');
            $types[] = 'post';
            //$types[] = 'page';

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
        
         public static function get_post_title_from_id($postId) {

            global $wpdb;
            
            if (!is_numeric($postId) || $postId <= 0) {
                return null;
            }

            
            $where_status = " ( p.post_status = 'publish' OR p.post_status = 'private' ) ";
            $where = " WHERE p.post_title <> '' AND p.post_type  " . $where_status . " AND p.ID =" .$postId;
            


            $result = $wpdb->get_row("SELECT p.post_title FROM wp_posts p " . $where);
            
            if($result){
                return $result->post_title;
            }else{
                return null;
            }
            
        }
        
        
         public static function get_post_titles_from_ids($listPostId) {

            global $wpdb;
            
            
            $values = explode(',',$listPostId);
            $valid = true;

            foreach($values as $value) {
                if(!ctype_digit($value)) {
                    $valid = false;
                    break;
                }
            }
            if (!$valid) {
                return null;
            }

            $types = get_post_types(array('_builtin' => false, 'public' => true), 'names');
            $types[] = 'post';

            foreach ($types as $t) {
                $array_types[] = "'" . $t . "'";
            }
            
           
            
            $where_status = " ( p.post_status = 'publish' OR p.post_status = 'private' ) ";
            $in_type = '( ' . implode(', ', $array_types) . ' )';
            $in_post = '( ' . $listPostId . ' )';
            $where = " WHERE p.post_title <> '' AND " . $where_status . "  AND post_type IN " . $in_type . " AND p.ID IN " . $in_post;
            $query = "SELECT p.ID, p.post_title FROM wp_posts p " . $where;
            

            $results_query = $wpdb->get_results($query );
            
            $results = array();
            foreach($results_query as $row){
                array_push($results, array('ID' => $row->ID, 'post_title' => $row->post_title));
            }
            
            return $results;
        }

       
        
    
        public static function ajax_query_posts() {
           // error_log(print_r($_POST, true));
            
            if (isset($_POST['tiny_request'])) {
                $request = $_POST['tiny_request'];
                switch ($request) {
                    case 'taxonomies':
                        echo json_encode(self::get_taxonomies_with_ids());
                        break;
                    case 'post_list':
                        if (isset($_POST['pageNum'])) {
                            echo json_encode(self::get_post_titles_ids_paged($_POST['pageNum']));
                        }
                        break;
                }
            }

            die();
        }

}  }
?>