import { ref } from 'vue'
import { defineStore } from 'pinia'
import { ofetch } from 'ofetch'

export interface Galerie {
  id: string
  titre: string
  description: string
  dateCreation: string
  url: string
}

export const useGalerieStore = defineStore('galerie', () => {

  const galeriesPubliques = ref<Galerie[]>([])

  // --- LE CLIENT API (Gateway Front) ---
  const api = ofetch.create({
    baseURL: import.meta.env.VITE_API_FRONT_URL || 'http://localhost:6080',
    // Configuration commune pour les appels au gateway front
    async onRequest({ options }) {
      const headers = new Headers(options.headers);
      options.headers = headers;
    }
  })

  // --- ACTIONS ---
  async function loadPublicGaleries() {
    try {
      const response = await api('/galeries', { method: 'GET' })
      galeriesPubliques.value = response
      return response
    } catch (error) {
      console.error('Erreur ofetch : Impossible de récupérer les galeries', error)
      throw error 
    }
  }

  return {
    galeriesPubliques,
    api,
    loadPublicGaleries
  }
})
