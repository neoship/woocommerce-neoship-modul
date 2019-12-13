<?php

class NeoshipApi
{
    private $accessData = false;
    private $loginData;

    public function login($test = false){
        $this->loginData = get_option('neoship_login');
        
        if($this->loginData == false) {
            $this->errorMessage( __('Please setup neoship login credentials', 'neohsip'), !$test);
        }

        $url = NEOSHIP_API_URL . '/oauth/v2/token?client_id='.urlencode($this->loginData['clientid']).'&client_secret='.urlencode($this->loginData['clientsecret']).'&grant_type=client_credentials';
        
        $response = wp_remote_get($url);
        if (wp_remote_retrieve_response_code($response) != 200){
            $this->errorMessage( __('Bad login credentials', 'neoship'), !$test );
            return;
        }
        
        $this->accessData = json_decode( wp_remote_retrieve_body($response), true );
        
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
        $response = wp_remote_get($url);
        
        if (wp_remote_retrieve_response_code($response) != 200){
            $this->errorMessage( __("Something is wrong. Please refresh the page and try again") );
        }
        
        $user = json_decode(wp_remote_retrieve_body($response), true);

        $this->loginData['userid'] = $user['id'];
        update_option('neoship_login', $this->loginData);
    }

    public function getUserAddress(){
        if ($this->accessData == false){
            $this->login();
        }

        $url = NEOSHIP_API_URL . '/user/?' . http_build_query($this->accessData);
        $response = wp_remote_get($url);

        if (wp_remote_retrieve_response_code($response) != 200){
            $this->errorMessage( __("Something is wrong. Please refresh the page and try again") );
        }
        
        $user = json_decode(wp_remote_retrieve_body($response), true);

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
        $response = wp_remote_get($url);

        if (wp_remote_retrieve_response_code($response) != 200){
            return 0;
        }
        
        $user = json_decode(wp_remote_retrieve_body($response), true);
        return round($user['kredit'], 2);
    }

    public function getStatesIds(){
        if ($this->accessData == false){
            $this->login();
        }

        $url = NEOSHIP_API_URL . '/state/?' . http_build_query($this->accessData);
        $response = wp_remote_get($url);   

        if (wp_remote_retrieve_response_code($response) != 200){
            $this->errorMessage( __("Something is wrong. Please refresh the page and try again") );
        }
        
        $states = json_decode(wp_remote_retrieve_body($response), true);
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
        $args = [
            'body' => $packages
        ];

        $response = wp_remote_post( $url, $args );

        if (wp_remote_retrieve_response_code($response) != 200){
            $this->errorMessage( __("Something is wrong. Please refresh the page and try again") );
        }
        
        return json_decode(wp_remote_retrieve_body($response), true);
    }

    public function getCurrenciesIds(){
        if ($this->accessData == false){
            $this->login();
        }

        $url = NEOSHIP_API_URL . '/currency/?' . http_build_query($this->accessData);
        $response = wp_remote_get($url);    

        if (wp_remote_retrieve_response_code($response) != 200){
            $this->errorMessage( __("Something is wrong. Please refresh the page and try again", 'neoship') );
        }
        
        $currencies = json_decode(wp_remote_retrieve_body($response), true);
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
        $response = wp_remote_get($url);   
        $this->handlePdf($response);
    }

    public function printAcceptanceProtocol($referenceNumber){
        if ($this->accessData == false){
            $this->login();
        }

        $data['ref'] = $referenceNumber;
        $data = (object) array_merge((array) $data, (array) $this->accessData);

        $url = NEOSHIP_API_URL . '/package/acceptance?' . http_build_query($data);
        $response = wp_remote_get($url);  
        $this->handlePdf($response);
    }

    private function handlePdf($response) {
        if (wp_remote_retrieve_response_code($response) != 200){
            wp_redirect(add_query_arg( array(
				'neoship_error' => '1',
				'error' => __('You are trying Neoship action on orders which are not imported to neoship', 'neoship'),
			), admin_url('edit.php?post_type=shop_order')));
            exit();
        }
        header('Cache-Control: public'); 
        header('Content-type: application/pdf');
        header('Content-Disposition: attachment; filename="acceptance.pdf"');
        header('Content-Length: '.strlen(wp_remote_retrieve_body($response)));
        echo wp_remote_retrieve_body($response);
        exit();
    }

    public function getParcelShops($all = false){
        $url = NEOSHIP_API_URL . '/public/parcelshop/';
        $response = wp_remote_get($url);   

        if (wp_remote_retrieve_response_code($response) != 200){
            $this->errorMessage( __('Something is wrong. Please refresh the page and try again', 'neoship') );
        }
        
        $parcelshops = json_decode(wp_remote_retrieve_body($response), true);

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
