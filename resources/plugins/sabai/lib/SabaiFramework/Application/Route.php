<?php
/**
 * Short description for class
 *
 * @package    SabaiFramework
 * @subpackage SabaiFramework_Application
 * @copyright  Copyright (c) 2006-2010 Kazumi Ono
 * @author     Kazumi Ono <onokazu@gmail.com>
 * @license    http://opensource.org/licenses/lgpl-license.php GNU LGPL
 * @version    CVS: $Id:$
 */
interface SabaiFramework_Application_Route
{
    public function __toString();
    public function isForward();
    public function getParams();
    public function getController();
    public function getControllerArgs();
    public function getControllerFile();
}