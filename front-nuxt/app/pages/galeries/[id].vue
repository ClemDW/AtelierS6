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

    <!-- MISE EN PAGE : SLIDESHOW / FULLSCREEN -->
    <v-card v-if="photos && photos.length && (galerie.modeMiseEnPage === 'slideshow' || galerie.modeMiseEnPage === 'fullscreen')" class="bg-black mb-6" rounded="lg">
      <v-carousel hide-delimiters height="75vh" show-arrows="hover" theme="dark">
        <v-carousel-item
          v-for="(photo, index) in photos"
          :key="photo.id"
        >
          <v-img
            :src="getImageUrl(photo.id)"
            :alt="photo.titre || 'Sans titre'"
            class="w-100 h-100"
            style="max-height: 75vh;"
          >
            <template v-slot:placeholder>
              <div class="d-flex align-center justify-center fill-height">
                <v-progress-circular indeterminate color="grey-lighten-1"></v-progress-circular>
              </div>
            </template>
            <div class="position-absolute text-center w-100" style="bottom: 2rem;">
              <h2 class="text-white" style="text-shadow: 1px 1px 4px rgba(0,0,0,0.8);">{{ photo.titre || 'Sans titre' }}</h2>
            </div>
          </v-img>
        </v-carousel-item>
      </v-carousel>
    </v-card>

    <!-- MISE EN PAGE : GRILLE -->
    <v-row v-else-if="photos && photos.length && galerie.modeMiseEnPage === 'grille'">
      <v-col
        v-for="(photo, index) in photos"
        :key="photo.id"
        cols="12"
        sm="6"
        md="4"
        lg="3"
      >
      
        <v-card>
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
      Cette galerie ne contient aucune photo pour le moment ou le mode de mise en page n'est pas reconnu.
    </v-alert>

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