// https://nuxt.com/docs/api/configuration/nuxt-config

export default defineNuxtConfig({
  compatibilityDate: '2025-07-15',
  devtools: { enabled: true },

  runtimeConfig: {
    public: {
      apiBase: process.env.NUXT_PUBLIC_API_BASE || '/api',
      storageBase: process.env.NUXT_PUBLIC_STORAGE_BASE || 'http://localhost:6083'
    }
  },

  routeRules: {
    // Rend possible l'appel de localhost:6067/api/... et le traduit en un appel HTTP docker interne:
    '/api/**': { proxy: 'http://gatewayfront.photopro/**' },
    // On proxyfie les images pour éviter les soucis de CORS/Chargement infini sur le navigateur
    '/proxy-storage/**': { proxy: 'http://service-storage.photopro/**' }
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
