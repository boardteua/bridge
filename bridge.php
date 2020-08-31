<?php

require __DIR__ . '/vendor/autoload.php';

use Medoo\Medoo;

class Bridge {

    private $open_method = array( 'ms_auth', 'get_discount');
    private $db;

    public function __construct() {

        $this->db = new Medoo([
            'database_type' => 'mssql',
            'database_name' => 'Bridge',
            'server' => 'localhost',
            'username' => 'sa',
            'password' => '*',
            'charset' => 'utf8'
        ]);
    }

    /*
     * json methods for local func
     * 
      public function create_user($data) {
      if (user_exist($data)) {
      if ($this->append_json('cards/user.json', $data)) {
      $this->printres(true, $data);
      } else {
      $this->printres(false, $data);
      }
      } else {
      $this->printres(true, 'user_exisit');
      }
      }

      public function create_card($data) {
      if ($this->append_json('cards/cards.json', $data)) {
      $this->printres(true, $data);
      } else {
      $this->printres(false, $data);
      };
      }

      public function card_id($id) {
      $cards = json_decode(file_get_contents('cards/cards.json'));
      for ($i = 0; $i <= count($cards); $i++) {
      if ($cards[$i]->card_id == $id) {
      $this->printres(true, $cards[$i]->card_info);
      }
      }
      $this->printres(false);
      }
     */

    public function auth($key) {
        $data = $this->db->query("exec InetCard_GetCustomer '0'," . $key . ";")->fetchAll();
        return md5($data[0]['codep']);
    }

    public function ms_auth($in) {
        $data = explode(',', $in);
        $data = $this->db->query("exec InetCard_GetCustomer '" . $data[0] . "'," . $data[1] . ";")->fetchAll();
        $this->printres(true, $data);
    }

    public function get_total($in) {
        $data = explode(',', $in);
        $data = $this->db->query("exec InetCard_GetCustomer '" . $data[0] . "'," . $data[1] . ";")->fetchAll();
        $this->printres(true, $data[0]['sumall']);
    }

    public function get_discount($in) {
        $data = $this->db->query("exec InetCard_GetCustomer 1,1;")->fetchAll();
        $this->printres(true, $data);
    }

    public function get_check($in) {
        $data = explode(',', $in);
        $data = $this->db->query("exec InetCard_GetOrder 4, '" . $data[0] . "'," . $data[1] . ";")->fetchAll();
        $this->printres(true, $data);
    }

    public function update_user($in) {
        $data = explode('|', $in);
        $data = $this->db->query("exec InetCard_UpdateCustomer " . $data[0] . ", '" . $data[1] . "', " . $data[2] . ", " . $data[3] . ", '" . $data[4] . "', '" . $data[5] . "' ;")->fetchAll();
        $this->printres(true, $in);
    }

    /* ----- */

    private function printres($res, $out = NULL) {
        $print = array();
        $print['success'] = (bool) $res;
        if ((bool) $res)
            $print['out'] = $out;
        echo json_encode($print);
        die();
    }
    
    /*
    private function append_json($file, $data) {
        $tempArray = json_decode(file_get_contents($file));
        $tempArray[] = $data;
        if (file_put_contents($file, json_encode($tempArray))) {
            return true;
        } else {
            return false;
        }
    }*/

    public function open_method($method) {
        if (in_array($method, $this->open_method)) {
            return true;
        }
        return false;
    }

}

$bridge = new Bridge();

foreach ($_REQUEST as $method_name => $arguments) {
    if ($bridge->auth($_REQUEST['key']) || $bridge->open_method($method_name)) {
        if (method_exists($bridge, $method_name)) {
            call_user_func(array($bridge, $method_name), $arguments);
        }
    } else {
        header('HTTP/1.0 403 Forbidden');
        echo 'yay!';
    }
}
	
