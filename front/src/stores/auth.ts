import { ref, computed } from 'vue'
import { defineStore } from 'pinia'
import { ofetch } from 'ofetch'

export const useAuthStore = defineStore('auth', () => {
  // --- ÉTAT ---
  const token = ref<string | null>(localStorage.getItem('auth_token'))
  const user = ref<{ id?: string; email?: string; role?: string } | null>(null)

  const isAuthenticated = computed(() => !!token.value)

  // --- LE CLIENT API ---
  const api = ofetch.create({
    baseURL: 'http://localhost:6081/api/back',
    async onRequest({ options }) {
      const headers = new Headers(options.headers);

      if (token.value) {
        headers.set('Authorization', `Bearer ${token.value}`);
      }

      options.headers = headers;
    }
  });

  // --- ACTIONS ---
  async function login(email: string, password?: string) {
    const response = await api('/auth/signin', { method: 'POST', body: { email, password } })

    token.value = response.access_token
    localStorage.setItem('auth_token', response.access_token)

    await fetchUserProfile()
  }

  async function register(name: string, email: string, password?: string) {
    const response = await api('/auth/register', { 
      method: 'POST', 
      body: { name, email, password } 
    })

    if (response.access_token) {
      token.value = response.access_token
      localStorage.setItem('auth_token', response.access_token)
      await fetchUserProfile()
    }
  }  

  async function fetchUserProfile() {
    try {
      const userData = await api('/me')
      user.value = userData
    } catch (error) {
      console.error("Erreur profil:", error)
      logout()
    }
  }

  function logout() {
    token.value = null
    user.value = null
    localStorage.removeItem('auth_token')
  }

  return { token, user, isAuthenticated, login, register, logout, fetchUserProfile }
})