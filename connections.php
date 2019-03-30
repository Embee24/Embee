<?php

define("DB_HOST", "");
define("DB_USERNAME", "");
define("DB_PASSWORD", "");
define("DB_DATABASE", "");

define("BATTUTA_KEY", '');
$google_api_key_from_cmd = "";
define("GOOGLE_PLACES_API", $google_api_key_from_cmd);



function conn()
{
    $servername     = DB_HOST;
    $username       = DB_USERNAME;
    $password       = DB_PASSWORD;
    $dbname         = DB_DATABASE;

    // Create connection
    $conn = mysqli_connect($servername, $username, $password, $dbname);

    /* change character set to utf8 */
    if (!mysqli_set_charset($conn, "utf8")) {
        printf("Error loading character set utf8: %s\n", mysqli_error($conn));
        exit();
    } else {
        printf("Current character set: %s\n", mysqli_character_set_name($conn));
    }


    // Check connection
    if (!$conn)
    {
        die("Connection failed: " . mysqli_connect_error());
    }
    return $conn;
}

function getNow($country_capital)
{
    $place          = "embassy in ". $country_capital;
    $place          = str_replace(' ','+',$place);
    return curlApi("https://maps.googleapis.com/maps/api/place/textsearch/json?query={$place}&key=".GOOGLE_PLACES_API);
}

function selectWhere($table, $column=null, $value=null, $orderBy=null, $orderValue = "DESC")
{
    $conn = conn();

    if ($column == null)
    {
        if($orderBy != null)
        {
            $sql    = "SELECT * FROM `".$table."` ORDER BY {$orderBy} {$orderValue}";
        }
        else
        {
            $sql    = "SELECT * FROM `".$table."`";
        }
    }
    else
    {
        if($orderBy != null)
        {
            $sql    = "SELECT * FROM `".$table."` WHERE `".$column."` = '".$value."'  ORDER BY {$orderBy} {$orderValue}";
        }
        else
        {
            $sql    = "SELECT * FROM `".$table."` WHERE `".$column."` = '".$value."'";
        }
    }

    $result     = mysqli_query($conn, $sql);

    if (mysqli_num_rows($result) > 0)
    {
        if (mysqli_num_rows($result) == 1)
        {
            $result         = $result->fetch_assoc();
            return $result;
        }
        else
        {
            // output data of each row
            while($data = $result->fetch_assoc())  // foreach continents perform the function below
            {
                $return[] = $data;
            }
            return $return;
        }

        // return $result->fetch_assoc();
    }
    else
    {
        return ["No records found"];
    }
}

function selectSql($sql)
{
    $conn       = conn();
    $result     = mysqli_query($conn, $sql);

    if(!is_bool($result))
    {
        if (mysqli_num_rows($result) > 0)
        {
            if (mysqli_num_rows($result) == 1)
            {
                $result         = $result->fetch_assoc();
                return $result;
            }
            else
            {
                $return     = [];
                // output data of each row
                while($data = $result->fetch_assoc())  // foreach results, push them to the return array
                {
                    $return[] = $data;
                }
                return $return;
            }

        }
        else
        {
            return false;
            // returns false if no data was found
        }
    }
    elseif($result == true)
    {
        return true;
    }
    else
    {
        var_dump( $result,$sql,"There's an issue with your Sql query " ,  mysqli_error($conn));

    }
}

function multiSql($sql)
{
    $conn       = conn();
    $result     = mysqli_query($conn, $sql);

    if(!is_bool($result))
    {
        if (mysqli_num_rows($result) > 0)
        {
            if (mysqli_num_rows($result) == 1)
            {
                $return         = [];
                $data           = $result->fetch_assoc();
                $return[]       = $data;
                return $return;
            }
            else
            {
                $return     = [];
                // output data of each row
                while($data = $result->fetch_assoc())  // foreach results, push them to the return array
                {
                    $return[] = $data;
                }
                return $return;
            }

        }
        else
        {
            return false;
            // returns false if no data was found
        }
    }
    elseif($result == true)
    {
        return true;
    }
    else
    {
        var_dump($result);
        //die($sql);
    }
}

function sqlInsert($sql)
{
    $conn       = conn();

    if (mysqli_query($conn, $sql))
    {
        return true;
    }
    else
    {
        return false;
    }
}

function curlApi($url)
{
    $ch = curl_init();

    //Set the URL that you want to GET by using the CURLOPT_URL option.
    curl_setopt($ch, CURLOPT_URL, $url);

    //Set CURLOPT_RETURNTRANSFER so that the content is returned as a variable.
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);

    //Set CURLOPT_FOLLOWLOCATION to true to follow redirects.
    curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true);

    //Execute the request.
    $data = curl_exec($ch);

    //Close the cURL handle.
    curl_close($ch);

    //Print the data out onto the page.
    if ($data)
    {
        return $data;
    }
    else
    {
        return "Internet connection error: could not connect to api";
    }
}

