<?php
require_once 'MDB2/Schema/Parser.php';

/**
 * Short description for class
 *
 * @package    SabaiFramework
 * @subpackage SabaiFramework_DB
 * @copyright  Copyright (c) 2006-2010 Kazumi Ono
 * @author     Kazumi Ono <onokazu@gmail.com>
 * @license    http://opensource.org/licenses/lgpl-license.php GNU LGPL
 * @version    CVS: $Id:$
 */
class SabaiFramework_DB_MDB2SchemaParser extends MDB2_Schema_Parser
{
    protected $_options;

    public function __construct($variables, $fail_on_invalid_names = true, $structure = false, $valid_types = array(), $force_defaults = true, $options = array())
    {
        parent::__construct($variables, $fail_on_invalid_names, $structure, $valid_types, $force_defaults);
        $this->srcenc = 'UTF-8'; // Override ISO-8859-1 encoding set by MDB2_Schema_Parser
        $this->_options = array_merge(array('table_prefix' => '', 'database_name' => ''), $options);
    }

    public function cdataHandler($xp, $data)
    {
        switch ($this->element) {
        case 'database-name':
            $data = $this->_options['database_name'];
            break;
        case 'database-table-initialization-insert-select-table':
        case 'database-table-name':
        case 'database-table-was':
        case 'database-table-declaration-foreign-references-table':
        case 'database-sequence-on-table':
            $data = $this->_options['table_prefix'] . $data;
            break;
        default:
            break;
        }
        parent::cdataHandler($xp, $data);
    }
}