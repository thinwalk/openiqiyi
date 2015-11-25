<?php 

class Iqiyi {

        private $appkey;
        private $appsecret;
        private $accesstoken;

        function __construct($iqiyiconf)
        {

            $this->appkey = $iqiyiconf['appkey'];
            $this->appsecret = $iqiyiconf['appsecret'];
        }

        public function setaccesstoken($accesstoken)
        {
            $this->accesstoken = $accesstoken;
        }

        public function getaccesstoken()
        {
            return $this->accesstoken;
        }

        public function authorizationcode($redirecturi)
        {
            $url = "https://openapi.iqiyi.com/api/oauth2/authorize?client_id=".$this->appkey."&response_type=code&redirect_uri=".$redirecturi;
            header("location: ".$url);
        } 
   
        public function accesstoken($code,$redirecturi)
        {
            $url = "https://openapi.iqiyi.com/api/oauth2/token?client_id=".$this->appkey."&client_secret=".$this->appsecret."&redirect_uri=".$redirecturi."&grant_type=authorization_code&code=".$code;
            $ch = curl_init($url) ;  
            curl_setopt($ch, CURLOPT_RETURNTRANSFER, true) ;  
            curl_setopt($ch, CURLOPT_BINARYTRANSFER, true) ;
            $json =  $output = curl_exec($ch) ;  
            $dataArr = json_decode($json,true);
            return $dataArr;
        }

        public function authorize()
        {
            $url = "https://openapi.iqiyi.com/api/iqiyi/authorize?client_id=".$this->appkey."&client_secret=".$this->appsecret;
            $ch = curl_init($url) ;  
            curl_setopt($ch, CURLOPT_RETURNTRANSFER, true) ;  
            curl_setopt($ch, CURLOPT_BINARYTRANSFER, true) ;
            $json =  $output = curl_exec($ch) ;  
            $dataArr = json_decode($json,true);
            curl_close ( $ch );
            return $dataArr;
        }

        public function openupload($path)
        {
            $url = "http://upload.iqiyi.com/openupload";
            $pathinfo = pathinfo($path);
            $headerArr = array();
            $headerArr[] = 'filetype: '.$pathinfo['extension'];
            $headerArr[] = 'filesize: '.filesize($path);
            $headerArr[] = 'access_token: '.$this->accesstoken;

            $ch = curl_init() ;  
            curl_setopt($ch, CURLOPT_URL, $url);
            curl_setopt($ch, CURLOPT_RETURNTRANSFER, true) ;
            curl_setopt($ch, CURLOPT_HTTPHEADER , $headerArr );
            $json =  $output = curl_exec($ch) ;  
            $dataArr = json_decode($json,true);
            curl_close ( $ch );
            return $dataArr;
        }


        public function upload($fileid,$path)
        {
            $url = 'http://qichuan.iqiyi.com/upload';
            $filesize = filesize($path) - 1;
            $headerArr = array();
            $headerArr[] = 'range: 0-'.$filesize;
            $headerArr[] = 'file_size: '.filesize($path);
            $headerArr[] = 'file_id: '.$fileid;
            $postData = array();
            $postData['file'] = new \CURLFile($path);;

            $ch = curl_init ();
            curl_setopt( $ch, CURLOPT_URL, $url );
            curl_setopt( $ch, CURLOPT_POST, 1 );
            curl_setopt( $ch, CURLOPT_RETURNTRANSFER, 1 );
            curl_setopt( $ch, CURLOPT_HTTPHEADER , $headerArr );
            curl_setopt( $ch, CURLOPT_POSTFIELDS, $postData );
            $json =  $output = curl_exec($ch); 
            $dataArr = json_decode($json,true);
            curl_close ( $ch );

            return $dataArr;
        }


        public function uploadfinish($fileid)
        {
            $url = 'http://upload.iqiyi.com/uploadfinish';
            $postData = array();
            $postData['range_finished'] = 'true';
            $postData['file_id'] = $fileid;
            $postData['access_token'] = $this->accesstoken;
            $url .="?access_token=".$postData['access_token']."&range_finished=".$postData['range_finished']."&file_id=".$postData['file_id'];
            $ch = curl_init ();
            curl_setopt( $ch, CURLOPT_URL, $url );
            curl_setopt( $ch, CURLOPT_RETURNTRANSFER, 1 );
            $json =  $output = curl_exec($ch);  
            $dataArr = json_decode($json,true);
            curl_close ( $ch );

            return $dataArr;
        }

        public function setmetainfo($fileid,$filename,$description,$tags='',$filetype=0)
        {
            $url = "http://openapi.iqiyi.com/api/file/info?access_token=".$this->accesstoken."&file_id=".$fileid."&file_name=".$filename."&description=".$description;
            if($tags)
            {
                $url.="&tags=".$tags;
            }

            if($filetype)
            {
                $url.="&file_type=".$filetype;
            }

            $ch = curl_init() ;  
            curl_setopt($ch, CURLOPT_URL, $url);
            curl_setopt($ch, CURLOPT_RETURNTRANSFER, true) ;   
            $json =  $output = curl_exec($ch) ;  
            $dataArr = json_decode($json,true);
            curl_close ( $ch );
            return $dataArr;
        }

        public function doupload($path,$filename,$description,$tags='',$filetype=0)
        {
            $dataopen = $this->openupload($path);
            $dataupload = $this->upload($dataopen['data']['file_id'],$path);
            $datafinish =  $this->uploadfinish($dataopen['data']['file_id']);
            $datametainfo = $this->setmetainfo($dataopen['data']['file_id'],$filename,$description,$tags,$filetype);
            return $dataopen['data']['file_id'];
        }

}
