<template>
  <div v-if="pending" class="text-center py-10">
    <v-progress-circular indeterminate color="primary" size="64"></v-progress-circular>
  </div>
  
  <div v-else-if="error" class="text-center py-10">
    <v-alert type="error" title="Erreur de chargement" text="Impossible de charger la galerie."></v-alert>
    <!-- Utilisation de 'href' pour forcer le chargement de page et le SSR complet de l'accueil -->
    <v-btn class="mt-4" href="/galeries" color="primary" variant="elevated">Retour à la liste</v-btn>
  </div>

  <GalerieDisplay 
    v-else-if="galerie" 
    :galerie="galerie" 
    back-link="/galeries" 
    back-text="Retour aux galeries"
    :is-private="false"
    :use-href-back="true"
  />
</template>

<script setup>
import { computed } from 'vue'
import GalerieDisplay from '~/components/GalerieDisplay.vue'

const route = useRoute()
const config = useRuntimeConfig()
const galerieId = route.params.id

// Exécution propre sans proxy: Utilise la route docker via le Serveur et localhost s'il est trigger par le Client
const apiUrl = import.meta.client ? config.public.apiBase : config.apiBase
const { data: galerie, error, pending } = await useFetch(`${apiUrl}/galeries/${galerieId}`, {
  key: `galerie-detail-${galerieId}`
})

useHead({
  title: computed(() => galerie.value?.titre ? `${galerie.value.titre} - PhotoPro` : 'Galerie - PhotoPro'),
  meta: [
    { name: 'description', content: computed(() => galerie.value?.description || 'Découvrez cette galerie photo.') }
  ]
})
</script>