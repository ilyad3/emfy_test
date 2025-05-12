<?php

class AMO {
    private $AMOINFO;
    function __construct($AMOINFO) {
        $db = $GLOBALS['db'];
       $refresh_time = $AMOINFO['expires_in'];
       $this->amoinfo = $AMOINFO;
       if ($refresh_time > time()) {
            $access_token = $AMOINFO['access_token'];
       }else{
            $refresh_token = $AMOINFO['refresh_token'];
            $link = 'https://' . $AMOINFO['subdomain'] . '.amocrm.ru/oauth2/access_token'; //Формируем URL для запроса
            $subdomain = $AMOINFO['subdomain'];
            /** Соберем данные для запроса */
            $data = [
                'client_id' => $AMOINFO['client_id'],
                'client_secret' => $AMOINFO['client_secret'],
                'grant_type' => 'refresh_token',
                'refresh_token' => $refresh_token,
                'redirect_uri' => $AMOINFO['redirect_uri'],
            ];
            /**
             * Нам необходимо инициировать запрос к серверу.
             * Воспользуемся библиотекой cURL (поставляется в составе PHP).
             * Вы также можете использовать и кроссплатформенную программу cURL, если вы не программируете на PHP.
             */

            $curl = curl_init(); //Сохраняем дескриптор сеанса cURL
            /** Устанавливаем необходимые опции для сеанса cURL  */
            curl_setopt($curl,CURLOPT_RETURNTRANSFER, true);
            curl_setopt($curl,CURLOPT_USERAGENT,'amoCRM-oAuth-client/1.0');
            curl_setopt($curl,CURLOPT_URL, $link);
            curl_setopt($curl,CURLOPT_HTTPHEADER,['Content-Type:application/json']);
            curl_setopt($curl,CURLOPT_HEADER, false);
            curl_setopt($curl,CURLOPT_CUSTOMREQUEST, 'POST');
            curl_setopt($curl,CURLOPT_POSTFIELDS, json_encode($data));
            curl_setopt($curl,CURLOPT_SSL_VERIFYPEER, 1);
            curl_setopt($curl,CURLOPT_SSL_VERIFYHOST, 2);
            $out = curl_exec($curl); //Инициируем запрос к API и сохраняем ответ в переменную
            $code = curl_getinfo($curl, CURLINFO_HTTP_CODE);
            curl_close($curl);
            /** Теперь мы можем обработать ответ, полученный от сервера. Это пример. Вы можете обработать данные своим способом. */
            $code = (int)$code;
            $errors = [
                400 => 'Bad request',
                401 => 'Unauthorized',
                403 => 'Forbidden',
                404 => 'Not found',
                500 => 'Internal server error',
                502 => 'Bad gateway',
                503 => 'Service unavailable',
            ];

            try
            {
                /** Если код ответа не успешный - возвращаем сообщение об ошибке  */
                if ($code < 200 || $code > 204) {
                    throw new Exception(isset($errors[$code]) ? $errors[$code] : 'Undefined error', $code);
                }
            }
            catch(\Exception $e)
            {
                print_r($data);
                echo "<br>";
                print_r($out);
                echo "<br>";
                die('Ошибка АВТОРИЗАЦИИ AMOCRM (AMO CLASS): ' . $e->getMessage() . PHP_EOL . 'Код ошибки: ' . $e->getCode());
            }
            
            /**
             * Данные получаем в формате JSON, поэтому, для получения читаемых данных,
             * нам придётся перевести ответ в формат, понятный PHP
             */
            $response = json_decode($out, true);
            $expires_in = $response['expires_in']+time();
            $access_token = $response['access_token'];
            $refresh_token = $response['refresh_token'];
            
            $AMOINFO['refresh_token'] = $refresh_token;
            $AMOINFO['access_token'] = $access_token;
            $AMOINFO['expires_in'] = $expires_in;
            $file = fopen(__DIR__."/amo.txt","w");
            fwrite($file,json_encode($AMOINFO));
            fclose($file);
            // $db->update('amocrm',$update_values,"subdomain='$subdomain'");
       }
       $this->access_token = $access_token;
   }
   
    /*
    Получение данных по контакту внутри амо
    */   
    
