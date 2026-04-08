<script setup lang="ts">
import { ref, onMounted } from "vue";
import { useRouter, useRoute } from "vue-router";
import { useAuthStore } from "../stores/auth";
import { useGalerieStore, type Photo } from "../stores/galerie";

const router = useRouter();
const route = useRoute();
const authStore = useAuthStore();
const galerieStore = useGalerieStore();

const isLoading = ref(true);
const errorMessage = ref("");

const photoFallback = "/img-placeholder.svg";
const stockPhotos = ref<Photo[]>([]);
const stockLoading = ref(false);

const resolvePhotoSrc = (photo: any) => {
  if (photo?.url) return photo.url;
  if (photo?.id) {
    const photoBase =
      import.meta.env.VITE_STORAGE_PHOTO_URL ||
      `${import.meta.env.VITE_API_BACK_URL || "http://dockertu.iutnc.univ-lorraine.fr:11202/api/back"}/storage/photos`;
    return `${photoBase}/${photo.id}`;
  }
  return photoFallback;
};

const refreshStockPhotos = async () => {
  const userId = authStore.user?.id;
  if (!userId) {
    stockPhotos.value = [];
    return;
  }

  stockLoading.value = true;
  try {
    const response = await galerieStore.loadUserPhotos(userId);
    stockPhotos.value = Array.isArray(response) ? response : [];
  } catch (error) {
    stockPhotos.value = [];
  } finally {
    stockLoading.value = false;
  }
};

const fetchGalerieContent = async () => {
  isLoading.value = true;
  errorMessage.value = "";
  try {
    const galerieId = route.params.id as string;
    if (galerieId) {
      await galerieStore.loadGalerieById(galerieId);
    }
  } catch (error) {
    errorMessage.value = "Impossible de charger le détail de cette galerie.";
  } finally {
    isLoading.value = false;
  }
};

const addStockPhotoToGalerie = async (photoId: string) => {
  const galerieId = galerieStore.currentGalerie?.id;
  if (!galerieId) return;

  try {
    await galerieStore.ajouterPhotoToGalerie(galerieId, photoId);
    await fetchGalerieContent();
  } catch (error) {
    alert("Impossible d'ajouter cette photo à la galerie.");
  }
};

const handleDelete = async () => {
  const galerie = galerieStore.currentGalerie;
  if (!galerie) return;

  if (
    confirm(
      `Êtes-vous sûr de vouloir supprimer définitivement la galerie "${galerie.titre}" ?`,
    )
  ) {
    try {
      await galerieStore.supprimerGalerie(galerie.id);
      router.push({ name: "my-galeries" });
    } catch (error) {
      alert("Impossible de supprimer la galerie.");
    }
  }
};

onMounted(() => {
  fetchGalerieContent();
  refreshStockPhotos();
});
</script>

