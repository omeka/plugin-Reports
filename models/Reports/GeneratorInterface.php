<?php
/**
 * @copyright Copyright 2007-2012 Roy Rosenzweig Center for History and New Media
 * @license http://www.gnu.org/licenses/gpl-3.0.txt GNU GPLv3
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
