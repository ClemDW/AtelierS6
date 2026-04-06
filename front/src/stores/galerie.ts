import { ref } from 'vue'
import { defineStore } from 'pinia'
import { ofetch } from 'ofetch'

export interface Photo {
  id: string
  url: string
  titre?: string
}

export interface Galerie {
  id: string
  titre: string
  description: string
  dateCreation: string
  url: string
  photos?: Photo[]
}

export const useGalerieStore = defineStore('galerie', () => {

  const galeriesPubliques = ref<Galerie[]>([])
  const currentGalerie = ref<Galerie | null>(null)

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

  async function loadGalerieById(id: string) {
    try {
      // Nettoyage avant chargement (éviter d'afficher une ancienne galerie)
      currentGalerie.value = null
      
      const response = await api(`/galeries/${id}`, { method: 'GET' })
      currentGalerie.value = response
      return response
    } catch (error) {
      console.error('Erreur ofetch : Impossible de récupérer la galerie', error)
      throw error
    }
  }

  return {
    galeriesPubliques,
    currentGalerie,
    api,
    loadPublicGaleries,
    loadGalerieById
  }
})
