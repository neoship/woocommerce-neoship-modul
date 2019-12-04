<?php

class NeoshipApi
{
    private $accessData = false;
    private $curl;
    private $loginData;

    function __construct() {
        $this->curl = curl_init();
    }

    public function login($test = false){
        $this->loginData = get_option('neoship_login');
        
        if($this->loginData == false) {
            $this->errorMessage( __('Please setup neoship login credentials', 'neohsip'), !$test);
        }

        $url = NEOSHIP_API_URL . '/oauth/v2/token?client_id='.urlencode($this->loginData['clientid']).'&client_secret='.urlencode($this->loginData['clientsecret']).'&grant_type=client_credentials';
        curl_setopt($this->curl, CURLOPT_URL, $url);
        curl_setopt($this->curl, CURLOPT_RETURNTRANSFER, 1); 
        
        $response = curl_exec($this->curl);

        if (curl_getinfo($this->curl, CURLINFO_HTTP_CODE) != 200){
            $this->errorMessage( __('Bad login credentials', 'neoship'), !$test );
            return;
        }
        
        $this->accessData = json_decode($response);
        
        if($test) {
            $this->saveUserId();
            $this->successMessage( __('Login credentials are correct', 'neoship'), !$test );
        }
    }

    public function saveUserId(){
        if ($this->accessData == false){
            $this->login();
        }

        $url = NEOSHIP_API_URL . '/user/?' . http_build_query($this->accessData);
        curl_setopt($this->curl, CURLOPT_URL, $url);
        $response = curl_exec($this->curl);      

        if (curl_getinfo($this->curl, CURLINFO_HTTP_CODE) != 200){
            $this->errorMessage( __("Something is wrong. Please refresh the page and try again") );
        }
        
        $user = json_decode($response, true);

        $this->loginData['userid'] = $user['id'];
        update_option('neoship_login', $this->loginData);
    }

    public function getUserAddress(){
        if ($this->accessData == false){
            $this->login();
        }

        $url = NEOSHIP_API_URL . '/user/?' . http_build_query($this->accessData);
        curl_setopt($this->curl, CURLOPT_URL, $url);
        $response = curl_exec($this->curl);      

        if (curl_getinfo($this->curl, CURLINFO_HTTP_CODE) != 200){
            $this->errorMessage( __("Something is wrong. Please refresh the page and try again") );
        }
        
        $user = json_decode($response, true);

        $this->loginData['userid'] = $user['id'];
        update_option('neoship_login', $this->loginData);

        $user['address']['state'] = $user['address']['state']['id'];
        unset($user['address']['id']);
        $user['address']['zIP'] = $user['address']['zip'];
        unset($user['address']['zip']);
        return $user['address'];
    }

    public function getUserCredit(){
        if ($this->accessData == false){
            $this->login();
        }

        $url = NEOSHIP_API_URL . '/user/?' . http_build_query($this->accessData);
        curl_setopt($this->curl, CURLOPT_URL, $url);
        $response = curl_exec($this->curl);      

        if (curl_getinfo($this->curl, CURLINFO_HTTP_CODE) != 200){
            return 0;
        }
        
        $user = json_decode($response, true);
        return round($user['kredit'], 2);
    }

    public function getStatesIds(){
        if ($this->accessData == false){
            $this->login();
        }

        $url = NEOSHIP_API_URL . '/state/?' . http_build_query($this->accessData);
        curl_setopt($this->curl, CURLOPT_URL, $url);
        $response = curl_exec($this->curl);      

        if (curl_getinfo($this->curl, CURLINFO_HTTP_CODE) != 200){
            $this->errorMessage( __("Something is wrong. Please refresh the page and try again") );
        }
        
        $states = json_decode($response, true);
        $stateIdsByCode = [];
        foreach ($states as $state) {
            $stateIdsByCode[$state['code']] = $state['id'];
        }
        return $stateIdsByCode;
    }

