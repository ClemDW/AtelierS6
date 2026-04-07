<script setup lang="ts">
import { useAuthStore } from '../stores/auth'
import { useRouter } from 'vue-router'

const authStore = useAuthStore()
const router = useRouter()

const handleLogout = () => {
  authStore.logout()
  router.push({ name: 'login' })
}
</script>

<template>
  <div class="dashboard-wrapper">
    <nav class="glass-nav">
      <div class="nav-brand">Photo<span class="highlight">Pro</span></div>
      
      <div class="nav-user">
        <div class="user-info">
          <span class="greeting">Hello, <strong>{{ authStore.user?.email || 'Photographer' }}</strong></span>
          <span v-if="authStore.user?.role" class="user-role">{{ authStore.user.role }}</span>
        </div>
        <button @click="handleLogout" class="logout-btn">
          <svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M9 21H5a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h4"></path><polyline points="16 17 21 12 16 7"></polyline><line x1="21" y1="12" x2="9" y2="12"></line></svg>
          Logout
        </button>
      </div>
    </nav>
    
    <main class="dashboard-content">
      <header class="page-header">
        <div class="header-info">
          <h2>Welcome to your studio</h2>
          <p>Manage your photos, galleries, and clients all from one place.</p>
        </div>
        <div class="header-actions">
          <RouterLink :to="{ name: 'my-galeries' }" class="secondary-btn">
            Mes Galeries
          </RouterLink>
          <RouterLink :to="{ name: 'create-galerie' }" class="create-btn">
            <svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><line x1="12" y1="5" x2="12" y2="19"></line><line x1="5" y1="12" x2="19" y2="12"></line></svg>
            Nouvelle galerie
          </RouterLink>
        </div>
      </header>
      
      <section class="stats-grid">
        <div class="stat-card">
          <div class="stat-icon galleries"></div>
          <div class="stat-data">
            <h3>Galleries</h3>
            <span class="number">12</span>
          </div>
        </div>
        <div class="stat-card">
          <div class="stat-icon photos"></div>
          <div class="stat-data">
            <h3>Photos</h3>
            <span class="number">3,240</span>
          </div>
        </div>
        <div class="stat-card">
          <div class="stat-icon storage"></div>
          <div class="stat-data">
            <h3>Storage Used</h3>
            <span class="number">64 GB</span>
          </div>
        </div>
      </section>
    </main>
  </div>
</template>

<style scoped>
.dashboard-wrapper {
  min-height: 100vh;
  background-color: #0b0f19;
  background-image: 
    radial-gradient(at 0% 0%, rgba(16, 185, 129, 0.15) 0px, transparent 50%),
    radial-gradient(at 100% 100%, rgba(59, 130, 246, 0.15) 0px, transparent 50%);
  display: flex;
  flex-direction: column;
}

.glass-nav {
  display: flex;
  justify-content: space-between;
  align-items: center;
  padding: 1rem 2rem;
  background: rgba(17, 24, 39, 0.4);
  backdrop-filter: blur(12px);
  -webkit-backdrop-filter: blur(12px);
  border-bottom: 1px solid rgba(255, 255, 255, 0.05);
  position: sticky;
  top: 0;
  z-index: 10;
}

.nav-brand {
  font-size: 1.5rem;
  font-weight: 700;
  letter-spacing: -0.5px;
}

.highlight {
  color: #3b82f6;
}

.nav-user {
  display: flex;
  align-items: center;
  gap: 1.5rem;
}

.greeting {
  color: #94a3b8;
}

.greeting strong {
  color: #f8fafc;
}

.user-info {
  display: flex;
  flex-direction: column;
  align-items: flex-end;
  line-height: 1.2;
}

.user-role {
  font-size: 0.7rem;
  background: rgba(59, 130, 246, 0.2);
  color: #60a5fa;
  padding: 0.15rem 0.5rem;
  border-radius: 4px;
  text-transform: uppercase;
  font-weight: 700;
  letter-spacing: 0.5px;
  margin-top: 4px;
}

