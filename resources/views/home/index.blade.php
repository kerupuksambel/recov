@extends('layout.home')

@section('title', 'Home')

@section('css')
	<link rel="stylesheet" href="https://unpkg.com/leaflet@1.7.1/dist/leaflet.css"
	integrity="sha512-xodZBNTC5n17Xt2atTPuE1HxjVMSvLVW9ocqUKLsCC5CXdbqCmblAshOMAS6/keqq/sMZMZ19scR4PsZChSR7A=="
	crossorigin=""/>
@endsection

@section('content')
	<div id="map" style="width: 100%; height: 100%;"></div>
	<div class="modal fade" id="detailModal" tabindex="-1" role="dialog" aria-labelledby="exampleModalLabel"
	aria-hidden="true">
		<div class="modal-dialog" role="document">
			<div class="modal-content">
				<div class="modal-header">
					<h5 class="modal-title" id="modalTitle">Modal title</h5>
					<button type="button" class="close" data-dismiss="modal" aria-label="Close">
						<span aria-hidden="true">&times;</span>
					</button>
				</div>
				<div class="modal-body" id="modalBody">
					
				</div>
				<div class="modal-footer">
					<button type="button" class="btn btn-secondary" data-dismiss="modal">Close</button>
					<button type="button" class="btn btn-primary">Save changes</button>
				</div>
			</div>
		</div>
	</div>
@endsection

@section('js')
	<script src="https://unpkg.com/leaflet@1.6.0/dist/leaflet.js" crossorigin=""></script>
	<script src="js/osmtogeojson.js"></script>
	<script>		
		var userLat, userLon

		$( document ).ready(function() {
			if(navigator.geolocation){
				navigator.geolocation.getCurrentPosition(fromUserLocation, fromDefaultLocation);
			}
		});

		function showDetail(e){
			console.log(e.layer.feature)
			var url = "{{ env('API_URL') }}/place/detail/" + e.layer.feature.id.split('/')[1]
			$.get(url, function(res){
				if(res.isFalse){
					alert("Ada")
				}else{
					var title;
					if(e.layer.feature.properties.name){
						title = e.layer.feature.properties.name
					}else if(e.layer.feature.properties["name:en"]){
						title = e.layer.feature.properties["name:en"]
					}else{
						if(e.layer.feature.properties.amenity == "cafe"){
							title = "Cafe"
						}else{
							title = "Restoran"
						}
					}
					$("#modalTitle").text(title)
					$("#modalBody").html('<li>Amenity : ' + e.layer.feature.properties.amenity + '</li>')
				}
			});
			$('#detailModal').modal({show: true})
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