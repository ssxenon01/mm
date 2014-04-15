<?php
class Sabai_Addon_Directory_Helper_RenderPhotoMeta extends Sabai_Helper
{
    public function help(Sabai $application, Sabai_Addon_Content_Entity $photo, $linkToListing = false)
    {
        if (!empty($linkToListing)) {
            $listing = $application->Content_ParentPost($photo);
        }
        if ($photo->content_reference) {
            return sprintf(
                __('%s by %s in %s', 'sabai-directory'),
                $application->Entity_Permalink($photo, array('title' => $application->DateDiff($photo->getTimestamp()))),
                $application->UserIdentityLink($application->Content_Author($photo)),
                $application->Entity_Permalink($photo->content_reference[0], array('title' => !empty($listing) ? $listing->getTitle() : null))
            );
        }
        if (empty($photo->directory_photo[0]['official'])) {
            if (!empty($listing)) {
                return sprintf(
                    __('%s by %s in %s', 'sabai-directory'),
                    $application->Entity_Permalink($photo, array('title' => $application->DateDiff($photo->getTimestamp()))),
                    $application->UserIdentityLink($application->Content_Author($photo)),
                    $application->Entity_Link($listing, array(), '/photos', array('photo_id' => $photo->getId()))
                );
            }
            return sprintf(
                __('%s by %s', 'sabai-directory'),
                $application->Entity_Permalink($photo, array('title' => $application->DateDiff($photo->getTimestamp()))),
                $application->UserIdentityLink($application->Content_Author($photo))
            );
        }
        if (!empty($listing)) {
            return sprintf(
                __('%s in %s', 'sabai-directory'),
                $application->Entity_Permalink($photo, array('title' => $application->DateDiff($photo->getTimestamp()))),
                $application->Entity_Link($listing, array(), '/photos', array('photo_id' => $photo->getId()))
            );
        }
        return $application->Entity_Permalink($photo, array('title' => $application->DateDiff($photo->getTimestamp())));
    }
}