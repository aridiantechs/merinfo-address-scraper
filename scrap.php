<?php

ini_set('display_errors', 1);
ini_set('max_execution_time', 0);
error_reporting(E_ALL);

include_once('simple_html_dom.php');
require_once (__DIR__ . '/vendor/autoload.php');
use Rct567\DomQuery\DomQuery;
use HeadlessChromium\BrowserFactory;


    function get_web_page( $url )
    {
        $user_agent = 'Mozilla/5.0 (Windows NT 6.1; rv:8.0) Gecko/20100101 Firefox/8.0';

        // $url = urlencode('https://www.amazon.com/dp/B00JITDVD2');

        $options = array(
    
            CURLOPT_CUSTOMREQUEST  => "GET",        //set request type post or get
            CURLOPT_POST           => false,        //set to GET
            CURLOPT_USERAGENT      => $user_agent, //set user agent
            CURLOPT_COOKIEFILE     => "cookie.txt", //set cookie file
            CURLOPT_COOKIEJAR      => "cookie.txt", //set cookie jar
            CURLOPT_RETURNTRANSFER => true,     // return web page
            CURLOPT_HEADER         => false,    // don't return headers
            CURLOPT_FOLLOWLOCATION => true,     // follow redirects
            CURLOPT_ENCODING       => "",       // handle all encodings
            CURLOPT_AUTOREFERER    => true,     // set referer on redirect
            CURLOPT_CONNECTTIMEOUT => 120,      // timeout on connect
            CURLOPT_TIMEOUT        => 120,      // timeout on response
            CURLOPT_MAXREDIRS      => 10,       // stop after 10 redirects
            CURLOPT_PROXY          => 'zproxy.lum-superproxy.io',
            CURLOPT_PROXYPORT      => '22225',
            CURLOPT_PROXYUSERPWD   => 'lum-customer-hl_fa848026-zone-daniel_sahlin_zone-country-se:0xwx5ytxlfcc',
            CURLOPT_HTTPPROXYTUNNEL=> 1,
        );
        
        $ch      = curl_init( $url );
        curl_setopt_array( $ch, $options );
        $content = curl_exec( $ch );
        $err     = curl_errno( $ch );
        $errmsg  = curl_error( $ch );
        $header  = curl_getinfo( $ch );
        curl_close( $ch );

        $header['errno']   = $err;
        $header['errmsg']  = $errmsg;
        $header['content'] = $content;
        return $header;
    }



    function getResidenceInfo( $uuid , $csrf )
    {
        $x_csrf = 'x-csrf-token:' . $csrf;

        $user_agent = 'Mozilla/5.0 (Windows NT 6.1; rv:8.0) Gecko/20100101 Firefox/8.0';

        $options = array(
    
            CURLOPT_CUSTOMREQUEST  => "POST",        //set request type post or get
            CURLOPT_POST           => true,        //set to GET
            CURLOPT_USERAGENT      => $user_agent, //set user agent
            CURLOPT_COOKIEFILE     =>"cookie.txt", //set cookie file
            CURLOPT_COOKIEJAR      =>"cookie.txt", //set cookie jar
            CURLOPT_RETURNTRANSFER => true,     // return web page
            CURLOPT_FOLLOWLOCATION => true,     // follow redirects
            CURLOPT_ENCODING       => "",       // handle all encodings
            CURLOPT_AUTOREFERER    => true,     // set referer on redirect
            CURLOPT_CONNECTTIMEOUT => 120,      // timeout on connect
            CURLOPT_TIMEOUT        => 120,      // timeout on response
            CURLOPT_MAXREDIRS      => 10,       // stop after 10 redirects
            CURLOPT_PROXY          => 'zproxy.lum-superproxy.io',
            CURLOPT_PROXYPORT      => '22225',
            CURLOPT_PROXYUSERPWD   => 'lum-customer-hl_fa848026-zone-daniel_sahlin_zone-country-se:0xwx5ytxlfcc',
            CURLOPT_HTTPPROXYTUNNEL=> 1,
            CURLOPT_HTTPHEADER     => array(
                                        'origin: https://www.merinfo.se',
                                        $x_csrf,
                                        'Content-Type: application/json',
                                    ),

        );
        
        $ch = curl_init( 'https://www.merinfo.se/api/v1/people/'.$uuid.'/description' );
        curl_setopt_array( $ch, $options );

        $x_csrf = 'x-csrf-token:' . $csrf;

        $uuid_aaray['uuid'] = $uuid;
        $json_data = json_encode($uuid_aaray);


        curl_setopt($ch, CURLOPT_POSTFIELDS, $json_data);

        // print_r($ch);die();

        $result = curl_exec( $ch );
        $err     = curl_errno( $ch );
        $errmsg  = curl_error( $ch );
        $header  = curl_getinfo( $ch );
        curl_close( $ch );

        $data = json_decode($result, true);

        $final_data = [];
        $final_data['living_type'] = '';
        $final_data['street']     = '';
        $final_data['zip']        = '';
        $final_data['city']       = '';
        $final_data['county']     = '';

        if(is_array($data)){
            if(array_key_exists('data', $data)){
                if(array_key_exists('residence', $data['data']))
                    if(array_key_exists('type', $data['data']['residence']))
                        $final_data['living_type'] = $data['data']['residence']['type'];

                if(array_key_exists('street', $data['data']))
                    $final_data['street'] = $data['data']['street'];

                if(array_key_exists('zip', $data['data']))
                    $final_data['zip'] = $data['data']['zip'];

                if(array_key_exists('city', $data['data']))
                    $final_data['city'] = $data['data']['city'];

                if(array_key_exists('county', $data['data']))
                    $final_data['county'] = $data['data']['county'];


            }
        }

        return $final_data;
        
    }


    function headLessRequest($url){

        $browserCommand = 'google-chrome';

        $browserFactory = new BrowserFactory($browserCommand);
        $browser = $browserFactory->createBrowser([
                    'customFlags' => ['--no-sandbox'],
                ]);

        try {
            // creates a new page and navigate to an url
            $page = $browser->createPage();
            $page->navigate($url)->waitForNavigation();

            return $page->getHtml();
        }
        finally {
            $browser->close();
        }
    }


    function getData($address,$key,$file_name)
    {

        // if($key > 1 && ($key % 20) == 0)
        //     sleep(5);

        $original_address = $address = trim(preg_replace('/\s\s+/', ' ', $address));

        $address = str_replace(' ', '+', urlencode($address));
        
        $url = 'https://www.merinfo.se/search?who='.$address.'=&where=';
        
        $result = get_web_page($url);
        $html   = $result['content'];
        $dom    = str_get_html($html);
        
        $page_links   = [];
        $page_link    = '';
        $living_type  = '';

        if(gettype($dom) !== 'boolean'){

            $found = false;

            foreach($dom->find('.link-primary') as $element){

                $page_link = $element->href;
                
                if($page_link)
                    $found = true;

                break;

            }


            if($found){

                createLog($key,$original_address,$page_link,true);

                $result = get_web_page($page_link);
                $html   = $result['content'];
                $dom    = str_get_html($html);

                if(gettype($dom) == 'boolean'){
                    createLog($key,$original_address,'Second loop error');
                    return;
                }

                $result = '';

                $element = $dom->find('profile-description', 0);

                $uuid = $element->uuid ?? '';

                $element = $dom->find('meta[name="csrf-token"]', 0);

                $csrf = $element->content ?? '';

                if($uuid && $csrf)
                    $result = getResidenceInfo($uuid, $csrf);
                else{

                    echo 'uuid or csrf not found';
                    createLog($key,$original_address,'second loop error');
                    return;
                }


                if (strpos($result['living_type'], 'bostadsrätt') !== false)
                    $living_type = 'bostadsrätt';
                
                else if (strpos($result['living_type'], 'hyresrätt') !== false)
                    $living_type = 'hyresrätt';
                
                else if (strpos($result['living_type'], 'småhus') !== false)
                    $living_type = 'småhus';
                
                else if ($result['living_type'] == '') 
                    $living_type = 'ingen-information';
                
                else{
                    createLog($key,$original_address,'third loop error');
                    return;
                }
                
            }

            else if(!$found){
                handleFailedAddresses($dom, $html, $key, $original_address);
                return;
            }

        }
        else{

            createLog($key,$original_address,'Proxy or Scraper not working');
            sleep(10);
            return;
        
        }

        // Store data
        $living_type = str_replace(' ', '', $living_type);
        
        if($living_type == ''){
            createLog($key,$original_address,'Headless issue');
        }
        else{

            $myfile = fopen('./uploads/'.$file_name.'.txt', "a") or die("Unable to open file!");
            $original_address = str_replace('+', ' ', $original_address );

            $txt = trim($original_address) . "\t" .
                   trim($living_type)      . "\t" .
                   trim($result['street']) . "\t" .
                   trim($result['zip'])    . "\t" .
                   trim($result['city'])   . "\t" .
                   trim($result['county']) . "\t";

            fwrite($myfile, $txt);
            fwrite($myfile, "\n");
            fclose($myfile);
        }

    }

    function createLog($key,$address,$page_link, $address_found = false){
        
        $myfile = fopen('./logs/log.txt', "a") or die("Unable to open file!");

        $txt = $key . ' - ' . $address . ' - ' .  $page_link;

        fwrite($myfile, $txt);
        fwrite($myfile, "\n");
        fclose($myfile);

        // End Log

        if(!$address_found){

            $myfile  = fopen('./logs/failed.txt', "a") or die("Unable to open file!");

            fwrite($myfile, urldecode($address));
            fwrite($myfile, "\n");
            fclose($myfile);

            $myfile  = fopen('./logs/failed-log.txt', "a") or die("Unable to open file!");

            $address = $address . ',';
            fwrite($myfile, $address);
            fwrite($myfile, "\n");
            fclose($myfile);            

        }
    }

    function handleFailedAddresses($dom, $html, $key, $address){

        foreach($dom->find('.h2') as $element){
            
            if($element == '<h2 class="h2"> Ingen träff </h2>'){
                createLog($key,$address,'Address not found',true);
                return;
            }

        }

        $dom = new DomQuery($html);
        if($dom->find('h1') == '<h1 data-translate="turn_on_js" style="color:#bd2426;">Please turn JavaScript on and reload the page.</h1><h1><span data-translate="checking_browser">Checking your browser before accessing</span> merinfo.se.</h1>'){
            
            createLog($key,$address,'Javascript error');
            return;

        }
        else if($dom->find('a') == '<a rel="noopener noreferrer" href="https://www.cloudflare.com/5xx-error-landing/" target="_blank">Cloudflare</a>'){

            createLog($key,$address,'Cloudflare error');

        }
        else{

            createLog($key,$address,'Unknown Error');

        }

    }


    function runFailedNumbers($file_name){

        $failed_addresses = fopen("logs/failed.txt", "r") or die("Unable to open file!");

        $addresses = [];

        while (($line = fgets($failed_addresses)) !== false) {

            $addresses[] = $line;
            
        }

        // print_r($addresses);

        file_put_contents("logs/failed.txt", "");

        foreach(array_unique($addresses) as $key => $address){

            getData($address,$key,$file_name);

        }
    }


    if (1) {
        
        $input_file_name = php_uname('n');
        
        $file_name = "final";

        if($input_file_name == 'DESKTOP-AJFT9FC')
        
            $file_addresses = fopen("source/input-1.txt", "r") or die("Unable to open file!");
        
        else{
        
            $input_file_name = str_replace("scraper", "input", $input_file_name);
            $input_file_name = 'source/' . $input_file_name . '.txt';
            $file_addresses = fopen($input_file_name, "r") or die("Unable to open file!");
        
        }

        $addresses = [];

        while (($line = fgets($file_addresses)) !== false) {
            $addresses[] = $line;
        }

        foreach(array_unique($addresses) as $key => $address){
            getData($address,$key,$file_name);
        }

        createLog(0001, 'loop 1', 'New 1 loop started', true);
        runFailedNumbers($file_name);

    }