<template>
  <div class="galerie-detail-wrapper">
    <header class="header">
      <div class="logo">Photo<span class="highlight">Pro</span></div>
      <nav>
        <RouterLink :to="{ name: 'my-galeries' }" class="nav-link"
          >← Mes Galeries</RouterLink
        >
      </nav>
    </header>

    <main class="main-content">
      <div v-if="isLoading" class="state-message loading">
        <div class="spinner"></div>
        Chargement de la galerie...
      </div>

      <div v-else-if="errorMessage" class="state-message error">
        <h2>Oups !</h2>
        <p>{{ errorMessage }}</p>
        <RouterLink :to="{ name: 'home' }" class="back-btn"
          >Retour au tableau de bord</RouterLink
        >
      </div>

      <div
        v-else-if="galerieStore.currentGalerie"
        class="galerie-content-section"
      >
        <!-- En-tête de la galerie -->
        <div class="galerie-header">
          <div class="title-with-actions">
            <h1>{{ galerieStore.currentGalerie.titre }}</h1>
            <button
              v-if="
                authStore.user &&
                (galerieStore.currentGalerie as any).photographe_id ===
                  authStore.user.id
              "
              @click="handleDelete"
              class="delete-btn-main"
              title="Supprimer cette galerie"
            >
              <svg
                xmlns="http://www.w3.org/2000/svg"
                width="18"
                height="18"
                viewBox="0 0 24 24"
                fill="none"
                stroke="currentColor"
                stroke-width="2"
                stroke-linecap="round"
                stroke-linejoin="round"
              >
                <polyline points="3 6 5 6 21 6"></polyline>
                <path
                  d="M19 6v14a2 2 0 0 1-2 2H7a2 2 0 0 1-2-2V6m3 0V4a2 2 0 0 1 2-2h4a2 2 0 0 1 2 2v2"
                ></path>
                <line x1="10" y1="11" x2="10" y2="17"></line>
                <line x1="14" y1="11" x2="14" y2="17"></line>
              </svg>
              Supprimer la galerie
            </button>
          </div>
          <p class="date">
            Créée le
            {{
              galerieStore.currentGalerie.date_creation
                ? new Date(
                    galerieStore.currentGalerie.date_creation,
                  ).toLocaleDateString("fr-FR")
                : "Date inconnue"
            }}
          </p>
          <p
            v-if="galerieStore.currentGalerie.description"
            class="description-longue"
          >
            {{ galerieStore.currentGalerie.description }}
          </p>

          <div v-if="authStore.user" class="stock-container">
            <div class="stock-header">
              <h2>Stock photo</h2>
              <p>
                Photos déjà uploadées, prêtes à être ajoutées à une galerie.
              </p>
            </div>

            <div v-if="stockLoading" class="no-photos">
              <div class="spinner"></div>
              Chargement du stock...
            </div>

            <div v-else-if="stockPhotos.length === 0" class="no-photos">
              <div class="empty-icon">🗂️</div>
              <p>Aucune photo dans le stock pour le moment.</p>
            </div>

            <div v-else class="stock-grid">
              <div
                v-for="photo in stockPhotos"
                :key="photo.id"
                class="photo-item stock-photo-item"
              >
                <img
                  :src="resolvePhotoSrc(photo)"
                  :alt="photo.titre || 'Photo du stock'"
                  loading="lazy"
                />
                <div class="stock-actions">
                  <button
                    class="add-stock-btn"
                    @click="addStockPhotoToGalerie(photo.id)"
                  >
                    Ajouter à cette galerie
                  </button>
                </div>
              </div>
            </div>
          </div>
        </div>

        <!-- Section des photos -->
        <div class="photos-container">
          <div
            v-if="
              !galerieStore.currentGalerie.photos ||
              galerieStore.currentGalerie.photos.length === 0
            "
            class="no-photos"
          >
            <div class="empty-icon">📷</div>
            <p>Cette galerie ne contient pas encore de photos.</p>
          </div>

          <div v-else class="photos-grid">
            <div
              v-for="(photo, index) in galerieStore.currentGalerie.photos"
              :key="(photo as any).id || index"
              class="photo-item"
            >
              <img
                :src="resolvePhotoSrc(photo as any)"
                :alt="(photo as any).titre || 'Photo de la galerie'"
                loading="lazy"
              />
              <div v-if="(photo as any).titre" class="photo-caption">
                {{ (photo as any).titre }}
              </div>
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
  border: 3px solid rgba(255, 255, 255, 0.3);
  border-radius: 50%;
  border-top-color: #fff;
  animation: spin 1s ease-in-out infinite;
  margin-right: 10px;
  vertical-align: middle;
}

@keyframes spin {
  to {
    transform: rotate(360deg);
  }
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

/* SECTION PHOTOS */
.photos-container {
  margin-top: 2rem;
}

.stock-container {
  margin-top: 3rem;
  padding-top: 2rem;
  border-top: 1px solid rgba(255, 255, 255, 0.08);
}

.stock-header {
  display: flex;
  justify-content: space-between;
  align-items: end;
  gap: 1rem;
  margin-bottom: 1.5rem;
  flex-wrap: wrap;
}

.stock-header h2 {
  margin: 0;
  font-size: 1.6rem;
}

.stock-header p {
  margin: 0;
  color: #9ca3af;
}

.stock-grid {
  display: grid;
  grid-template-columns: repeat(auto-fill, minmax(170px, 1fr));
  gap: 0.9rem;
  align-items: start;
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

.stock-photo-item {
  display: flex;
  flex-direction: column;
}

.stock-photo-item img {
  width: 100%;
  height: 110px;
  object-fit: cover;
}

.stock-actions {
  display: flex;
  justify-content: center;
  padding: 0.55rem;
  background: rgba(17, 24, 39, 0.95);
}

.add-stock-btn {
  padding: 0.35rem 0.65rem;
  border: 1px solid rgba(59, 130, 246, 0.35);
  background: rgba(59, 130, 246, 0.12);
  color: #bfdbfe;
  border-radius: 999px;
  font-weight: 600;
  font-size: 0.78rem;
  cursor: pointer;
  transition: all 0.2s ease;
}

.add-stock-btn:hover {
  background: rgba(59, 130, 246, 0.22);
  transform: translateY(-1px);
}

.photo-caption {
  position: absolute;
  bottom: 0;
  left: 0;
  right: 0;
  background: linear-gradient(to top, rgba(0, 0, 0, 0.9), transparent);
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
  from {
    opacity: 0;
  }
  to {
    opacity: 1;
  }
}
</style>
