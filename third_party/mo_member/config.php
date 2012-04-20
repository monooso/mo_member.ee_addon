<?php

/**
 * Mo&#039; Member NSM Add-on Updater information.
 *
 * @author          Stephen Lewis (http://github.com/experience/)
 * @copyright       Experience Internet
 * @package         Mo_member
 * @version         0.1.0
 */

if ( ! defined('MO_MEMBER_NAME'))
{
  define('MO_MEMBER_NAME', 'Mo_member');
  define('MO_MEMBER_TITLE', 'Mo&#039; Member');
  define('MO_MEMBER_VERSION', '0.1.0');
}

$config['name']     = MO_MEMBER_NAME;
$config['version']  = MO_MEMBER_VERSION;
$config['nsm_addon_updater']['versions_xml']
  = 'http://experienceinternet.co.uk/software/feeds/mo-member';

/* End of file      : config.php */
/* File location    : third_party/mo_member/config.php */