<?php if ( ! defined('BASEPATH')) exit('Direct script access not allowed');

/**
 * Mo&#039; Member 'Package' model.
 *
 * @author          Stephen Lewis (http://github.com/experience/)
 * @copyright       Experience Internet
 * @package         Mo_member
 */

require_once dirname(__FILE__) .'/../config.php';
require_once dirname(__FILE__) .'/../helpers/EI_number_helper.php';

class Mo_member_model extends CI_Model {

  protected $EE;
  protected $_namespace;
  protected $_package_name;
  protected $_package_title;
  protected $_package_version;
  protected $_site_id;


  /* --------------------------------------------------------------
   * PUBLIC METHODS
   * ------------------------------------------------------------ */

  /**
   * Constructor.
   *
   * @access  public
   * @param   string    $package_name       Package name. Used for testing.
   * @param   string    $package_title      Package title. Used for testing.
   * @param   string    $package_version    Package version. Used for testing.
   * @param   string    $namespace          Session namespace. Used for testing.
   * @return  void
   */
  public function __construct($package_name = '', $package_title = '',
    $package_version = '', $namespace = ''
  )
  {
    parent::__construct();

    $this->EE =& get_instance();

    // Load the OmniLogger class.
    if (file_exists(PATH_THIRD .'omnilog/classes/omnilogger.php'))
    {
      include_once PATH_THIRD .'omnilog/classes/omnilogger.php';
    }

    $this->_namespace = $namespace ? strtolower($namespace) : 'experience';

    $this->_package_name = $package_name
      ? strtolower($package_name) : strtolower(MO_MEMBER_NAME);

    $this->_package_title = $package_title
      ? $package_title : MO_MEMBER_TITLE;

    $this->_package_version = $package_version
      ? $package_version : MO_MEMBER_VERSION;

    // Initialise the add-on cache.
    if ( ! array_key_exists($this->_namespace, $this->EE->session->cache))
    {
      $this->EE->session->cache[$this->_namespace] = array();
    }

    if ( ! array_key_exists($this->_package_name,
      $this->EE->session->cache[$this->_namespace]))
    {
      $this->EE->session->cache[$this->_namespace]
        [$this->_package_name] = array();
    }
  }


  /**
   * Returns an associative array of data for the specified member.
   *
   * @access  public
   * @param   int|string  $member_id  The member ID.
   * @param   string      $prefix     Optional prefix for member data keys.
   * @return  array
   */
  public function get_member_data($member_id, $prefix = '')
  {
    // Check for idiocy.
    if ( ! valid_int($member_id, 1))
    {
      $message = sprintf($this->EE->lang->line('exception_invalid_member_id'),
        strval($member_id), __METHOD__);

      throw new Exception($message);
    }

    if ( ! is_string($prefix))
    {
      $message = sprintf($this->EE->lang->line('exception_invalid_prefix'),
        __METHOD__);

      throw new Exception($message);
    }

    // Retrieve the custom member fields.
    $db_member_fields = $this->EE->db
      ->select('CONCAT("m_field_id_", m_field_id) AS m_field_id, m_field_name')
      ->get('member_fields');

    $select_fields = array(
      'members.avatar_filename',
      'members.avatar_height',
      'members.avatar_width',
      'members.bday_d',
      'members.bday_m',
      'members.bday_y',
      'members.bio',
      'members.email',
      'members.group_id',
      'members.interests',
      'members.location',
      'members.member_id',
      'members.occupation',
      'members.screen_name',
      'members.url',
      'members.username'
    );

    foreach ($db_member_fields->result_array() AS $db_row)
    {
      $select_fields[] = 'member_data.' .$db_row['m_field_id']
        .' AS ' .$db_row['m_field_name'];
    }

    // Retrieve the member data.
    $db_member_data = $this->EE->db
      ->select(implode(', ', $select_fields))
      ->from('members')
      ->join('member_data', 'member_data.member_id = members.member_id',
          'inner')
      ->where('members.member_id', $member_id)
      ->limit(1)
      ->get();

    // Did we find the member?
    if ($db_member_data->num_rows() !== 1)
    {
      $message = sprintf($this->EE->lang->line('exception_unknown_member'),
        strval($member_id), __METHOD__);

      throw new Exception($message);
    }

    // If the prefix is empty, our job is easy.
    if ($prefix == '')
    {
      return $db_member_data->row_array();
    }

    // Construct the return data with the prefix.
    $return_data = array();

    foreach ($db_member_data->row_array() AS $key => $val)
    {
      $return_data[$prefix .$key] = $val;
    }

    return $return_data;
  }


