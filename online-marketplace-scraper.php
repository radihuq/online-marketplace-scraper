<?php

require 'simple_html_dom.php';

// //Connect to Database
// $servername = "";
// $username = "";
// $password = "";
// $dbname = "";

// $conn = mysqli_connect($servername, $username, $password, $dbname);

// if (!$conn) {
//     die("Connection failed: " . mysqli_connect_error());
// }

$file = "https://thisopenspace.com/spaces/in/Toronto--ON?price_unit=hour&page=";
$pages = 1; // Maximum number of pages to scrape. Up to 23 pages available as of Nov 14, 2019

$data = array();

for ($x = 1; $x <= $pages; $x++) {
    $html = file_get_html($file . $x);
    
    foreach($html->find('.space-container') as $spacecontainer) {
        $venue = new stdClass();

        //find location
        foreach($spacecontainer->find('.space') as $space) {
            $datalat = 'data-lat';
            $datalng = 'data-lng';
            $venue->lat = $space->$datalat;
            $venue->lng = $space->$datalng;
        }

        //find photo
        foreach($spacecontainer->find('.space-photo') as $spacephoto) {
            $datastyle = 'data-style';
            preg_match("/\(([^\)]*)\)/", $spacephoto->$datastyle, $aMatches);
            $venue->img = $aMatches[1];
        }
        
        foreach($spacecontainer->find('.space-content') as $spacecontent) {
            //find price
            foreach($spacecontent->find('.dollar-amount') as $dollaramount) { 
                    $dollaramountClean = str_replace("$","",$dollaramount->plaintext);
                    $venue->cost = $dollaramountClean;
            }

            //find URL
            foreach($spacecontent->find('a[target=_blank]') as $link) {
                    $linkClean = "https://thisopenspace.com".$link->href;
                    $venue->link = $linkClean;
            }     

            //find sqft
            foreach($spacecontent->find('.gray-dark') as $sqft) {  
                $cleansqft = preg_replace("/[^0-9]/", "", $sqft->plaintext);
                if($cleansqft !== "") {
                    $venue->sqft = $cleansqft;      
                }
            }

            //find venue name
            foreach($spacecontent->find('h4') as $venueName) {
                    $venueNameClean = $venueName->plaintext;
                    $venue->name = $venueNameClean;
            }
        }

        array_push($data, $venue);

        // //Write to Database
        // $stmt = $conn->prepare(INSERT INTO venues (name, lat, lng, img, cost, sqft, link) VALUES (?, ?, ?, ?, ?, ?, ?));
        // $stmt->bind_param("sssssss", $name, $lat, $lng, $img, $cost, $sqft, $link);

        // $name = $venue->name;
        // $lat = $venue->lat;
        // $lng = $venue->lng;
        // $img = $venue->img;
        // $cost = $venue->cost;
        // $sqft = $venue->sqft;
        // $link = $venue->link;

        // $stmt->execute();
    }
} 

// echo json_encode($data);

?>