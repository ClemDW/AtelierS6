<script setup lang="ts">
import { ref, onMounted, watch } from "vue";
import { useRouter } from "vue-router";
import { useAuthStore } from "../stores/auth";
import { useGalerieStore } from "../stores/galerie";

const router = useRouter();
const authStore = useAuthStore();
const galerieStore = useGalerieStore();

const myGaleries = ref<any[]>([]);
const isLoading = ref(false);
const isUserLoading = ref(true);
const errorMessage = ref("");
const photoFallback = "/img-placeholder.svg";

function resolveCoverPhotoSrc(galerie: any) {
  const coverId = galerie.photo_entete_id || galerie.photoEnteteId;
  if (!coverId) return photoFallback;

  const photoBase =
    import.meta.env.VITE_STORAGE_PHOTO_URL ||
    `${import.meta.env.VITE_API_BACK_URL || "http://localhost:6081/api/back"}/storage/photos`;
  return `${photoBase}/${coverId}`;
}

async function fetchMyGaleries() {
  console.log("--- fetchMyGaleries START ---");
  if (!authStore.isAuthenticated) {
    console.log("Non authentifié, redirection vers login");
    router.push({ name: "login" });
    return;
  }

  if (!authStore.user) {
    console.log("Utilisateur non chargé...");
    isUserLoading.value = true;
    return;
  }

  isUserLoading.value = false;
  const userId = authStore.user.id;
  console.log("Tentative de chargement pour userId:", userId);

  isLoading.value = true;
  errorMessage.value = "";

  try {
    const result = await galerieStore.loadUserGaleries(userId);
    console.log("RÉUSSITE:", result);
    myGaleries.value = result || [];
  } catch (error) {
    console.error("ERREUR API:", error);
    errorMessage.value = "Erreur lors du chargement des galeries.";
  } finally {
    isLoading.value = false;
    console.log("--- fetchMyGaleries END ---");
  }
}

// On surveille le chargement de l'utilisateur
watch(
  () => authStore.user,
  (u) => {
    if (u) {
      fetchMyGaleries();
    } else if (!authStore.isAuthenticated) {
      router.push({ name: "login" });
    }
  },
  { immediate: true },
);

async function confirmDelete(id: string, title: string) {
  if (
    confirm(
      `Êtes-vous sûr de vouloir supprimer la galerie "${title}" ? Cette action est irréversible.`,
    )
  ) {
    try {
      await galerieStore.supprimerGalerie(id);
      // On rafraîchit la liste locale
      myGaleries.value = myGaleries.value.filter((g) => g.id !== id);
    } catch (error) {
      alert("Erreur lors de la suppression de la galerie.");
    }
  }
}

function handleLogout() {
  authStore.logout();
  router.push({ name: "login" });
}

onMounted(() => {
  if (authStore.user) {
    fetchMyGaleries();
  }
});
</script>

