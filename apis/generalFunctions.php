<?php
    class generalFunctions{

        function convertCharacters($data){
            $data = str_replace("%22", "", $data);
            $data = str_replace("%20", " ", $data);
            $data = str_replace("%C3%A1", "á", $data);
            $data = str_replace("%C3%81", "Á", $data);
            $data = str_replace("%C3%A0", "à", $data);
            $data = str_replace("%C3%80", "À", $data);
            $data = str_replace("%C3%A3", "ã", $data);
            $data = str_replace("%C3%83", "Ã", $data);
            $data = str_replace("%C3%A2", "â", $data);
            $data = str_replace("%C3%82", "Â", $data);
            $data = str_replace("%C3%A9", "é", $data);
            $data = str_replace("%C3%89", "É", $data);
            $data = str_replace("%C3%A8", "è", $data);
            $data = str_replace("%C3%88", "È", $data);
            $data = str_replace("%C3%AA", "ê", $data);
            $data = str_replace("%C3%8A", "Ê", $data);
            $data = str_replace("%C3%AD", "í", $data);
            $data = str_replace("%C3%8D", "Í", $data);
            $data = str_replace("%C3%AC", "ì", $data);
            $data = str_replace("%C3%8C", "Ì", $data);
            $data = str_replace("%C3%B5", "õ", $data);
            $data = str_replace("%C3%95", "Õ", $data);
            $data = str_replace("%C3%B3", "ó", $data);
            $data = str_replace("%C3%93", "Ó", $data);
            $data = str_replace("%C3%B4", "ô", $data);
            $data = str_replace("%C3%94", "Ô", $data);
            $data = str_replace("%C3%BA", "ú", $data);
            $data = str_replace("%C3%9A", "Ú", $data);
            $data = str_replace("%C3%BC", "ü", $data);
            $data = str_replace("%C3%9C", "Ü", $data);
            $data = str_replace("%C3%A7", "ç", $data);
            $data = str_replace("%C3%87", "Ç", $data);
            $data = str_replace("%26", "&", $data);
            $data = str_replace("%3C", "<", $data);
            $data = str_replace("%3E", ">", $data);


            return $data;
        }

        function xApiKeyHeaderInformed($headers) {
            $xApiKeyHeader = false;

            foreach($headers as $key => $value) {
                //x-api-key utilizado em produção e Authorization em homologação
                if($key == 'X-Api-Key' && $value != '' || $key == 'Authorization' && $value != '') {
                    $xApiKeyHeader = true;
                }
            }

            return $xApiKeyHeader;
        }

        function getAccessToken($headers) {
            $accessToken = '';

            foreach($headers as $key => $value) {
                //x-api-key utilizado em produção e Authorization em homologação
                if($key == 'X-Api-Key' && $value != '' || $key == 'Authorization' && $value != '') {

                    $accessToken = $value;
                }
            }

            return $accessToken;
        }

        function convertToMonetary($value) {

            $monetaryValue = '';

            if($value==null) {
                $monetaryValue = 'R$ 0,00';
            } else {
                switch(strlen($value)) {
                    case 1:
                        $monetaryValue = 'R$ '.$value.',00';
                        break;
                    case 2:
                        $monetaryValue = 'R$ '.$value.',00';
                        break;
                    case 3:
                        if(str_contains($value, '.')) {
                            $monetaryValue = 'R$ '.str_replace('.', ',', $value).'0';
                        } else {
                            $monetaryValue = 'R$ '.$value.',00';
                        }
                        break;
                    case 4:
                        if(str_contains($value, '.')) {
                            if(substr($value, 1, 1)=='.') {
                                $monetaryValue = 'R$ '.str_replace('.', ',', $value);
                            } else {
                                $monetaryValue = 'R$ '.str_replace('.', ',', $value).'0';
                            }
                            
                        } else {
                            $monetaryValue = 'R$ '.substr($value, 0, 1).'.'.substr($value, 1, 4).',00';
                        }
                        
                        break;
                    case 5:
                        $monetaryValue = 'R$ '.str_replace('.', ',', $value).'0';
                        break;
                    case 6:
                        $value = str_replace('.', ',', $value);
                        $monetaryValue = 'R$ '.substr($value, 0, 1).'.'.substr($value, 1, 4).substr($value, 5, 6).'0';
                        break;
                    case 7:
                        $value = str_replace('.', ',', $value);
                        $checkValue = explode(',', $value);
                        if(strlen($checkValue[0])==4) {
                            $monetaryValue = 'R$ '.substr($value, 0, 1).'.'.substr($value, 1, 4).substr($value, 5, 7);
                        } else {
                            $monetaryValue = 'R$ '.substr($value, 0, 2).'.'.substr($value, 2, 4).substr($value, 6, 7).'0';
                        }
                        
                        break;
                    case 8:
                        $value = str_replace('.', ',', $value);
                        $checkValue = explode(',', $value);
                        if(strlen($checkValue[0])==5) {
                            $monetaryValue = 'R$ '.substr($value, 0, 2).'.'.substr($value, 2, 8);
                        } else {
                            $monetaryValue = 'R$ '.substr($value, 0, 3).'.'.substr($value, 3, 8).'0';
                        }
                        break;
                    case 9:
                        $monetaryValue = 'R$ '.substr($value, 0, 3).'.'.str_replace('.', ',', substr($value, 3, 9));
                        break;
                }
                
            }

            return $monetaryValue;
        }
    }
?>