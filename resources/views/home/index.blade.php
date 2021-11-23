@extends('layout.home')

@section('title', 'Home')

@section('css')
	<link rel="stylesheet" href="https://unpkg.com/leaflet@1.7.1/dist/leaflet.css"
	integrity="sha512-xodZBNTC5n17Xt2atTPuE1HxjVMSvLVW9ocqUKLsCC5CXdbqCmblAshOMAS6/keqq/sMZMZ19scR4PsZChSR7A=="
	crossorigin=""/>
@endsection

@section('content')
	<div id="map" style="width: 100%; height: 100%;"></div>
@endsection

@section('js')
	<script src="https://unpkg.com/leaflet@1.6.0/dist/leaflet.js" integrity="sha512-gZwIG9x3wUXg2hdXF6+rVkLF/0Vi9U8D2Ntg4Ga5I5BZpVkVxlJWbSQtXPSiUTtC0TjtGOmxa1AJPuV0CPthew==" crossorigin=""></script>
	<script src="js/osmtogeojson.js"></script>
	<script src="https://code.jquery.com/jquery-1.12.4.min.js" integrity="sha256-ZosEbRLbNQzLpnKIkEdrPv7lOy9C27hHQ+Xp8a4MxAQ=" crossorigin="anonymous"></script>
	<script>		
		var userLat, userLon

		$( document ).ready(function() {
			if(navigator.geolocation){
				navigator.geolocation.getCurrentPosition(fromUserLocation, fromDefaultLocation);
			}
		});

		function showDetail(e){
			console.log(e.layer.feature)
		}

		function fromUserLocation(location){
			userLon = location.coords.longitude - 0.1;
			userLat = location.coords.latitude - 0.05;

			getData(userLon, userLat);
		}

		function fromDefaultLocation(location){
			userLon = 106.7828;
			userLat = -6.2237;

			getData(userLon, userLat);
		}

		function getData(lon, lat){
			var map = L.map('map').setView([lat + 0.06,lon + 0.06], 13);
			L.tileLayer('http://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
				// maxZoom: 18,
				attribution: 'Map data © <a href="http://openstreetmap.org">OpenStreetMap</a>'
			}).addTo(map);
			
			var userBox = lon + "," + lat + "," + (lon + 0.2) + "," + (lat + 0.1)

			var overpassApiUrl = 'https://overpass-api.de/api/interpreter?data=[out:xml][bbox][timeout:180];node[amenity=cafe];out;&bbox=' + userBox;
			console.log(overpassApiUrl)
			$.get(overpassApiUrl, function (osmXml) {
				var OSMGeojson = osmtogeojson(osmXml);
				console.log(OSMGeojson) 
				var resultLayer = L.geoJson(OSMGeojson).addTo(map).on('click', showDetail);
			});
		}
	</script>
@endsection