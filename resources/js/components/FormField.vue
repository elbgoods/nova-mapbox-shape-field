<template>
  <DefaultField :field="field" :errors="errors" :show-help-text="showHelpText">
    <template #field>
      <div id="mapContainer" class="basemap"></div>
      <p v-if="hasError" class="my-2 text-danger">
        {{ firstError }}
      </p>
    </template>
  </DefaultField>
</template>

<script>
import { FormField, HandlesValidationErrors } from 'laravel-nova'

import mapboxgl from "mapbox-gl";
import MapboxDraw from "@mapbox/mapbox-gl-draw";

export default {
  mixins: [FormField, HandlesValidationErrors],

  props: ['resourceName', 'resourceId', 'field'],

  data() {
    return {
      editedShape: false,
      map: null,
      draw: null,
      marker: null,
      geoJsonSource: {
        type: "geojson",
        data: {
          type: "Feature",
          geometry: this.field.geoJson,
        }
      }
    };
  },

  mounted() {
    // go at least to dortmund, germany
    const longitude = this.field.longitude || 7.466;
    const latitude = this.field.latitude || 51.51494;
    mapboxgl.accessToken = this.field.accessToken;
    this.map = new mapboxgl.Map({
      container: "mapContainer",
      style: "mapbox://styles/mapbox/streets-v11",
      center: [longitude, latitude], // starting position
      zoom: 7,
    });
    this.map.addControl(new mapboxgl.NavigationControl({
      showCompass: false
    }), 'top-left');
    this.map.on('load', () => {
      // drawer
      this.draw = new MapboxDraw({
        displayControlsDefault: false,
        controls: {
          polygon: true,
          trash: true
        },
        defaultMode: 'draw_polygon'
      });
      this.map.addControl(this.draw);
      // draggable marker
      this.marker = new mapboxgl.Marker({
        draggable: true
      }).setLngLat([longitude, latitude]).addTo(this.map);
      // shape
      this.map.addSource('layer', this.geoJsonSource)
      this.map.addLayer({
        id: 'layer',
        type: 'fill',
        source: 'layer',
        layout: {},
        paint: {
          'fill-color': '#0080ff', // blue color fill
          'fill-opacity': 0.5
        }
      });
    })
    this.map.on('draw.create', () => {
      this.editedShape = true
    })
  },

  methods: {
    setInitialValue() {
      this.value = this.field.value || ''
    },

    fill(formData) {
      let geo = {
        location: this.marker.getLngLat(),
      }
      if (this.editedShape) {
        geo.coordinates = this.draw.getAll().features[0].geometry.coordinates;
      }
      formData.append(this.field.attribute, JSON.stringify(geo))
    }
  },
}
</script>
<style lang="scss" scoped>
@import "~mapbox-gl/dist/mapbox-gl.css";
@import '@mapbox/mapbox-gl-draw/dist/mapbox-gl-draw.css';
#mapContainer {
  height: 600px;
}
</style>
