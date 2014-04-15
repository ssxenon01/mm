<?php
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
class SabaiFramework_DB_Schema
{
    /**
     * @var MDB2_Schema
     */
    protected $_mdb2Schema;
    /**
     * @var array
     */
    protected $_createTableOptions;
    
    protected $_databaseDefinition;

    /**
     * Constructor
     *
     * @param MDB2_Schema $mdb2Schema
     * @param array $createTableOptions
     * @return SabaiFramework_DB_Schema
     */
    public function __construct(SabaiFramework_DB_MDB2Schema $mdb2Schema, array $createTableOptions = array())
    {
        $this->_mdb2Schema = $mdb2Schema;
        $this->_createTableOptions = $createTableOptions;
    }

    /**
     * Creates a SabaiFramework_DB_Schema instance
     *
     * @param SabaiFramework_DB $db
     * @param array $options
     * @param array $parserOptions
     * @param array $createTableOptions
     * @return mixed SabaiFramework_DB_Schema
     * @throws SabaiFramework_DB_SchemaException
     */
    public static function factory(SabaiFramework_DB $db, array $options = array(), array $parserOptions = array(), array $createTableOptions = array())
    {
        $default = array(
            'log_line_break' => '<br />',
            'idxname_format' => '%s',
            'debug' => true,
            'quote_identifier' => true,
            'force_defaults' => true,
            'portability' => false,
            'parser' => 'SabaiFramework_DB_MDB2SchemaParser',
            'use_transactions' => false, // ToDo: See why setting this option to true causes MDB2_SAVEPINT_2 not found error when schema has insert data
            'drop_missing_tables' => true
        );
        //$mdb2_schema =& MDB2_Schema::factory($db->getConnection()->getDSN(), array_merge($default, $options));
        $create_table_options = array_merge($db->getMDB2CreateTableOptions(), $createTableOptions);
        $schema_options = array(
            'create_table_options' => $create_table_options,
            'parser_options' => array_merge(array(
                'table_prefix'  => $db->getResourcePrefix(),
                'database_name' => $db->getConnection()->getResourceName()
            ), $parserOptions)
        );
        $mdb2_schema = SabaiFramework_DB_MDB2Schema::factory($db->getConnection()->getDSN(), $schema_options, array_merge($default, $options));
        if (PEAR::isError($mdb2_schema)) {
            throw new SabaiFramework_DB_SchemaException(self::_getPearErrorMessage($mdb2_schema));
        }

        return new self($mdb2_schema, $create_table_options);
    }

    public function create($schema)
    {
        $definition = $this->_mdb2Schema->parseDatabaseDefinition($schema);
        if (PEAR::isError($definition)) {
            throw new SabaiFramework_DB_SchemaException(self::_getPearErrorMessage($definition));
        }

        // Always overwrite tables on creation 
        $this->_createTableOptions['overwrite_table'] = true;        
        $result = $this->_mdb2Schema->createDatabase($definition, $this->_createTableOptions);
        if (PEAR::isError($result)) {
            throw new SabaiFramework_DB_SchemaException(self::_getPearErrorMessage($result));
        }
    }

    public function update($schema, $previousSchema)
    {
        $definition = $this->_mdb2Schema->parseDatabaseDefinition($schema);
        if (PEAR::isError($definition)) {
            throw new SabaiFramework_DB_SchemaException(self::_getPearErrorMessage($definition));
        }

        $result = $this->_mdb2Schema->updateDatabase($schema, $previousSchema);
        if (PEAR::isError($result)) {
            throw new SabaiFramework_DB_SchemaException(self::_getPearErrorMessage($result));
        }

        // Do execute insert/update/delete queries if any
        foreach ((array)@$definition['tables'] as $table_name => $table) {
            if (empty($table['initialization'])) continue;

            $result = $this->_mdb2Schema->initializeTable($table_name, $table);
            if (PEAR::isError($result)) {
                // Should we throw an exception here as well?
            }
        }
    }

    public function drop($previousSchema)
    {
        $changes = array();
        $definition = $this->_mdb2Schema->parseDatabaseDefinition($previousSchema);
        if (PEAR::isError($definition)) {
            throw new SabaiFramework_DB_SchemaException(self::_getPearErrorMessage($definition));
        }
        foreach (array_keys((array)@$definition['tables']) as $table_name) {
            $changes['tables']['remove'][$table_name] = true;
        }
        foreach (array_keys((array)@$definition['sequences']) as $sequence_name) {
            $changes['sequences']['remove'][$sequence_name] = true;
        }
        $result = $this->_mdb2Schema->alterDatabase($definition, $definition, $changes);
        if (PEAR::isError($result)) {
            throw new SabaiFramework_DB_SchemaException(self::_getPearErrorMessage($result));
        }
    }

    protected static function _getPearErrorMessage(PEAR_Error $error)
    {
        return sprintf('%s (%s)', $error->getMessage(), $error->getUserInfo());
    }
    
    public function getDefinitionFromDatabase($force = false)
    {
        if (!isset($this->_databaseDefinition) || $force) {
            $this->_databaseDefinition = $this->_mdb2Schema->getDefinitionFromDatabase();
        }
        return $this->_databaseDefinition;
    }
    
    public function listTableFields($table)
    {
        return $this->_mdb2Schema->db->manager->listTableFields($table);
    }
        
    public function listTableIndexes($table)
    {
        return $this->_mdb2Schema->db->manager->listTableIndexes($table);
    }
    
    public function listTableConstraints($table)
    {
        return $this->_mdb2Schema->db->manager->listTableConstraints($table);
    }
}