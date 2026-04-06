<script setup lang="ts">
import { ref, onMounted } from 'vue'
import { useGalerieStore } from '../stores/galerie'

const galerieStore = useGalerieStore()

const isLoading = ref(true)
const errorMessage = ref('')

const fetchGaleries = async () => {
  isLoading.value = true
  errorMessage.value = ''
  try {
    await galerieStore.loadPublicGaleries()
  } catch (error) {
    errorMessage.value = 'Impossible de charger les galeries publiques.'
  } finally {
    isLoading.value = false
  }
}

onMounted(() => {
  fetchGaleries()
})
</script>

<template>
  <div class="public-wrapper">
    <header class="header">
      <div class="logo">Photo<span class="highlight">Pro</span></div>
      <nav>
        <RouterLink :to="{ name: 'login' }" class="nav-link">Se connecter</RouterLink>
      </nav>
    </header>

    <main class="main-content">
      <div class="header-section">
        <h1>Galeries Publiques</h1>
        <p>Découvrez les œuvres de nos créateurs</p>
      </div>

      <div v-if="isLoading" class="state-message loading">
        <div class="spinner"></div> Chargement des galeries...
      </div>
      
      <div v-else-if="errorMessage" class="state-message error">
        {{ errorMessage }}
      </div>

      <div v-else-if="galerieStore.galeriesPubliques.length === 0" class="state-message empty">
        Aucune galerie publique pour le moment.
      </div>

      <div v-else class="galeries-grid">
        <div v-for="galerie in galerieStore.galeriesPubliques" :key="galerie.id" class="galerie-card">
          <div class="galerie-content">
            <h2>{{ galerie.titre }}</h2>
            <p class="date">{{ new Date(galerie.dateCreation).toLocaleDateString('fr-FR') }}</p>
            <p class="description">{{ galerie.description }}</p>
            <div class="actions">
              <RouterLink :to="{ name: 'galerie-detail', params: { id: galerie.id } }" class="view-btn">Voir la galerie</RouterLink>
            </div>
          </div>
        </div>
      </div>
    </main>
  </div>
</template>

<style scoped>
.public-wrapper {
  min-height: 100vh;
  background-color: #0b0f19;
  color: #fff;
  font-family: inherit;
}

.header {
  display: flex;
  justify-content: space-between;
  align-items: center;
  padding: 1.5rem 3rem;
  background: rgba(17, 24, 39, 0.8);
  border-bottom: 1px solid rgba(255, 255, 255, 0.05);
}

.logo {
  font-size: 1.5rem;
  font-weight: bold;
}

.highlight {
  color: #3b82f6;
}

.nav-link {
  color: #9ca3af;
  text-decoration: none;
  font-weight: 500;
  transition: color 0.3s;
}

.nav-link:hover {
  color: #fff;
}

.main-content {
  max-width: 1200px;
  margin: 0 auto;
  padding: 3rem 1.5rem;
}

.header-section {
  margin-bottom: 3rem;
  text-align: center;
}

.header-section h1 {
  font-size: 2.5rem;
  margin-bottom: 0.5rem;
}

.header-section p {
  color: #9ca3af;
  font-size: 1.1rem;
}

.state-message {
  text-align: center;
  padding: 3rem;
  color: #9ca3af;
  font-size: 1.1rem;
}

.spinner {
  display: inline-block;
  width: 20px;
  height: 20px;
  border: 3px solid rgba(255,255,255,0.3);
  border-radius: 50%;
  border-top-color: #fff;
  animation: spin 1s ease-in-out infinite;
  margin-right: 10px;
  vertical-align: middle;
}

@keyframes spin {
  to { transform: rotate(360deg); }
}

.error {
  color: #ef4444;
}

.galeries-grid {
  display: grid;
  grid-template-columns: repeat(auto-fill, minmax(300px, 1fr));
  gap: 2rem;
}

.galerie-card {
  background: rgba(17, 24, 39, 0.6);
  border: 1px solid rgba(255, 255, 255, 0.08);
  border-radius: 16px;
  overflow: hidden;
  transition: transform 0.3s, box-shadow 0.3s;
}

.galerie-card:hover {
  transform: translateY(-5px);
  box-shadow: 0 10px 25px rgba(0, 0, 0, 0.3);
  border-color: rgba(59, 130, 246, 0.3);
}

.galerie-content {
  padding: 1.5rem;
}

.galerie-content h2 {
  font-size: 1.25rem;
  margin: 0 0 0.5rem 0;
  color: #fff;
}

.date {
  font-size: 0.85rem;
  color: #6b7280;
  margin-bottom: 1rem;
}

.description {
  color: #9ca3af;
  font-size: 0.95rem;
  line-height: 1.5;
  margin-bottom: 1.5rem;
  display: -webkit-box;
  -webkit-line-clamp: 3;
  -webkit-box-orient: vertical;
  overflow: hidden;
}

.actions {
  display: flex;
  justify-content: flex-end;
}

.view-btn {
  background-color: #3b82f6;
  color: white;
  text-decoration: none;
  padding: 0.5rem 1rem;
  border-radius: 8px;
  font-size: 0.9rem;
  font-weight: 500;
  transition: background-color 0.3s;
}

.view-btn:hover {
  background-color: #2563eb;
}
</style>
