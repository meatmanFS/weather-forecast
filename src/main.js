import './styles.scss'
import webNorthLogo from './webnorth.svg?raw'
import bookmark from './bookmark.svg?raw'
import spinner from './spinner.svg?raw'
import bookmarkAdded from './bookmark-added.svg?raw'

import 'ol/ol.css';
import Map from 'ol/Map';
import View from 'ol/View';
import TileLayer from 'ol/layer/Tile';
import VectorLayer from 'ol/layer/Vector';
import VectorSource from 'ol/source/Vector';
import OSM from 'ol/source/OSM';
import Feature from 'ol/Feature';
import Point from 'ol/geom/Point';
import Style from 'ol/style/Style';
import Icon from 'ol/style/Icon';
import { createEmpty, extend } from 'ol/extent';
import { fromLonLat, toLonLat } from 'ol/proj';
import { getDistance } from 'ol/sphere';


var WeatherMap = {

  jsonUrl: weatherMapData.jsonUrl,
  mapData: weatherStations,
  weatherDataLoaded: false,
  selectedPin: null,
  jsonError: null,
  selectedPinShowInitially: false,
  weatherUnit: 'Celsius',
  userBookmarks: [],
  myLocationsMode: false,
  map: null,

  init: function () {

    this.loadUserBookmarks();

    this.buildApp();

    this.buildMap();

    this.gradientScroll();

    this.weatherUnitToggle();
    this.bookmarkToggle();
    this.openLocations();
    this.closeLocations();
    this.bookMarkLocationToggle();


  },

  loadUserBookmarks: function () {
    let userBookmarks =  JSON.parse(localStorage.getItem("weather-map-bookmarks"));

    if (!!userBookmarks) {

      this.userBookmarks = userBookmarks;

    }
  },

  isPinAddedBookmark: function () {
    if (!this.selectedPin) {
      return false;
    }

    let bookmarkedPin = this.userBookmarks.filter(bookmarkId => {
      return bookmarkId === this.selectedPin.id;
    });

    return bookmarkedPin.length > 0;
  },

  toggleBookmark: function () {
    if (!this.selectedPin) {
      return false;
    }

    if (this.isPinAddedBookmark()) {
      this.userBookmarks = this.userBookmarks.filter(bookmarkId => {
        return bookmarkId !== this.selectedPin.id;
      });
    } else {

      this.userBookmarks.push(this.selectedPin.id);
    }

    localStorage.setItem("weather-map-bookmarks", JSON.stringify(this.userBookmarks));
  },

  buildApp: function () {
    this.checkPin();

    let sidebar = this.buildSidebar();

    document.querySelector('#app').innerHTML = `
      <div>
        <div class="header">
          <div class="logo">
            ${webNorthLogo}
          </div>
          <h1>WeatherWay</h1>
        </div>
        <div class="map-wrap">
          <div id="gradient"></div>
          <div id="sidebar">
            ${sidebar}
          </div>
          <div id="map"></div>
        </div>
      </div>
    `
  },

  buildSidebar: function () {
    let sidebar = '';

    if (this.myLocationsMode) {

      let userBookmarks = this.mapData.filter(coord => {

        let userBookmarks = this.userBookmarks.filter(bookmarkId => {
          return coord.id === bookmarkId;
        });

        if (userBookmarks.length > 0) {
          return true;
        }

        return false;
      });

      let myBookmarks = userBookmarks.map(coord => {

        if (!!this.selectedPin && coord.id === this.selectedPin.id) {

          let locationTitle = `<div class="title my-locations-title my-locations-title-open" data="${coord.id}">${coord.title}</div>`;
          let weatherData  = this.selectedPinWeatherData();
          return locationTitle + weatherData;
        }

        return `<div class="title my-locations-title" data="${coord.id}">${coord.title}</div>`;
      });

      let openMyLocation = '';

      if (!!this.selectedPin) {
        openMyLocation = 'open-my-location';
      }

      sidebar = `
        <div class="description my-locations-list ${openMyLocation}">
          ${myBookmarks.join('')}
        </div>
        <div class="my-locations" id="close-locations">
          Close
        </div>
      `;

      return sidebar;
    }


    if (!this.selectedPin) {
      sidebar = `
        <div class="logo">
          ${webNorthLogo}
        </div>
        <div class="description">
          Click on the map to get weather data        
        </div>
        <div class="my-locations" id="locations">
          My Locations
        </div>
      `;
    } else {

      let weatherData = this.selectedPinWeatherData();
      let celciusActive, fahrenheitActive;

      if (this.weatherUnit === 'Celsius') {
        celciusActive = 'active';
      } else if (this.weatherUnit === 'Fahrenheit') {
        fahrenheitActive = 'active';
      }

      let selectedPinBookmark = '';

      if (this.isPinAddedBookmark()) {
        selectedPinBookmark = bookmarkAdded;
      } else {
        selectedPinBookmark = bookmark;
      }

      sidebar = `
        <div class="bookmark-wrap">
          <div class="type-toggle">
            <div id="celcius" class="celcius ${celciusActive}">Celsius</div> 
            /
            <div id="fahrenheit" class="fahrenheit ${fahrenheitActive}">Fahrenheit</div>         
          </div>
          <div id="bookmark" class="bookmark">
            ${selectedPinBookmark}
          </div>      
        </div>
        <div class="description">
          <div class="title">${this.selectedPin.title}</div>   
          ${weatherData}
        </div>
        <div class="my-locations" id="locations">
          My Locations
        </div>
      `;

    }



    return sidebar;
  },

  selectedPinWeatherData: function () {

    let weatherData = '';

    if (!!this.selectedPin.weather_data) {

      weatherData = this.buildWeatherData(this.selectedPin.weather_data);

    } else {

      if (this.weatherDataLoaded && !!this.selectedPin.weather_data) {
        weatherData = this.buildWeatherData(this.selectedPin.weather_data);
      } else {
        if (this.jsonError) {
          weatherData = `
              <div class="weather-data">
                <div class="spinner-wrap">
                  ${this.jsonError}
                </div>
              </div>
            `;
        } else {
          weatherData = `
              <div class="weather-data">
                <div class="spinner-wrap">
                  ${spinner}
                </div>
              </div>
            `;
          this.fetchJson();
        }

      }
    }

    return weatherData;

  },

  weatherUnitToggle: function () {
    let _this = this;

    if (!document.querySelector('#celcius')) {
      return;
    }

    document.querySelector('#celcius').addEventListener('click', function() {
      _this.weatherUnit = 'Celsius';
      _this.rebuildSidebar();
    });

    document.querySelector('#fahrenheit').addEventListener('click', function() {
      _this.weatherUnit = 'Fahrenheit';
      _this.rebuildSidebar();
    });
  },

  bookmarkToggle: function () {
    let _this = this;

    if (!document.querySelector('#bookmark')) {
      return;
    }

    document.querySelector('#bookmark').addEventListener('click', function() {
      _this.toggleBookmark();
      _this.rebuildSidebar();
    });
  },

  openLocations: function () {
    let _this = this;

    if (!document.querySelector('#locations')) {
      return;
    }

    document.querySelector('#locations').addEventListener('click', function() {
      _this.myLocationsMode = true;
      _this.weatherUnit = 'Celsius';
      _this.selectedPin = null;
      document.querySelector('#map').classList.add('hidden');

      _this.rebuildSidebar();
    });
  },

  closeLocations: function () {
    let _this = this;

    if (!document.querySelector('#close-locations')) {
      return;
    }

    document.querySelector('#close-locations').addEventListener('click', function() {
      _this.myLocationsMode = false;
      _this.selectedPin = null;

      document.querySelector('#map').classList.remove('hidden');


      _this.rebuildSidebar();
    });
  },
  
  bookMarkLocationToggle: function () {
    let _this = this;

    if (!document.querySelectorAll('.my-locations-title')) {
      return;
    }

    document.querySelectorAll('.my-locations-title').forEach(myLocation => {
      myLocation.addEventListener('click', function() {
        let location = this.getAttribute('data');
        if (!!_this.selectedPin && _this.selectedPin.id === parseInt(location)) {// close opened
          _this.selectedPin = null;
          document.querySelector('#map').classList.add('hidden');
          _this.rebuildSidebar();
          return;
        }

        document.querySelector('#map').classList.remove('hidden');

        _this.selectPin(parseInt(location));

        if (!!_this.selectedPin) {
          _this.map.getView().setCenter(fromLonLat([_this.selectedPin.lng, _this.selectedPin.lat]));
          _this.map.getView().setZoom(10);
        }
        _this.rebuildSidebar();
      });
    });
  },

  fetchJson: function () {
    this.weatherDataLoaded = false;
    let _this = this;

    fetch(this.jsonUrl + '/' + this.selectedPin.id)
      .then(res => res.json())
      .then(function (res) {
        _this.selectedPin.weather_data = res.weather_data;
        _this.weatherDataLoaded = true;
        _this.rebuildSidebar();
      })
      .catch(function() {
        this.jsonError = 'An error occure while fethching a weather data. Please try again.';
        _this.rebuildSidebar();
      });
  },

  buildWeatherData: function (weather) {
    let temp, feelsLike;

    if (this.weatherUnit === 'Celsius') {
      temp = weather.main.temp;
      feelsLike = weather.main.feels_like;
    } else if (this.weatherUnit === 'Fahrenheit') {
      temp = weather.main.temp_fahrenheit;
      feelsLike = weather.main.feels_like_fahrenheit;
    }

    return `
       <div class="weather-data">
          <div>
            <span class="title">Weather:</span> <span class="text">${weather.weather[0]?.main} - ${weather.weather[0]?.description}</span>
          </div>
          <div>
            <span class="title">Temp:</span> <span class="text">${temp} / ${feelsLike} ${this.weatherUnit}</span>
          </div>
          <div>
            <span class="title">Pressure: </span> <span class="text">${weather.main.pressure}</span>
          </div>
          <div>
            <span class="title">Humidity: </span> <span class="text">${weather.main.humidity}</span>
          </div>
      </div> 
    `;
  },

  checkPin: function () {
    const hash = window.location.hash; // Get the fragment from the URL (e.g., #id)
    if (hash) {
      const id = parseInt(hash.substring(1)); // Remove the '#' and get the ID (e.g., id)
      let selectedPins = this.mapData.filter(coord => {
        return coord.id === id;
      });
      if (selectedPins.length > 0) {
        this.selectedPin = selectedPins[0];
      }
    }
  },

  selectPin: function (weatherStationId) {
    let selectedPins = this.mapData.filter(coord => {
      return coord.id === weatherStationId;
    });
    if (selectedPins.length > 0) {
      this.selectedPin = selectedPins[0];
      window.location.hash = weatherStationId;
    }
    this.rebuildSidebar();
  },

  rebuildSidebar: function () {
    document.querySelector('#sidebar').innerHTML = this.buildSidebar();
    this.weatherUnitToggle();
    this.bookmarkToggle();
    this.openLocations();
    this.closeLocations();
    this.bookMarkLocationToggle();
  },

  buildMap: function () {
    let _this = this;

    const features = this.mapData.map(coord => {
      const feature = new Feature({
        geometry: new Point(fromLonLat([coord.lng, coord.lat]))
      });
      feature.setId(coord.id);
      return feature;
    });

    const vectorSource = new VectorSource({
      features: features
    });

    const vectorLayer = new VectorLayer({
      source: vectorSource
    });

    // Initialize the map
    const map = new Map({
      target: 'map',
      layers: [
        new TileLayer({
          source: new OSM()
        }),
        vectorLayer
      ],
      view: new View({
        center: fromLonLat([0, 0]),
        zoom: 2
      })
    });

    if (this.mapData.length > 0) {

      const mapExtend = createEmpty();
      vectorSource.forEachFeature(feature => {
        extend(mapExtend, feature.getGeometry().getExtent());
      });

      // Fit the map view to the extent of all features
      map.getView().fit(mapExtend, {
        size: map.getSize(),
        maxZoom: 10,
        padding: [50, 50, 50, 50] // Add a margin of 50 pixels to each side
      });
      const currentZoom = map.getView().getZoom();
      map.getView().setZoom(currentZoom - 1);

    }

    if (!_this.selectedPinShowInitially && _this.selectedPin) {
      map.getView().setCenter(fromLonLat([_this.selectedPin.lng, _this.selectedPin.lat]));
      map.getView().setZoom(10);
      _this.selectedPinShowInitially = true;
    }

    map.on('singleclick', function(evt) {
      const clickCoordinate = toLonLat(evt.coordinate);

      let closesPins = _this.mapData.map(coord => {

        const distance = Math.sqrt(
          Math.pow(coord.lng - clickCoordinate[0], 2) +
          Math.pow(coord.lat - clickCoordinate[1], 2)
        );

        return {
          "id": coord.id,
          "distance": distance,
          "lng": coord.lng,
          "lat": coord.lat
        };

      });
      closesPins.sort((a,b) => (a.distance > b.distance) ? 1 : ((b.distance > a.distance) ? -1 : 0));

      if (closesPins[0]) {
        const closesPin = closesPins[0];

        const closestCoordinate = fromLonLat([closesPin.lng, closesPin.lat]);
        map.getView().setCenter(closestCoordinate);
        map.getView().setZoom(10);

        _this.selectPin(closesPin.id);
      }
    });

    this.map = map;
  },

  gradientScroll: function () {
    let lastScrollTop = 0;

    window.addEventListener('scroll', function() {
      const gradient = document.getElementById('gradient');
      const rect = gradient.getBoundingClientRect();
      const gradientBottom = rect.bottom; // Bottom position of the gradient element
      const scrollY = window.scrollY;
      const maxScroll = gradientBottom; // Adjust based on the gradient's position

      const opacity = Math.max(0, 1 - (scrollY / maxScroll));
      gradient.style.opacity = opacity;

      let scrollTop = window.pageYOffset || document.documentElement.scrollTop;
      let mapWrap = document.getElementsByClassName('map-wrap')[0];

      if (scrollTop > lastScrollTop) {// scroll down
        gradient.scrollIntoView({
          behavior: 'smooth',
          block: 'start'
        });

        mapWrap.classList.add('show');

      } else {

        mapWrap.classList.remove('show');

      }
      lastScrollTop = scrollTop <= 0 ? 0 : scrollTop; // For Mobile or negative scrolling

    });
  }

}

WeatherMap.init();




