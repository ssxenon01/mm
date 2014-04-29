<?php
class Sabai_Helper_Cron extends Sabai_Helper
{
    public function help(Sabai $application, $lastRunTimestamp = null)
    {
        // Get cached last run timestamp if not speficied
        if (!isset($lastRunTimestamp)) {
            $lastRunTimestamp = $application->getPlatform()->getCache('sabai_cron_lastrun');
        }
        $logs = new ArrayObject(array(
            'Cron last run - ' . $application->DateTime($lastRunTimestamp),
            'Cron started - ' . $application->DateTime(time())
        ));
        $application->doEvent('SabaiRunCron', array(intval($lastRunTimestamp), &$logs));
        
        $application->getPlatform()->setCache(time(), 'sabai_cron_lastrun', 0);
        
        $logs[] = 'Cron complete - ' . $application->DateTime(time());

        return $logs;
    }
}