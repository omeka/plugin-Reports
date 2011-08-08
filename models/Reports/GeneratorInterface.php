<?php
/**
 * @package Reports
 * @subpackage Generators
 * @copyright Copyright (c) 2011 Center for History and New Media
 * @license http://www.gnu.org/licenses/gpl-3.0.txt
 */
 
/**
 * Abstract parent class for report generators.
 *
 * @package Reports
 * @subpackage Generators
 */
interface Reports_GeneratorInterface
{
    /**
     * Returns the readable name of the subclass' output format.
     *
     * @return string Human-readable name for output format
     */
    static function getReadableName();
}