<template>
  <div class="my-galeries-wrapper">
    <!-- Navigation -->
    <nav class="glass-nav">
      <RouterLink :to="{ name: 'home' }" class="nav-brand"
        >Photo<span class="highlight">Pro</span></RouterLink
      >
      <div class="nav-actions">
        <RouterLink :to="{ name: 'home' }" class="nav-link">Accueil</RouterLink>
        <RouterLink :to="{ name: 'my-photos' }" class="nav-link"
          >Mes Photos</RouterLink
        >
        <button @click="handleLogout" class="logout-btn">
          <svg
            xmlns="http://www.w3.org/2000/svg"
            width="16"
            height="16"
            viewBox="0 0 24 24"
            fill="none"
            stroke="currentColor"
            stroke-width="2"
            stroke-linecap="round"
            stroke-linejoin="round"
          >
            <path d="M9 21H5a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h4"></path>
            <polyline points="16 17 21 12 16 7"></polyline>
            <line x1="21" y1="12" x2="9" y2="12"></line>
          </svg>
        </button>
      </div>
    </nav>

    <main class="main-content">
      <header class="page-header">
        <div class="header-info">
          <h1>Mes Galeries</h1>
          <p>Gérez vos séries de photos et leur visibilité.</p>
        </div>
        <RouterLink :to="{ name: 'create-galerie' }" class="create-btn">
          <span>+</span> Nouvelle galerie
        </RouterLink>
      </header>

      <!-- Chargement Utilisateur -->
      <div v-if="isUserLoading" class="state-container">
        <div class="spinner"></div>
        <p>Vérification de votre session...</p>
      </div>

      <!-- Chargement Galeries -->
      <div v-else-if="isLoading" class="state-container">
        <div class="spinner"></div>
        <p>Récupération de vos galeries...</p>
      </div>

      <!-- Erreur -->
      <div v-else-if="errorMessage" class="state-container error">
        <div class="error-icon">⚠️</div>
        <p>{{ errorMessage }}</p>
        <button @click="fetchMyGaleries" class="retry-btn">Réessayer</button>
      </div>

      <!-- Vide -->
      <div v-else-if="myGaleries.length === 0" class="empty-state">
        <div class="empty-illustration">📸</div>
        <h2>Aucune galerie pour le moment</h2>
        <p>
          Commencez à partager votre travail en créant votre première galerie.
        </p>
        <RouterLink :to="{ name: 'create-galerie' }" class="create-btn-big">
          Créer ma première galerie
        </RouterLink>
      </div>

      <!-- Grille de galeries -->
      <div v-else class="galeries-grid">
        <div
          v-for="galerie in myGaleries"
          :key="galerie.id"
          class="galerie-card"
          @click="
            router.push({ name: 'galerie-detail', params: { id: galerie.id } })
          "
        >
          <div class="card-image">
            <img
              :src="resolveCoverPhotoSrc(galerie)"
              :alt="`Photo d'entête de ${galerie.titre}`"
              class="cover-image"
              loading="lazy"
            />
            <div class="badges">
              <span
                class="badge"
                :class="(galerie.type_galerie || 'private').toLowerCase()"
              >
                {{
                  (galerie.type_galerie || "").toLowerCase() === "public"
                    ? "Publique"
                    : "Privée"
                }}
              </span>
              <span v-if="galerie.est_publiee" class="badge published"
                >En ligne</span
              >
              <span v-else class="badge draft">Brouillon</span>
            </div>
            <button
              @click.stop="confirmDelete(galerie.id, galerie.titre)"
              class="delete-btn-overlay"
              title="Supprimer la galerie"
            >
              <svg
                xmlns="http://www.w3.org/2000/svg"
                width="18"
                height="18"
                viewBox="0 0 24 24"
                fill="none"
                stroke="currentColor"
                stroke-width="2.5"
                stroke-linecap="round"
                stroke-linejoin="round"
              >
                <polyline points="3 6 5 6 21 6"></polyline>
                <path
                  d="M19 6v14a2 2 0 0 1-2 2H7a2 2 0 0 1-2-2V6m3 0V4a2 2 0 0 1 2-2h4a2 2 0 0 1 2 2v2"
                ></path>
              </svg>
            </button>
          </div>
          <div class="card-content">
            <h3>{{ galerie.titre }}</h3>
            <p class="description">
              {{ galerie.description || "Aucune description" }}
            </p>
            <div class="card-footer">
              <span class="date">{{
                galerie.date_creation
                  ? new Date(galerie.date_creation).toLocaleDateString("fr-FR")
                  : "Date inconnue"
              }}</span>
              <span class="photo-link">Gérer →</span>
            </div>
          </div>
        </div>
      </div>
    </main>
  </div>
</template>

<style scoped>
.my-galeries-wrapper {
  min-height: 100vh;
  background-color: #0b0f19;
  color: #f1f5f9;
}

/* --- NAV --- */
.glass-nav {
  display: flex;
  justify-content: space-between;
  align-items: center;
  padding: 1rem 2rem;
  background: rgba(17, 24, 39, 0.4);
  backdrop-filter: blur(12px);
  border-bottom: 1px solid rgba(255, 255, 255, 0.05);
  position: sticky;
  top: 0;
  z-index: 10;
}

.nav-brand {
  font-size: 1.5rem;
  font-weight: 700;
  text-decoration: none;
  color: #f1f5f9;
}

.highlight {
  color: #3b82f6;
}

.nav-actions {
  display: flex;
  align-items: center;
  gap: 1.5rem;
}

.nav-link {
  color: #94a3b8;
  text-decoration: none;
  font-weight: 500;
  transition: color 0.3s;
}
.nav-link:hover {
  color: #fff;
}

.logout-btn {
  display: flex;
  align-items: center;
  background: rgba(239, 68, 68, 0.1);
  color: #ef4444;
  border: 1px solid rgba(239, 68, 68, 0.2);
  padding: 0.5rem;
  border-radius: 8px;
  cursor: pointer;
}

/* --- MAIN --- */
.main-content {
  max-width: 1200px;
  margin: 0 auto;
  padding: 3rem 1.5rem;
}

.page-header {
  display: flex;
  justify-content: space-between;
  align-items: flex-end;
  margin-bottom: 3rem;
  animation: fadeIn 0.5s ease;
}

.page-header h1 {
  font-size: 2.5rem;
  margin: 0 0 0.5rem 0;
}

.page-header p {
  color: #94a3b8;
  margin: 0;
}

.create-btn {
  display: flex;
  align-items: center;
  gap: 0.5rem;
  padding: 0.8rem 1.5rem;
  background: #3b82f6;
  color: white;
  text-decoration: none;
  border-radius: 12px;
  font-weight: 600;
  transition: all 0.3s;
  box-shadow: 0 4px 12px rgba(59, 130, 246, 0.3);
}

.create-btn:hover {
  background: #2563eb;
  transform: translateY(-2px);
}

/* --- STATES --- */
.state-container {
  text-align: center;
  padding: 5rem 0;
}

