<?php
/**
 * The class setup for repository custom otp order
 * @version 0.3.2
 */
if (!class_exists('Btz_Otp_Repository')) {

    /**
     * Classe e metodi per implementazione repository
     */
    class Btz_Otp_Repository {
        
        public static function get_otp_navigation_from_post_id($postId){
           
            global $wpdb;

            if(is_user_logged_in()){
                $where_status = " ( p.post_status = 'publish' OR p.post_status = 'private' ) ";
            }else{
                $where_status = " p.post_status = 'publish' ";
            }


            //var_dump($where_status);

            $statement = 'SELECT tax.*, pp.post_title as title_prev, pp.guid as guid_prev , pn.post_title as title_next, pn.guid as guid_next, tt.taxonomy, t.name
                FROM
                (
                SELECT mid.*, trp.object_id as otp_prev, trn.object_id as otp_next 
                FROM
                (SELECT tr.object_id, tr.term_taxonomy_id ,
                                                                (SELECT MAX(trs.otp_order)
                                                                                FROM `wp_term_relationships` trs INNER JOIN `wp_posts` p ON p.ID = trs.object_id 
                                                                                WHERE ' . $where_status . ' AND trs.term_taxonomy_id = tr.term_taxonomy_id AND trs.otp_order < tr.otp_order ) as order_prev,
                                                                (SELECT MIN(trs.otp_order)
                                                                                FROM `wp_term_relationships` trs INNER JOIN `wp_posts` p ON p.ID = trs.object_id 
                                                                                WHERE ' . $where_status . ' AND trs.term_taxonomy_id = tr.term_taxonomy_id AND trs.otp_order > tr.otp_order ) as order_next

                FROM `wp_term_relationships` tr
                ) as mid
                LEFT JOIN `wp_term_relationships` trp ON trp.term_taxonomy_id = mid.term_taxonomy_id AND trp.otp_order = mid.order_prev
                LEFT JOIN `wp_term_relationships` trn ON trn.term_taxonomy_id = mid.term_taxonomy_id AND trn.otp_order = mid.order_next
                WHERE ( (mid.order_prev is not null) OR (mid.order_next is not null))
                ) as tax
                INNER JOIN wp_term_taxonomy AS tt ON tax.term_taxonomy_id = tt.term_taxonomy_id
                INNER JOIN wp_terms AS t ON t.term_id = tt.term_id
                LEFT join wp_posts pp ON pp.ID = tax.otp_prev
                LEFT join wp_posts pn ON pn.ID = tax.otp_next
                WHERE tax.object_id = %d
                                 ';   


                $result = $wpdb->get_results($wpdb->prepare($statement, $postId ));
          //      error_log(print_r($result, true));
                return $result;
        }   
        
        public static function get_otp_leaders($query_object, $taxleader, $startrow, $numrows){
            global  $wpdb;

            $query_object->request = "
                    SELECT pp.*, tt.term_taxonomy_id, tt.taxonomy, t.term_id, t.name as term_name, t.slug
                FROM
                (
                SELECT MIN(tr.object_id) AS object_id,  leaders.term_taxonomy_id, tr.otp_order
                FROM
                (SELECT tr.term_taxonomy_id, MIN(tr.otp_order) as first
                FROM `wp_term_relationships` tr 
                GROUP BY tr.term_taxonomy_id
                ) as leaders
                INNER JOIN `wp_term_relationships` tr ON leaders.term_taxonomy_id = tr.term_taxonomy_id AND leaders.first = tr.otp_order
                GROUP BY term_taxonomy_id, otp_order
                ) as pivot
                INNER JOIN wp_term_taxonomy AS tt ON pivot.term_taxonomy_id = tt.term_taxonomy_id
                INNER JOIN wp_terms AS t ON t.term_id = tt.term_id
                INNER JOIN wp_posts pp ON pp.ID = pivot.object_id
                WHERE tt.taxonomy = '$taxleader'
                ORDER BY pp.post_title
                LIMIT $startrow , $numrows
             ";
            
            
            
            $sql_result = $wpdb->get_results($query_object->request, OBJECT);
         //   error_log(print_r($sql_result, true));
            return $sql_result;
            
            
            
        }
        
        public static function get_otp_leaders_count($taxleader){
            global  $wpdb;
             $total = "
              SELECT COUNT(*)
           FROM
            (
            SELECT MIN(tr.object_id) AS object_id,  leaders.term_taxonomy_id, tr.otp_order
            FROM
            (SELECT tr.term_taxonomy_id, MIN(tr.otp_order) as first
            FROM `wp_term_relationships` tr 
            GROUP BY tr.term_taxonomy_id
            ) as leaders
            INNER JOIN `wp_term_relationships` tr ON leaders.term_taxonomy_id = tr.term_taxonomy_id AND leaders.first = tr.otp_order
            GROUP BY term_taxonomy_id, otp_order
            ) as pivot
            INNER JOIN wp_term_taxonomy AS tt ON pivot.term_taxonomy_id = tt.term_taxonomy_id
            INNER JOIN wp_terms AS t ON t.term_id = tt.term_id
            INNER JOIN wp_posts pp ON pp.ID = pivot.object_id
            WHERE tt.taxonomy = '$taxleader'
             "; 
             
            $totalposts = $wpdb->get_var($total); 
            return $totalposts;
        }
        
        public static function get_otp_trailers_from_tt_id($tt_id, $small=true){
            global $wpdb;
            
            $fields = ( $small ) ? "pp.ID, pp.post_title, pp.guid " : " pp.* ";   
            $query = "
             SELECT $fields , tt.term_taxonomy_id, tt.taxonomy, t.term_id, t.name as term_name, t.slug
FROM
(
SELECT MAX(tr.object_id) AS object_id,  trailers.term_taxonomy_id, tr.otp_order
FROM
(SELECT tr.term_taxonomy_id, MAX(tr.otp_order) as last
FROM `wp_term_relationships` tr 
GROUP BY tr.term_taxonomy_id
) as trailers
INNER JOIN `wp_term_relationships` tr ON trailers.term_taxonomy_id = tr.term_taxonomy_id AND trailers.last = tr.otp_order
GROUP BY term_taxonomy_id, otp_order
) as pivot
INNER JOIN wp_term_taxonomy AS tt ON pivot.term_taxonomy_id = tt.term_taxonomy_id
INNER JOIN wp_terms AS t ON t.term_id = tt.term_id
INNER JOIN wp_posts pp ON pp.ID = pivot.object_id
WHERE tt.term_taxonomy_id = '$tt_id'
LIMIT 0 , 1
            ";
         
            $sql_result = $wpdb->get_row($query, OBJECT);
           // error_log(print_r($sql_result, true));
            return $sql_result;
         
        }
        
        public static function get_otp_leaders_from_tt_id($tt_id, $small=true){
            global $wpdb;
            
            $fields = ( $small ) ? "pp.ID, pp.post_title, pp.guid " : " pp.* ";   
            $query = "
             SELECT $fields , tt.term_taxonomy_id, tt.taxonomy, t.term_id, t.name as term_name, t.slug
FROM
(
SELECT MIN(tr.object_id) AS object_id,  trailers.term_taxonomy_id, tr.otp_order
FROM
(SELECT tr.term_taxonomy_id, MIN(tr.otp_order) as last
FROM `wp_term_relationships` tr 
GROUP BY tr.term_taxonomy_id
) as trailers
INNER JOIN `wp_term_relationships` tr ON trailers.term_taxonomy_id = tr.term_taxonomy_id AND trailers.last = tr.otp_order
GROUP BY term_taxonomy_id, otp_order
) as pivot
INNER JOIN wp_term_taxonomy AS tt ON pivot.term_taxonomy_id = tt.term_taxonomy_id
INNER JOIN wp_terms AS t ON t.term_id = tt.term_id
INNER JOIN wp_posts pp ON pp.ID = pivot.object_id
WHERE tt.term_taxonomy_id = '$tt_id'
LIMIT 0 , 1
            ";
         
            $sql_result = $wpdb->get_row($query, OBJECT);
         //   error_log(print_r($sql_result, true));
            return $sql_result;
         
        }
    }
}
?>
