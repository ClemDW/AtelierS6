<script setup lang="ts">
import { ref, onMounted } from 'vue'
import { useRouter, useRoute } from 'vue-router'
import { useAuthStore } from '../stores/auth'
import { useGalerieStore } from '../stores/galerie'

const router = useRouter()
const route = useRoute()
const authStore = useAuthStore()
const galerieStore = useGalerieStore()

const isLoading = ref(true)
const errorMessage = ref('')

// --- Upload & Drag state ---
const isUploading = ref(false)
const isDragging = ref(false)
const uploadProgress = ref(0)
const uploadTotal = ref(0)
const uploadSuccessCount = ref(0)

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

const handleDrop = (e: DragEvent) => {
  isDragging.value = false
  const files = e.dataTransfer?.files
  if (files && files.length > 0) {
    processFiles(Array.from(files))
  }
}

const onFileSelect = (e: Event) => {
  const input = e.target as HTMLInputElement
  if (input.files && input.files.length > 0) {
    processFiles(Array.from(input.files))
  }
}

const processFiles = async (files: File[]) => {
  const userId = authStore.user?.id
  const galerieId = galerieStore.currentGalerie?.id

  if (!userId || !galerieId) return

  isUploading.value = true
  uploadTotal.value = files.length
  uploadProgress.value = 0
  uploadSuccessCount.value = 0

  try {
    for (const file of files) {
      console.log(`Début upload pour: ${file.name}`);
      const result = await galerieStore.uploadPhoto(userId, file)
      // Robust parsing in case of content-type issues
      let uploadResult = result;
      if (typeof result === 'string') {
        try { uploadResult = JSON.parse(result); } catch(e) { console.error('Parse error:', e); }
      }
      
      const photoId = uploadResult.photo_id || uploadResult.photoId || uploadResult.id;
      if (!photoId) throw new Error('ID manquant');

      await galerieStore.ajouterPhotoToGalerie(galerieId, photoId)
      
      uploadSuccessCount.value++
      uploadProgress.value = Math.round((uploadSuccessCount.value / uploadTotal.value) * 100)
    }
    await fetchGalerieContent()
  } catch (error) {
    console.error('Upload error:', error)
    alert('Erreur lors de l\'ajout des photos.')
  } finally {
    isUploading.value = false
  }
}

