<?php

require_once 'C:\Users\asus\vendor\autoload.php';

class AmoModel {
    public const APIKEY = '10da3ac6607bdf2589462d8d85cfc6e9107c920d';
    //public const PHONE_NUMBER = '+375172150077';
    public const PHONE_NUMBER = '+375292507794';
    public const SUBDOMAIN = 'radis';
    public const LOGIN = 'alexey@radis.by';

    public const IN_WORK_STATUS = '142';
    public const CLOSED_STATUS = '143';
    public const STATUS_GOT_CONTACT = '14783997';

    public const LEAD_API_URL = '/api/v2/leads/add';

    public function __construct() {
        $this->user=array(
            'USER_LOGIN'=>'alexey@radis.by',
            

        );
        $this->user['USER_HASH'] = self::APIKEY;
    }

    public function linksInitialization() {
        return new \AmoCRM\Client(self::SUBDOMAIN, self::LOGIN, self::APIKEY);
    }

    public function getStatuses() {
        $amo = $this->linksInitialization();
        $account = $amo->account;
        print_r($account->apiCurrent()['leads_statuses']);
    }

    public function phoneValidation($phone) {
        if (trim($phone) == "" || strlen($phone) < 5) {
            throw new Exception('Телефон должен содержать не менее 5-ти символов!');
        } else {
            return $phone;
        }
    }

    public function getContactByField() {
        $amo = $this->linksInitialization();
        return $amo->contact->apiList([
            'query' => $this->phoneValidation(self::PHONE_NUMBER),
        ]);
    }
    
    public function getDealsByQuery() {
        $amo = $this->linksInitialization();
        return $amo->lead->apiList([
            'query' => self::PHONE_NUMBER,
        ]);
    }

    public function issetOpenedDeals() {
        $deals = $this->getDealsByQuery();
        $issetOpenedDeal = false;
        foreach ($deals as $deal) {
            if ($deal["status_id"] != self::CLOSED_STATUS) {
                $issetOpenedDeal = true;
                echo 'У данного пользователя есть открытые сделки!<br><br>';
                break;
            }
        }
        return $issetOpenedDeal;
    }

    public function createContact() {
        $amo = $this->linksInitialization();
        $contact = $amo->contact;
        $contact['name'] = self::PHONE_NUMBER;
        $contact['tags'] = ['Тестирование телефонии'];
        $contactId = $contact->apiAdd();
        echo 'Пользователь с id '.$contactId.' добавлен!<br><br>';
        return $contactId;
    }

    public function createLead1($contactId) {
        $amo = $this->linksInitialization();
        $lead = $amo->lead;
        $lead['name'] = 'Тестовая сделка Aliosha';
        $lead['status_id'] = self::STATUS_GOT_CONTACT;
        $lead['tags'] = ['телефония'];
        $lead['contacts_id'] = array(
                                $contactId,
                            );
        $lid = $lead->apiAdd();
        echo 'Создана сделка с id '.$lid.'!<br><br>';
        return $lid;
    }

    public function createLead($contactId) {

        $url ='https://'.self::SUBDOMAIN.'.amocrm.ru/api/v2/leads?USER_LOGIN='.self::LOGIN.'&USER_HASH='.self::APIKEY;
        $curl = curl_init();

        curl_setopt($curl,CURLOPT_RETURNTRANSFER,true);
        curl_setopt($curl,CURLOPT_USERAGENT,'amoCRM-API-client/1.0');
        
        $leads['add']=array(
            array(
              'name'=>'test Aliosha',
              'status_id'=>self::STATUS_GOT_CONTACT,
              'tags' => 'Телефония',
              'contacts_id' => array(
                    $contactId,
              )
            )
        );
        
        curl_setopt($curl,CURLOPT_URL,$url);
        curl_setopt($curl,CURLOPT_CUSTOMREQUEST,'POST');
        curl_setopt($curl,CURLOPT_POSTFIELDS, json_encode($leads));
        curl_setopt($curl,CURLOPT_HTTPHEADER,array('Content-Type: application/json'));
        curl_setopt($curl,CURLOPT_HEADER,false);
        curl_setopt($curl,CURLOPT_SSL_VERIFYPEER, false);
        curl_setopt($curl,CURLOPT_SSL_VERIFYHOST, false);

        $authAnswer = curl_exec($curl);
        echo 'Создана новая сделка!<br><br>'; 
        curl_close($curl);
    }


    public function createContactIfNotIssetAndCreateLeadIfNotIsset() {
        try {
            if (count($this->getContactByField())>0) {
                $contactId = $this->getContactByField()[0]['id'];
                echo 'Пользователь с номером '.self::PHONE_NUMBER.' найден! Его id - '.$contactId.'<br><br>';
                if (!$this->issetOpenedDeals()) {
                    $this->createLead($contactId);
                }
                return $contactId;
            } else {
                $contactId = $this->createContact();
                $this->createLead($contactId);
            }
        }  
        catch (\AmoCRM\Exception $e) {
            printf('Error (%d): %s', $e->getCode(), $e->getMessage());
        }
    }
}

$amoW = new AmoModel();

$amoW -> createContactIfNotIssetAndCreateLeadIfNotIsset();
//$amoW -> getStatuses();
//$amoW ->createLead();