  /**
   * Returns the package name.
   *
   * @access  public
   * @return  string
   */
  public function get_package_name()
  {
    return $this->_package_name;
  }


  /**
   * Returns the package theme URL.
   *
   * @access  public
   * @return  string
   */
  public function get_package_theme_url()
  {
    // Much easier as of EE 2.4.0.
    if (defined('URL_THIRD_THEMES'))
    {
      return URL_THIRD_THEMES .$this->get_package_name() .'/';
    }

    return $this->EE->config->slash_item('theme_folder_url')
      .'third_party/' .$this->get_package_name() .'/';
  }


  /**
   * Returns the package title.
   *
   * @access  public
   * @return  string
   */
  public function get_package_title()
  {
    return $this->_package_title;
  }


  /**
   * Returns the package version.
   *
   * @access  public
   * @return  string
   */
  public function get_package_version()
  {
    return $this->_package_version;
  }


  /**
   * Returns the site ID.
   *
   * @access  public
   * @return  int
   */
  public function get_site_id()
  {
    if ( ! $this->_site_id)
    {
      $this->_site_id = (int) $this->EE->config->item('site_id');
    }

    return $this->_site_id;
  }


  /**
   * Logs a message to OmniLog.
   *
   * @access  public
   * @param   string      $message        The log entry message.
   * @param   int         $severity       The log entry 'level'.
   * @return  void
   */
  public function log_message($message, $severity = 1)
  {
    if (class_exists('Omnilog_entry') && class_exists('Omnilogger'))
    {
      switch ($severity)
      {
        case 3:
          $notify = TRUE;
          $type   = Omnilog_entry::ERROR;
          break;

        case 2:
          $notify = FALSE;
          $type   = Omnilog_entry::WARNING;
          break;

        case 1:
        default:
          $notify = FALSE;
          $type   = Omnilog_entry::NOTICE;
          break;
      }

      $omnilog_entry = new Omnilog_entry(array(
        'addon_name'    => 'Mo_member',
        'date'          => time(),
        'message'       => $message,
        'notify_admin'  => $notify,
        'type'          => $type
      ));

      Omnilogger::log($omnilog_entry);
    }
  }


  /**
   * Updates a 'base' array with data contained in an 'update' array. Both
   * arrays are assumed to be associative.
   *
   * - Elements that exist in both the base array and the update array are
   *   updated to use the 'update' data.
   * - Elements that exist in the update array but not the base array are
   *   ignored.
   * - Elements that exist in the base array but not the update array are
   *   preserved.
   *
   * @access public
   * @param  array  $base   The 'base' array.
   * @param  array  $update The 'update' array.
   * @return array
   */
  public function update_array_from_input(Array $base, Array $update)
  {
    return array_merge($base, array_intersect_key($update, $base));
  }




  /* --------------------------------------------------------------
   * PRIVATE METHODS
   * ------------------------------------------------------------ */

  /**
   * Returns a references to the package cache. Should be called
   * as follows: $cache =& $this->_get_package_cache();
   *
   * @access  private
   * @return  array
   */
  protected function &_get_package_cache()
  {
    return $this->EE->session->cache[$this->_namespace][$this->_package_name];
  }


}


/* End of file      : mo_member_model.php */
/* File location    : third_party/mo_member/models/mo_member_model.php */
