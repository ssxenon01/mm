<?php
class Sabai_Helper_Filter extends Sabai_Helper
{
    public function help(Sabai $application, $filterName, $filterValue, array $filterArgs = array())
    {
        return $application->doFilter($filterName, $filterValue, $filterArgs);
    }
}