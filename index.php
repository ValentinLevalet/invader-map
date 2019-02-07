<!doctype html>
<html>
<head>
    <title>invader map</title>
    <meta charset="utf-8">
    <link rel="stylesheet" href="styles/leaflet.css" />
    <script src="scripts/leaflet-src.js"></script>
    <style type="text/css">
        html, body, #mapid {
            width: 100%;
            height: 100%;
        }
        body {
            margin: 0;
        }
        .leaflet-marker-icon {
            height: 40px;
            width: 40px;
            border: solid 2px white;
            background: white;
            box-shadow: -2px 4px 4px rgba(0,0,0,.25);
            border-radius: 4px;
            transition: all .15s ease;
        }
        .leaflet-marker-icon:hover{
            height: 80px;
            width: 80px;
        }
    </style>

    <script type="text/javascript">
    shutterOnload = function(){shutterReloaded.Init();}
    </script>
    <script src="scripts/shutter.min.js" type="text/javascript"></script>
    <link rel="stylesheet" href="styles/shutter.css" type="text/css" media="screen" />
</head>
	<body>
<?php

// convert Exif GPS array https://stackoverflow.com/a/16437888/4264406
function gps($coordinate, $hemisphere) {
  if (is_string($coordinate)) {
    $coordinate = array_map("trim", explode(",", $coordinate));
  }
  for ($i = 0; $i < 3; $i++) {
    $part = explode('/', $coordinate[$i]);
    if (count($part) == 1) {
      $coordinate[$i] = $part[0];
    } else if (count($part) == 2) {
      $coordinate[$i] = floatval($part[0])/floatval($part[1]);
    } else {
      $coordinate[$i] = 0;
    }
  }
  list($degrees, $minutes, $seconds) = $coordinate;
  $sign = ($hemisphere == 'W' || $hemisphere == 'S') ? -1 : 1;
  return $sign * ($degrees + $minutes/60 + $seconds/3600);
}

$files = scandir('images/');

// ignorer dossier thumbs et répertoires .. .
$files = array_slice($files, 2);
$key = array_search('thumbs', $files);
if(($key = array_search('thumbs', $files)) !== false) {
    unset($files[$key]);
}
// var_dump($files);

foreach ($files as $key => $file) {

        // var_dump($key);

        $arr1[] = $file;


        $data = exif_read_data('images/'.$file);
        $search = array_key_exists('GPSLatitude', $data);
        // var_dump($data);
        // var_dump($data['GPSLatitude']);
        // var_dump($data['GPSLongitude']);

        // var_dump($search);
        // print_r(array_keys($data));
        // var_dump($data);
        /*while($data_name = current($data)) {
            if($data_name == 'GPSLatitude') {
                echo key($data).'<br>';
            }
            next($data);
        }*/

        $latitude = gps($data["GPSLatitude"], $data['GPSLatitudeRef']);
        $arrLat[] = $latitude;
        $longitude = gps($data["GPSLongitude"], $data['GPSLongitudeRef']);
        $arrLong[] = $longitude;

        $multArray[] = array($file,$latitude,$longitude);

        // var_dump($latitude);
        // var_dump($longitude);

}

// var_dump($arr1);
// var_dump($multArray);
// var_dump($arrLat);
// var_dump($arrLong);

?>
    <div id="mapid"></div>

