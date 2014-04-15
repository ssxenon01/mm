<?php
class Sabai_Helper_SliceArray extends Sabai_Helper
{
    public function help(Sabai $application, array $arr, $columnCount)
    {
        if ($columnCount < 1
            || (!$entity_count = count($arr))
        ) {
            return $arr;
        }

        $entity_count_per_slice = ceil($entity_count / $columnCount);
        $last_slice_index = $columnCount - 1;
        $ret = array();
        for ($i = 0; $i <= $last_slice_index; $i++) {
            foreach (array_slice($arr, $i * $entity_count_per_slice, $i === $last_slice_index ? null : $entity_count_per_slice) as $row => $value) {
                $ret[$row][] = $value;
            }
        }
        return $ret;
    }
}