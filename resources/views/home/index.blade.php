@extends('layout.home')

@section('title', 'Home')

@section('css')
	<link rel="stylesheet" href="https://unpkg.com/leaflet@1.7.1/dist/leaflet.css"
	integrity="sha512-xodZBNTC5n17Xt2atTPuE1HxjVMSvLVW9ocqUKLsCC5CXdbqCmblAshOMAS6/keqq/sMZMZ19scR4PsZChSR7A=="
	crossorigin=""/>
	<style type="text/css">
		#btnNav{
			position: absolute;
			top: 10px;
			right: 10px;
			width: 50px;
			height: 50px;
			z-index: 9999;
			font-size: 20px;
			padding: 15px 0;
			vertical-align: middle;
			text-align: center;
			display: inline;
		}

		#btnNav:hover{
			cursor: pointer;
		}
	</style>
@endsection

@section('content')
	<div class="container col-md-12 p-0">
		<div id="btnNav" class="btn btn-secondary">
			<i class="fa fa-bars fa-fw"></i>
		</div>
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
					<div class="modal-body">
						<div id="infoBody"></div>
						<div id="modalBody"></div>
					</div>
					<div class="modal-footer">
						<button type="button" class="btn btn-secondary" data-dismiss="modal">Tutup</button>
					</div>
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
			var url = "/api/place/detail/" + e.layer.feature.id.split('/')[1]
			$.get(url, function(res){
				if(res.isFound){
					$("#infoBody").html('<ul><li>Amenity : <b>'+e.layer.feature.properties.amenity +'</b></li><li>Rating : <b>'+res.rating+'</b></li></ul>')
				}else{
					$("#infoBody").html('<ul><li>Amenity : <b>'+e.layer.feature.properties.amenity +'</b></li></ul>')
				}
			});
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
			$("#modalBody").html('<form action="/place/submit/'+ e.layer.feature.id.split('/')[1] +'" method="POST">{{csrf_field()}}<textarea name="komentar" class="form-control"></textarea><input type="number" min="1" max="5" class="form-control" name="rating"></form>')
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

		function urlBuilder(lon, lat, radiusKm, accepted){
			radiusDeg = radiusKm * (1/111);
			// radiusDeg = Math.round((radiusKm * (1/6378) + Number.EPSILON) * 10000) / 10000
			userBox = lon + "," + lat + "," + (lon + radiusDeg) + "," + (lat + radiusDeg)
			filters = ''
			if(accepted.indexOf('cafe') != -1) filters += 'node[amenity=cafe];';
			if(accepted.indexOf('restaurant') != -1) filters += 'node[amenity=restaurant];';
			return 'https://overpass-api.de/api/interpreter?data=[out:xml][bbox][timeout:180];('+ filters +');out;&bbox=' + userBox;
		}

		function getData(lon, lat){
			var map = L.map('map').setView([lat + 0.06,lon + 0.06], 13);
			L.tileLayer('http://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
				zoomControl: true,
				attribution: 'Map data © <a href="http://openstreetmap.org">OpenStreetMap</a>'
			}).addTo(map);
			
			var userBox = lon + "," + lat + "," + (lon + 0.2) + "," + (lat + 0.1)

			var overpassApiUrl = urlBuilder(lon, lat, 50, ['cafe', 'restaurant']);
			console.log(overpassApiUrl)
			
			const iconCafe = L.icon({
				iconUrl: "/img/icon/cafe.png",
				iconSize: [32, 32],
			});

			const iconResto = L.icon({
				iconUrl: "/img/icon/restaurant.png",
				iconSize: [32, 32],
			});
			
			$.get(overpassApiUrl, function (osmXml) {
				var OSMGeojson = osmtogeojson(osmXml);
				console.log(OSMGeojson) 
				var resultLayer = L.geoJson(OSMGeojson, {
					pointToLayer: function(feature, latlng){
						var marker;
						if(feature.properties.amenity == "cafe"){
							marker = L.marker(latlng, {icon: iconCafe})
						}else{
							marker = L.marker(latlng, {icon: iconResto})
						}

						return marker.bindTooltip(feature.properties.name ? feature.properties.name : feature.properties["name:en"])
					}					
				}).addTo(map).on('click', showDetail);
			});
		}
	</script>
@endsection