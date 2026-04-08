<template>
  <div>
    <!-- Vue initiale: Saisie du code d'accès -->
    <div v-if="!galeriePrivee">
      <v-row justify="center" class="mt-10">
        <v-col cols="12" sm="8" md="6" lg="4">
          <v-card class="pa-6" elevation="4">
            <v-card-title class="text-h4 font-weight-bold text-center text-primary mb-2">
              Galerie Privée
            </v-card-title>
            <v-card-subtitle class="text-center text-body-1 mb-6">
              Saisissez le code d'accès reçu par mail pour visualiser la galerie.
            </v-card-subtitle>
            
            <v-card-text>
              <v-form @submit.prevent="accederGalerie">
                <v-text-field
                  v-model="codeAcces"
                  label="Code d'accès"
                  placeholder="Ex: ABC-1234"
                  variant="outlined"
                  prepend-inner-icon="mdi-lock"
                  :readonly="loading"
                  required
                  class="mb-2"
                ></v-text-field>
                
                <v-alert v-if="errorMsg" type="error" variant="tonal" class="mb-4">
                  {{ errorMsg }}
                </v-alert>
                
                <v-btn
                  type="submit"
                  color="primary"
                  size="x-large"
                  block
                  :loading="loading"
                  :disabled="!codeAcces"
                >
                  Accéder à la galerie
                </v-btn>
              </v-form>
            </v-card-text>
          </v-card>
        </v-col>
      </v-row>
    </div>

    <!-- Vue Galerie: Affichée en pur CSR (Client-Side Rendering) après soumission valide -->
    <div v-else>
      <v-btn prepend-icon="mdi-arrow-left" variant="text" @click="quitterGalerie" class="mb-4">
        Quitter la galerie
      </v-btn>

      <!-- En-tête de la galerie privée -->
      <v-row>
        <v-col cols="12" md="8">
          <v-chip color="warning" class="mb-4" size="small" prepend-icon="mdi-lock">
            Accès privé
          </v-chip>
          <h1 class="text-h3 font-weight-bold mb-2">{{ galeriePrivee.titre }}</h1>
          <div class="text-subtitle-1 text-medium-emphasis mb-4">
            Ajoutée le {{ new Date(galeriePrivee.dateCreation).toLocaleDateString('fr-FR') }}
          </div>
          <p class="text-body-1">{{ galeriePrivee.description }}</p>
        </v-col>
      </v-row>

      <v-divider class="my-6"></v-divider>

      <!-- Section des photos -->
      <h2 class="text-h4 mb-6">Photos</h2>

      <v-row v-if="photos && photos.length">
        <v-col
          v-for="photo in photos"
          :key="photo.id"
          cols="12"
          sm="6"
          md="4"
          :lg="galeriePrivee.modeMiseEnPage === 'liste' ? 12 : 3"
        >
          <v-card hover>
            <v-img
              :src="getImageUrl(photo.cleS3)"
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
        Cette galerie privée ne contient aucune photo pour le moment.
      </v-alert>
    </div>
  </div>
</template>

<script setup>

const route = useRoute()
const config = useRuntimeConfig()


const codeAcces = ref(route.query.code || '')
const galeriePrivee = ref(null)
const loading = ref(false)
const errorMsg = ref('')

const photos = computed(() => {
  return galeriePrivee.value?.photos || []
})

const accederGalerie = async () => {
  if (!codeAcces.value.trim()) return

  loading.value = true
  errorMsg.value = ''

  try {
    const data = await $fetch('/galeries/code', {
      method: 'POST',
      baseURL: config.public.apiBase, 
      body: {
        code: codeAcces.value
      }
    })

    galeriePrivee.value = data
  } catch (err) {
    if (err.status === 404) {
      errorMsg.value = "Ce code d'accès est invalide ou la galerie n'existe pas."
    } else {
      errorMsg.value = "Une erreur serveur est survenue lors de la vérification du code."
    }
  } finally {
    loading.value = false
  }
}

// remettre l'affichage à zero
const quitterGalerie = () => {
  galeriePrivee.value = null
}

// Fetch fluide des images S3
const getImageUrl = (cleS3) => {
  if (!cleS3) return ''
  if (cleS3.startsWith('http')) return cleS3
  return `${config.public.storageBase}/photos/${cleS3}`
}

// Lancement automatique code  paramètre d'URL
onMounted(() => {
  if (codeAcces.value) {
    accederGalerie()
  }
})

useHead({
  title: 'Accès Galerie Privée - PhotoPro'
})
</script>