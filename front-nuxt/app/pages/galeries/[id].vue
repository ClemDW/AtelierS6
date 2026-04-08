<template>
  <div v-if="pending" class="text-center py-10">
    <v-progress-circular indeterminate color="primary" size="64"></v-progress-circular>
  </div>
  
  <div v-else-if="error" class="text-center py-10">
    <v-alert type="error" title="Erreur de chargement" text="Impossible de charger la galerie."></v-alert>
    <v-btn class="mt-4" to="/galeries" color="primary">Retour à la liste</v-btn>
  </div>

  <div v-else-if="galerie">
    <v-btn prepend-icon="mdi-arrow-left" variant="text" to="/galeries" class="mb-4">
      Retour aux galeries
    </v-btn>

    <!-- En-tête de la galerie -->
    <v-row>
      <v-col cols="12" md="8">
        <h1 class="text-h3 font-weight-bold mb-2">{{ galerie.titre }}</h1>
        <div class="text-subtitle-1 text-medium-emphasis mb-4">
          Ajoutée le {{ new Date(galerie.dateCreation).toLocaleDateString('fr-FR') }}
        </div>
        <p class="text-body-1">{{ galerie.description }}</p>
      </v-col>
    </v-row>

    <v-divider class="my-6"></v-divider>

    <!-- Section des photos -->
    <h2 class="text-h4 mb-6">Photos de la galerie</h2>

    <v-row v-if="photos && photos.length">
      <!-- On gère l'affichage en fonction du mode de mise en page, par défaut grille simple -->
      <v-col
        v-for="(photo, index) in photos"
        :key="photo.id"
        cols="12"
        sm="6"
        md="4"
        :lg="galerie.modeMiseEnPage === 'liste' ? 12 : 3"
      >
        <v-card hover @click="ouvrirLightbox(index)">
          <!-- SSR : Les balises img seront pré-rendues par le serveur -->
          <v-img
            :src="getImageUrl(photo.id)"
            :alt="photo.titre || photo.nomOriginal"
            cover
            height="300"
          >
            <template v-slot:placeholder>
              <div class="d-flex align-center justify-center fill-height bg-grey-lighten-2">
                <v-progress-circular indeterminate color="grey-lighten-1"></v-progress-circular>
              </div>
            </template>
          </v-img>
          <v-card-title class="text-subtitle-1">{{ photo.titre || 'Sans titre' }}</v-card-title>
        </v-card>
      </v-col>
    </v-row>
    
    <v-alert v-else type="info" variant="tonal">
      Cette galerie ne contient aucune photo pour le moment.
    </v-alert>

    <!-- LIGHTBOX (v-dialog plein écran enveloppé dans ClientOnly pour éviter les bugs Nuxt SSR) -->
    <ClientOnly>
      <v-dialog v-model="lightboxOpen" fullscreen transition="dialog-bottom-transition">
        <v-card class="bg-black h-100" rounded="0">
          <!-- Bouton Fermer (Croix) -->
          <v-card-title class="d-flex justify-end pa-4 position-absolute w-100" style="z-index: 20;">
            <v-btn icon="mdi-close" variant="text" color="white" @click="lightboxOpen = false"></v-btn>
          </v-card-title>

          <!-- L'image en grand format avec navigation -->
          <v-card-text class="pa-0 d-flex flex-column justify-center align-center h-100 position-relative">
            
            <!-- Image -->
            <v-img
              v-if="currentPhoto"
              :src="getImageUrl(currentPhoto.id)"
              :alt="currentPhoto.titre"
              max-height="85vh"
              max-width="90vw"
              contain
            ></v-img>

            <!-- Bouton Précédent (Gauche) -->
            <v-btn
              icon="mdi-chevron-left"
              variant="text"
              color="white"
              size="x-large"
              class="position-absolute"
              style="left: 16px; top: 50%; transform: translateY(-50%); z-index: 10;"
              @click.stop="photoPrecedente"
              v-if="photos.length > 1"
            ></v-btn>

            <!-- Bouton Suivant (Droite) -->
            <v-btn
              icon="mdi-chevron-right"
              variant="text"
              color="white"
              size="x-large"
              class="position-absolute"
              style="right: 16px; top: 50%; transform: translateY(-50%); z-index: 10;"
              @click.stop="photoSuivante"
              v-if="photos.length > 1"
            ></v-btn>
            
            <!-- Affichage du titre en bas -->
            <div class="position-absolute" style="bottom: 2rem; text-align: center; width: 100%;">
              <h2 class="text-white">{{ currentPhoto?.titre || 'Sans titre' }}</h2>
              <p class="text-grey">{{ currentPhotoIndex + 1 }} / {{ photos.length }}</p>
            </div>
          </v-card-text>
        </v-card>
      </v-dialog>
    </ClientOnly>

  </div>
</template>

<script setup>
import { ref, computed } from 'vue'

const route = useRoute()
const config = useRuntimeConfig()
const galerieId = route.params.id

// Key d'hydratation unique. Le proxy gère la requête interne proprement
const { data: galerie, error, pending } = await useFetch(`${config.public.apiBase}/galeries/${galerieId}`, {
  key: `galerie-detail-${galerieId}`
})

// On gère l'extraction des photos
const photos = computed(() => {
  return galerie.value?.photos || []
})

// --- LOGIQUE LIGHTBOX ---
const lightboxOpen = ref(false)
const currentPhotoIndex = ref(0) // Index de la photo actuellement regardée

// Récupère la photo active (gérée grâce à l'index)
const currentPhoto = computed(() => {
  if (!photos.value || photos.value.length === 0) return null
  return photos.value[currentPhotoIndex.value]
})

// Ouvre la lightbox sur la photo spécifiée (appelé au @click sur la v-card)
const ouvrirLightbox = (index) => {
  currentPhotoIndex.value = index
  lightboxOpen.value = true
}

// Naviguer
const photoSuivante = () => {
  currentPhotoIndex.value = (currentPhotoIndex.value + 1) % photos.value.length
}
const photoPrecedente = () => {
  currentPhotoIndex.value = (currentPhotoIndex.value - 1 + photos.value.length) % photos.value.length
}

// On utilise notre /proxy-storage/ défini dans nuxt.config pour récupérer l'image directement, adieu localhost:6083 et ses erreurs de navigateur
const getImageUrl = (photoId) => {
  if (!photoId) return ''
  if (photoId.startsWith('http')) return photoId
  return `/proxy-storage/photos/${photoId}`
}

useHead({
  title: computed(() => galerie.value?.titre ? `${galerie.value.titre} - PhotoPro` : 'Galerie - PhotoPro'),
  meta: [
    { name: 'description', content: computed(() => galerie.value?.description || 'Découvrez cette galerie photo.') }
  ]
})
</script>