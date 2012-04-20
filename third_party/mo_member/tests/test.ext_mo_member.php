<?php if ( ! defined('BASEPATH')) exit('Invalid file request');

/**
 * Mo&#039; Member extension tests.
 *
 * @author          Stephen Lewis (http://github.com/experience/)
 * @copyright       Experience Internet
 * @package         Mo_member
 */

require_once PATH_THIRD .'mo_member/ext.mo_member.php';
require_once PATH_THIRD .'mo_member/models/mo_member_extension_model.php';

class Test_mo_member_ext extends Testee_unit_test_case {

  private $_ext_model;
  private $_pkg_version;
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

    // Generate the mock model.
    Mock::generate('Mo_member_extension_model',
      get_class($this) .'_mock_ext_model');

    /**
     * The subject loads the models using $this->EE->load->model().
     * Because the Loader class is mocked, that does nothing, so we
     * can just assign the mock models here.
     */

    $this->EE->mo_member_extension_model = $this->_get_mock('ext_model');
    $this->_ext_model = $this->EE->mo_member_extension_model;

    // Called in the constructor.
    $this->_pkg_version = '2.3.4';
    $this->_ext_model->setReturnValue('get_package_version',
      $this->_pkg_version);

    $this->_subject = new Mo_member_ext();
  }


  public function test__activate_extension__calls_model_install_method_with_correct_arguments()
  {
    $hooks = array('template_fetch_template');

    $this->_ext_model->expectOnce('install',
      array(get_class($this->_subject), $this->_pkg_version, $hooks));

    $this->_subject->activate_extension();
  }


  public function test__disable_extension__calls_model_uninstall_method_with_correct_arguments()
  {
    $this->_ext_model->expectOnce('uninstall',
      array(get_class($this->_subject)));

    $this->_subject->disable_extension();
  }


  public function test__on_template_fetch_template__retrieves_the_member_data_and_adds_it_to_the_global_variables()
  {
    $member_data  = array('jack' => 'white', 'bob' => 'dylan');
    $member_id    = 123;
    $global_vars  = array('member_id' => $member_id, 'color' => 'purple');

    // Assign the global variables to the config object.
    $this->EE->config->_global_vars = $global_vars;

    $this->EE->session->expectOnce('userdata', array('member_id'));
    $this->EE->session->returns('userdata', $member_id, array('member_id'));

    $this->_ext_model->expectOnce('get_member_data', array($member_id, '*'));
    $this->_ext_model->returns('get_member_data', $member_data);

    // Run the method.
    $this->_subject->on_template_fetch_template(array());

    // Check that the global variables have been updated.
    $expected_result = array_merge($member_data, $global_vars);
    $this->assertIdentical($expected_result, $this->EE->config->_global_vars);
  }


  public function test__on_template_fetch_template__fails_if_no_session_member_id()
  {
    $this->EE->session->expectOnce('userdata', array('member_id'));
    $this->EE->session->returns('userdata', FALSE);

    $this->_ext_model->expectNever('get_member_data');

    $this->_subject->on_template_fetch_template(array());
  }


  public function test__on_template_fetch_template__handles_model_exception()
  {
    $member_id  = 123;
    $message    = 'Oh noes!';

    $this->EE->session->expectOnce('userdata', array('member_id'));
    $this->EE->session->returns('userdata', $member_id);

    // Throw the exception.
    $this->_ext_model->expectOnce('get_member_data');
    $this->_ext_model->throwOn('get_member_data', new Exception($message));

    // Log the error.
    $this->_ext_model->expectOnce('log_message', array($message, 3));

    $this->_subject->on_template_fetch_template(array());
  }


  public function test__update_extension__calls_model_update_method_with_correct_arguments_and_honors_return_value()
  {
    $installed  = '1.2.3';
    $result     = 'Ciao a tutti!';    // Could be anything.

    $this->_ext_model->expectOnce('update',
      array(get_class($this->_subject), $installed, $this->_pkg_version));

    $this->_ext_model->setReturnValue('update', $result);

    $this->assertIdentical($result,
      $this->_subject->update_extension($installed));
  }


}


/* End of file      : test.ext_mo_member.php */
/* File location    : third_party/mo_member/tests/test.ext_mo_member.php */
