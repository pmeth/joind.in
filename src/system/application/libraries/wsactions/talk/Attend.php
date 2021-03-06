<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');

class Attend extends BaseWsRequest {

    var $CI		= null;
    var $xml	= null;

    public function Attend($xml) {
        $this->CI=&get_instance(); //print_r($this->CI);
        $this->xml=$xml;
    }
    public function checkSecurity($xml) {
        // Just check the key combination on the URL
        if ($this->isValidLogin($xml) || $this->checkPublicKey()) {
            return true;
        }

        return false;
    }
    //-----------------------
    public function run() {
        $this->CI->load->library('wsvalidate');
        $this->CI->load->model('user_attend_talk_model');

        $rules=array(
            'tid'		=>'required|istalk',
            //'reqkey'	=>'required|reqkey'
        );
        $tid=$this->xml->action->tid;
        $ret=$this->CI->wsvalidate->validate($rules, $this->xml->action);
        if (!$ret) {
            //see if were logged in - if not, we return the redirect: message back
            if ($this->CI->wsvalidate->validate_loggedin() || $this->isValidLogin($this->xml)) {
                $uid=$this->CI->session->userdata('ID');
                if (!$uid) {
                    // its an API call, grab from the XML
                    $user=$this->CI->user_model->getUser($this->xml->auth->user);
                    $uid = $user[0]->ID;
                }

                //check to see if they have a record - if they do, remove
                //if they don't, add...
                $this->CI->user_attend_talk_model->chgAttendStat($uid, $tid);

                return array('output'=>'json','data'=>array('items'=>array('msg'=>'Success')));

            } else {
                $this->CI->session->set_userdata('ref_url','talk/view/'.$tid);
                return array('output'=>'json','data'=>array('items'=>array('msg'=>'redirect:/user/login')));
            }
        } else { return array('output'=>'json','data'=>array('items'=>array('msg'=>'Fail'))); }
    }
    //-----------------------
}
?>
