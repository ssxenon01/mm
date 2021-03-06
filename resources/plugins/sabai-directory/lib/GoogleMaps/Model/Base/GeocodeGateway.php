<?php
/* This file has been auto-generated. Do not edit this file directly. */

abstract class Sabai_Addon_GoogleMaps_Model_Base_GeocodeGateway extends SabaiFramework_Model_Gateway
{
    public function getName()
    {
        return 'googlemaps_geocode';
    }

    public function getFields()
    {
        return array('geocode_query' => SabaiFramework_Model::KEY_TYPE_VARCHAR, 'geocode_hash' => SabaiFramework_Model::KEY_TYPE_VARCHAR, 'geocode_lat' => SabaiFramework_Model::KEY_TYPE_DECIMAL, 'geocode_lng' => SabaiFramework_Model::KEY_TYPE_DECIMAL, 'geocode_address' => SabaiFramework_Model::KEY_TYPE_VARCHAR, 'geocode_hits' => SabaiFramework_Model::KEY_TYPE_INT, 'geocode_viewport' => SabaiFramework_Model::KEY_TYPE_VARCHAR, 'geocode_id' => SabaiFramework_Model::KEY_TYPE_INT, 'geocode_created' => SabaiFramework_Model::KEY_TYPE_INT, 'geocode_updated' => SabaiFramework_Model::KEY_TYPE_INT);
    }

    protected function _getIdFieldName()
    {
        return 'geocode_id';
    }

    protected function _getSelectByIdQuery($id, $fields)
    {
        return sprintf(
            'SELECT %s FROM %sgooglemaps_geocode WHERE geocode_id = %d',
            empty($fields) ? '*' : implode(', ', $fields),
            $this->_db->getResourcePrefix(),
            $id
        );
    }

    protected function _getSelectByIdsQuery($ids, $fields)
    {
        return sprintf(
            'SELECT %s FROM %sgooglemaps_geocode WHERE geocode_id IN (%s)',
            empty($fields) ? '*' : implode(', ', $fields),
            $this->_db->getResourcePrefix(),
            implode(', ', array_map('intval', $ids))
        );
    }

    protected function _getSelectByCriteriaQuery($criteriaStr, $fields)
    {
        return sprintf(
            'SELECT %1$s FROM %2$sgooglemaps_geocode googlemaps_geocode WHERE %3$s',
            empty($fields) ? '*' : implode(', ', $fields),
            $this->_db->getResourcePrefix(),
            $criteriaStr
        );
    }

    protected function _getInsertQuery(&$values)
    {
        $values['geocode_created'] = time();
        $values['geocode_updated'] = 0;
        return sprintf('INSERT INTO %sgooglemaps_geocode(geocode_query, geocode_hash, geocode_lat, geocode_lng, geocode_address, geocode_hits, geocode_viewport, geocode_created, geocode_updated) VALUES(%s, %s, %F, %F, %s, %d, %s, %d, %d)', $this->_db->getResourcePrefix(), $this->_db->escapeString($values['geocode_query']), $this->_db->escapeString($values['geocode_hash']), $values['geocode_lat'], $values['geocode_lng'], $this->_db->escapeString($values['geocode_address']), $values['geocode_hits'], $this->_db->escapeString($values['geocode_viewport']), $values['geocode_created'], $values['geocode_updated']);
    }

    protected function _getUpdateQuery($id, $values)
    {
        $last_update = $values['geocode_updated'];
        $values['geocode_updated'] = time();
        return sprintf('UPDATE %sgooglemaps_geocode SET geocode_query = %s, geocode_hash = %s, geocode_lat = %F, geocode_lng = %F, geocode_address = %s, geocode_hits = %d, geocode_viewport = %s, geocode_updated = %d WHERE geocode_id = %d AND geocode_updated = %d', $this->_db->getResourcePrefix(), $this->_db->escapeString($values['geocode_query']), $this->_db->escapeString($values['geocode_hash']), $values['geocode_lat'], $values['geocode_lng'], $this->_db->escapeString($values['geocode_address']), $values['geocode_hits'], $this->_db->escapeString($values['geocode_viewport']), $values['geocode_updated'], $id, $last_update);
    }

    protected function _getDeleteQuery($id)
    {
        return sprintf('DELETE FROM %1$sgooglemaps_geocode WHERE geocode_id = %2$d', $this->_db->getResourcePrefix(), $id);
    }

    protected function _getUpdateByCriteriaQuery($criteriaStr, $sets)
    {
        $sets['geocode_updated'] = 'geocode_updated=' . time();
        return sprintf('UPDATE %sgooglemaps_geocode googlemaps_geocode SET %s WHERE %s', $this->_db->getResourcePrefix(), implode(', ', $sets), $criteriaStr);
    }

    protected function _getDeleteByCriteriaQuery($criteriaStr)
    {
        return sprintf('DELETE googlemaps_geocode FROM %1$sgooglemaps_geocode googlemaps_geocode WHERE %2$s', $this->_db->getResourcePrefix(), $criteriaStr);
    }

    protected function _getCountByCriteriaQuery($criteriaStr)
    {
        return sprintf('SELECT COUNT(*) FROM %1$sgooglemaps_geocode googlemaps_geocode WHERE %2$s', $this->_db->getResourcePrefix(), $criteriaStr);
    }
}