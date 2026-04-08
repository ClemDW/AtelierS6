<template>
  <div>
    <h1 class="text-h3 mb-6">Galeries Publiques</h1>
    <v-row v-if="galeries && galeries.length">
      <v-col
        v-for="galerie in galeries"
        :key="galerie.id"
        cols="12"
        sm="6"
        md="4"
      >
        <v-card class="mx-auto" max-width="400">
          <!-- Si la galerie a une image de couverture, on l'affiche avec v-img pour le SSR -->
          <v-img
            v-if="galerie.photo_entete_id"
            :src="getImageUrl(galerie.photo_entete_id)"
            height="200px"
            cover
          ></v-img>
          
          <v-card-title>{{ galerie.titre }}</v-card-title>
          
          <v-card-subtitle>
            Créée en {{ new Date(galerie.date_creation).getFullYear() }}
          </v-card-subtitle>
          
          <v-card-text>
            <p>{{ galerie.description }}</p>
          </v-card-text>

          <v-card-actions>
            <v-btn
              color="primary"
              variant="text"
              :to="`/galeries/${galerie.id}`"
            >
              Voir la galerie
            </v-btn>
          </v-card-actions>
        </v-card>
      </v-col>
    </v-row>
    <v-alert v-else type="info">Aucune galerie publique pour le moment.</v-alert>
  </div>
</template>

<script setup>
const config = useRuntimeConfig()

// On utilise le proxy de Nitro qui fera l'appel interne vers GatewayFront
const { data, error, pending } = await useFetch(`${config.public.apiBase}/galeries`, {
  key: 'galeries-list'
})

// L'API renvoie directement un tableau d'objets (GaleriesListeDTO)
const galeries = computed(() => {
  if (Array.isArray(data.value)) {
    return data.value
  }
  return []
})

// Les images sont TOUJOURS rendues par le navigateur, on pointe donc toujours vers l'URL publique du service de stockage
const getImageUrl = (imagePath) => {
  if (!imagePath) return ''
  if (imagePath.startsWith('http')) return imagePath
  return `${config.public.storageBase}/photos/${imagePath}`
}

// Optionnel: Gérer les erreurs de fetch en développement
if (error.value) {
  console.error("Erreur de récupération SSR : ", error.value)
}
</script>