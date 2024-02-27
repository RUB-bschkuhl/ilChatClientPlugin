
<?php

class chatclient
{
    private $url;

    public function __construct()
    {
        //return error apiurl not set
        // try {
        $chaturl = ""; //get_config('block_rub_chatbot_settings', 'apiurl');
        if (!$chaturl) {
            // throw new moodle_exception('apiurlnotset', 'block_rub_chatbot');
        }
        $this->url = $chaturl;
        // } catch (moodle_exception $e) {
        //     echo ($e->getMessage());
        //     die();
        // }
    }

    public function get_interact_url()
    {
        return $this->url . '/interact';
    }

    public function get_upload_url()
    {
        return $this->url . '/upload';
    }

    //function to make HTTP request using curl
    public function curl_interact($url, $data)
    {
        $url = 'https://oc-worker02-stage.ruhr-uni-bochum.de/api/LLM/ConversationChat';

        $headers = array(
            "Content-Type:application/json"
        );

        $curl = curl_init();
        //TODO HTTPS,VERIFYPEERS ?
        curl_setopt($curl, CURLOPT_URL, $url);
        curl_setopt($curl, CURLOPT_POST, true);
        curl_setopt($curl, CURLOPT_POSTFIELDS, json_encode($data));
        if ($headers) {
            curl_setopt($curl, CURLOPT_HTTPHEADER, $headers);
        }
        curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($curl, CURLOPT_VERBOSE, true);

        $response = curl_exec($curl);
        curl_close($curl);
        
        return $response;
    }

    //function to upload file via curl
    public function curl_upload_file($url, $file, $headers = null)
    {
        $file_to_upload = $this->get_file_from_hash($file['filehash']);
        if ($file_to_upload == false) {
            return false;
        }
        $url = 'http://host.docker.internal:5003/upload';
        $mcurl = new curl();
        // todo: implement curl_multi für bulk 

        $mcurl->setHeader(array("Content-Type: multipart/form-data"));
        $response =  $mcurl->post($url, ["file" => $file_to_upload, "cid" => "1", "uid" => "1"]);

        if ($response != false) {
            //TODO save hash to succesful uploads db to primitively prevent duplicate uploads
        }
        return $response;
    }

    public function get_file_from_hash($filehash)
    {
        // $fs = get_file_storage();
        // $filebyhash = $fs->get_file_by_hash($filehash);

        // return $filebyhash;
    }
}
