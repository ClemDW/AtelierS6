<script setup lang="ts">
import { ref, onMounted } from 'vue'
import { useRoute } from 'vue-router'
import { useGalerieStore } from '../stores/galerie'

const route = useRoute()
const galerieStore = useGalerieStore()

const isLoading = ref(true)
const errorMessage = ref('')

const fetchGalerieContent = async () => {
  isLoading.value = true
  errorMessage.value = ''
  try {

    const galerieId = route.params.id as string
    if (galerieId) {
      await galerieStore.loadGalerieById(galerieId)
    }
  } catch (error) {
    errorMessage.value = 'Impossible de charger le détail de cette galerie.'
  } finally {
    isLoading.value = false
  }
}

onMounted(() => {
  fetchGalerieContent()
})
</script>

<template>
  <div class="galerie-detail-wrapper">
    <header class="header">
      <div class="logo">Photo<span class="highlight">Pro</span></div>
      <nav>
        <RouterLink :to="{ name: 'galeries' }" class="nav-link">← Retour aux galeries</RouterLink>
      </nav>
    </header>

    <main class="main-content">
      <div v-if="isLoading" class="state-message loading">
        <div class="spinner"></div> Chargement de la galerie...
      </div>
      
      <div v-else-if="errorMessage" class="state-message error">
        <h2>Oups !</h2>
        <p>{{ errorMessage }}</p>
        <RouterLink :to="{ name: 'galeries' }" class="back-btn">Retour aux galeries publiques</RouterLink>
      </div>

      <div v-else-if="galerieStore.currentGalerie" class="galerie-content-section">
        <!-- En-tête de la galerie -->
        <div class="galerie-header">
          <h1>{{ galerieStore.currentGalerie.titre }}</h1>
          <p class="date">Créée le {{ new Date(galerieStore.currentGalerie.dateCreation).toLocaleDateString('fr-FR') }}</p>
          <p v-if="galerieStore.currentGalerie.description" class="description-longue">
            {{ galerieStore.currentGalerie.description }}
          </p>
        </div>

        <!-- Section des photos (Grid proportionnelle) -->
        <div class="photos-container">
          <div v-if="!galerieStore.currentGalerie.photos || galerieStore.currentGalerie.photos.length === 0" class="no-photos">
            <p>Cette galerie ne contient pas encore de photos.</p>
          </div>
          
          <div v-else class="photos-grid">
            <div v-for="(photo, index) in galerieStore.currentGalerie.photos" :key="photo.id || index" class="photo-item">
              <!-- placeholder style si l'api ne renvoie pas de vraies images encore -->
              <img :src="photo.url ? photo.url : 'https://via.placeholder.com/800x600?text=Photo+' + (index + 1)" :alt="photo.titre || 'Photo de la galerie'" loading="lazy" />
              <div v-if="photo.titre" class="photo-caption">{{ photo.titre }}</div>
            </div>
          </div>
        </div>
      </div>
    </main>
  </div>
</template>

<style scoped>
.galerie-detail-wrapper {
  min-height: 100vh;
  background-color: #050811; 
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
  max-width: 1400px;
  margin: 0 auto;
  padding: 3rem 1.5rem;
}

.state-message {
  text-align: center;
  padding: 5rem 2rem;
  color: #9ca3af;
  background: rgba(17, 24, 39, 0.4);
  border-radius: 12px;
  margin-top: 2rem;
}

.error {
  color: #ef4444;
}

.back-btn {
  display: inline-block;
  margin-top: 1.5rem;
  padding: 0.75rem 1.5rem;
  background-color: transparent;
  color: #ef4444;
  border: 1px solid #ef4444;
  border-radius: 8px;
  text-decoration: none;
  transition: all 0.3s;
}

.back-btn:hover {
  background-color: #ef4444;
  color: white;
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

.galerie-header {
  text-align: center;
  margin-bottom: 4rem;
  max-width: 800px;
  margin-left: auto;
  margin-right: auto;
}

.galerie-header h1 {
  font-size: 3rem;
  margin-bottom: 0.5rem;
  line-height: 1.2;
}

.date {
  color: #6b7280;
  font-size: 1rem;
  margin-bottom: 2rem;
  text-transform: uppercase;
  letter-spacing: 1px;
}

.description-longue {
  color: #d1d5db;
  font-size: 1.15rem;
  line-height: 1.7;
}

/* SECTION PHOTOS */
.photos-container {
  margin-top: 2rem;
}

.no-photos {
  text-align: center;
  padding: 4rem;
  background: rgba(255, 255, 255, 0.02);
  border-radius: 12px;
  color: #9ca3af;
  font-style: italic;
}

/* Grille de maçonnerie simulée très simple */
.photos-grid {
  display: grid;
  grid-template-columns: repeat(auto-fill, minmax(300px, 1fr));
  grid-gap: 1.5rem;
  align-items: start;
}

.photo-item {
  position: relative;
  border-radius: 8px;
  overflow: hidden;
  background: #111827;
  transition: transform 0.3s ease;
  box-shadow: 0 4px 6px rgba(0, 0, 0, 0.3);
}

.photo-item:hover {
  transform: scale(1.02);
  z-index: 10;
  box-shadow: 0 10px 20px rgba(0, 0, 0, 0.5);
}

.photo-item img {
  width: 100%;
  height: auto;
  display: block;
  object-fit: cover;
}

.photo-caption {
  position: absolute;
  bottom: 0;
  left: 0;
  right: 0;
  background: linear-gradient(to top, rgba(0,0,0,0.8), transparent);
  color: white;
  padding: 1.5rem 1rem 1rem;
  font-size: 0.9rem;
  opacity: 0;
  transition: opacity 0.3s;
}

.photo-item:hover .photo-caption {
  opacity: 1;
}
</style>