const handleDelete = async () => {
  const galerie = galerieStore.currentGalerie
  if (!galerie) return

  if (confirm(`Êtes-vous sûr de vouloir supprimer définitivement la galerie "${galerie.titre}" ?`)) {
    try {
      await galerieStore.supprimerGalerie(galerie.id)
      router.push({ name: 'my-galeries' })
    } catch (error) {
      alert('Impossible de supprimer la galerie.')
    }
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
        <RouterLink :to="{ name: 'my-galeries' }" class="nav-link">← Mes Galeries</RouterLink>
      </nav>
    </header>

    <main class="main-content">
      <div v-if="isLoading" class="state-message loading">
        <div class="spinner"></div> Chargement de la galerie...
      </div>
      
      <div v-else-if="errorMessage" class="state-message error">
        <h2>Oups !</h2>
        <p>{{ errorMessage }}</p>
        <RouterLink :to="{ name: 'home' }" class="back-btn">Retour au tableau de bord</RouterLink>
      </div>

      <div v-else-if="galerieStore.currentGalerie" class="galerie-content-section">
        <!-- En-tête de la galerie -->
        <div class="galerie-header">
          <div class="title-with-actions">
            <h1>{{ galerieStore.currentGalerie.titre }}</h1>
            <button 
              v-if="authStore.user && (galerieStore.currentGalerie as any).photographe_id === authStore.user.id" 
              @click="handleDelete" 
              class="delete-btn-main"
              title="Supprimer cette galerie"
            >
              <svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><polyline points="3 6 5 6 21 6"></polyline><path d="M19 6v14a2 2 0 0 1-2 2H7a2 2 0 0 1-2-2V6m3 0V4a2 2 0 0 1 2-2h4a2 2 0 0 1 2 2v2"></path><line x1="10" y1="11" x2="10" y2="17"></line><line x1="14" y1="11" x2="14" y2="17"></line></svg>
              Supprimer la galerie
            </button>
          </div>
          <p class="date">Créée le {{ galerieStore.currentGalerie.date_creation ? new Date(galerieStore.currentGalerie.date_creation).toLocaleDateString('fr-FR') : 'Date inconnue' }}</p>
          <p v-if="galerieStore.currentGalerie.description" class="description-longue">
            {{ galerieStore.currentGalerie.description }}
          </p>

          <!-- Zone de dépot / Upload -->
          <div v-if="authStore.user" class="upload-area-container">
            <div 
              class="dropzone-zone" 
              :class="{ 'is-dragging': isDragging, 'is-uploading': isUploading }"
              @dragover.prevent="isDragging = true"
              @dragleave.prevent="isDragging = false"
              @drop.prevent="handleDrop"
              @click="($refs.fileInput as HTMLInputElement)?.click()"
            >
              <div class="dropzone-content">
                <div class="dropzone-icon">
                  <svg v-if="!isUploading" xmlns="http://www.w3.org/2000/svg" width="32" height="32" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M21 15v4a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2v-4"></path><polyline points="17 8 12 3 7 8"></polyline><line x1="12" y1="3" x2="12" y2="15"></line></svg>
                  <div v-else class="spinner-blue"></div>
                </div>
                <div class="dropzone-text">
                  <template v-if="isUploading">
                    <strong>Envoi en cours...</strong>
                    <span>{{ uploadSuccessCount }} / {{ uploadTotal }} fichiers traités</span>
                  </template>
                  <template v-else>
                    <strong>Ajouter des photos</strong>
                    <span>Faites glisser vos images ou cliquez ici</span>
                  </template>
                </div>
              </div>
              
              <!-- Progress bar discrète en bas de la zone -->
              <div v-if="isUploading" class="dropzone-progress">
                <div class="progress-bar-fill" :style="{ width: uploadProgress + '%' }"></div>
              </div>
            </div>
            
            <input 
              ref="fileInput" 
              type="file" 
              multiple 
              accept="image/*" 
              @change="onFileSelect" 
              class="hidden-input"
            />
          </div>
        </div>

        <!-- Section des photos -->
        <div class="photos-container">
          <div v-if="!galerieStore.currentGalerie.photos || galerieStore.currentGalerie.photos.length === 0" class="no-photos">
            <div class="empty-icon">📷</div>
            <p>Cette galerie ne contient pas encore de photos.</p>
          </div>
          
          <div v-else class="photos-grid">
            <div v-for="(photo, index) in galerieStore.currentGalerie.photos" :key="(photo as any).id || index" class="photo-item">
              <img :src="(photo as any).url ? (photo as any).url : 'https://via.placeholder.com/800x600?text=Photo+' + (index + 1)" :alt="(photo as any).titre || 'Photo de la galerie'" loading="lazy" />
              <div v-if="(photo as any).titre" class="photo-caption">{{ (photo as any).titre }}</div>
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
  max-width: 1000px;
  margin-left: auto;
  margin-right: auto;
}

.title-with-actions {
  display: flex;
  justify-content: center;
  align-items: center;
  gap: 2rem;
  margin-bottom: 1rem;
  flex-wrap: wrap;
}

.delete-btn-main {
  display: flex;
  align-items: center;
  gap: 0.5rem;
  padding: 0.6rem 1.2rem;
  background: rgba(239, 68, 68, 0.1);
  color: #ef4444;
  border: 1px solid rgba(239, 68, 68, 0.2);
  border-radius: 10px;
  font-weight: 600;
  font-size: 0.9rem;
  cursor: pointer;
  transition: all 0.2s;
}

.delete-btn-main:hover {
  background: #ef4444;
  color: white;
  transform: translateY(-2px);
  box-shadow: 0 4px 12px rgba(239, 68, 68, 0.3);
}

.galerie-header h1 {
  font-size: 3rem;
  margin: 0;
  line-height: 1.2;
}

.header-actions {
  display: flex;
  align-items: center;
}

.upload-area-container {
  margin: 1.5rem auto 3rem;
  max-width: 800px;
}

.dropzone-zone {
  position: relative;
  border: 2px dashed rgba(255, 255, 255, 0.15);
  border-radius: 16px;
  padding: 2.5rem;
  background: rgba(255, 255, 255, 0.03);
  transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
  cursor: pointer;
  overflow: hidden;
}

.dropzone-zone:hover {
  border-color: rgba(59, 130, 246, 0.5);
  background: rgba(59, 130, 246, 0.05);
}

.dropzone-zone.is-dragging {
  border-color: #3b82f6;
  background: rgba(59, 130, 246, 0.1);
  transform: scale(1.02);
}

.dropzone-zone.is-uploading {
  pointer-events: none;
  border-style: solid;
}

.dropzone-content {
  display: flex;
  flex-direction: column;
  align-items: center;
  gap: 1.25rem;
  text-align: center;
}

.dropzone-icon {
  width: 64px;
  height: 64px;
  display: flex;
  align-items: center;
  justify-content: center;
  background: rgba(255, 255, 255, 0.05);
  border-radius: 50%;
  color: #3b82f6;
  transition: all 0.3s;
}

.dropzone-zone:hover .dropzone-icon {
  background: #3b82f6;
  color: white;
  transform: translateY(-5px);
}

.dropzone-text {
  display: flex;
  flex-direction: column;
  gap: 0.5rem;
}

.dropzone-text strong {
  font-size: 1.25rem;
  color: #fff;
}

.dropzone-text span {
  color: #9ca3af;
  font-size: 0.95rem;
}

.dropzone-progress {
  position: absolute;
  bottom: 0;
  left: 0;
  right: 0;
  height: 4px;
  background: rgba(255, 255, 255, 0.05);
}

.spinner-blue {
  width: 32px;
  height: 32px;
  border: 3px solid rgba(59, 130, 246, 0.2);
  border-top-color: #3b82f6;
  border-radius: 50%;
  animation: spin 0.8s linear infinite;
}

.hidden-input {
  display: none;
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
  margin-bottom: 2rem;
}

/* UPLOAD PROGRESS */
.upload-progress-container {
  margin: 2rem auto 0;
  max-width: 400px;
  background: rgba(17, 24, 39, 0.6);
  padding: 1.25rem;
  border-radius: 12px;
  border: 1px solid rgba(59, 130, 246, 0.2);
  animation: fadeIn 0.3s ease;
}

.progress-info {
  font-size: 0.9rem;
  color: #93c5fd;
  margin-bottom: 0.75rem;
  font-weight: 500;
}

.progress-bar-bg {
  height: 6px;
  background: rgba(255, 255, 255, 0.1);
  border-radius: 10px;
  overflow: hidden;
}

.progress-bar-fill {
  height: 100%;
  background: linear-gradient(90deg, #3b82f6, #60a5fa);
  transition: width 0.3s ease;
}

/* SECTION PHOTOS */
.photos-container {
  margin-top: 2rem;
}

.no-photos {
  text-align: center;
  padding: 5rem 2rem;
  background: rgba(255, 255, 255, 0.02);
  border-radius: 20px;
  border: 1px dashed rgba(255, 255, 255, 0.1);
  color: #9ca3af;
}

.empty-add-btn {
  margin-top: 1.5rem;
  padding: 0.8rem 2rem;
  background: transparent;
  color: #3b82f6;
  border: 2px solid #3b82f6;
  border-radius: 50px;
  font-weight: 600;
  cursor: pointer;
  transition: all 0.3s;
}

.empty-add-btn:hover {
  background: rgba(59, 130, 246, 0.1);
  transform: translateY(-2px);
}

/* Grille de maçonnerie simulée très simple */
.photos-grid {
  display: grid;
  grid-template-columns: repeat(auto-fill, minmax(320px, 1fr));
  grid-gap: 2rem;
  align-items: start;
}

.photo-item {
  position: relative;
  border-radius: 12px;
  overflow: hidden;
  background: #111827;
  transition: all 0.4s cubic-bezier(0.16, 1, 0.3, 1);
  box-shadow: 0 4px 20px rgba(0, 0, 0, 0.4);
}

.photo-item:hover {
  transform: translateY(-8px) scale(1.01);
  z-index: 10;
  box-shadow: 0 20px 40px rgba(0, 0, 0, 0.6);
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
  background: linear-gradient(to top, rgba(0,0,0,0.9), transparent);
  color: white;
  padding: 2rem 1rem 1rem;
  font-size: 0.95rem;
  opacity: 0;
  transform: translateY(10px);
  transition: all 0.3s;
}

.photo-item:hover .photo-caption {
  opacity: 1;
  transform: translateY(0);
}

@keyframes fadeIn {
  from { opacity: 0; }
  to { opacity: 1; }
}
</style>