<?php
/**
 * QSF Portal
 * Copyright (c) 2006-2015 The QSF Portal Development Team
 * https://github.com/Arthmoor/QSF-Portal
 *
 * Based on:
 *
 * Quicksilver Forums
 * Copyright (c) 2005-2011 The Quicksilver Forums Development Team
 * http://code.google.com/p/quicksilverforums/
 *
 * This program is free software; you can redistribute it and/or
 * modify it under the terms of the GNU General Public License
 * as published by the Free Software Foundation; either version 2
 * of the License, or (at your option) any later version.
 *
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU General Public License for more details.
 *
 **/

if (!defined('QUICKSILVERFORUMS')) {
	header('HTTP/1.0 403 Forbidden');
	die;
}

/**
 * Base modlet class
 *
 * @author Geoffrey Dunn <quicken@swiftdsl.com.au>
 * @since 1.1.5
 **/
class modlet
{
    /**
     * Pointer to the running module object
     **/
    var $qsf;
    
    /**
     * Constructor.
     *
     * Set any variables specific to your
     * class in the constructor
     *
     * @param object reference to running module
     * @author Geoffrey Dunn <geoff@warmage.com>
     * @since 1.1.5
     **/
    function modlet(&$forumobject)
    {
        $this->qsf =& $forumobject;
    }

    /**
     * Main interface
     *
     * This is what's run to generate output for the page
     *
     * @param string optional string that is passed from the template
     * @author Geoffrey Dunn <geoff@warmage.com>
     * @since 1.1.5
     * @return string HTML to appear within the template
     **/
    function run($param)
    {
    }
}
?>