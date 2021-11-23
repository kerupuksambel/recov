@extends('layout.home')

@section('title', 'Home')

@section('css')
	<link rel="stylesheet" href="https://unpkg.com/leaflet@1.7.1/dist/leaflet.css"
	integrity="sha512-xodZBNTC5n17Xt2atTPuE1HxjVMSvLVW9ocqUKLsCC5CXdbqCmblAshOMAS6/keqq/sMZMZ19scR4PsZChSR7A=="
	crossorigin=""/>
@endsection

@section('content')
	<div id="map" style="width: 600px; height: 400px;"></div>
@endsection

@section('js')
	<script src="https://unpkg.com/leaflet@1.6.0/dist/leaflet.js" integrity="sha512-gZwIG9x3wUXg2hdXF6+rVkLF/0Vi9U8D2Ntg4Ga5I5BZpVkVxlJWbSQtXPSiUTtC0TjtGOmxa1AJPuV0CPthew==" crossorigin=""></script>
	<script src="js/osmtogeojson.js"></script>
	<script src="https://code.jquery.com/jquery-1.12.4.min.js" integrity="sha256-ZosEbRLbNQzLpnKIkEdrPv7lOy9C27hHQ+Xp8a4MxAQ=" crossorigin="anonymous"></script>
	<script>
		var map = L.map('map').setView([-6.1829957,106.8444433], 13);
		L.tileLayer('http://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
			// maxZoom: 18,
			attribution: 'Map data © <a href="http://openstreetmap.org">OpenStreetMap</a>'
		}).addTo(map);
			
		$( document ).ready(function() {
			var overpassApiUrl = 'https://overpass-api.de/api/interpreter?data=[out:xml][bbox][timeout:180];node[amenity=hospital];out;&bbox=106.7828,-6.2237,106.8695,-6.1469';
			$.get(overpassApiUrl, function (osmXml) {
				var OSMGeojson = osmtogeojson(osmXml); 
				var resultLayer = L.geoJson(OSMGeojson).addTo(map).on('click', showDetail);
			});
		});

		function showDetail(e){
			console.log(e)
		}
	</script>
@endsection