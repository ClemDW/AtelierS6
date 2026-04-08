<template>
  <div>
    <v-btn prepend-icon="mdi-arrow-left" variant="text" :to="backLink" @click="emit('back-click')" class="mb-4">
      {{ backText }}
    </v-btn>

    <!-- En-tête de la galerie -->
    <v-row>
      <v-col cols="12" md="8">
        <v-chip v-if="isPrivate" color="warning" class="mb-4" size="small" prepend-icon="mdi-lock">
          Accès privé
        </v-chip>
        <h1 class="text-h3 font-weight-bold mb-2">{{ galerie.titre }}</h1>
        <div class="text-subtitle-1 text-medium-emphasis mb-4">
          Ajoutée le {{ new Date(galerie.dateCreation).toLocaleDateString('fr-FR') }}
        </div>
        <p class="text-body-1">{{ galerie.description }}</p>
      </v-col>
    </v-row>

    <v-divider class="my-6"></v-divider>

    <!-- Section des photos -->
    <h2 class="text-h4 mb-6">Photos</h2>

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
    <v-row v-else-if="photos && photos.length && (galerie.modeMiseEnPage === 'grille' || !galerie.modeMiseEnPage)">
      <v-col
        v-for="(photo, index) in photos"
        :key="photo.id"
        cols="12"
        sm="6"
        md="4"
        lg="3"
      >
        <v-card>
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
import { computed } from 'vue'

const props = defineProps({
  galerie: {
    type: Object,
    required: true
  },
  backLink: {
    type: String,
    default: undefined
  },
  backText: {
    type: String,
    default: 'Retour'
  },
  isPrivate: {
    type: Boolean,
    default: false
  }
})

const emit = defineEmits(['back-click'])

const photos = computed(() => {
  return props.galerie?.photos || []
})

const getImageUrl = (photoId) => {
  if (!photoId) return ''
  if (photoId.startsWith('http')) return photoId
  return `/proxy-storage/photos/${photoId}`
}
</script>