.logout-btn {
  display: flex;
  align-items: center;
  gap: 0.5rem;
  background: rgba(239, 68, 68, 0.1);
  color: #ef4444;
  border: 1px solid rgba(239, 68, 68, 0.2);
  padding: 0.6rem 1rem;
  border-radius: 8px;
  cursor: pointer;
  font-family: inherit;
  font-weight: 600;
  transition: all 0.2s;
}

.logout-btn:hover {
  background: rgba(239, 68, 68, 0.2);
  transform: translateY(-1px);
}

.dashboard-content {
  padding: 3rem 2rem;
  max-width: 1200px;
  margin: 0 auto;
  width: 100%;
}

.page-header {
  margin-bottom: 3rem;
  animation: fadeIn 0.5s ease;
  display: flex;
  justify-content: space-between;
  align-items: center;
  flex-wrap: wrap;
  gap: 1rem;
}

.page-header h2 {
  font-size: 2.2rem;
  margin: 0 0 0.5rem 0;
}

.page-header p {
  color: #94a3b8;
  font-size: 1.1rem;
  margin: 0;
}

.header-actions {
  display: flex;
  gap: 1rem;
}

.secondary-btn {
  display: flex;
  align-items: center;
  gap: 0.5rem;
  padding: 0.75rem 1.5rem;
  border-radius: 12px;
  background: rgba(255, 255, 255, 0.05);
  border: 1px solid rgba(255, 255, 255, 0.1);
  color: #ffffff;
  text-decoration: none;
  font-size: 0.95rem;
  font-weight: 600;
  transition: all 0.2s;
  white-space: nowrap;
}

.secondary-btn:hover {
  background: rgba(255, 255, 255, 0.1);
  border-color: rgba(255, 255, 255, 0.2);
  transform: translateY(-2px);
}

.create-btn:hover {
  transform: translateY(-2px);
  box-shadow: 0 10px 25px -10px rgba(59, 130, 246, 0.5);
}

.stats-grid {
  display: grid;
  grid-template-columns: repeat(auto-fit, minmax(300px, 1fr));
  gap: 1.5rem;
}

.stat-card {
  background: rgba(30, 41, 59, 0.5);
  border: 1px solid rgba(255, 255, 255, 0.05);
  border-radius: 16px;
  padding: 1.5rem;
  display: flex;
  align-items: center;
  gap: 1.5rem;
  transition: transform 0.3s, background 0.3s;
  animation: slideUp 0.6s cubic-bezier(0.16, 1, 0.3, 1) backwards;
}

.stat-card:nth-child(2) { animation-delay: 0.1s; }
.stat-card:nth-child(3) { animation-delay: 0.2s; }

.stat-card:hover {
  transform: translateY(-5px);
  background: rgba(30, 41, 59, 0.8);
}

.stat-icon {
  width: 64px;
  height: 64px;
  border-radius: 16px;
  background-size: cover;
  background-position: center;
}

.stat-icon.galleries {
  background: linear-gradient(135deg, rgba(16, 185, 129, 0.2), rgba(16, 185, 129, 0.05));
  border: 1px solid rgba(16, 185, 129, 0.3);
}

.stat-icon.photos {
  background: linear-gradient(135deg, rgba(59, 130, 246, 0.2), rgba(59, 130, 246, 0.05));
  border: 1px solid rgba(59, 130, 246, 0.3);
}

.stat-icon.storage {
  background: linear-gradient(135deg, rgba(139, 92, 246, 0.2), rgba(139, 92, 246, 0.05));
  border: 1px solid rgba(139, 92, 246, 0.3);
}

.stat-data h3 {
  margin: 0;
  font-size: 1rem;
  color: #94a3b8;
  font-weight: 500;
}

.stat-data .number {
  font-size: 2rem;
  font-weight: 700;
  color: #f8fafc;
}

@keyframes fadeIn {
  from { opacity: 0; }
  to { opacity: 1; }
}

@keyframes slideUp {
  from { opacity: 0; transform: translateY(20px); }
  to { opacity: 1; transform: translateY(0); }
}
</style>
