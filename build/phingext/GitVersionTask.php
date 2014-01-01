<?php
require_once 'phing/Task.php';
require_once 'phing/tasks/ext/svn/SvnBaseTask.php';

// Required for Zend Server 6 on Mac OS X
putenv("DYLD_LIBRARY_PATH=''");

/**
 * Git latest tree hash to Phing property
 * @version $Id$
 * @package akeebabuilder
 * @copyright Copyright (c)2009-2014 Nicholas K. Dionysopoulos
 * @license GNU GPL version 3 or, at your option, any later version
 * @author nicholas
 */
class GitVersionTask extends SvnBaseTask
{
    private $propertyName = "git.version";

    /**
     * Sets the name of the property to use
     */
    function setPropertyName($propertyName)
    {
        $this->propertyName = $propertyName;
    }

    /**
     * Returns the name of the property to use
     */
    function getPropertyName()
    {
        return $this->propertyName;
    }

    /**
     * Sets the path to the working copy
     */
    function setWorkingCopy($wc)
    {
        $this->workingCopy = $wc;
    }

    /**
     * The main entry point
     *
     * @throws BuildException
     */
    function main()
    {
		$this->setup('info');

		exec('git log --format=%h -n1 '.escapeshellarg(realpath($this->workingCopy)), $out);
		$this->project->setProperty($this->getPropertyName(), strtoupper(trim($out[0])));
    }
}