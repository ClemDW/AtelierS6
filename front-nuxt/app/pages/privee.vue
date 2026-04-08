<template>
  <div>
    <!-- Vue initiale: Saisie du code d'accès -->
    <div v-if="!galeriePrivee">
      <v-btn prepend-icon="mdi-arrow-left" variant="text" to="/" class="mb-4">
        Retour à l'accueil
      </v-btn>

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
      <GalerieDisplay 
        :galerie="galeriePrivee" 
        back-link="/" 
        back-text="Retour à l'accueil"
        :is-private="true"
        @back-click="quitterGalerie"
      />
    </div>
  </div>
</template>

<script setup>
import { ref, computed, onMounted } from 'vue'
import GalerieDisplay from '~/components/GalerieDisplay.vue'

const route = useRoute()
const config = useRuntimeConfig()

const codeAcces = ref(route.query.code || '')
const galeriePrivee = ref(null)
const loading = ref(false)
const errorMsg = ref('')

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