    public function createPackages($packages){
        if ($this->accessData == false){
            $this->login();
        }

        $url = NEOSHIP_API_URL . '/package/bulk?' . http_build_query($this->accessData);
        curl_setopt($this->curl, CURLOPT_URL, $url);   
        curl_setopt($this->curl, CURLOPT_POST, 1);                                                              
        curl_setopt($this->curl, CURLOPT_HTTPHEADER, array('Content-Type: application/json'));
        curl_setopt($this->curl, CURLOPT_POSTFIELDS, json_encode($packages));
        $response = curl_exec($this->curl);
       
        if (curl_getinfo($this->curl, CURLINFO_HTTP_CODE) != 200){
            $this->errorMessage( __("Something is wrong. Please refresh the page and try again") );
        }
        
        return json_decode($response, true);
    }

    public function getCurrenciesIds(){
        if ($this->accessData == false){
            $this->login();
        }

        $url = NEOSHIP_API_URL . '/currency/?' . http_build_query($this->accessData);
        curl_setopt($this->curl, CURLOPT_URL, $url);
        $response = curl_exec($this->curl);      

        if (curl_getinfo($this->curl, CURLINFO_HTTP_CODE) != 200){
            $this->errorMessage( __("Something is wrong. Please refresh the page and try again", 'neoship') );
        }
        
        $currencies = json_decode($response, true);
        $currencyIdsByCode = [];
        foreach ($currencies as $currency) {
            $currencyIdsByCode[$currency['code']] = $currency['id'];
        }
        return $currencyIdsByCode;
    }

    private function errorMessage($message, $exit = true){
        ?>
            <div class="notice error notice-error is-dismissible">
                <p><?php echo $message ?></p>
            </div>
        <?php
        if($exit){
            exit();
        }
    }

    private function successMessage($message, $exit = true){
        ?>
            <div class="notice updated notice-success is-dismissible">
                <p><?php echo $message ?></p>
            </div>
        <?php
        if($exit){
            exit();
        }
    }

    public function printSticker($template,$referenceNumber){
        if ($this->accessData == false){
            $this->login();
        }

        $data['ref'] = $referenceNumber;
        $data['template'] = $template;
        $data = (object) array_merge((array) $data, (array) $this->accessData);

        $url = NEOSHIP_API_URL . '/package/sticker?' . http_build_query($data);
        curl_setopt($this->curl, CURLOPT_URL, $url);
        $response = curl_exec($this->curl);    
        $this->handlePdf($response);
    }

    public function printAcceptanceProtocol($referenceNumber){
        if ($this->accessData == false){
            $this->login();
        }

        $data['ref'] = $referenceNumber;
        $data = (object) array_merge((array) $data, (array) $this->accessData);

        $url = NEOSHIP_API_URL . '/package/acceptance?' . http_build_query($data);
        curl_setopt($this->curl, CURLOPT_URL, $url);
        $response = curl_exec($this->curl);
        $this->handlePdf($response);
    }

    private function handlePdf($response) {
        if (curl_getinfo($this->curl, CURLINFO_HTTP_CODE) != 200){
            wp_redirect(add_query_arg( array(
				'neoship_error' => '1',
				'error' => __('You are trying Neoship action on orders which are not imported to neoship', 'neoship'),
			), admin_url('edit.php?post_type=shop_order')));
            exit();
        }
        header('Cache-Control: public'); 
        header('Content-type: application/pdf');
        header('Content-Disposition: attachment; filename="acceptance.pdf"');
        header('Content-Length: '.strlen($response));
        echo $response;
        curl_close($response);
        exit();
    }

    public function getParcelShops($all = false){
        $url = NEOSHIP_API_URL . '/public/parcelshop/';
        curl_setopt($this->curl, CURLOPT_URL, $url);
        curl_setopt($this->curl, CURLOPT_RETURNTRANSFER, 1); 
        $response = curl_exec($this->curl);      

        if (curl_getinfo($this->curl, CURLINFO_HTTP_CODE) != 200){
            $this->errorMessage( __('Something is wrong. Please refresh the page and try again', 'neoship') );
        }
        
        $parcelshops = json_decode($response, true);
        /* echo '<pre>';
        var_dump($parcelshops);
        echo '</pre>';
        die(); */
        $parcelShops = [];
        foreach ($parcelshops as $parcelshop) {
            if($all){
                $parcelShops[$parcelshop['id']] = $parcelshop;
            }
            else{
                $parcelShops[$parcelshop['id']] = $parcelshop['address']['city'].', '.$parcelshop['address']['company'];
            }
        }
        return $parcelShops;
    }
}
