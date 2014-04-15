<?php
class Sabai_Helper_DateDiff extends Sabai_Helper
{
    public function help(Sabai $application, $timestamp)
    {
        $diff = $timestamp - time();
        if ($ago = (0 >= $diff)) $diff = abs($diff);

        if ($diff >= 86400) {
            if ($diff >= 31536000) {
                return $ago
                    ? $application->Date($timestamp)
                    : sprintf(_n('%d year', '%d years', $years = round($diff / 31536000), 'sabai'), $years);
            }

            if ($diff >= 604800) {
                return $this->_weeksAgo($application, $ago, round($diff / 604800));
            }

            return $this->_daysAgo($application, $ago, round($diff / 86400));
        }

        if ($diff >= 3600) {
            return $this->_hoursAgo($application, $ago, round($diff / 3600));
        }

        if ($diff >= 60) {
            return $this->_minutesAgo($application, $ago, round($diff / 60));
        }

        return $ago
            ? sprintf(_n('%d second ago', '%d seconds ago', $diff, 'sabai'), $diff)
            : sprintf(_n('%d second', '%d seconds', $diff, 'sabai'), $diff);
    }

    private function _weeksAgo($application, $ago, $weeks)
    {
        return $ago
            ? sprintf(_n('Last week', '%d weeks ago', $weeks, 'sabai'), $weeks)
            : sprintf(_n('%d week', '%d weeks', $weeks, 'sabai'), $weeks);
    }

    private function _daysAgo($application, $ago, $days)
    {
        return $ago
            ? sprintf(_n('Yesterday', '%d days ago', $days, 'sabai'), $days)
            : sprintf(_n('%d day', '%d days', $days, 'sabai'), $days);
    }

    private function _hoursAgo($application, $ago, $hours)
    {
         return $ago
            ? sprintf(_n('%d hour ago', '%d hours ago', $hours, 'sabai'), $hours)
            : sprintf(_n('%d hour', '%d hours', $hours, 'sabai'), $hours);
    }

    private function _minutesAgo($application, $ago, $minutes)
    {
         return $ago
            ? sprintf(_n('%d minute ago', '%d minutes ago', $minutes, 'sabai'), $minutes)
            : sprintf(_n('%d minute', '%d minutes', $minutes, 'sabai'), $minutes);
    }
}