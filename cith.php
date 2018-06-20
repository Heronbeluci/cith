<?php
defined('BASEPATH') OR exit('No direct script access allowed');

  /*
  // ╔═╗╔══╗╔══╗╔╗╔╗
  // ║╔╝╚║║╝╚╗╔╝║╚╝║
  // ║╚╗╔║║╗─║║─║╔╗║
  // ╚═╝╚══╝─╚╝─╚╝╚╝
  // A Simple and complete authentication library for Codeigniter.
  // From HexSeed
  */

class Cith {
  protected $CI;

  public function __construct () {
    $CI =& get_instance();

    $CI->load->database();
    $CI->load->library('session');
    $CI->load->helper('url');
  }

  public function register ($data, $autoLogin=false) {
    $CI =& get_instance();

    $cfg_table = $CI->config->item('Cith_table');
    $cfg_login = $CI->config->item('Cith_loginIndex');
    $cfg_pass = $CI->config->item('Cith_passIndex');

    $login = $data[$cfg_login];
    $password = $data[$cfg_pass];

    if (!$login) {
      return 'invalid-login';
    } else if (!$password) {
      return 'invalid-password';
    }

    $query = $CI->db->get_where($cfg_table, array($cfg_login => $login));
    if ($query->num_rows() > 0) {
      return 'login-exists';
    }

    $data[$cfg_pass] = password_hash($password, PASSWORD_BCRYPT, ['cost' => $CI->config->item('Cith_cryptCost')]);

    if (!$CI->db->insert($cfg_table, $data)) {
      return 'db-insert-error';
    } else if (!$autoLogin) {
      return 'success';
    }

    $auth = $this->login($login, $password);
    if ($auth === 'authenticated') {
      return 'success';
    } else {
      return $auth;
    }
  }

	public function login ($login=null, $password=null) {
    $CI =& get_instance();

    if ($login === null || $login === '') {
      return 'invalid-login';
    } else if ($password === null || $password === '') {
      return 'invalid-password';
    }

    $cfg_table = $CI->config->item('Cith_table');
    $cfg_login = $CI->config->item('Cith_loginIndex');
    $cfg_pass = $CI->config->item('Cith_passIndex');

    $query = $CI->db->query("SELECT * FROM `{$cfg_table}` WHERE `{$cfg_login}` = ? LIMIT 1", $login);
    $account = $query->row(0);

    if ($account === null) {
      return 'invalid-login';
    }

    if (!password_verify($password, $account->$cfg_pass)) {
      return 'invalid-password';
    }

    $CI->session->set_userdata((array) $account);
    $CI->session->set_userdata('Cith_uncriptedPassword', $password);

    return 'authenticated';
  }

	public function logout ($destiny=null) {
    $CI =& get_instance();
    $CI->session->sess_destroy();

    if ($destiny) {
      redirect(base_url($destiny));
    } else {
      redirect(base_url($CI->config->item('Cith_loginPage')));
    }
    return exit(0);
  }

  public function force ($deep=false) {
    $CI =& get_instance();

    $cfg_table = $CI->config->item('Cith_table');
    $cfg_login = $CI->config->item('Cith_loginIndex');
    $cfg_pass = $CI->config->item('Cith_passIndex');
    $cfg_loginPage = $CI->config->item('Cith_loginPage');

    if (!$CI->session->has_userdata($cfg_login) || !$CI->session->has_userdata($cfg_pass)) {
      redirect(base_url($cfg_loginPage));
      return exit(0);
    }

    if ($deep) {
      $query = $CI->db->query("SELECT * FROM `{$cfg_table}` WHERE `{$cfg_login}` = ? LIMIT 1", $CI->session->userdata($cfg_login));
      $account = $query->row(0);
      $password = $CI->session->userdata('Cith_uncriptedPassword');

      if ($account === null) {
        redirect(base_url($cfg_loginPage));
        return exit(0);
      }

      if (!password_verify($CI->session->userdata('Cith_uncriptedPassword'), $account->$cfg_pass)) {
        redirect(base_url($cfg_loginPage));
        return exit(0);
      }
      $CI->session->set_userdata((array) $account);
      $CI->session->set_userdata('Cith_uncriptedPassword', $password);
    }
  }

  public function account ($updated=false) {
    $CI =& get_instance();

    $cfg_table = $CI->config->item('Cith_table');
    $cfg_login = $CI->config->item('Cith_loginIndex');
    $cfg_pass = $CI->config->item('Cith_passIndex');
    $cfg_loginPage = $CI->config->item('Cith_loginPage');

    if (!$updated) {
      if (!$CI->session->has_userdata($cfg_login) || !$CI->session->has_userdata($cfg_pass)) {
        return null;
      }

      $data = $CI->session->get_userdata();

      // Remove Privade Items
      unset($data['Cith_uncriptedPassword']);
      unset($data['__ci_last_regenerate']);

      return (object) $data;

    } else {
      $query = $CI->db->query("SELECT * FROM `{$cfg_table}` WHERE `{$cfg_login}` = ? LIMIT 1", $CI->session->userdata($cfg_login));
      $account = $query->row(0);
      $password = $CI->session->userdata('Cith_uncriptedPassword');

      if ($account === null) {
        return null;
      }

      if (!password_verify($password, $account->$cfg_pass)) {
        return null;
      }

      $CI->session->set_userdata((array) $account);
      $CI->session->set_userdata('Cith_uncriptedPassword', $password);

      $data = $CI->session->get_userdata();

      // Remove Private Items
      unset($data['Cith_uncriptedPassword']);
      unset($data['__ci_last_regenerate']);

      return (object) $data;
    }
  }

}
