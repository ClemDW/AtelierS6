import { ref, computed } from 'vue'
import { defineStore } from 'pinia'
import { ofetch } from 'ofetch'

export const useAuthStore = defineStore('auth', () => {
  // --- ÉTAT ---
  const token = ref<string | null>(localStorage.getItem('auth_token'))
  const user = ref<{ id: string; email: string; role: string } | null>(null)
  const loading = ref(false)

  // --- GETTERS ---
  const isAuthenticated = computed(() => !!token.value)

  // --- CLIENT API CONFIGURÉ ---
  const api = ofetch.create({
    baseURL: 'http://localhost:6081/api/back',
    async onRequest({ options }) {
      if (token.value) {
        const headers = new Headers(options.headers || {})
        headers.set('Authorization', `Bearer ${token.value}`)
        options.headers = headers
      }
    },
    async onResponseError({ response }) {
      if (response.status === 401) {
        // Si la gateway renvoie 401 (JWT invalide), on déconnecte
        logout()
      }
    }
  })

  // --- ACTIONS ---

  async function login(email: string, password?: string) {
    loading.value = true
    try {
      // Appel vers /auth/signin (ton groupe de routes spécifique)
      const response = await api('/auth/signin', {
        method: 'POST',
        body: { email, password }
      })

      // On stocke le token d'abord
      token.value = response.token
      localStorage.setItem('auth_token', response.token)

      // On récupère les infos de l'utilisateur via la route /me de la Gateway
      await fetchUserProfile()

    } catch (error) {
      logout()
      throw error
    } finally {
      loading.value = false
    }
  }

  async function fetchUserProfile() {
    try {
      // Utilise la route /api/back/me définie dans ta Gateway
      const userData = await api('/me')
      user.value = userData
    } catch (error) {
      logout()
    }
  }

  function logout() {
    token.value = null
    user.value = null
    localStorage.removeItem('auth_token')
  }

  return {
    isAuthenticated,
    user,
    loading,
    login,
    logout,
    fetchUserProfile
  }
})