function postCurl($url,$postDatas)
{
    // Get cURL resource
    $curl = curl_init();

    // Set some options - we are passing in a useragent too here
    curl_setopt_array($curl, array(
        CURLOPT_RETURNTRANSFER => 1,
        CURLOPT_URL => "{$url}",
        CURLOPT_USERAGENT => 'HNG Content',
        CURLOPT_POST => 1,
        CURLOPT_POSTFIELDS => $postDatas
    ));

    // Send the request & save response to $resp
    $response = curl_exec($curl);

    // Close request to clear up some resources
    curl_close($curl);

    if($response)
    {
        return $response;
    }
    else
    {
        return "Internet connection error: could not connect to api";
    }
}

function callBattutaApi($request)
{
    return curlApi('http://battuta.medunes.net/api/'.$request.'/?key='.BATTUTA_KEY);
}

function statesApi($country_code)
{
    return curlApi('http://battuta.medunes.net/api/region/'.$country_code.'/all/?key='.BATTUTA_KEY);
}

function citiesApi($country_code,$state_name)
{
    $state_name  = str_replace(' ','+',$state_name);
    return curlApi('http://battuta.medunes.net/api/city/'.$country_code.'/search/?region='.$state_name.'&key='.BATTUTA_KEY);
}

function newStates($array,$state_name)
{
    foreach($array as $key => $arr)
    {
        if(array_search($state_name,$arr))
        {
            return array_slice($array,$key);
        }
    }
}

function placeGps($place)
{
    $place  = str_replace(' ','+',$place);
    return curlApi('https://maps.googleapis.com/maps/api/geocode/json?address='.$place.'&key='.GOOGLE_PLACES_API);
}

function getPois($category,$city_name)
{
    $place          = $category ." in ". $city_name;
    $place          = str_replace(' ','+',$place);

    return curlApi("https://maps.googleapis.com/maps/api/place/textsearch/json?query={$place}&key=".GOOGLE_PLACES_API);
}

function getPictures($photo_reference,$width)
{
    return curlApi("https://maps.googleapis.com/maps/api/place/photo?maxwidth={$width}&photoreference={$photo_reference}&key=".GOOGLE_PLACES_API);
}

function poiDetails($place_id)
{
    return curlApi("https://maps.googleapis.com/maps/api/place/details/json?placeid={$place_id}&key=".GOOGLE_PLACES_API);
}

function nextPageToken($next_page_token)
{
    return curlApi("https://maps.googleapis.com/maps/api/place/nearbysearch/json?pagetoken={$next_page_token}&key=".GOOGLE_PLACES_API);
}


