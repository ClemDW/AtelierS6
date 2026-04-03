<script setup lang="ts">
import { ref } from 'vue'
import { useAuthStore } from '../stores/auth'
import { useRouter } from 'vue-router'

const authStore = useAuthStore()
const router = useRouter()

const name = ref('')
const email = ref('')
const password = ref('')
const confirmPassword = ref('')
const isLoading = ref(false)
const errorMessage = ref('')

const handleRegister = async () => {
  if (password.value !== confirmPassword.value) {
    errorMessage.value = 'Passwords do not match.'
    return
  }

  isLoading.value = true
  errorMessage.value = ''
  
  try {
    // On suppose que ton store a une méthode register
    await authStore.register(name.value, email.value, password.value, confirmPassword.value)
    router.push({ name: 'home' })
  } catch (error) {
    errorMessage.value = 'Registration failed. This email might already be in use.'
  } finally {
    isLoading.value = false
  }
}
</script>

<template>
  <div class="login-wrapper">
    <div class="blob blob-1"></div>
    <div class="blob blob-2"></div>
    
    <div class="login-card">
      <div class="card-header">
        <h1>Photo<span class="highlight">Pro</span></h1>
        <p>Join our community of creators.</p>
      </div>

      <form @submit.prevent="handleRegister" class="form-container">
        <div class="input-group">
          <label for="name">Full Name</label>
          <input 
            type="text" 
            id="name" 
            v-model="name" 
            placeholder="John Doe" 
            required 
            :disabled="isLoading"
          />
        </div>

        <div class="input-group">
          <label for="email">E-mail</label>
          <input 
            type="email" 
            id="email" 
            v-model="email" 
            placeholder="photographer@studio.com" 
            required 
            :disabled="isLoading"
          />
        </div>
        
        <div class="input-group">
          <label for="password">Password</label>
          <input 
            type="password" 
            id="password" 
            v-model="password" 
            placeholder="••••••••" 
            required 
            :disabled="isLoading"
          />
        </div>

        <div class="input-group">
          <label for="confirmPassword">Confirm Password</label>
          <input 
            type="password" 
            id="confirmPassword" 
            v-model="confirmPassword" 
            placeholder="••••••••" 
            required 
            :disabled="isLoading"
          />
        </div>

        <p v-if="errorMessage" class="error-text">{{ errorMessage }}</p>

        <button type="submit" class="submit-btn" :disabled="isLoading">
          {{ isLoading ? 'Creating account...' : 'Create Account' }}
        </button>
      </form>
      
      <div class="card-footer">
        Already have an account? <RouterLink :to="{ name: 'login' }" class="link">Sign in</RouterLink>
      </div>
    </div>
  </div>
</template>

<style scoped>
/* Les styles sont identiques à ton fichier original pour garder la cohérence */
.login-wrapper {
  position: relative;
  display: flex;
  align-items: center;
  justify-content: center;
  min-height: 100vh;
  width: 100%;
  background-color: #0b0f19;
  overflow: hidden;
  z-index: 1;
}

.blob {
  position: absolute;
  filter: blur(80px);
  z-index: -1;
  opacity: 0.6;
  animation: float 10s infinite ease-in-out alternate;
}

.blob-1 {
  width: 400px;
  height: 400px;
  background: linear-gradient(135deg, #10b981, #065f46);
  border-radius: 50%;
  top: -10%;
  left: -5%;
}

.blob-2 {
  width: 350px;
  height: 350px;
  background: linear-gradient(135deg, #3b82f6, #1d4ed8);
  border-radius: 50%;
  bottom: -10%;
  right: -5%;
  animation-delay: -5s;
}

@keyframes float {
  0% { transform: translate(0, 0) scale(1); }
  100% { transform: translate(40px, 40px) scale(1.1); }
}

.login-card {
  background: rgba(17, 24, 39, 0.6);
  backdrop-filter: blur(16px);
  -webkit-backdrop-filter: blur(16px);
  border: 1px solid rgba(255, 255, 255, 0.08);
  border-radius: 24px;
  padding: 2.5rem; /* Légèrement réduit pour compenser les champs en plus */
  width: 100%;
  max-width: 440px;
  box-shadow: 0 25px 50px -12px rgba(0, 0, 0, 0.5);
  animation: slideUp 0.6s cubic-bezier(0.16, 1, 0.3, 1);
}

@keyframes slideUp {
  from { opacity: 0; transform: translateY(30px); }
  to { opacity: 1; transform: translateY(0); }
}

.card-header {
  text-align: center;
  margin-bottom: 2rem;
}

.card-header h1 {
  font-size: 2.2rem;
  margin: 0;
  font-weight: 700;
  letter-spacing: -1px;
}

.highlight {
  color: #3b82f6;
}

.card-header p {
  color: #94a3b8;
  margin-top: 0.5rem;
  font-size: 0.95rem;
}

.form-container {
  display: flex;
  flex-direction: column;
  gap: 1.2rem;
}

.input-group {
  display: flex;
  flex-direction: column;
  gap: 0.4rem;
}

.input-group label {
  font-size: 0.85rem;
  font-weight: 600;
  color: #cbd5e1;
}

.input-group input {
  padding: 0.75rem 1rem;
  border-radius: 12px;
  border: 1px solid rgba(255, 255, 255, 0.1);
  background: rgba(15, 23, 42, 0.5);
  color: #f8fafc;
  font-size: 1rem;
  font-family: inherit;
  transition: all 0.3s ease;
  outline: none;
}

.input-group input:focus {
  border-color: #3b82f6;
  background: rgba(15, 23, 42, 0.8);
  box-shadow: 0 0 0 4px rgba(59, 130, 246, 0.15);
}

.error-text {
  color: #ef4444;
  font-size: 0.875rem;
  margin: 0;
  text-align: center;
}

.submit-btn {
  margin-top: 0.5rem;
  padding: 1rem;
  border-radius: 12px;
  border: none;
  background: linear-gradient(135deg, #3b82f6, #2563eb);
  color: #ffffff;
  font-size: 1rem;
  font-weight: 600;
  font-family: inherit;
  cursor: pointer;
  transition: transform 0.2s, box-shadow 0.2s;
}

.submit-btn:hover:not(:disabled) {
  transform: translateY(-2px);
  box-shadow: 0 10px 20px -10px rgba(59, 130, 246, 0.7);
}

.card-footer {
  margin-top: 1.5rem;
  text-align: center;
  color: #94a3b8;
  font-size: 0.9rem;
}

.link {
  color: #3b82f6;
  text-decoration: none;
  font-weight: 600;
}

.link:hover {
  color: #60a5fa;
  text-decoration: underline;
}
</style>