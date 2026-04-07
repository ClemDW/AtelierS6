import '@mdi/font/css/materialdesignicons.css'
import 'vuetify/styles'
import { createVuetify } from 'vuetify'
import * as components from 'vuetify/components'
import * as directives from 'vuetify/directives'

export default defineNuxtPlugin((nuxtApp) => {
  const vuetify = createVuetify({
    ssr: true, // Très important pour votre sujet (Server-Side Rendering)
    components,
    directives,
  })
  nuxtApp.vueApp.use(vuetify)
})