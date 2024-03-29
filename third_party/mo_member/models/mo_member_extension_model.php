<?php if ( ! defined('BASEPATH')) exit('Direct script access not allowed');

/**
 * Mo&#039; Member extension model.
 *
 * @author          Stephen Lewis (http://github.com/experience/)
 * @copyright       Experience Internet
 * @package         Mo_member
 */

require_once dirname(__FILE__) .'/mo_member_model.php';

class Mo_member_extension_model extends Mo_member_model {

  /* --------------------------------------------------------------
   * PUBLIC METHODS
   * ------------------------------------------------------------ */

  /**
   * Constructor.
   *
   * @access  public
   * @param   string  $package_name     Package name. Used for testing.
   * @param   string  $package_title    Package title. Used for testing.
   * @param   string  $package_version  Package version. Used for testing.
   * @param   string  $namespace        Session namespace. Used for testing.
   * @return  void
   */
  public function __construct($package_name = '', $package_title = '',
    $package_version = '', $namespace = ''
  )
  {
    parent::__construct($package_name, $package_title, $package_version,
      $namespace);
  }


  /**
   * Installs the extension.
   *
   * @access  public
   * @param   string    $class      The extension class.
   * @param   string    $version    The extension version.
   * @param   array     $hooks      The extension hooks.
   * @return  void
   */
  public function install($class, $version, Array $hooks)
  {
    // Guard against nonsense.
    if ( ! is_string($class) OR $class == ''
      OR ! is_string($version) OR $version == ''
      OR ! $hooks
    )
    {
      return;
    }

    // EE is rather picky class name capitalisation.
    $class = ucfirst(strtolower($class));

    $default_hook_data = array(
      'class'     => $class,
      'enabled'   => 'y',
      'hook'      => '',
      'method'    => '',
      'priority'  => '5',
      'settings'  => '',
      'version'   => $version
    );

    foreach ($hooks AS $hook)
    {
      if ( ! is_string($hook) OR $hook == '')
      {
        continue;
      }

      $this->EE->db->insert('extensions', array_merge(
        $default_hook_data,
        array('hook' => $hook, 'method' => 'on_' .$hook)
      ));
    }
  }


  /**
   * Uninstalls the extension.
   *
   * @access    public
   * @param     string    $class    The extension class.
   * @return    void
   */
  public function uninstall($class)
  {
    if ( ! is_string($class) OR $class == '')
    {
      return;
    }

    $this->EE->db->delete('extensions', array('class' => $class));
  }


  /**
   * Updates the extension.
   *
   * @access  public
   * @param   string    $class        The extension class.
   * @param   string    $installed    The installed version.
   * @param   string    $package      The package version.
   * @return  bool
   */
  public function update($class, $installed, $package)
  {
    // Can't do anything without valid data.
    if ( ! is_string($class) OR $class == ''
      OR ! is_string($installed) OR $installed == ''
      OR ! is_string($package) OR $package == ''
    )
    {
      return FALSE;
    }

    // Up to date?
    if (version_compare($installed, $package, '>='))
    {
      return FALSE;
    }

    // Update the version number in the database.
    $this->EE->db->update('extensions',
      array('version' => $package), array('class' => $class));

    return TRUE;
  }


}


/* End of file      : mo_member_extension_model.php */
/* File location    : third_party/mo_member/models/mo_member_extension_model.php */