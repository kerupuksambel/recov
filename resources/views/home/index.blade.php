@extends('layout.home')

@section('title', 'Home')

@section('css')
	<link rel="stylesheet" href="https://unpkg.com/leaflet@1.7.1/dist/leaflet.css"
	integrity="sha512-xodZBNTC5n17Xt2atTPuE1HxjVMSvLVW9ocqUKLsCC5CXdbqCmblAshOMAS6/keqq/sMZMZ19scR4PsZChSR7A=="
	crossorigin=""/>
	<style type="text/css">
		#navBtn{
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
			background-color: rgba(50,50,50,0.8);
			color: #fff
		}

		#addBtn{
			position: absolute;
			top: 70px;
			right: 10px;
			width: 50px;
			height: 50px;
			z-index: 9999;
			font-size: 20px;
			padding: 15px 0;
            vertical-align: middle;
            text-align: center;
            display: inline;
            background-color: rgba(40,167,69, 0.8);
            color: #fff
		}

        .btnAdd{
        }

        .btnStop{
            background-color: rgba(217, 83, 79, 0.8) !important;
            color: #fff
        }

		#navMenu{
			position: absolute;
			top: 10px;
			right: 70px;
			z-index: 9999;
			padding: 10px;
			background-color: rgba(255, 255, 255, 0.9);
		}

		#navBtn:hover{
			cursor: pointer;
		}
	</style>
@endsection

