<?php
/*
Controller name: BD
Controller description: Directory API
*/

class JSON_API_Bd_Controller {

    public function tenant(){

        global $wpdb;

       /* $result = wp_cache_get( 'tenant_list' );
        if ( false === $result ) {*/
            $result = $wpdb->get_results('SELECT a.post_title as title, a.post_published as published, a.post_views as view_count, a.post_id as id , d.value as description , f.value as featured,
                  /*contact*/ co.phone , co.mobile , co.fax, co.email , co.website,
                  /*address*/ l.address , l.lat , l.lng, fl.value as fl, rating.average as rating,
                  /*social*/ s.twitter , s.facebook , s.googleplus,  rc.value as review_count,pc.value as photo_count,
                  /*custom fields*/ deal.value as deal, environment.value as environment, feature.value as feature ,  lmt.value as lmt , parking.value as parking , price.value as price

                  /*GROUP_CONCAT(DISTINCT tf.value) AS tf,GROUP_CONCAT(DISTINCT wh.value) as wh,GROUP_CONCAT(DISTINCT cat.value) as category,GROUP_CONCAT(DISTINCT wt.value) as wt,GROUP_CONCAT(DISTINCT parent.entity_id) as photos,GROUP_CONCAT(DISTINCT hours.value) as hours*/
                FROM menu_sabai_content_post a
                LEFT JOIN (menu_sabai_entity_field_content_body d) ON (d.bundle_id = 7 AND d.entity_id = a.post_id )
                LEFT JOIN (menu_sabai_entity_field_content_featured f) ON (f.bundle_id = 7 AND f.entity_id = a.post_id )
                LEFT JOIN (menu_sabai_entity_field_directory_contact co) ON (co.bundle_id = 7 AND co.entity_id = a.post_id )
                LEFT JOIN (menu_sabai_entity_field_directory_location l) ON (l.bundle_id = 7 AND l.entity_id = a.post_id )
                LEFT JOIN (menu_sabai_entity_field_directory_social s) ON (s.bundle_id = 7 AND s.entity_id = a.post_id )
                LEFT JOIN (menu_sabai_entity_field_field_deal deal) ON (deal.bundle_id = 7 AND deal.entity_id = a.post_id )
                LEFT JOIN (menu_sabai_entity_field_field_environment environment) ON (environment.bundle_id = 7 AND environment.entity_id = a.post_id )
                LEFT JOIN (menu_sabai_entity_field_field_feature feature) ON (feature.bundle_id = 7 AND feature.entity_id = a.post_id )
                LEFT JOIN (menu_sabai_entity_field_field_limit lmt) ON (lmt.bundle_id = 7 AND lmt.entity_id = a.post_id )
                /*LEFT JOIN (menu_sabai_entity_field_field_hours hours) ON (hours.bundle_id = 7 AND hours.entity_id = a.post_id )*/
                LEFT JOIN (menu_sabai_entity_field_field_parking parking) ON (parking.bundle_id = 7 AND parking.entity_id = a.post_id )
                LEFT JOIN (menu_sabai_entity_field_field_price price) ON (price.bundle_id = 7 AND price.entity_id = a.post_id )
                /*LEFT JOIN (menu_sabai_entity_field_field_tenant_feature tf) ON (tf.bundle_id = 7 AND tf.entity_id = a.post_id )*/
                /*LEFT JOIN (menu_sabai_entity_field_field_what wt) ON (wt.bundle_id = 7 AND wt.entity_id = a.post_id )*/
                /*LEFT JOIN (menu_sabai_entity_field_field_with_whom wh) ON (wh.bundle_id = 7 AND wh.entity_id = a.post_id )*/
                LEFT JOIN (menu_sabai_entity_field_field_location fl) ON (fl.bundle_id = 7 AND fl.entity_id = a.post_id )
                /*LEFT JOIN (menu_sabai_entity_field_content_parent parent) ON (parent.bundle_id = 9 AND parent.value = a.post_id )*/
                LEFT JOIN (menu_sabai_entity_field_content_children_count rc) ON (rc.bundle_id = 7 AND rc.entity_id = a.post_id AND rc.child_bundle_name = "directory_listing_review" )
                LEFT JOIN (menu_sabai_entity_field_content_children_count pc) ON (pc.bundle_id = 7 AND pc.entity_id = a.post_id AND pc.child_bundle_name = "directory_listing_photo" )
                LEFT JOIN (menu_sabai_entity_field_voting_rating rating) ON (rating.bundle_id = 7 AND rating.entity_id = a.post_id )
                /*LEFT JOIN (menu_sabai_entity_field_directory_category cat) ON (cat.bundle_id = 7 AND cat.entity_id = a.post_id )*/
                WHERE a.post_status="published"
                AND a.post_entity_bundle_name = "directory_listing" GROUP BY a.post_id'
            );
            foreach($result as $single){
                $single->hours = $this->toArray($wpdb->get_results("SELECT a.value as val FROM menu_sabai_entity_field_field_hours a WHERE a.bundle_id = 7 AND a.entity_id = $single->id"));
                $single->tf = $this->toArray($wpdb->get_results("SELECT a.value as val FROM menu_sabai_entity_field_field_tenant_feature a WHERE a.bundle_id = 7 AND a.entity_id = $single->id"));
                $single->wt = $this->toArray($wpdb->get_results("SELECT a.value as val FROM menu_sabai_entity_field_field_what a WHERE a.bundle_id = 7 AND a.entity_id = $single->id"));
                $single->wh = $this->toArray($wpdb->get_results("SELECT a.value as val FROM menu_sabai_entity_field_field_with_whom a WHERE a.bundle_id = 7 AND a.entity_id = $single->id"));
                $single->photos = $this->toArray($wpdb->get_results("SELECT a.entity_id as val FROM menu_sabai_entity_field_content_parent a WHERE a.bundle_id = 9 AND a.value = $single->id"));
                $single->category = $this->toArray($wpdb->get_results("SELECT a.value as val FROM menu_sabai_entity_field_directory_category a WHERE a.bundle_id = 7 AND a.entity_id = $single->id"));
                $single->comments = $wpdb->get_results("SELECT a.post_title as title, FROM_UNIXTIME(a.post_created) as dateCreated, b.value as description, u.display_name as username, um.meta_value as thumbnail FROM menu_sabai_content_post a
                  INNER JOIN (menu_sabai_entity_field_content_parent p) ON (p.entity_id = a.post_id AND p.value = $single->id)
                  LEFT JOIN (menu_sabai_entity_field_content_body b) ON (b.bundle_id = 8 AND b.entity_id = a.post_id)
                  LEFT JOIN (menu_users u) ON (a.post_user_id = u.id)
                  LEFT JOIN (menu_usermeta um) ON (a.post_user_id = um.user_id AND meta_key = 'facebookall_user_thumbnail')
                  WHERE a.post_status='published' AND a.post_entity_bundle_name = 'directory_listing_review' ORDER BY a.post_created DESC LIMIT 10");
            }

            /* wp_cache_set( 'tenant_list', $result );
        }*/
        return array(
            'count' => count($result),
            'data' => $result
        );
    }
    public function category(){

        global $wpdb;
        $result = wp_cache_get( 'category_list' );
        if ( false === $result ) {
            $result = $wpdb->get_results('SELECT a.term_id as id, a.term_parent as parent, a.term_title as title, COUNT(cat.entity_type) as child_count FROM menu_sabai_taxonomy_term a , menu_sabai_content_post p , menu_sabai_entity_field_directory_category cat
            WHERE p.post_status = "published" AND p.post_id = cat.entity_id AND cat.bundle_id = 7 AND cat.value = a.term_id GROUP BY a.term_id');
            wp_cache_set( 'category_list', $result );
        }
        return array(
            'count' => count($result),
            'data' => $result
        );

    }

    private function toArray($array)
    {
        $nArray = [];

        foreach ($array as $value)
        {
            array_push($nArray,$value->val);
        }

        return $nArray;
    }
  
}

?>
