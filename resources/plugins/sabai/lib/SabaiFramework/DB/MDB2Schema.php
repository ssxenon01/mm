<?php
require_once 'MDB2/Schema.php';

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
class SabaiFramework_DB_MDB2Schema extends MDB2_Schema
{
    /**
     * @var array
     */
    private $_schemaOptions;

    /**
     * Constructor
     *
     * @param array $schemaOptions
     * @return SabaiFramework_DB_MDB2Schema
     */
    private function __construct(array $schemaOptions = array())
    {
        $default = array('create_table_options' => array(), 'parser_options' => array());
        $this->_schemaOptions = array_merge($default, $schemaOptions);
    }

    public /*static*/ function factory($db, array $schemaOptions = array(), array $dbOptions = array())
    {
        $obj = new SabaiFramework_DB_MDB2Schema($schemaOptions);
        $result = $obj->connect($db, $dbOptions);

        return PEAR::isError($result) ? $result : $obj;
    }

    /**
     * Overrides the parent method to fix the MDB2_Schema bug where create table options
     * are not always passed to this method.
     *
     * http://pear.php.net/bugs/bug.php?id=13779
     */
    public function createTable($table_name, $table, $overwrite = false, $options = array())
    {
        $options += $this->_schemaOptions['create_table_options'];
        if (isset($options['overwrite_table'])) {
            //$overwrite = (bool)$options['overwrite_table'];
        }
        if (isset($table['comment'])) {
            $options += array('comment' => $table['comment']);
        }

        return parent::createTable($table_name, $table, $overwrite, $options);
    }

    /**
     * Overrides the parent method so that additional parameters can be passed to
     * the custom parser.
     *
     * http://pear.php.net/bugs/bug.php?id=13411
     */
    public function parseDatabaseDefinitionFile($input_file, $variables = array(),
        $fail_on_invalid_names = true, $structure = false)
    {
        $dtd_file = $this->options['dtd_file'];
        if ($dtd_file) {
            require_once 'XML/DTD/XmlValidator.php';
            $dtd = new XML_DTD_XmlValidator;
            if (!$dtd->isValid($dtd_file, $input_file)) {
                return $this->raiseError(MDB2_SCHEMA_ERROR_PARSE, null, null, $dtd->getMessage());
            }
        }

        $class_name = $this->options['parser'];
        $result = MDB2::loadClass($class_name, $this->db->getOption('debug'));
        if (PEAR::isError($result)) {
            return $result;
        }

        //$parser =& new $class_name($variables, $fail_on_invalid_names, $structure, $this->options['valid_types'], $this->options['force_defaults']);
        $parser = new $class_name($variables, $fail_on_invalid_names, $structure, $this->options['valid_types'], $this->options['force_defaults'], $this->_schemaOptions['parser_options']);
        $result = $parser->setInputFile($input_file);
        if (PEAR::isError($result)) {
            return $result;
        }

        $result = $parser->parse();
        if (PEAR::isError($result)) {
            return $result;
        }
        if (PEAR::isError($parser->error)) {
            return $parser->error;
        }

        return $parser->database_definition;
    }
}