.spinner {
  width: 40px;
  height: 40px;
  border: 3px solid rgba(255, 255, 255, 0.1);
  border-top-color: #3b82f6;
  border-radius: 50%;
  animation: spin 1s linear infinite;
  margin: 0 auto 1rem;
}

.empty-state {
  text-align: center;
  padding: 6rem 2rem;
  background: rgba(255, 255, 255, 0.02);
  border-radius: 24px;
  border: 1px dashed rgba(255, 255, 255, 0.1);
}

.empty-illustration {
  font-size: 4rem;
  margin-bottom: 1.5rem;
  opacity: 0.5;
}

.create-btn-big {
  display: inline-block;
  margin-top: 2rem;
  padding: 1rem 2.5rem;
  background: white;
  color: #0b0f19;
  text-decoration: none;
  border-radius: 50px;
  font-weight: 700;
  transition: all 0.3s;
}

.create-btn-big:hover {
  transform: scale(1.05);
  box-shadow: 0 10px 30px rgba(255, 255, 255, 0.1);
}

/* --- GRID --- */
.galeries-grid {
  display: grid;
  grid-template-columns: repeat(auto-fill, minmax(340px, 1fr));
  gap: 2rem;
}

.galerie-card {
  background: rgba(255, 255, 255, 0.03);
  border: 1px solid rgba(255, 255, 255, 0.06);
  border-radius: 20px;
  overflow: hidden;
  cursor: pointer;
  transition: all 0.4s cubic-bezier(0.16, 1, 0.3, 1);
}

.galerie-card:hover {
  transform: translateY(-8px);
  background: rgba(255, 255, 255, 0.05);
  border-color: rgba(59, 130, 246, 0.3);
  box-shadow: 0 20px 40px -10px rgba(0, 0, 0, 0.5);
}

.card-image {
  height: 180px;
  background: #1e293b;
  position: relative;
  overflow: hidden;
}

.cover-image {
  width: 100%;
  height: 100%;
  object-fit: cover;
  display: block;
}

.placeholder-pattern {
  width: 100%;
  height: 100%;
  background-image: radial-gradient(
    circle at 2px 2px,
    rgba(255, 255, 255, 0.05) 1px,
    transparent 0
  );
  background-size: 24px 24px;
}

.badges {
  position: absolute;
  top: 1rem;
  left: 1rem;
  display: flex;
  gap: 0.5rem;
}

.badge {
  padding: 0.25rem 0.75rem;
  border-radius: 50px;
  font-size: 0.7rem;
  font-weight: 700;
  text-transform: uppercase;
  letter-spacing: 0.5px;
  backdrop-filter: blur(4px);
}

.badge.public {
  background: rgba(16, 185, 129, 0.2);
  color: #10b981;
  border: 1px solid rgba(16, 185, 129, 0.3);
}
.badge.private {
  background: rgba(245, 158, 11, 0.2);
  color: #f59e0b;
  border: 1px solid rgba(245, 158, 11, 0.3);
}
.badge.published {
  background: rgba(59, 130, 246, 0.2);
  color: #60a5fa;
  border: 1px solid rgba(59, 130, 246, 0.3);
}
.badge.draft {
  background: rgba(148, 163, 184, 0.2);
  color: #94a3b8;
  border: 1px solid rgba(148, 163, 184, 0.3);
}

.card-content {
  padding: 1.5rem;
}

.card-content h3 {
  margin: 0 0 0.5rem 0;
  font-size: 1.25rem;
}

.description {
  color: #94a3b8;
  font-size: 0.9rem;
  margin-bottom: 1.5rem;
  display: -webkit-box;
  -webkit-line-clamp: 2;
  -webkit-box-orient: vertical;
  overflow: hidden;
}

.card-footer {
  display: flex;
  justify-content: space-between;
  align-items: center;
  border-top: 1px solid rgba(255, 255, 255, 0.05);
  padding-top: 1rem;
}

.date {
  font-size: 0.8rem;
  color: #64748b;
}

.photo-link {
  font-size: 0.85rem;
  color: #3b82f6;
  font-weight: 600;
}

.delete-btn-overlay {
  position: absolute;
  top: 1rem;
  right: 1rem;
  width: 36px;
  height: 36px;
  border-radius: 10px;
  background: rgba(239, 68, 68, 0.15);
  backdrop-filter: blur(8px);
  border: 1px solid rgba(239, 68, 68, 0.3);
  color: #ef4444;
  display: flex;
  align-items: center;
  justify-content: center;
  cursor: pointer;
  opacity: 0;
  transform: translateY(-5px);
  transition: all 0.3s cubic-bezier(0.16, 1, 0.3, 1);
}

.galerie-card:hover .delete-btn-overlay {
  opacity: 1;
  transform: translateY(0);
}

.delete-btn-overlay:hover {
  background: #ef4444;
  color: white;
  transform: scale(1.1) !important;
}

@keyframes spin {
  to {
    transform: rotate(360deg);
  }
}
@keyframes fadeIn {
  from {
    opacity: 0;
  }
  to {
    opacity: 1;
  }
}
</style>
