<?php
/**
 * Created by PhpStorm.
 * User: Ray
 * Date: 2018/04/23
 * Time: 9:02 AM
 */

namespace App\Http\Processors;


use SimpleXMLElement;
use stdClass;

class SmsApi
{
    /**
     * SmsApi constructor
     */
    public function __construct() {
        $this->url = 'http://www.mymobileapi.com/api5/http5.aspx';
        $this->username = 'devdurbs';
        $this->password = 'Tia@2006';
    }

    /**
     * Return the current credits
     *
     * @return null|string
     */
    public function checkCredits() {
        $data = array(
            'Type' => 'credits',
            'Username' => $this->username,
            'Password' => $this->password
        );
        $response = $this->querySmsServer($data);
        // NULL response only if connection to sms server failed or timed out
        if ($response == NULL) {
            return 'Server not available';
        } elseif ($response->call_result->result) {
            return $response->data->credits;
        }

        return null;
    }

    /**
     * Send an sms
     *
     * @param $mobile_number
     * @param $msg
     * @return StdClass
     */
    public function sendSms($mobile_number, $msg) {
        $data = array(
            'Type' => 'sendparam',
            'Username' => $this->username,
            'Password' => $this->password,
            'numto' => $mobile_number, //phone numbers (can be comma separated)
            'data1' => $msg, //your sms message

        );
        $response = $this->querySmsServer($data);
        return $this->returnResult($response);
    }

    /**
     * Query API server and return response in object format
     *
     * @param      $data
     * @param null $optional_headers
     * @return SimpleXMLElement|null
     */
    private function querySmsServer($data, $optional_headers = null) {

        $ch = curl_init($this->url);
        curl_setopt($ch, CURLOPT_POST, true);
        curl_setopt($ch, CURLOPT_POSTFIELDS, $data);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        // prevent large delays in PHP execution by setting timeouts while connecting and querying the 3rd party server
        curl_setopt($ch, CURLOPT_CONNECTTIMEOUT_MS, 2000); // response wait time
        curl_setopt($ch, CURLOPT_TIMEOUT_MS, 2000); // output response time
        $response = curl_exec($ch);
        if (!$response) return NULL;
        else return new SimpleXMLElement($response);
    }

    /**
     * Handle sms server response
     * @param $response
     * @return StdClass
     */
    private function returnResult($response) {
        $return = new StdClass();
        $return->pass = NULL;
        $return->msg = '';

        // customised
        if ($response == NULL) {
            $return->pass = 'error';
            $return->msg = 'server error';
        } elseif ($response->call_result->error > ' ') {
            $return->pass = 'error';
            $return->msg = $response->call_result->error;
        } elseif ($response->call_result->result) {
            $return->pass = 'success';
            $return->msg = 'sms sent';
        } else {
            $return->pass = 'error';
            $return->msg = $response->call_result->error;
        }

        // original
//        if ($response == NULL) {
//            $return->pass = FALSE;
//            $return->msg = 'SMS connection error.';
//        } elseif ($response->call_result->result) {
//            $return->pass = 'CallResult: '.TRUE . '</br>';
//            $return->msg = 'EventId: '.$response->send_info->eventid .'</br>Error: '.$response->call_result->error;
//        } else {
//            $return->pass = 'CallResult: '.FALSE. '</br>';
//            $return->msg = 'Error: '.$response->call_result->error;
//        }
//        echo $return->pass;
//        echo $return->msg;

        return $return;
    }
}