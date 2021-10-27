<?php

ini_set('display_errors', 1);
ini_set('max_execution_time', 0);
error_reporting(E_ALL);

include_once('simple_html_dom.php');
require_once (__DIR__ . '/vendor/autoload.php');
use Rct567\DomQuery\DomQuery;


// $servername = "localhost";
// $username = "aridtlpn_kundkontakter_user";
// $password = "A)Ro#7Ups_ZN";
// $dbname = "aridtlpn_kundkontakter";

// Create connection
// $conn = new mysqli($servername, $username, $password, $dbname);
// // Check connection
// if ($conn->connect_error) {
//   die("Connection failed: " . $conn->connect_error);
// }


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
            CURLOPT_PROXYUSERPWD   => 'lum-customer-hl_fa848026-zone-daniel_sahlin_zone:0xwx5ytxlfcc',
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

    function getData($number,$key,$file_name)
    {    

        if($key > 1 && ($key % 20) == 0)
            sleep(5);

        
        $url = 'https://www.merinfo.se/search?who=0'.$number.'&where=';
            
        $result = get_web_page($url);

        $html   = $result['content'];
        
        $dom    = str_get_html($html);
        
        $page_links = [];
        $page_link  = '';

        // print_r($page_links);die();
        if(gettype($dom) !== 'boolean'){
            
            $found = false;
            
            foreach($dom->find('.link-primary') as $element){
                $page_link = $page_links[] = $element->href;
                $found = true;
            }

            if($found)
                createLog($key,$number,$page_link,true);

            else if(!$found){

                foreach($dom->find('.h2') as $element){
                    if($element == '<h2 class="h2"> Ingen träff </h2>'){
                        createLog($key,$number,'Number not found',true);
                        return;
                    }
                }

                $dom = new DomQuery($html);
                if($dom->find('h1') == '<h1 data-translate="turn_on_js" style="color:#bd2426;">Please turn JavaScript on and reload the page.</h1><h1><span data-translate="checking_browser">Checking your browser before accessing</span> merinfo.se.</h1>'){
                    
                    createLog($key,$number,'Javascript error');
                    return;

                }
                else if($dom->find('a') == '<a rel="noopener noreferrer" href="https://www.cloudflare.com/5xx-error-landing/" target="_blank">Cloudflare</a>'){

                    createLog($key,$number,'Cloudflare error');

                }
                else{
                    createLog($key,$number,'Unknown Error');
                }


            }

        }
        else{
            createLog($key,$number,'Proxy or Scraper not working');
            sleep(15);
        }


        if(!empty($page_links)){

            foreach($dom->find('.btn-primary') as $element){

                if(trim($element->text()) == 'Företag'){
                    
                    $company_name = [];
                    
                    foreach($dom->find('.link-primary') as $element){

                        $company_name[] = $element->text();

                    }

                    $myfile = fopen('./uploads/'.$file_name.'.txt', "a") or die("Unable to open file!");

                    $txt = '0' . $number .' - '. implode( ' | ' ,$company_name) . "\n" ;

                    fwrite($myfile, $txt);

                    fclose($myfile);
                }
                
            }
        }
        
    }

    function createLog($key,$number,$page_link, $number_found = false){
        
        $myfile = fopen('./logs/log.txt', "a") or die("Unable to open file!");

        $txt = $key . ' - ' . $number . ' - ' .  $page_link;

        fwrite($myfile, $txt);

        fwrite($myfile, "\n");

        fclose($myfile);

        // End Log

        if(!$number_found){

            $myfile = fopen('./logs/failed.txt', "a") or die("Unable to open file!");
            $number = $number;
            fwrite($myfile, $number);
            fwrite($myfile, "\n");
            fclose($myfile);

            $myfile = fopen('./logs/failed-log.txt', "a") or die("Unable to open file!");
            $number = $number . ',';
            fwrite($myfile, $number);
            fwrite($myfile, "\n");
            fclose($myfile);            

        }
    }

    function handleFailedNumbers($file_name){

        $failed_numbers = fopen("logs/failed.txt", "r") or die("Unable to open file!");

        $numbers = array();

        while ( ($line = fgets($failed_numbers)) !== false) {

            $numbers[] = (int)$line;
            
        }

        file_put_contents("logs/failed.txt", "");

        foreach($numbers as $key => $number){

            getData($number,$key,$file_name);

        }
    }


    if (1) {
        
        $errors = array();

        $numbers = [700009302,
700009510,
700011041,
700011501,
700011527,
700011859,
700017046,
700017466,
700017777,
700017969,
793495161,
793496136,
799796665];

        $file_name = date("Y-m-d-h-i-sa");

        $file = fopen('uploads/'.$file_name.'.txt', "w");

        fclose($file);

        foreach($numbers as $key => $number){

            getData($number,$key,$file_name);

        }


        handleFailedNumbers($file_name);
        handleFailedNumbers($file_name);
        handleFailedNumbers($file_name);

        

        // echo 'Finsihed';
    }