function countryCodesAndContinents()
{
    // All countries and their respective continents
    $contries = '{
            "AD": "Europe",
            "AE": "Asia",
            "AF": "Asia",
            "AG": "North America",
            "AI": "North America",
            "AL": "Europe",
            "AM": "Asia",
            "AN": "North America",
            "AO": "Africa",
            "AQ": "Antarctica",
            "AR": "South America",
            "AS": "Australia",
            "AT": "Europe",
            "AU": "Australia",
            "AW": "North America",
            "AZ": "Asia",
            "BA": "Europe",
            "BB": "North America",
            "BD": "Asia",
            "BE": "Europe",
            "BF": "Africa",
            "BG": "Europe",
            "BH": "Asia",
            "BI": "Africa",
            "BJ": "Africa",
            "BM": "North America",
            "BN": "Asia",
            "BO": "South America",
            "BR": "South America",
            "BS": "North America",
            "BT": "Asia",
            "BW": "Africa",
            "BY": "Europe",
            "BZ": "North America",
            "CA": "North America",
            "CC": "Asia",
            "CD": "Africa",
            "CF": "Africa",
            "CG": "Africa",
            "CH": "Europe",
            "CI": "Africa",
            "CK": "Australia",
            "CL": "South America",
            "CM": "Africa",
            "CN": "Asia",
            "CO": "South America",
            "CR": "North America",
            "CU": "North America",
            "CV": "Africa",
            "CX": "Asia",
            "CY": "Asia",
            "CZ": "Europe",
            "DE": "Europe",
            "DJ": "Africa",
            "DK": "Europe",
            "DM": "North America",
            "DO": "North America",
            "DZ": "Africa",
            "EC": "South America",
            "EE": "Europe",
            "EG": "Africa",
            "EH": "Africa",
            "ER": "Africa",
            "ES": "Europe",
            "ET": "Africa",
            "FI": "Europe",
            "FJ": "Australia",
            "FK": "South America",
            "FM": "Australia",
            "FO": "Europe",
            "FR": "Europe",
            "GA": "Africa",
            "GB": "Europe",
            "GD": "North America",
            "GE": "Asia",
            "GF": "South America",
            "GG": "Europe",
            "GH": "Africa",
            "GI": "Europe",
            "GL": "North America",
            "GM": "Africa",
            "GN": "Africa",
            "GP": "North America",
            "GQ": "Africa",
            "GR": "Europe",
            "GS": "Antarctica",
            "GT": "North America",
            "GU": "Australia",
            "GW": "Africa",
            "GY": "South America",
            "HK": "Asia",
            "HN": "North America",
            "HR": "Europe",
            "HT": "North America",
            "HU": "Europe",
            "ID": "Asia",
            "IE": "Europe",
            "IL": "Asia",
            "IM": "Europe",
            "IN": "Asia",
            "IO": "Asia",
            "IQ": "Asia",
            "IR": "Asia",
            "IS": "Europe",
            "IT": "Europe",
            "JE": "Europe",
            "JM": "North America",
            "JO": "Asia",
            "JP": "Asia",
            "KE": "Africa",
            "KG": "Asia",
            "KH": "Asia",
            "KI": "Australia",
            "KM": "Africa",
            "KN": "North America",
            "KP": "Asia",
            "KR": "Asia",
            "KW": "Asia",
            "KY": "North America",
            "KZ": "Asia",
            "LA": "Asia",
            "LB": "Asia",
            "LC": "North America",
            "LI": "Europe",
            "LK": "Asia",
            "LR": "Africa",
            "LS": "Africa",
            "LT": "Europe",
            "LU": "Europe",
            "LV": "Europe",
            "LY": "Africa",
            "MA": "Africa",
            "MC": "Europe",
            "MD": "Europe",
            "ME": "Europe",
            "MG": "Africa",
            "MH": "Australia",
            "MK": "Europe",
            "ML": "Africa",
            "MM": "Asia",
            "MN": "Asia",
            "MO": "Asia",
            "MP": "Australia",
            "MQ": "North America",
            "MR": "Africa",
            "MS": "North America",
            "MT": "Europe",
            "MU": "Africa",
            "MV": "Asia",
            "MW": "Africa",
            "MX": "North America",
            "MY": "Asia",
            "MZ": "Africa",
            "NA": "Africa",
            "NC": "Australia",
            "NE": "Africa",
            "NF": "Australia",
            "NG": "Africa",
            "NI": "North America",
            "NL": "Europe",
            "NO": "Europe",
            "NP": "Asia",
            "NR": "Australia",
            "NU": "Australia",
            "NZ": "Australia",
            "OM": "Asia",
            "PA": "North America",
            "PE": "South America",
            "PF": "Australia",
            "PG": "Australia",
            "PH": "Asia",
            "PK": "Asia",
            "PL": "Europe",
            "PM": "North America",
            "PN": "Australia",
            "PR": "North America",
            "PS": "Asia",
            "PT": "Europe",
            "PW": "Australia",
            "PY": "South America",
            "QA": "Asia",
            "RE": "Africa",
            "RO": "Europe",
            "RS": "Europe",
            "RU": "Europe",
            "RW": "Africa",
            "SA": "Asia",
            "SB": "Australia",
            "SC": "Africa",
            "SD": "Africa",
            "SE": "Europe",
            "SG": "Asia",
            "SH": "Africa",
            "SI": "Europe",
            "SJ": "Europe",
            "SK": "Europe",
            "SL": "Africa",
            "SM": "Europe",
            "SN": "Africa",
            "SO": "Africa",
            "SR": "South America",
            "ST": "Africa",
            "SV": "North America",
            "SY": "Asia",
            "SZ": "Africa",
            "TC": "North America",
            "TD": "Africa",
            "TF": "Antarctica",
            "TG": "Africa",
            "TH": "Asia",
            "TJ": "Asia",
            "TK": "Australia",
            "TM": "Asia",
            "TN": "Africa",
            "TO": "Australia",
            "TR": "Asia",
            "TT": "North America",
            "TV": "Australia",
            "TW": "Asia",
            "TZ": "Africa",
            "UA": "Europe",
            "UG": "Africa",
            "US": "North America",
            "UY": "South America",
            "UZ": "Asia",
            "VC": "North America",
            "VE": "South America",
            "VG": "North America",
            "VI": "North America",
            "VN": "Asia",
            "VU": "Australia",
            "WF": "Australia",
            "WS": "Australia",
            "YE": "Asia",
            "YT": "Africa",
            "ZA": "Africa",
            "ZM": "Africa",
            "ZW": "Africa"
          }';

    return $contries;
}