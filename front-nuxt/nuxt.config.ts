// https://nuxt.com/docs/api/configuration/nuxt-config

export default defineNuxtConfig({
  compatibilityDate: '2025-07-15',
  devtools: { enabled: true },

  runtimeConfig: {
    // API accessible côté serveur SSR
    apiBase: process.env.NUXT_API_BASE || 'http://gatewayfront.photopro',
    public: {
      // API et storage accessibles côté client CSR
      apiBase: process.env.NUXT_PUBLIC_API_BASE || 'http://localhost:6080',
      storageBase: process.env.NUXT_PUBLIC_STORAGE_BASE || 'http://localhost:6083'
    }
  },

  modules: [
    'vuetify-nuxt-module'
  ],

  vuetify: {
    moduleOptions: {
      /* Paramètres additionnels Vuetify si nécessaire */
    },
    vuetifyOptions: {
      theme: {
        defaultTheme: 'light' // Force le thème clair pour éviter le mismatch Serveur (Clair) / Navigateur (Sombre)
      }
    }
  }
})
