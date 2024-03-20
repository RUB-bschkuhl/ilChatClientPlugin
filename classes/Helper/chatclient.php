
<?php

/*
 * @author Bastian Schmidt-Kuhl <bastian.schmidt-kuhl@ruhr-uni-bochum.de>
 */

class Chatclient
{
    private ilChatClientPlugin $pl;
    private $interact_url;
    private $upload_url;

    public function __construct()
    {

        $this->pl = ilChatClientPlugin::getInstance();
        $this->interact_url = $this->pl::getValue("interact_url");
        $this->upload_url =$this->pl::getValue("upload_url");
        
        if (!$this->interact_url) {
            //TODO warn
        }
        if (!$this->upload_url) {
            //TODO warn
        }
    }

    //function to make HTTP request using curl
    public function curl_interact($data)
    {
        $url = $this->interact_url;

        $headers = array(
            "Content-Type:application/json"
        );

        $curl = curl_init();

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
    public function curl_upload_file($file, $headers = null)
    {
        $file_to_upload = $this->get_file_from_hash($file['filehash']);
        if ($file_to_upload == false) {
            return false;
        }
        $url = $this->upload_url;

        $curl = new curl();
        // todo: implement curl_multi für bulk 

        $curl->setHeader(array("Content-Type: multipart/form-data"));
        $response =  $curl->post($url, ["file" => $file_to_upload, "course_id" => "1", "user_id" => "1"]);

        if ($response != false) {
            //TODO save hash to succesful uploads table to prevent duplicate uploads in a first effort
        }
        return $response;
    }

    public function get_file_from_hash($filehash)
    {
        //TODO security
        return new ilObjFile($filehash, false);
    }
}
