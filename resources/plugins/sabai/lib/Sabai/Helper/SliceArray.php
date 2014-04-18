<?php
class Sabai_Helper_SliceArray extends Sabai_Helper
{
    public function help(Sabai $application, array $arr, $columnCount, $vertical = true)
    {
        if ($columnCount < 1
            || (!$entity_count = count($arr))
        ) {
            return array();
        }

        $ret = array();
        if ($vertical) {
            $entity_count_per_slice = ceil($entity_count / $columnCount);
            $last_slice_index = $columnCount - 1;
            for ($i = 0; $i <= $last_slice_index; $i++) {
                $slice = array_slice($arr, $i * $entity_count_per_slice, $i === $last_slice_index ? null : $entity_count_per_slice, true);
                $slice_entity_count = count($slice);
                $slice_keys = array_keys($slice);
                $slice_values = array_values($slice);
                for ($j = 0; $j < $slice_entity_count; $j++) {
                    $ret[$j][$slice_keys[$j]] = $slice_values[$j];
                }
            }
        } else {
            $entity_count_per_slice = $columnCount;
            $last_slice_index = ceil($entity_count / $columnCount) - 1;
            for ($i = 0; $i <= $last_slice_index; $i++) {
                $ret[$i] = array_slice($arr, $i * $entity_count_per_slice, $i === $last_slice_index ? null : $entity_count_per_slice, true);
            }
        }
        return $ret;
    }
}
