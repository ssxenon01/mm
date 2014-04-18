<?php
class Sabai_Addon_Directory_Controller_Admin_ViewListingClaim extends Sabai_Addon_Form_Controller
{
    protected function _doGetFormSettings(Sabai_Context $context, array &$formStorage)
    {
        $context->claim->with('Entity'); // load listing associated with the claim
        $this->Entity_LoadFields($context->claim->Entity);
        if ($context->claim->status === 'pending') {
            $this->_submitButtons = array(
                'reject' => array(
                    '#value' => __('Reject', 'sabai-directory'),
                    '#weight' => 1,
                    '#submit' => array(array(array($this, 'rejectClaim'), array($context))),
                    '#btn_type' => 'danger',
                ),
                'approve' => array(
                    '#value' => __('Approve', 'sabai-directory'),
                    '#weight' => 2,
                    '#submit' => array(array(array($this, 'approveClaim'), array($context))),
                    '#btn_type' => 'success',
                ),
            );
            if (!$context->claim->Entity->isPublished()) {
                $warning = __('This claim may not be approved or rejected because the listing for which the claim was submitted has not yet been published.', 'sabai-directory');
                $this->_submitButtons['reject']['#attributes']['class'] = 'sabai-btn-danger sabai-disabled'; 
                $this->_submitButtons['approve']['#attributes']['class'] = 'sabai-btn-success sabai-disabled'; 
                $this->_submitButtons['reject']['#attributes']['disabled'] = 'disabled'; 
                $this->_submitButtons['approve']['#attributes']['disabled'] = 'disabled'; 
            } else {
                // Allow approving this claim only if the listing does not already have a valid claim
                if ($this->Directory_ListingOwner($context->claim->Entity)) {
                    $warning = __('This claim may not be approved because the listing has already been claimed by another user.', 'sabai-directory');
                    $this->_submitButtons['approve']['#attributes']['class'] = 'sabai-btn-success sabai-disabled'; 
                    $this->_submitButtons['approve']['#attributes']['disabled'] = 'disabled'; 
                }
            }
        } else {
            $this->_submitButtons = array(
                'delete' => array(
                    '#value' => __('Delete', 'sabai-directory'),
                    '#weight' => 1,
                    '#submit' => array(array(array($this, 'deleteClaim'), array($context))),
                    '#btn_type' => 'danger',
                ),
            );
        }
        return array(
            '#header' => isset($warning) ? array('<div class="sabai-warning">' . $warning . '</div>') : array(),
            'info' => array(
                '#type' => 'markup',
                '#value' => '<p>' . sprintf(
                    __('%s submitted %s for listing %s', 'sabai-directory'),
                    $this->UserIdentityLinkWithThumbnailSmall($context->claim->User),
                    $this->DateTime($context->claim->created),
                    $context->claim->Entity ? ($context->claim->Entity->isPublished() ? $this->LinkTo($context->claim->Entity->getTitle(), $this->Entity_Bundle($context->claim->Entity)->getPath() . '/' . $context->claim->Entity->getId()) : Sabai::h($context->claim->Entity->getTitle())) : __('Unknown', 'sabai-directory')
                ) . '</p>',
            ),
            'name' => array(
                '#title' => __('Contact name', 'sabai-directory'),
                '#type' => 'item',
                '#value' => Sabai::h($context->claim->name),
            ),
            'email' => array(
                '#title' => __('E-mail', 'sabai-directory'),
                '#type' => 'item',
                '#value' => Sabai::h($context->claim->email),
            ),
            'comment' => array(
                '#title' => __('Comment', 'sabai-directory'),
                '#type' => 'item',
                '#value' => $context->claim->comment_html,
            ),
            'admin_note' => array(
                '#type' => $context->claim->status === 'pending' ? 'textarea' : 'markup',
                '#title' => __('Admin note', 'sabai-directory'),
                '#description' => $context->claim->status === 'pending'
                    ? __('This note may be used for administration purpose or embedded in notifcation mail using the {claim_admin_note} tag.', 'sabai-directory')
                    : null,
                '#rows' => 5,
            ),
        );
    }
    
    public function deleteClaim(Sabai_Addon_Form_Form $form, Sabai_Context $context)
    {
        $context->claim->markRemoved()->commit();
        $context->setSuccess($context->bundle->getPath() . '/claims');
        // Notify
        $this->doEvent('DirectoryListingClaimDeleted', array($context->claim));
    }
    
    public function approveClaim(Sabai_Addon_Form_Form $form, Sabai_Context $context)
    {
        if (!$context->claim->Entity->isPublished()) return;

        // Allow approving this claim only if the listing does not already have a valid claim
        if ($this->Directory_ListingOwner($context->claim->Entity)) return;
        
        $this->Directory_ClaimListing($context->claim->Entity, $context->claim->User, $this->getAddon()->getConfig('claims', 'duration'));
        $this->_updateClaim($context->claim, 'approved', $form->values['admin_note']);
        $context->setSuccess($context->bundle->getPath() . '/claims');
    }
    
    public function rejectClaim(Sabai_Addon_Form_Form $form, Sabai_Context $context)
    {
        if (!$context->claim->Entity->isPublished()) return;
        
        $this->_updateClaim($context->claim, 'rejected', $form->values['admin_note']);
        $context->setSuccess($context->bundle->getPath() . '/claims');
    }
        
    protected function _updateClaim(Sabai_Addon_Directory_Model_Claim $claim, $status, $adminNote)
    {
        $claim->set('status', $status)->set('admin_note', $adminNote)->commit()->reload();
        $this->doEvent('DirectoryListingClaimStatusChange', array($claim));
    }
}