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
        defaultTheme: 'photoproDark',
        themes: {
          photoproDark: {
            dark: true,
            colors: {
              background: '#0b0f19',
              surface: '#0f172a',
              primary: '#3b82f6',
              secondary: '#10b981',
              error: '#ef4444',
              info: '#3b82f6',
              success: '#10b981',
              warning: '#f59e0b',
            }
          }
        }
      }
    }
  }
})
