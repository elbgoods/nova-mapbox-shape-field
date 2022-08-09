<template>
  <PanelItem :index="index" :field="field">
    <template #value>
      <div id="mapContainer" class="basemap"></div>
    </template>
  </PanelItem>
</template>

<script>
import mapboxgl from "mapbox-gl"
export default {
  props: ['index', 'resource', 'resourceName', 'resourceId', 'field'],
  data() {
    return {
      map: null,
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
    mapboxgl.accessToken = this.field.accessToken;

    const longitude = this.field.longitude;
    const latitude = this.field.latitude;

    const mapOptions = {
      container: "mapContainer",
      style: "mapbox://styles/mapbox/streets-v11",
      center: [longitude, latitude],
      zoom: this.field.zoom || 8,
    }

    this.map = new mapboxgl.Map(mapOptions);
    this.map.addControl(new mapboxgl.NavigationControl({
      showCompass: false
    }), 'top-left');

    this.map.on('load', () => {
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
      // marker
      new mapboxgl.Marker()
          .setLngLat([longitude, latitude])
          .addTo(this.map);
    })
  },
}
</script>
<style lang="scss" scoped>
@import "~mapbox-gl/dist/mapbox-gl.css";
#mapContainer {
  height: 600px;
}
</style>
