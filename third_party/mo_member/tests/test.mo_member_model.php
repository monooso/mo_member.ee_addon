<?php if ( ! defined('BASEPATH')) exit('Invalid file request');

/**
 * Mo&#039; Member model tests.
 *
 * @author          Stephen Lewis (http://github.com/experience/)
 * @copyright       Experience Internet
 * @package         Mo_member
 */

require_once PATH_THIRD .'mo_member/models/mo_member_model.php';

class Test_mo_member_model extends Testee_unit_test_case {

  private $_namespace;
  private $_package_name;
  private $_package_title;
  private $_package_version;
  private $_subject;


  /* --------------------------------------------------------------
   * PUBLIC METHODS
   * ------------------------------------------------------------ */

  /**
   * Constructor.
   *
   * @access  public
   * @return  void
   */
  public function setUp()
  {
    parent::setUp();

    $this->_namespace       = 'com.google';
    $this->_package_name    = 'Example_package';
    $this->_package_title   = 'Example Package';
    $this->_package_version = '1.0.0';

    $this->_subject = new Mo_member_model($this->_package_name,
      $this->_package_title, $this->_package_version, $this->_namespace);
  }


  public function test__get_member_data__retrieves_member_data_from_database_cached_it_and_returns_associative_array()
  {
    $member_id  = 123;
    $prefix     = 'prefix:';

    $db_member_fields = $this->_get_mock('db_query');
    $db_member_data   = $this->_get_mock('db_query');

    $db_member_fields_rows = array(
      array('m_field_id' => '10', 'm_field_name' => 'first_name'),
      array('m_field_id' => '20', 'm_field_name' => 'last_name')
    );

    $db_member_data_row = array(
      'avatar_filename'   => 'jimbob.jpg',
      'avatar_height'     => '100',
      'avatar_width'      => '150',
      'bday_d'            => '19',
      'bday_m'            => '02',
      'bday_y'            => '1973',
      'bio'               => 'JimBob forever',
      'email'             => 'jim@bob.com',
      'group_id'          => '999',
      'interests'         => 'Bobbing, weaving',
      'location'          => 'JimBob, Arizona',
      'member_id'         => strval($member_id),
      'occupation'        => 'Bobby',
      'screen_name'       => 'JimBob',
      'url'               => 'http://jimbob.com',
      'username'          => 'jimbob_username',
      'first_name'        => 'Jim',
      'last_name'         => 'Bob'
    );

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
      'members.username',
      'member_data.m_field_id_10 AS first_name',
      'member_data.m_field_id_20 AS last_name'
    );

    // General expectations.
    $this->EE->db->expectCallCount('select', 2);
    $this->EE->db->expectCallCount('get', 2);

    // Retrieve the custom member fields.
    $this->EE->db->expectAt(0, 'select', array('m_field_id, m_field_name'));
    $this->EE->db->expectAt(0, 'get', array('member_fields'));

    $this->EE->db->returnsByReferenceAt(0, 'get', $db_member_fields);
    $db_member_fields->returns('result_array', $db_member_fields_rows);

    // Retrieve the member data.
    $this->EE->db->expectAt(1, 'select', array(implode(', ', $select_fields)));
    $this->EE->db->expectOnce('from', array('members'));

    $this->EE->db->expectOnce('join',
      array('member_data', 'member_data.member_id = members.member_id',
        'inner'));

    $this->EE->db->expectOnce('where', array('members.member_id', $member_id));
    $this->EE->db->expectOnce('limit', array(1));
    $this->EE->db->expectAt(1, 'get', array());

    $this->EE->db->returnsByReferenceAt(1, 'get', $db_member_data);
    $db_member_data->returns('num_rows', 1);
    $db_member_data->returns('row_array', $db_member_data_row);

    // Expected result.
    $expected_result = array();

    foreach ($db_member_data_row AS $key => $val)
    {
      $expected_result[$prefix .$key] = $val;
    }

    // Run the tests. We run everything twice, to confirm the caching works.
    $this->assertIdentical($expected_result,
      $this->_subject->get_member_data($member_id, $prefix));

