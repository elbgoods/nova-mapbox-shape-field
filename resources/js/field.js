import DetailField from './components/DetailField'
import FormField from './components/FormField'

Nova.booting((app, store) => {
  app.component('detail-nova-mapbox-shape-field', DetailField)
  app.component('form-nova-mapbox-shape-field', FormField)
})