@section('content')
	<div class="container col-md-12 p-0">
		<div id="navBtn" class="btn">
			<i class="fa fa-bars fa-fw"></i>
		</div>
		<div id="addBtn" class="btn">
			<i class="fa fa-plus fa-fw"></i>
		</div>
		<div id="navMenu">
			<div class="form-group">
				<label for="radius" class="font-weight-bold">Radius (km)</label>
				<div class="" id="radiusVal"></div>
			    <input type="range" id="filterRadius" class="form-control-range" min="1" max="15" value="5" step="0.1">
			</div>
			<div class="form-group">
				<label for="radius" class="font-weight-bold">Tempat</label>
				<div class="row">
					<div class="col-md-6">
						<div class="form-check">
							<input class="form-check-input" type="checkbox" value="1" id="filterRestaurant" checked="checked">
							<label class="form-check-label" for="filterRestaurant">
							  Restoran
							</label>
						  </div>
					</div>
					<div class="col-md-6">
						<div class="form-check">
							<input class="form-check-input" type="checkbox" value="1" id="filterCafe" checked="checked">
							<label class="form-check-label" for="filterCafe">
							  Kafe
							</label>
						  </div>
					</div>
				</div>
			</div>

			<div class="form-group">
				<label for="nama" class="font-weight-bold">Nama</label>
				<input type="text" class="form-control" id="filterName">
			</div>

            <div class="form-group">
				<label for="nama" class="font-weight-bold">Rating</label>
                <div id="ratingVal"></div>
				<input type="number" class="form-control-range" id="filterRating" max="5" min="1" step="0.01">
			</div>

			<button class="btn btn-primary" onclick="updateMap()">Filter</button>
		</div>
		<div id="map" style="width: 100%; height: 100%;"></div>

        {{-- Modal Detail --}}
        <div class="modal fade" id="detailModal" tabindex="-1" role="dialog" aria-labelledby="exampleModalLabel"
		aria-hidden="true">
			<div class="modal-dialog" role="document">
				<div class="modal-content">
					<div class="modal-header">
						<h5 class="modal-title" id="detailTitle">Modal title</h5>
						<button type="button" class="close" data-dismiss="modal" aria-label="Close">
							<span aria-hidden="true">&times;</span>
						</button>
					</div>
					<div class="modal-body">
						<div id="infoBody"></div>
						<div id="modalBody" style="padding-top: 20px"></div>
					</div>
				</div>
			</div>
		</div>

        {{-- Modal Add --}}
        <div class="modal fade" id="addModal" tabindex="-1" role="dialog" aria-labelledby="exampleModalLabel"
		aria-hidden="true">
			<div class="modal-dialog" role="document">
				<div class="modal-content">
					<div class="modal-header">
						<h5 class="modal-title">Add Place</h5>
						<button type="button" class="close" data-dismiss="modal" aria-label="Close">
							<span aria-hidden="true">&times;</span>
						</button>
					</div>
					<div class="modal-body">
                        <form action="/api/place/add/" method="POST">
                            {{ csrf_field() }}
                            <div class="row">
                                <div class="col-md-6 form-group">
                                    <label for="placeLat">Latitude</label>
                                    <input type="text" name="lat" readonly class="form-control" id="placeLat">
                                </div>
                                <div class="col-md-6 form-group">
                                    <label for="placeLong">Longtitude</label>
                                    <input type="text" name="lon" readonly class="form-control" id="placeLong">
                                </div>
                            </div>
                            <div class="row">
                                <div class="col-md-9 form-group">
                                    <label for="placeNama">Nama</label>
                                    <input type="text" name="nama" class="form-control" id="placeNama">
                                </div>
                                <div class="col-md-3 form-group">
                                    <label for="placeType">Tipe</label>
                                    <select name="amenity" class="form-control" id="placeType">
                                        <option value="cafe">Cafe</option>
                                        <option value="restaurant">Restoran</option>
                                    </select>
                                </div>
                            </div>
                            <input type="submit" value="Tambahkan" class="btn btn-primary">
                        </form>
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
		var isNav = false;
        var isAdd = true;
		var markers;
		var map

		$( document ).ready(function() {
			if(navigator.geolocation){
				navigator.geolocation.getCurrentPosition(fromUserLocation, fromDefaultLocation);
			}

			$("#radiusVal").text($("#filterRadius").val() + " km")

			$("#navMenu").hide();
		});

		$("#navBtn").click(function(){
			if(isNav){
				$("#navMenu").hide();
				isNav = false;
			}else{
				$("#navMenu").show();
				isNav = true;
			}
		})

		$("#filterRadius").change(function(){
			$("#radiusVal").text($(this).val() + " km")
		})

		function showDetail(e){
			console.log(e.layer.feature)
			var url = "/api/place/detail/" + e.layer.feature.id.split('/')[1]
			$.get(url, function(res){
				if(res.isFound){
					var komentar = ''
					for (let index = 0; index < res.komentar.length; index++) {
						komentar += '<li>' + res.komentar[index] + '</li>';
					}
					$("#infoBody").html('<div><b>Rating</b><br/> '+res.rating+` / 5</div>
					<b>Komentar Terbaru</b>` + komentar)
				}else{
					$("#infoBody").html(`<div class='text-center'>Belum ada informasi untuk restoran ini.</div>`)
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

			if(e.layer.feature.properties.amenity == "cafe"){
				title += " <div class='badge badge-primary'>Cafe</div>"
			}else{
				title += " <div class='badge badge-danger'>Restoran</div>"
			}

			$("#detailTitle").html(title)
			$("#modalBody").html(`
				<form action="/api/review/submit/`+ e.layer.feature.id.split('/')[1] +`" method="POST">
					{{csrf_field()}}
					<div class="form-group">
						<label class="font-weight-bold">Komentar</label>
						<textarea name="komentar" class="form-control"></textarea>
					</div>
					<div class="form-group">
						<label class="font-weight-bold">Rating (1-5)</label>
						<input type="number" min="1" max="5" class="form-control" name="rating">
					</div>
					<input type="submit" class="btn btn-primary" value="Tambah">
				</form>
				`)
			$('#detailModal').modal({show: true})
		}

		function fromUserLocation(location){
			userLon = location.coords.longitude;
			userLat = location.coords.latitude;

			getData(userLon, userLat);
		}

		function fromDefaultLocation(location){
			userLon = 106.7828;
			userLat = -6.2237;

			getData(userLon, userLat);
		}

		// getData() untuk inisialisasi data
		function getData(lon, lat){
			map = L.map('map').setView([lat,lon], 13);
			L.tileLayer('http://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
				zoomControl: true,
				attribution: 'Map data ?? <a href="http://openstreetmap.org">OpenStreetMap</a>'
			}).addTo(map);

            var overpassApiUrl = '/api/place/all';

			const iconCafe = L.icon({
				iconUrl: "/img/icon/cafe.png",
				iconSize: [32, 32],
			});

			const iconResto = L.icon({
				iconUrl: "/img/icon/restaurant.png",
				iconSize: [32, 32],
			});

			$.get(overpassApiUrl, {
                "lat": lat,
                "lon": lon,
                "radius": 5,
                "amenity": ["restaurant", "cafe"]
            }, function (osmXml) {
				console.log(osmXml)
				var OSMGeojson = osmtogeojson(osmXml);
				markers = L.geoJson(OSMGeojson, {
					pointToLayer: function(feature, latlng){
						var marker;
						if(feature.properties.amenity == "cafe"){
							marker = L.marker(latlng, {icon: iconCafe})
						}else{
							marker = L.marker(latlng, {icon: iconResto})
						}

						return marker.bindTooltip(feature.properties.name ? feature.properties.name : feature.properties["name:en"])
					}
				});

				markers.addTo(map).on('click', showDetail);
			});
		}

		function updateMap(){
			markers.remove()

			var rad = $("#filterRadius").val()
			var place = []

			if($("#filterRestaurant").is(':checked')) place.push("restaurant")
			if($("#filterCafe").is(':checked')) place.push("cafe")

            var overpassApiUrl = '/api/place/all'

			const iconCafe = L.icon({
				iconUrl: "/img/icon/cafe.png",
				iconSize: [32, 32],
			});

			const iconResto = L.icon({
				iconUrl: "/img/icon/restaurant.png",
				iconSize: [32, 32],
			});

			$.get(overpassApiUrl, {
                "lat": userLat,
                "lon": userLon,
                "radius": rad,
                "amenity": place
            }, function (osmXml) {
				var OSMGeojson = osmtogeojson(osmXml);
				var queryName = $("#filterName").val().toLowerCase()
				if(queryName != ""){
					console.log(OSMGeojson.features)
					var newFeatures = OSMGeojson.features
					console.log(newFeatures)
					for (let i = 0; i < newFeatures.length; i++) {
						if(newFeatures[i].properties.name){
							var placeName = newFeatures[i].properties.name.toLowerCase();
							if(!(placeName.includes(queryName))){
								newFeatures.splice(i, 1)
								i--
							}else{
								console.log(placeName)
								console.log(queryName)
							}
						}else{
							// console.log('not found')
							newFeatures.splice(i, 1)
							i--
						}
					}

					OSMGeojson.features = newFeatures
				}

                var queryRating = parseFloat($("#filterRating").val())
                console.log(queryRating)
                if(!isNaN(queryRating)){
					var newFeatures = OSMGeojson.features
					for (let i = 0; i < newFeatures.length; i++) {
						if(newFeatures[i].properties.rating){
							var placeRating = newFeatures[i].properties.rating;
							if(!(placeRating >= queryRating)){
								newFeatures.splice(i, 1)
								i--
							}
						}else{
							newFeatures.splice(i, 1)
							i--
						}
					}

					OSMGeojson.features = newFeatures
				}

                console.log(OSMGeojson)

				markers = L.geoJson(OSMGeojson, {
					pointToLayer: function(feature, latlng){
						var marker;
						if(feature.properties.amenity == "cafe"){
							marker = L.marker(latlng, {icon: iconCafe})
						}else{
							marker = L.marker(latlng, {icon: iconResto})
						}

						return marker.bindTooltip(feature.properties.name ? feature.properties.name : feature.properties["name:en"])
					}
				});

				markers.addTo(map).on('click', showDetail);
			});
		}

        function showAdd(ev){
            var latlng = map.mouseEventToLatLng(ev.originalEvent);
            $('#addModal').modal({show: true});
            $("#placeLat").val(latlng.lat);
            $("#placeLong").val(latlng.lng);
        }

		$("#addBtn").click(function(){
            console.log(isAdd)
            if(isAdd){
                map.on('click', showAdd)
                $("#addBtn").html('<i class="fa fa-times fa-fw"></i>').toggleClass("btnStop")
                isAdd = false
            }else{
                map.off('click', showAdd)
                $("#addBtn").html('<i class="fa fa-plus fa-fw"></i>').toggleClass("btnStop")
                isAdd = true
            }
		});
	</script>
@endsection
