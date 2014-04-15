<?php
abstract class Sabai_Addon_System_Helper_SendEmail extends Sabai_Helper
{    
    public function help(Sabai $application, $addonName, $name, array $tags = array(), $user = null, array $attachments = null)
    {
        if (!$settings = $application->getPlatform()->getOption($addonName . '_emails', false)) {
            $settings = $application->Filter('SystemEmailSettings', $this->_getEmailSettings($application, $addonName), array($application->getAddon($addonName)->getType()));
        }
        if (empty($settings[$name]['enable'])) {
            return;
        }
        
        $recipients = array();
        if ($settings[$name]['type'] === 'roles') {
            if (empty($settings[$name]['roles'])) {
                return; // no roles defined
            }
            foreach ($application->getPlatform()->getUsersByUserRole($settings[$name]['roles']) as $identity) {
                // if $user is set, it is the author of an updated content and make sure notification is not sent to the author
                if (isset($user) && $user->id === $identity->id) { 
                    continue;
                }
                if (!$identity->email) {
                    continue;
                }
                $recipients[$identity->email] = $identity->name;
            }
        } elseif ($settings[$name]['type'] === 'admin') {
            foreach ($application->SuperUsers() as $identity) {
                if (!$identity->email) {
                    continue;
                }
                $recipients[$identity->email] = $identity->name;
            }
        } else {
            // $user is the recipient
            if (!isset($user)) {
                return;
            }
            if (is_object($user)) {
                if (!$user->email) {
                    return;
                }
                if (!$user->id && empty($settings[$name]['send_to_guest'])) {
                    return;
                }
                $recipients[$user->email] = $user->name;
            } elseif (is_array($user)) {
                foreach ($user as $identity) {
                    if (is_object($identity)) {
                        if (!$identity->email) {
                            continue;
                        }
                        if (!$identity->id && empty($settings[$name]['send_to_guest'])) {
                            continue;
                        }
                        $recipients[$identity->email] = $identity->name;
                    } elseif (is_array($identity)) {
                        $recipients[$identity['email']] = $identity['name'];
                    }
                }
            } else {
                return;
            }
            // CC to users of selected roles?
            if (!empty($settings[$name]['cc_roles']) && !empty($recipients) && !empty($settings[$name]['roles'])) {
                $cc_recipients = array();
                foreach ($application->getPlatform()->getUsersByUserRole($settings[$name]['roles']) as $identity) {
                    if ($identity->id === $user->id || !$identity->email) {
                        continue;
                    }
                    $cc_recipients[$identity->email] = $identity->name;
                }
            }
        }
        if (empty($recipients)) {
            return;
        }
        $tags += array(
            '{site_name}' => $application->getPlatform()->getSiteName(),
            '{site_email}' => $application->getPlatform()->getSiteEmail(),
            '{site_url}' => $application->getPlatform()->getSiteUrl(),
        );
        $subject = strtr($settings[$name]['email']['subject'], $tags);
        $body = strtr($settings[$name]['email']['body'], $tags);
        $search = array('{recipient_name}');
        foreach ($recipients as $recipient_email => $recipient_name) {
            $replace = array($recipient_name);
            $application->getPlatform()->mail($recipient_email, str_replace($search, $replace, $subject), str_replace($search, $replace, $body), $attachments);
        }
        if (!empty($cc_recipients)) {
            $body = array(
                __('---------- Forwarded message ----------', 'sabai'),
                sprintf(__('Sent From: %s', 'sabai'), $application->getPlatform()->getSiteEmail()),
                sprintf(1 === ($count = count($recipients)) ? __('Sent to: %s <%s>', 'sabai') : __('Sent to: %s <%s> and %d other recipient(s)', 'sabai'), $recipient->name, $recipient->email, $count),
                sprintf(__('Original Subject: %s', 'sabai'), $subject),
                '',
                $body
            );
            $subject = sprintf(__('FW: %s', 'sabai'), $subject);
            $body = implode("\n", $body);
            foreach ($cc_recipients as $cc_recipient_email => $cc_recipient_name) {
                $application->getPlatform()->mail($cc_recipient_email, str_replace($search, $replace, $subject), str_replace($search, $replace, $body), $attachments);
            }
        }
    }
    
    abstract protected function _getEmailSettings(Sabai $application, $addonName);
}