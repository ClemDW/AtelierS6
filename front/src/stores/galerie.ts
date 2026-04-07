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
    onRequest({ options }) {
      const token = localStorage.getItem('auth_token');
      if (token) {
        options.headers = {
          ...options.headers,
          Authorization: `Bearer ${token}`
        };
      }
    }
  })

  // --- LE CLIENT API AUTHENTIFIÉ (Gateway Back) ---
  const authApi = ofetch.create({
    baseURL: import.meta.env.VITE_API_BACK_URL || 'http://localhost:6081/api/back',
    onRequest({ options }) {
      const token = localStorage.getItem('auth_token');
      if (token) {
        options.headers = {
          ...options.headers,
          Authorization: `Bearer ${token}`
        };
        console.log('Sending token to CUSTOM back gateway:', token.substring(0, 10) + '...');
      } else {
        console.warn('No authentication token found in localStorage');
      }
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

  async function createGalerie(data: {
    photographeId: string
    typeGalerie: string
    titre: string
    description: string
    estPubliee: boolean
    modeMiseEnPage: string
    emailsClients?: string[]
    photos?: string[]
  }) {
    try {
      const response = await authApi('/galeries', {
        method: 'POST',
        body: {
          photographeId: data.photographeId,
          typeGalerie: data.typeGalerie,
          titre: data.titre,
          description: data.description,
          estPubliee: data.estPubliee,
          modeMiseEnPage: data.modeMiseEnPage,
          emailsClients: data.emailsClients || [],
          photos: data.photos || []
        }
      })
      return response
    } catch (error) {
      console.error('Erreur : Impossible de créer la galerie', error)
      throw error
    }
  }

  async function uploadPhoto(userId: string, file: File, titre?: string) {
    try {
      const formData = new FormData()
      formData.append('photo', file)
      if (titre) {
        formData.append('titre', titre)
      }

      const response = await authApi(`/photos/upload/${userId}`, {
        method: 'POST',
        body: formData
      })
      return response
    } catch (error) {
      console.error('Erreur : Impossible d\'uploader la photo', error)
      throw error
    }
  }

  async function ajouterPhotoToGalerie(galerieId: string, photoId: string) {
    try {
      await authApi(`/galeries/${galerieId}/photos`, {
        method: 'POST',
        body: { photoId }
      })
    } catch (error) {
      console.error('Erreur : Impossible d\'ajouter la photo à la galerie', error)
      throw error
    }
  }

  async function loadUserGaleries(userId: string) {
    try {
      const response = await authApi(`/photographes/${userId}/galeries`, { method: 'GET' })
      return response
    } catch (error) {
      console.error('Erreur : Impossible de récupérer vos galeries', error)
      throw error
    }
  }

  return {
    galeriesPubliques,
    currentGalerie,
    api,
    authApi,
    loadPublicGaleries,
    loadGalerieById,
    createGalerie,
    uploadPhoto,
    ajouterPhotoToGalerie,
    loadUserGaleries
  }
})
