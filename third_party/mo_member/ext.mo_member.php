<?php if ( ! defined('BASEPATH')) exit('Direct script access not allowed');

/**
 * Mo&#039; Member extension.
 *
 * @author          Stephen Lewis (http://github.com/experience/)
 * @copyright       Experience Internet
 * @package         Mo_member
 */

class Mo_member_ext {

  private $EE;
  private $_ext_model;

  public $description;
  public $docs_url;
  public $name;
  public $settings;
  public $settings_exist;
  public $version;


  /* --------------------------------------------------------------
   * PUBLIC METHODS
   * ------------------------------------------------------------ */

  /**
   * Constructor.
   *
   * @access  public
   * @param   mixed     $settings     Extension settings.
   * @return  void
   */
  public function __construct($settings = '')
  {
    $this->EE =& get_instance();

    $this->EE->load->add_package_path(PATH_THIRD .'mo_member/');

    // Still need to specify the package...
    $this->EE->lang->loadfile('mo_member_ext', 'mo_member');

    $this->EE->load->model('mo_member_extension_model');
    $this->_ext_model = $this->EE->mo_member_extension_model;

    // Set the public properties.
    $this->description = $this->EE->lang->line(
      'mo_member_extension_description');

    $this->docs_url = 'http://experienceinternet.co.uk/';
    $this->name     = $this->EE->lang->line('mo_member_extension_name');
    $this->settings = $settings;
    $this->settings_exist = 'n';
    $this->version  = $this->_ext_model->get_package_version();
  }


  /**
   * Activates the extension.
   *
   * @access  public
   * @return  void
   */
  public function activate_extension()
  {
    $hooks = array('template_fetch_template');
    $this->_ext_model->install(get_class($this), $this->version, $hooks);
  }


  /**
   * Disables the extension.
   *
   * @access  public
   * @return  void
   */
  public function disable_extension()
  {
    $this->_ext_model->uninstall(get_class($this));
  }


  /**
   * Handles the template_fetch_template extension hook.
   *
   * @access  public
   * @param   array   $row    Template data.
   * @return  array
   */
  public function on_template_fetch_template($row)
  {
    if (($last_call = $this->EE->extensions->last_call) !== FALSE)
    {
      $row = $last_call;
    }

    // Retrieve and validate the member ID.
    $member_id = $this->EE->session->userdata('member_id');
    if ( ! valid_int($member_id, 1))
    {
      return $row;
    }

    // All Mo' Member globals have the prefix 'mo_member:'.
    $prefix = strtolower($this->_ext_model->get_package_name()) .':';

    // Retrieve the member data.
    try
    {
      $member_data = $this->_ext_model->get_member_data($member_id, $prefix);
    }
    catch (Exception $e)
    {
      $this->_ext_model->log_message($e->getMessage(), 3);
      return $row;
    }

    // Add the member data to the global variables.
    $this->EE->config->_global_vars = array_merge($member_data,
      $this->EE->config->_global_vars);

    // Play nicely with others.
    return $row;
  }


  /**
   * Updates the extension.
   *
   * @access  public
   * @param   string    $installed_version    The installed version.
   * @return  mixed
   */
  public function update_extension($installed_version = '')
  {
    return $this->_ext_model->update(get_class($this), $installed_version,
      $this->_ext_model->get_package_version());
  }


}


/* End of file      : ext.mo_member.php */
/* File location    : third_party/mo_member/ext.mo_member.php */
