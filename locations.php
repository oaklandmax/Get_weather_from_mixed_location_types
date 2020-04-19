<?
////////////////
// Instructions 
////////////////
/*
Given a string of multiple locations separated by newline characters, 
determine and print out the location that is currently experiencing the hottest temperature by using openweathermap's 
public HTTP API. Locations may be in any of three formats: Lat-Long,  Zip Code, and US City Name.
API Documentation:
https://openweathermap.org/current
API key and Docs: d7457467f2492af08b5aa255be8b2e2e
https://openweathermap.org/appid
Example Input Strings:
"61.210841,-149.888735\nSan Francisco\n33132,us"
*/

/////////////////
// Declare class 
/////////////////

class weatherApp {

    public $sample_location = "61.210841,-149.888735\nSan Francisco,ca,us\n33132,us"; // Anchorage, SF ,Miami
    // private $api_key = 'd7457467f2492af08b5aa255be8b2e2e';
    // public $api_url = 'https://openweathermap.org/appid';
    private $api_url = 'https://api.openweathermap.org/data/2.5/weather?units=imperial&appid=d7457467f2492af08b5aa255be8b2e2e';

    public $temps; // associative array for storing location and temps
    public $messages = array();
    public $temp_lat;
    public $temp_lon;

    public function isCoord($test_coord) {
        // split string at comma
        $val_arr = explode(",", $test_coord);
        // try to cast lat and lon into floats
        $lat = (float) $val_arr[0];
        $lon = (float) $val_arr[1];
        // if there are two values, both floats, then this is a coord
        if (count($val_arr) === 2 && $lat && $lon) {
            $this->temp_lat = $lat;
            $this->temp_lon = $lon;
            return true;
        } else {
            return false;
        }
    }

    Public function isZip($value){
        $isZip = false;
        // see if value has ',us' in it
        if (preg_match("/,us/", $value)) {
            // this is prob a 'zip,country' value. checking further by splitting value up at the comma
            $value_arr = explode(",", $value);
            // if total size is 8, with first value length being 5 and second value being us then this is likely a zip. cast first value to int 
            $zip_int = (int) $value_arr[0]; // can the first element be cast to a 5 length int
            if (strlen($value) === 8 && strlen($value_arr[0]) === 5 && $value_arr[1] === 'us' && $zip_int) {
                $isZip = true;
            } else {
                $isZip = false;
            }
        }
        return $isZip;
    }

    public function setTempsFromLocations($input_location_string) {
        // split input string by newline \n
        $location_arr = explode("\n", $input_location_string);
        // find locations that are zipcodes
        foreach( $location_arr as $key=>$value) {
            if ($this->isZip($value)){
                $this->temps[$value] = $this->getWeatherFromZip($value);
                array_push($this->messages, $value . ' is a zip code.');
            } 
            elseif ($this->isCoord($value)) {
                $this->temps[$value] = $this->getWeatherFromCoord($this->temp_lat, $this->temp_lon);
                array_push($this->messages, $value . ' are coordinates.');
            } 
            else { // warning: this will greedily show zip,country temps as well. thats why its last.
                //$value_arr = explode(",", $value);
                $this->temps[$value] = $this->getWeatherFromCity($value);
                array_push($this->messages, $value . ' is a city.');
            }
        }
        return;
    }
    public function getTempsFromLocations() {
        arsort($this->temps); // sort reverse by value temperature
        // echo var_dump($this->temps);
        // echo "temps: " . var_dump($this->temps);
        // $hottest = array_slice($this->temps, 0, 1, true); // just show the highest temp and location.
        // return var_dump($hottest);
        return($this->temps);
    }

    // these functions could be condenced as they are identical except for the $url. This is easier to read though..

    public function getWeatherFromZip($zip_country){
        $url = $this->api_url . '&zip=' . $zip_country;
        // echo '<br>' . $url . '<br>';
        $result = file_get_contents($url);
        $result = json_decode($result);
        // get just the temp
        $output = $result->main->temp . ' (' . $result->name . ')';
        return($output);
    }

    public function getWeatherFromCoord($lat, $lon){
        $url = $this->api_url . '&lat=' . $lat . '&lon=' . $lon;
        $result = file_get_contents($url);
        $result = json_decode($result);
        // get just the temp
        $output = $result->main->temp . ' (' . $result->name . ')';
        return($output);
    }

    public function getWeatherFromCity($city){
        // api.openweathermap.org/data/2.5/weather?q={city name}&appid=' . $this->api_key;
        // api.openweathermap.org/data/2.5/weather?q={city name},{state}&appid=' . $this->api_key;
        // api.openweathermap.org/data/2.5/weather?q={city name},{state},{country code}&appid=' . $this->api_key;
        $url = $this->api_url . '&q=' . $city;
        $result = file_get_contents($url);
        $result = json_decode($result);
        // get just the temp
        $output = $result->main->temp . ' (' . $result->name . ')';
        return($output);
    }

    public function getUserInput() {
        return '
        <form method="post" action="">
            <label for="location">Location: </label>
            <input type="text" id="location" name="location">
            <input type="submit" value="Submit">
            <button type="submit" name="default" id="default" value="default">Use Sample Locations</button>
        </form>
        ';
    }

}


///////////////////////////////////////
// Instantiate object, run the program
///////////////////////////////////////

// we could have made this all a method, called it an just printed the output, but we are trying to keep the business logic
// and presentation separate. 

print("<p>Get Highest Temp</p>");
?>

<div>
    <pre>
    Given a string of multiple locations separated by newline characters, 
    determine and print out the location that is currently experiencing the hottest temperature by using openweathermap's 
    public HTTP API. Locations may be in any of three formats: Lat-Long,  Zip Code, and US City Name.
    Example Input Strings:
    "61.210841,-149.888735\nSan Francisco\n33132,us"
    API Documentation:
    https://openweathermap.org/current
    https://openweathermap.org/appid
    </pre>
</div>

<?

$w = new weatherApp;

if( $_POST['location'] ) {
    $w->setTempsFromLocations($_POST['location']);
    echo '<a href="">Try Again</a>';
} else if ( $_POST['default'] ) {
    $w->setTempsFromLocations($w->sample_location);
    echo '<a href="">Try Again</a>';
} else {
    echo $w->getUserInput();
}


print('<p>Sample Locations:</p>');
print('<pre>' . $w->sample_location . '</pre>');


print('<p>Returned Values:</p><pre>');

// $w->setTempsFromLocations($w->sample_location);
$temp_arr = $w->getTempsFromLocations();

// print which values are zipcode, lat-lon, or city name
foreach( $w->messages as $key=>$val){
    print('<br>' . $val);
}
print('<br><br>');

// print all temp and locations
var_dump($temp_arr);
print('<br></pre>');

// print hottest location and temp
$hottest = array_slice($temp_arr, 0, 1, true); // just show the highest temp and location.
foreach( $hottest as $key=>$val){
    print('<p>Hottest temp: ' . $key . ': ' . $val . '&deg;f</p>');
}