<script type="text/javascript">
    /*var latitude = "<?php echo $latitude;?>";
    var longitude = "<?php echo $longitude;?>";
    var arr1 = '<?php echo json_encode($arr1);?>';
    var arrLat = '<?php echo json_encode($arrLat);?>';
    var arrLong = '<?php echo json_encode($arrLong);?>';*/
    var multArray = '<?php echo json_encode($multArray);?>';
    var data = JSON.parse(multArray);

    var arrLatarray = [];

    /*for(var i in arrLat) {
        if(arrLat.hasOwnProperty(i) && !isNaN(+i)){
            arrLatarray[+i] = arrLat[i];
        }
    }*/

    // console.log(arr1);
    // console.log(arrLatarray);
    // console.log(arrLat);
    // console.log(arrLong);

    var mymap = L.map('mapid').setView([51.505, -0.09], 13);

    L.tileLayer('https://api.tiles.mapbox.com/v4/{id}/{z}/{x}/{y}.png?access_token=pk.eyJ1IjoibWFwYm94IiwiYSI6ImNpejY4NXVycTA2emYycXBndHRqcmZ3N3gifQ.rJcFIG214AriISLbB6B5aw', {
        maxZoom: 18,
        attribution: 'Map data &copy; <a href="https://www.openstreetmap.org/">OpenStreetMap</a> contributors, ' +
            '<a href="https://creativecommons.org/licenses/by-sa/2.0/">CC-BY-SA</a>, ' +
            'Imagery © <a href="https://www.mapbox.com/">Mapbox</a>',
        id: 'mapbox.streets'
    }).addTo(mymap);

    // L.marker([51.5, -0.09]).addTo(mymap)
    //     .bindPopup("<b>Hello world!</b><br />I am a popup.").openPopup();

    // for (var i = 0; i < arr1.length; i++) {
    //     L.marker([latitude, longitude]).addTo(mymap).bindPopup("<b>Hello world!</b><br />I am a popup.<img src='images/thumbs/image-thumb.jpg' >").openPopup();
    // }

    var bounds = [];
    var markerCarte = document.getElementsByClassName('leaflet-marker-icon');

    for (var i = 0; i < data.length; i++) {
        // console.log(data[i])
        var latLong = [data[i][1], data[i][2]];
        bounds.push(latLong)
        var imgIcon = './images/thumbs/'+data[i][0];

        var customIcon = L.icon({ iconUrl: imgIcon });

        // console.log(imgIcon);
        // L.marker(latLong,{iconUrl: imgIcon}).addTo(mymap).bindPopup(L.popup({autoClose:false}).setContent("<!--<b>Hello world!</b><br />I am a popup.--><img src='images/thumbs/"+(data[i][0]).replace(".jpg","")+"-thumb.jpg' width='40' height='40'>")).openPopup();
        L.marker(latLong,{icon: customIcon,riseOnHover: true}).addTo(mymap);/*.bindPopup(L.popup({autoClose:false}).setContent("<!--<b>Hello world!</b><br />I am a popup.--><img src='images/thumbs/"+(data[i][0]).replace(".jpg","")+"-thumb.jpg' width='40' height='40'>")).openPopup();*/

        // wrap img with a href element https://plainjs.com/javascript/manipulation/wrap-an-html-structure-around-an-element-28/
        var wrapper = document.createElement('a');
        var cheminImg = './images/'+data[i][0];
        wrapper.setAttribute('href',cheminImg);
        wrapper.setAttribute('target','_blank');
        markerCarte[i].parentNode.insertBefore(wrapper, markerCarte[i]);
        wrapper.appendChild(markerCarte[i]);
    }

    // console.log(markerCarte);
    /*for (var i = 0; i < markerCarte.length; i++) {
    }*/

    mymap.fitBounds(bounds);

    // L.marker([latitude, longitude]).addTo(mymap)
    //     .bindPopup("<b>Hello world!</b><br />I am a popup.<img src='image-thumb.jpg' >").openPopup();

    /*L.circle([51.508, -0.11], 500, {
        color: 'red',
        fillColor: '#f03',
        fillOpacity: 0.5
    }).addTo(mymap).bindPopup("I am a circle.");

    L.polygon([
        [51.509, -0.08],
        [51.503, -0.06],
        [51.51, -0.047]
    ]).addTo(mymap).bindPopup("I am a polygon.");


    var popup = L.popup();

    function onMapClick(e) {
        popup
            .setLatLng(e.latlng)
            .setContent("You clicked the map at " + e.latlng.toString())
            .openOn(mymap);
    }

    mymap.on('click', onMapClick);*/


</script>

	</body>
</html>