<?php
class TestMethods{

    public const APIKEY = '10da3ac6607bdf2589462d8d85cfc6e9107c920d';
    //public const PHONE_NUMBER = '+375172150077';
    //public const PHONE_NUMBER = '+375292507794';
    public const SUBDOMAIN = 'radis';
    public const LOGIN = 'alexey@radis.by';

    public const IN_WORK_STATUS = '142';
    public const CLOSED_STATUS = '143';
    public const STATUS_GOT_CONTACT = '14783997';

    public function __construct() {
        $this->user=array(
            'USER_LOGIN'=>'alexey@radis.by'
        );
        $this->user['USER_HASH'] = self::APIKEY;
    }

    public function amoAuth() {
        $url ='https://'.self::SUBDOMAIN.'.amocrm.ru/private/api/auth.php?type=json';
        $curl = curl_init();

        curl_setopt($curl,CURLOPT_RETURNTRANSFER,true);
        curl_setopt($curl,CURLOPT_USERAGENT,'amoCRM-API-client/1.0');

        
        curl_setopt($curl,CURLOPT_URL,$url);
        curl_setopt($curl,CURLOPT_CUSTOMREQUEST,'POST');
        curl_setopt($curl,CURLOPT_POSTFIELDS,json_encode($this->user));
        curl_setopt($curl,CURLOPT_HTTPHEADER,array('Content-Type: application/json'));
        curl_setopt($curl,CURLOPT_HEADER,false);
        curl_setopt($curl,CURLOPT_SSL_VERIFYPEER, false);
        curl_setopt($curl,CURLOPT_SSL_VERIFYHOST, false);

        $authAnswer = curl_exec($curl);
        //var_dump(json_decode($authAnswer, true)); 
        curl_close($curl);


    }

    public function getContacts() {
        
        $limit = 500;
        $i = 0;

        do {
            $link='https://'.self::SUBDOMAIN.'.amocrm.ru/api/v2/contacts/?'.http_build_query($this->user).'&limit_rows='.$limit.'&limit_offset='.$limit*$i;
            $curl=curl_init();
            
            curl_setopt($curl,CURLOPT_RETURNTRANSFER,true);
            curl_setopt($curl,CURLOPT_USERAGENT,'amoCRM-API-client/1.0');
            curl_setopt($curl,CURLOPT_URL,$link);
            curl_setopt($curl,CURLOPT_HEADER,false);
            
            curl_setopt($curl,CURLOPT_SSL_VERIFYPEER,false);
            curl_setopt($curl,CURLOPT_SSL_VERIFYHOST,false);
            $out = curl_exec($curl);
            
            $items = (json_decode($out, true))["_embedded"]["items"];
            echo $items[$i]["id"]."<br><br>";

            $itemsCount = count($items);
            $i++;
            curl_close($curl);

        } while ($itemsCount == $limit); //забраковать
    }
    public function getCurrentId() {
        $users = $amo->account->apiCurrent()['users'];

        foreach ($users as $user) {
            if (trim($user['login']) == self::LOGIN) {
                $this->currentUserId = $user['id'];
                return $this->currentUserId;
            }
        }
        
    }
}
$tm = new TestMethods();
$tm->amoAuth();