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
            v-if="galerie.cover_image"
            :src="getImageUrl(galerie.cover_image)"
            height="200px"
            cover
          ></v-img>
          
          <v-card-title>{{ galerie.titre }}</v-card-title>
          
          <v-card-subtitle>
            Créée en {{ new Date(galerie.created_at).getFullYear() }}
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

// Exécution SSR: Nuxt va appeler le microservice galerie en interne via l'API Gateway ou directement
const { data, error } = await useFetch(`${config.public.apiBase}/galeries`)

// On extrait la liste des galeries renvoyées par l'API
const galeries = computed(() => {
  if (data.value && data.value.type === 'collection') {
    return data.value.galeries
  }
  return []
})

// Si une URL locale S3 est retournée, il faut la formater (À adapter selon votre config SeaweedFS/S3)
const getImageUrl = (imagePath) => {
  return imagePath // Ajustez l'URL `http://gateway...` ou `http://s3...` si nécessaire
}

// Optionnel: Gérer les erreurs de fetch en développement
if (error.value) {
  console.error("Erreur de récupération SSR : ", error.value)
}
</script>