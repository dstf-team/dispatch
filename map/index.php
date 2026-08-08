<!DOCTYPE html>
<html>
<head>
    <title>Web GIS</title>

    <link rel="stylesheet"
    href="https://unpkg.com/leaflet/dist/leaflet.css"/>

    <style>

        body{
            margin:0;
        }

        #map {
            height: 100vh;
        }

        #searchBox{
            position:absolute;
            top:10px;
            left:50px;
            z-index:1000;
            padding:10px;
            width:250px;
        }

    </style>
</head>

<body>

<input type="text"
id="searchBox"
placeholder="Cari lokasi...">

<div id="map"></div>

<script src="https://unpkg.com/leaflet/dist/leaflet.js"></script>

<script>

var map = L.map('map').setView([-7.2575,112.7521], 11);

L.tileLayer(
'https://tile.openstreetmap.org/{z}/{x}/{y}.png',
{
    attribution: '&copy; OpenStreetMap'
}).addTo(map);

var geojsonLayer;

fetch('data/Konstruksi1.json')
.then(res => res.json())
.then(data => {

    geojsonLayer = L.geoJSON(data, {

        onEachFeature: function(feature, layer) {

            layer.bindPopup(
                feature.properties.nama
            );

        }

    }).addTo(map);

});


document.getElementById("searchBox")
.addEventListener("keyup", function(){

    var keyword =
    this.value.toLowerCase();

    geojsonLayer.eachLayer(function(layer){

        var nama =
        layer.feature.properties.nama;

        layer.setStyle({
            color:'blue'
        });

        if(nama){

            if(
                nama.toLowerCase()
                .includes(keyword)
            ){

                layer.setStyle({
                    color:'red',
                    weight:4
                });

                map.fitBounds(
                    layer.getBounds()
                );

                layer.openPopup();

            }

        }

    });

});

</script>

</body>
</html>