    $this->assertIdentical($expected_result,
      $this->_subject->get_member_data($member_id, $prefix));
  }


  public function test__get_member_data__throws_exception_if_passed_invalid_member_id()
  {
    $member_id  = 0;
    $message    = 'WTF dude?';

    $this->EE->lang->returns('line', $message,
      array('exception_invalid_member_id'));

    $this->expectException(new Exception($message));

    $this->EE->db->expectNever('select');
    $this->EE->db->expectNever('get');

    $this->_subject->get_member_data($member_id);
  }


  public function test__get_member_data__throws_exception_if_passed_non_string_prefix()
  {
    $member_id  = 123;
    $prefix     = new StdClass();
    $message    = 'Idiot';

    $this->EE->lang->returns('line', $message,
      array('exception_invalid_prefix'));

    $this->expectException(new Exception($message));

    $this->EE->db->expectNever('select');
    $this->EE->db->expectNever('get');

    $this->_subject->get_member_data($member_id, $prefix);
  }


  public function test__get_member_data__throws_exception_if_member_not_found()
  {
    $member_id  = 123;
    $message    = 'Unknown member';

    $db_member_fields = $this->_get_mock('db_query');
    $db_member_data   = $this->_get_mock('db_query');

    // General expectations.
    $this->EE->db->expectCallCount('select', 2);
    $this->EE->db->expectCallCount('get', 2);

    // Retrieve the custom member fields.
    $this->EE->db->returnsByReferenceAt(0, 'get', $db_member_fields);
    $db_member_fields->returns('result_array', array());

    // Retrieve the member data.
    $this->EE->db->returnsByReferenceAt(1, 'get', $db_member_data);
    $db_member_data->returns('num_rows', 0);
    $db_member_data->returns('row_array', array());

    // Exception.
    $this->EE->lang->returns('line', $message,
      array('exception_unknown_member'));

    $this->expectException(new Exception($message));

    $this->_subject->get_member_data($member_id);
  }


  public function test__get_package_name__returns_correct_package_name_converted_to_lowercase()
  {
    $this->assertIdentical(strtolower($this->_package_name),
      $this->_subject->get_package_name());
  }


  public function test__get_package_theme_url__pre_240_works()
  {
    if (defined('URL_THIRD_THEMES'))
    {
      $this->pass();
      return;
    }

    $package    = strtolower($this->_package_name);
    $theme_url  = 'http://example.com/themes/';
    $full_url   = $theme_url .'third_party/' .$package .'/';

    $this->EE->config->expectOnce('slash_item', array('theme_folder_url'));
    $this->EE->config->setReturnValue('slash_item', $theme_url);

    $this->assertIdentical($full_url, $this->_subject->get_package_theme_url());
  }


  public function test__get_package_title__returns_correct_package_title()
  {
    $this->assertIdentical($this->_package_title,
      $this->_subject->get_package_title());
  }


  public function test__get_package_version__returns_correct_package_version()
  {
    $this->assertIdentical($this->_package_version,
      $this->_subject->get_package_version());
  }


  public function test__get_site_id__returns_site_id_as_integer()
  {
    $site_id = '100';

    $this->EE->config->expectOnce('item', array('site_id'));
    $this->EE->config->setReturnValue('item', $site_id);

    $this->assertIdentical((int) $site_id, $this->_subject->get_site_id());
  }


  public function test__update_array_from_input__ignores_unknown_keys_and_updates_known_keys_and_preserves_unaltered_keys()
  {
    $base_array = array(
      'first_name'  => 'John',
      'last_name'   => 'Doe',
      'gender'      => 'Male',
      'occupation'  => 'Unknown'
    );

    $update_array = array(
      'dob'         => '1941-05-24',
      'first_name'  => 'Bob',
      'last_name'   => 'Dylan',
      'occupation'  => 'Writer'
    );

    $expected_result = array(
      'first_name'  => 'Bob',
      'last_name'   => 'Dylan',
      'gender'      => 'Male',
      'occupation'  => 'Writer'
    );

    $this->assertIdentical($expected_result,
      $this->_subject->update_array_from_input($base_array, $update_array));
  }


}


/* End of file      : test.mo_member_model.php */
/* File location    : third_party/mo_member/tests/test.mo_member_model.php */
