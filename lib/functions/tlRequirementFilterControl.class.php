<?php

/**
 * TestLink Open Source Project - http://testlink.sourceforge.net/
 * This script is distributed under the GNU General Public License 2 or later.
 *
 * @package    TestLink
 * @author     Andreas Simon
 * @copyright  2006-2010, TestLink community
 * @version    CVS: $Id: tlRequirementFilterControl.class.php,v 1.3 2010/06/28 16:19:37 asimon83 Exp $
 * @link       http://www.teamst.org/index.php
 * @filesource http://testlink.cvs.sourceforge.net/viewvc/testlink/testlink/lib/functions/tlRequirementFilterControl.class.php?view=markup
 *
 * This class extends tlFilterPanel for the specific use with requirement tree.
 * It holds logic to be used at GUI level to manage a common set of settings and filters for requirements.
 * 
 * @internal Revisions:
 * 
 * 20100624 - asimon - CVS merge (experimental branch to HEAD)
 * 20100503 - asimon - start of implementation of filter panel class hierarchy
 *                     to simplify/generalize filter panel handling
 *                     for test cases and requirements
 */

/**
 * This class extends tlFilterPanel for the specific use with requirement tree.
 * It holds logic to be used at GUI level to manage a common set of settings and filters for requirements.
 * 
 * @author Andreas Simon
 * 
 * @package TestLink
 **/
class tlRequirementFilterControl extends tlFilterControl {
	
	// TODO asimon, implement filtering for requirements with this class

}

// TODO asimon: The following lines have to be added to config.inc.php
// after this class has been implemented.

//$tlCfg->tree_filter_cfg->requirements->filter_req_doc_id = ENABLED; // TODO
//$tlCfg->tree_filter_cfg->requirements->filter_version = ENABLED; // TODO
//$tlCfg->tree_filter_cfg->requirements->filter_title = ENABLED; // TODO
//$tlCfg->tree_filter_cfg->requirements->filter_status = ENABLED; // TODO
//$tlCfg->tree_filter_cfg->requirements->filter_type = ENABLED; // TODO
//$tlCfg->tree_filter_cfg->requirements->filter_coverage = ENABLED; // TODO
//$tlCfg->tree_filter_cfg->requirements->filter_relations = ENABLED; // TODO
//$tlCfg->tree_filter_cfg->requirements->filter_tc_id = ENABLED; // TODO
//$tlCfg->tree_filter_cfg->requirements->advanced_filter_mode_choice = ENABLED;

?>