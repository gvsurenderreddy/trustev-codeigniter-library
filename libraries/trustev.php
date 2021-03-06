<?php

if (!defined('BASEPATH'))
    exit('No direct script access allowed');

class Trustev
{

    /**
     * Get a Token from Trustev's API
     * Each Token generated is valid for 1 hour from the time of creation.
     * The expiration time of the token is specified in the Token object returned with a succesful GetToken call.
     * @param string $username
     * @param string $password
     * @param string $secret
     * @param string $api_url
     * @return array $result_array
     */
    public function get_token($username, $password, $secret, $api_url)
    {
        $datestamp = date("YmdHis");
        $passwordHash = $this->Get256Hash($datestamp . "." . $password);
        $passwordHash = $this->Get256Hash($passwordHash . "." . $secret);
        $sha256Hash = $this->Get256Hash($datestamp . "." . $username);
        $sha256Hash = $this->Get256Hash($sha256Hash . "." . $secret);

        $request = array(
            'UserName' => $username,
            'Password' => $passwordHash,
            'Sha256Hash' => $sha256Hash,
            'Timestamp' => '/Date(' . (strtotime($datestamp) * 1000) . ')/'
        );

        $json_string = json_encode(array('request' => $request));

        $json_result = $this->request($api_url, $json_string);

        $result_array = json_decode($json_result, TRUE);

        return $result_array;
    }

    /**
     * This API method allows you to share authentication and access information relating to one or more social network accounts.
     * The API will create a new Trustev account for every time is invoked, unless an account already exists for any of the specified social network accounts.
     * URL to Add https://api.trustev.com/v1/Social/rest/AddProfile
     * URL to Add https://api.trustev.com/v1/Social/rest/UpdateProfile
     * @param string $username
     * @param array $credentials
     * @param string $action // add/update
     * @return array $result_array
     */
    public function profile_add($username, $credentials, $action = 'add')
    {
        $api_url = 'https://api.trustev.com/v1/Social/rest/';

        $api_url = ($action == 'add') ? $api_url . 'AddProfile' : $api_url . 'UpdateProfile';

        $request = array(
            'Token' => $credentials["Token"],
            'UserName' => $username,
            'SocialNetworks' => array(
                'Type' => 'SocialNetworkType',
                'Id' => 'String',
                'ShortTermAccessToken' => 'String',
                'LongTermAccessToken' => 'String',
                'ShortTermAccessTokenExpiry' => 'DateTime',
                'LongTermAccessTokenExpiry' => 'DateTime',
                'Secret' => 'String'
            )
        );

        $json_string = json_encode(array('request' => $request));

        $json_result = $this->request($api_url, $json_string);

        $result_array = json_decode($json_result, TRUE);

        return $result_array;
    }

    /**
     * This API method allows you delete one or many social profile accounts from a Trustev account.
     * This will delete the social network authentication data that has been shared with Trustev, 
     * and remove any data relevant to the social profile from Trustev’s systems.
     * URL https://api.trustev.com/v1/Social/rest/DeleteProfile
     * Authentication Required Yes
     * Format JSON
     * Method POST
     */
    public function profile_delete()
    {
        $api_url = 'https://api.trustev.com/v1/Social/rest/DeleteProfile';

        $request = array(
            'Token' => $credentials["Token"],
            'UserName' => $username,
            'SocialNetworks' => array(
                'Type' => 'SocialNetworkType',
                'Id' => 'String'
            )
        );

        $json_string = json_encode(array('request' => $request));

        $json_result = $this->request($api_url, $json_string);

        $result_array = json_decode($json_result, TRUE);

        return $result_array;
    }

    /**
     * Hash a string
     * @param type $unhashed
     * @return type
     */
    public function Get256Hash($unhashed)
    {
        $resultBytes = hash('sha256', $unhashed);
        return $resultBytes;
    }

    /**
     * Perform the actual Curl request
     * @param type $api_url
     * @param type $json_string
     * @return type
     */
    public function request($api_url, $json_string)
    {
        $ch = curl_init($api_url);

        // Configuring curl options
        $options = array(
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_HTTPHEADER => array('Content-type: application/json'),
            CURLOPT_POSTFIELDS => $json_string
        );

        curl_setopt_array($ch, $options);

        $json_result = curl_exec($ch);

        return $json_result;
    }

}

/* End of file Trustev.php */