<?php
class Sabai_Addon_Directory_Helper_ListingsQuery extends Sabai_Helper
{
    public function help(Sabai $application, Sabai_Addon_Entity_Query $query, array $latlng, array $keywords, $category, $sort, $distance = 0, $isMile = false, $featuredOnly = false)
    {
        if (!empty($latlng)) {
            $lat = $latlng[0];
            $lng = $latlng[1];
            if (!is_array($distance)) {
                if ($isMile) {
                    $distance = $distance * 1.609344;
                }
                $lat1 = round($lat - ($distance / 69), 6);
                $lat2 = round($lat + ($distance / 69), 6);
                $lng1 = round($lng - $distance / abs(cos(deg2rad($lat)) * 69), 6);
                $lng2 = round($lng + $distance / abs(cos(deg2rad($lat)) * 69), 6);
            } else {
                $lat1 = $distance[0][0];
                $lat2 = $distance[1][0];
                $lng1 = $distance[0][1];
                $lng2 = $distance[1][1];
            }
            $query->fieldIsOrGreaterThan('directory_location', $lat1, 'lat')
                ->fieldIsOrSmallerThan('directory_location', $lat2, 'lat')
                ->fieldIsOrGreaterThan('directory_location', $lng1, 'lng')
                ->fieldIsOrSmallerThan('directory_location', $lng2, 'lng');
        }
        if (!empty($keywords[0])) {
            foreach ($keywords[0] as $keyword) {
                $query->startCriteriaGroup('OR')
                    ->fieldContains('content_body', $keyword)
                    ->propertyContains('post_title', $keyword)
                    ->finishCriteriaGroup();
            }
        }
        if (!empty($category)) {
            $category_ids = array($category);
            foreach ($application->Taxonomy_Descendants($category) as $_category) {
                $category_ids[] = $_category->id;
            }
            $query->fieldIsIn('directory_category', $category_ids);
        }
        if ($featuredOnly) {
            $query->fieldIs('content_featured', 1);
        }
        switch ($sort) {
            case 'rating':
                return $query->sortByField('voting_rating', 'DESC', 'average')
                    ->sortByProperty('post_published', 'DESC');
            case 'reviews':
                return $query->fieldIs('content_children_count', 'directory_listing_review', 'child_bundle_name')
                    ->sortByField('content_children_count', 'DESC', 'value');
            case 'distance':
                if (!empty($latlng)) {
                    return $query->sortByExtraField('distance', 'ASC')->addExtraField(
                        'distance',
                        sprintf(
                            '(3959 * acos(cos(radians(%1$.6f)) * cos(radians(directory_location.lat)) * cos(radians(directory_location.lng) - radians(%2$.6f)) + sin(radians(%1$.6f)) * sin(radians(directory_location.lat))))',
                            $lat,
                            $lng
                        )
                    );
                }
            case 'title':
                return $query->sortByProperty('post_title', 'ASC');
            case 'random':
                return $query->sortByRandom();
            default:
                return $query->sortByProperty('post_published', 'DESC');
        }
    }
}