    function GET_CONTACT_INFO($query) {
        $method = "contacts";
        $request = $this->GET_REQUEST($method,'?query='.$query);
        if ($request != 0) {
            $answer = 0;
            for ($i=0;$i<count($request);$i++) {
                $custom_fields = $request[$i]['custom_fields_values'];
                for ($a=0;$a<count($custom_fields);$a++) {
                    if ($custom_fields[$a]['values'][0]['value'] == $query) {
                        $answer = $request[$i]['id'];
                    }
                }
            }
        }else{
            $answer = 0;
        }
        return $answer;
    }
    /*
        Все запросы к апи
    */
    function GET_REQUEST($method,$query = null,$ver="4") {
        $link = 'https://' . $this->amoinfo['subdomain'] . '.amocrm.ru/api/v'.$ver.'/'.$method.$query; //Формируем URL для запроса
        /* Получаем access_token из вашего хранилища */
        /* Формируем заголовки */
        $headers = [
            'Authorization: Bearer ' . $this->access_token
        ];
        /*
         * Нам необходимо инициировать запрос к серверу.
         * Воспользуемся библиотекой cURL (поставляется в составе PHP).
         * Вы также можете использовать и кроссплатформенную программу cURL, если вы не программируете на PHP.
         */
        $curl = curl_init(); //Сохраняем дескриптор сеанса cURL
        /* Устанавливаем необходимые опции для сеанса cURL  */
        curl_setopt($curl,CURLOPT_RETURNTRANSFER, true);
        curl_setopt($curl,CURLOPT_USERAGENT,'amoCRM-oAuth-client/1.0');
        curl_setopt($curl,CURLOPT_URL, $link);
        curl_setopt($curl,CURLOPT_HTTPHEADER, $headers);
        curl_setopt($curl,CURLOPT_HEADER, false);
        curl_setopt($curl,CURLOPT_SSL_VERIFYPEER, 1);
        curl_setopt($curl,CURLOPT_SSL_VERIFYHOST, 2);
        $out = curl_exec($curl); //Инициируем запрос к API и сохраняем ответ в переменную
        $code = curl_getinfo($curl, CURLINFO_HTTP_CODE);
        curl_close($curl);
        /* Теперь мы можем обработать ответ, полученный от сервера. Это пример. Вы можете обработать данные своим способом. */
        $response = json_decode($out, true);
        if (isset($response['_embedded'][$method])) {
            return $response['_embedded'][$method];
        }else{
            return $response;
        }
    }
    
    /*
    Добавления поля к запросу
    */
    
    function ADD_FILED($field,$value,$type,$array = null,$type_value = null) {
        if ($type_value == "int") {
            if ($array == NULL) {
                $array = array (
                    array (
                        $field => (int) $value,
                    ),
                );
            }else{
                $array[0][$field] = $value;
            }
        }else{
            if ($array == NULL) {
                $array = array (
                    array (
                        $field => $value,
                    ),
                );
            }else{
                $array[0][$field] = $value;
            }
        }
        return $array;
    }
    
    /*
    Добавление теги к запросу
    */
    
    function ADD_TAGS($array,$value) {
        $tags_array = 
            array(
                    'name' => $value,
            );
        array_push($array[0]['_embedded']['tags'],$tags_array);
        return $array;
    }
    
    /*
    Добавление кастомных полей к запросу
    */
    
    function ADD_CUSTOM($array,$field,$value,$type,$enum = null) {
        if ($enum == NULL) {
            $custom_array = array(
            'field_id' => $field,
            'values' => array(
                0 => array(
                        'value' => $value,
                    ),
                ),
            );
        }else{
            $custom_array = array(
            'field_id' => $field,
            'values' => array(
                0 => array(
                        'value' => $value,
                        'enum_id' => $enum,
                    ),
                ),
            );
        }
        array_push($array[0]['custom_fields_values'],$custom_array);
        return $array;
    }
    
    /*
    Отправка запросов к api
    */
    
    function POST_REQUEST($array,$method,$method_request = null) {
        if ($method_request == NULL) {
            $method_request = "POST";
        }
        $curl = curl_init();
        
        // print_r("https://".$this->amoinfo['subdomain'].".amocrm.ru/api/v4/".$method);
        curl_setopt_array($curl, array(
          CURLOPT_URL => "https://".$this->amoinfo['subdomain'].".amocrm.ru/api/v4/".$method,
          CURLOPT_RETURNTRANSFER => true,
          CURLOPT_ENCODING => "",
          CURLOPT_MAXREDIRS => 10,
          CURLOPT_TIMEOUT => 0,
          CURLOPT_FOLLOWLOCATION => true,
          CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
          CURLOPT_CUSTOMREQUEST => $method_request,
          CURLOPT_POSTFIELDS =>json_encode($array),
          CURLOPT_HTTPHEADER => array(
            "Authorization: Bearer " . $this->access_token,
            "Content-Type: application/json",
          ),
        ));
        
        $response = curl_exec($curl);
        $response = json_decode($response,TRUE);
        if (isset($response['_embedded'][$method])) {
            return $response['_embedded'][$method][0]['id'];
        }else{
            return $response;
        }
        
    }
}

?>