<template>
    <div>
        <div ref="map_trazo" class="map">
            <div id="legend"></div>
        </div>
        
        <div id="popup" class="ol-popup">
            <a href="#" id="popup-closer" class="ol-popup-closer"></a>
            <div id="popup-content"></div>
        </div>
        
    </div>
  </template>
  
  <script>

  import { fromLonLat, transform } from "ol/proj";
  import Google from 'ol/source/Google.js';
  import Layer from 'ol/layer/WebGLTile.js';
  import 'ol/ol.css';
  import "ol-layerswitcher/dist/ol-layerswitcher.css";
  import Map from 'ol/Map';
  import View from 'ol/View';
  import Group from 'ol/layer/Group';
  import TileLayer from 'ol/layer/Tile';
  import OSM from 'ol/source/OSM';
  import Overlay from 'ol/Overlay';
  import Control from 'ol/control/Control';
  import MousePosition from 'ol/control/MousePosition';
  import ZoomSlider from 'ol/control/ZoomSlider';
  import ZoomToExtent from 'ol/control/ZoomToExtent';
  import ScaleLine from 'ol/control/ScaleLine';
  import Popup from 'ol-popup';
  import ImageWMS from "ol/source/ImageWMS";
  import { Image as ImageLayer, Group as LayerGroup } from "ol/layer";
  import TileWMS from "ol/source/TileWMS";
  import { getRenderPixel } from "ol/render";
  import LayerSwitcher from "ol-layerswitcher";
  
  import {toLonLat} from 'ol/proj.js';
  import {toStringHDMS} from 'ol/coordinate.js';
  
  import point from "ol/geom/Point";
  import Style from "ol/style/Style";
  import Icon from "ol/style/Icon";
  
  import GeoJSON from 'ol/format/GeoJSON.js';
  
  import MultiPoint from 'ol/geom/MultiPoint.js';
  import VectorLayer from 'ol/layer/Vector.js';
  import VectorSource from 'ol/source/Vector.js';
  
  import {Circle as CircleStyle, Fill, Stroke} from 'ol/style.js';

  import Feature from 'ol/Feature';
  import Polygon from 'ol/geom/Polygon';

  import XYZ from 'ol/source/XYZ.js';

  import L from 'leaflet';
  import 'leaflet/dist/leaflet.css';
  import '@/Leaflet.GoogleMutant.js';
  import { combineEventUis } from "@fullcalendar/core/internal";

  import { Loader } from '@googlemaps/js-api-loader'
  const GOOGLE_MAPS_API_KEY = 'AIzaSyAXBoNRK0ADS3c8pzKL8nbfAaKUsRj6C-k'
  
  //import LayerSwitcher from 'ol-ext/control/LayerSwitcher';
  
  export default {
    data() {
        return {
            loader: new Loader({ apiKey: GOOGLE_MAPS_API_KEY }),
        }
    },
    name: 'MapComponent',
    props:{
        controlEdit: Boolean,
        ideditor: 0,
        data_polygono: [],
        formPlanta_data: {},
	},
    watch:{
        formPlanta_data(newValue){
            this.validar_registro(newValue,1);
        },
        controlEdit(newValue){
            this.validar_registro(newValue,0);
            if(newValue){
            }else{
            }
        }
    },
    mounted() {
        this.loader.load();
        this.map_data = new Map({
            target: this.$refs.map_trazo,
            view: new View({
                //center: transform([-64.7617, -16.2902],'EPSG:4326', 'EPSG:3857') ,
                //center: [-16.2902, -64.7617],
                center: [-7309526.511096828, -1943304.7471654885],
                zoom: 6,
                zoomControl: true,
                attributionControl: true, 
            })
        });

        var base_maps = new Group({
            'title': 'Base maps',
            layers: [
                new TileLayer({
                    title: 'OSM',
                    type: 'base',
                    visible: true,
                    source: new OSM()
                }),
                new TileLayer({
                    title: 'Satellite',
                    type: 'base',
                    visible: true,
                    source: new XYZ({
                        attributions: ['Esri','Community'],
                        attributionsCollapsible: false,
                        url: 'https://services.arcgisonline.com/ArcGIS/rest/services/World_Imagery/MapServer/tile/{z}/{y}/{x}',
                        maxZoom: 23
                    })
                }),
                new TileLayer({
                    title: 'Google',
                    type: 'base',
                    visible: true,
                    source: new XYZ({
                        url: "https://mt1.google.com/vt/lyrs=r&x={x}&y={y}&z={z}"
                    })
                }),
                new TileLayer({
                    title: 'Google Satellite',
                    type: 'base',
                    visible: true,
                    source: new XYZ({
                        url: "http://www.google.cn/maps/vt?lyrs=s@189&gl=cn&x={x}&y={y}&z={z}"
                    })
                }),
                
                
            ]
        });

        const url_sig = 'http://geoportal.vicetierras.gob.bo:8080/geoserver/wms?';
        
        var overlaysbolivia  = new Group({
            'title': 'Bolivia',
            layers: [new TileLayer({
                    source: new TileWMS({
                        url: url_sig,
                        params: {'LAYERS': 'viceministerio_autonomias:Bolivia','TILED': true},
                        
                    }),
                    visible: true,
                })]
        });
        
        var overlaysDepartamento  = new Group({
            'title': 'Departamento',
            layers: [new TileLayer({
                    source: new TileWMS({
                        url: url_sig,
                        params: {'LAYERS':'viceministerio_autonomias:Departamentos_de_Bolivia', 'TILED': true},
                    }),
                    visible: true,
                })]
        });

        var overlaysMunicipio  = new Group({
            'title': 'Municipio',
            layers: [new TileLayer({
                    source: new TileWMS({
                        url: url_sig,
                        params: {'LAYERS':'viceministerio_autonomias:Municipios_de_Bolivia', 'TILED': true},
                    }),
                    visible: false,
                })]
        });
        
        this.map_data.addLayer(base_maps);
        this.map_data.addLayer(overlaysbolivia);
        this.map_data.addLayer(overlaysDepartamento);
        this.map_data.addLayer(overlaysMunicipio);
        
        //var mouse_position = new MousePosition();
        //this.map_data.addControl(mouse_position);
        var slider = new ZoomSlider();
        this.map_data.addControl(slider);

        var zoom_ex = new ZoomToExtent({
            extent: [
                65.90, 7.48,
                98.96, 40.30
            ]
        });
        this.map_data.addControl(zoom_ex);
        
        
        var layerSwitcher = new LayerSwitcher({
            activationMode: 'click',
            reverse: true,
            tipLabel: 'Layers', // Optional label for button
            groupSelectStyle: 'children', // Can be 'children' [default], 'group' or 'none'
            collapseTipLabel: 'Collapse layers',
            //groupSelectStyle: "group"
        });
        var container = document.getElementById('popup');
        var content = document.getElementById('popup-content');
        var closer = document.getElementById('popup-closer');

        var overlay = new Overlay({
            element: container,
            autoPan: true,
            autoPanAnimation: {
                duration: 250
            }
        });
        

        if(this.controlEdit){
            if(this.formPlanta_data.latitud != null){
                var lng = this.formPlanta_data.longitud; //latlong[0];
                var lat = this.formPlanta_data.latitud; //latlong[1];
                content.innerHTML = '<p>Punto:</p><code>Latitud:'+lat+'<br>  Longitud:'+lng+'</code>';
                const coordinate = transform([lng, lat],'EPSG:4326', 'EPSG:3857');
                //this.$emit('valueSent', [lng, lat]);
                overlay.setPosition(coordinate);
            }else{
                overlay.setPosition(undefined);
                this.$emit('valueSent', ['','']);
            }
        }else{
            //overlay.setPosition(undefined);
            //this.$emit('valueSent', ['','']);
        }

        //this.map_data.addControl(layerSwitcher);
        //src: '@/assets/images/bicycle-parking.svg',
        /*
        closer.onclick = function () {
            overlay.setPosition(undefined);
            closer.blur();
            return false;
        };*/
        
        this.map_data.addOverlay(overlay);
        
        this.map_data.on('singleclick', (event) => {
            const coordinate = event.coordinate;
            const latlong = transform([coordinate[0], coordinate[1]], 'EPSG:3857','EPSG:4326');
            this.$emit('valueSent', latlong);
            //this.handleMapClick(latlong);
            var lng = latlong[0];
            var lat = latlong[1];
            content.innerHTML = '<p>Punto:</p><code>Latitud:'+lat+'<br>  Longitud:'+lng+'</code>';
            overlay.setPosition(coordinate);
            this.map_data.addOverlay(overlay);
        });
        
    },
    methods: {
        validar_registro(resitro,sw){
            if(sw==0){
                if(this.formPlanta_data.latitud != null){

                    var container = document.getElementById('popup');
                    var content = document.getElementById('popup-content');
                    var overlay = new Overlay({
                        element: container,
                        autoPan: true,
                        autoPanAnimation: {
                            duration: 250
                        }
                    });
                    var lng = this.formPlanta_data.longitud; //latlong[0];
                    var lat = this.formPlanta_data.latitud; //latlong[1];
                    content.innerHTML = '<p>Punto:</p><code>Latitud:'+lat+'<br>  Longitud:'+lng+'</code>';
                    const coordinate_data = transform([lng, lat],'EPSG:4326', 'EPSG:3857');
                    overlay.setPosition(coordinate_data);
                    //this.map_data.addOverlay(overlay);
                }else{
                    var container = document.getElementById('popup');
                    var closer = document.getElementById('popup-closer');
                    var overlay = new Overlay({
                        element: container,
                        autoPan: true,
                        autoPanAnimation: {
                            duration: 250
                        }
                    });
                    overlay.setPosition(undefined);
                    closer.blur();
                }
                
            }else{
                    
                var closer = document.getElementById('popup-closer');
                closer.blur();
                
            }
        },
        
        handleMapClick(event) {
            this.$emit('valueSent', event); // Esto debería funcionar correctamente
        },
        initializeMap() {

            this.map_data = new Map({
                target: this.$refs.map_trazo,
                view: new View({
                    //center: transform([-64.7617, -16.2902],'EPSG:4326', 'EPSG:3857') ,
                    //center: [-16.2902, -64.7617],
                    center: [-7309526.511096828, -1943304.7471654885],
                    zoom: 6,
                    zoomControl: true,
                    attributionControl: true, 
                })
            });
            
            var base_maps = new Group({
                'title': 'Base maps',
                layers: [
                    new TileLayer({
                        title: 'OSM',
                        type: 'base',
                        visible: true,
                        source: new OSM()
                    }),
                    new TileLayer({
                        title: 'Satellite',
                        type: 'base',
                        visible: true,
                        source: new XYZ({
                            attributions: ['Esri','Community'],
                            attributionsCollapsible: false,
                            url: 'https://services.arcgisonline.com/ArcGIS/rest/services/World_Imagery/MapServer/tile/{z}/{y}/{x}',
                            maxZoom: 23
                        })
                    }),
                    new TileLayer({
                        title: 'Google',
                        type: 'base',
                        visible: true,
                        source: new XYZ({
                            url: "https://mt1.google.com/vt/lyrs=r&x={x}&y={y}&z={z}"
                        })
                    }),
                    new TileLayer({
                        title: 'Google Satellite',
                        type: 'base',
                        visible: true,
                        source: new XYZ({
                            url: "http://www.google.cn/maps/vt?lyrs=s@189&gl=cn&x={x}&y={y}&z={z}"
                        })
                    }),
                    
                    
                ]
            });

            const url_sig = 'http://geoportal.vicetierras.gob.bo:8080/geoserver/wms?';

            
            var overlaysbolivia  = new Group({
                'title': 'Bolivia',
                layers: [new TileLayer({
                        source: new TileWMS({
                            url: url_sig,
                            params: {'LAYERS': 'viceministerio_autonomias:Bolivia','TILED': true},
                            
                        }),
                        visible: true,
                    })]
            });
        
            
            var overlaysDepartamento  = new Group({
                'title': 'Departamento',
                layers: [new TileLayer({
                        source: new TileWMS({
                            url: url_sig,
                            params: {'LAYERS':'viceministerio_autonomias:Departamentos_de_Bolivia', 'TILED': true},
                        }),
                        visible: true,
                    })]
            });

            var overlaysMunicipio  = new Group({
                'title': 'Municipio',
                layers: [new TileLayer({
                        source: new TileWMS({
                            url: url_sig,
                            params: {'LAYERS':'viceministerio_autonomias:Municipios_de_Bolivia', 'TILED': true},
                        }),
                        visible: false,
                    })]
            });
            
            this.map_data.addLayer(base_maps);
            this.map_data.addLayer(overlaysbolivia);
            this.map_data.addLayer(overlaysDepartamento);
            this.map_data.addLayer(overlaysMunicipio);
            
            //var mouse_position = new MousePosition();
            //this.map_data.addControl(mouse_position);
            var slider = new ZoomSlider();
            this.map_data.addControl(slider);

            var zoom_ex = new ZoomToExtent({
                extent: [
                    65.90, 7.48,
                    98.96, 40.30
                ]
            });
            this.map_data.addControl(zoom_ex);
            

            var layerSwitcher = new LayerSwitcher({
                activationMode: 'click',
                reverse: true,
                tipLabel: 'Layers', // Optional label for button
                groupSelectStyle: 'children', // Can be 'children' [default], 'group' or 'none'
                collapseTipLabel: 'Collapse layers',
                //groupSelectStyle: "group"
            });
            this.map_data.addControl(layerSwitcher);

            const container = document.getElementById('popup');
            const content = document.getElementById('popup-content');
            const closer = document.getElementById('popup-closer');
            const overlay = new Overlay({
                element: container,
                autoPan: {
                    animation: {
                    duration: 250,
                    },
                },
            });
            closer.onclick = function () {
                overlay.setPosition(undefined);
                closer.blur();
                return false;
            };
            
            this.map_data.on('click', function (evt) {
                const coordinate = evt.coordinate;
                const latlong = transform([coordinate[0], coordinate[1]], 'EPSG:3857','EPSG:4326');
                this.$emit('valueSent', latlong[0]);
            });
        },
        
    }
  };
  </script>
  
  <style scoped>
  .map {
    width: 100%;
    height: 400px;
  }

  .ol-popup {
        position: absolute;
        background-color: white;
        box-shadow: 0 1px 4px rgba(0,0,0,0.2);
        padding: 15px;
        border-radius: 10px;
        border: 1px solid #cccccc;
        bottom: 12px;
        left: -50px;
        min-width: 280px;
      }
      .ol-popup:after, .ol-popup:before {
        top: 100%;
        border: solid transparent;
        content: " ";
        height: 0;
        width: 0;
        position: absolute;
        pointer-events: none;
      }
      .ol-popup:after {
        border-top-color: white;
        border-width: 10px;
        left: 48px;
        margin-left: -10px;
      }
      .ol-popup:before {
        border-top-color: #cccccc;
        border-width: 11px;
        left: 48px;
        margin-left: -11px;
      }
      .ol-popup-closer {
        text-decoration: none;
        position: absolute;
        top: 2px;
        right: 8px;
      }
      .ol-popup-closer:after {
        content: "✖";
      }
  
</style>