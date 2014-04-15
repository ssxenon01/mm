<?php
class Sabai_Helper_Path extends Sabai_Helper
{
    public function help(Sabai $application, $path)
    {
        if (strpos($path, '\\') === false) {
            return $path; // not a windows path
        }
        $path = str_replace('\\', '/', $path);
        if (0 !== $first_slash_pos = strpos($path, '/')) {
            $path = substr($path, $first_slash_pos);  // remove c: part
        }
        return $path;
    }
}