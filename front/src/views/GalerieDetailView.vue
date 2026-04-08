<script setup lang="ts">
import { computed, ref, onMounted, watch } from "vue";
import { useRouter, useRoute } from "vue-router";
import { useAuthStore } from "../stores/auth";
import { useGalerieStore, type Galerie, type Photo } from "../stores/galerie";
import PhotoSearchField from "../components/PhotoSearchField.vue";
import PhotoThumbnailCard from "../components/PhotoThumbnailCard.vue";
import GallerySlideshow from "../components/GallerySlideshow.vue";

const router = useRouter();
const route = useRoute();
const authStore = useAuthStore();
const galerieStore = useGalerieStore();

const isLoading = ref(true);
const errorMessage = ref("");
const saveError = ref("");
const saveSuccess = ref("");
const isSavingInfos = ref(false);
const isSavingPublication = ref(false);

const photoFallback = "/img-placeholder.svg";
const stockPhotos = ref<Photo[]>([]);
const stockSearchQuery = ref("");
const stockLoading = ref(false);
const stockPanelCollapsed = ref(true);
const infosSaveDebounce = ref<ReturnType<typeof setTimeout> | null>(null);
const newClientEmail = ref("");
const isAddingClientEmail = ref(false);
const isSavingCoverPhoto = ref(false);
const editForm = ref({
  titre: "",
  description: "",
  estPubliee: false,
  modeMiseEnPage: "grille",
});

const invitedEmails = computed(() => {
  const galerie = galerieStore.currentGalerie as any;
  return galerie?.emailsClients || galerie?.emails_clients || [];
});

const currentCoverPhotoId = computed(() => {
  const galerie = galerieStore.currentGalerie as any;
  return galerie?.photoEnteteId || galerie?.photo_entete_id || null;
});

const currentLayoutMode = computed(() => {
  const galerie = galerieStore.currentGalerie as any;
  return galerie?.modeMiseEnPage || galerie?.mode_mise_en_page || "grille";
});

const filteredStockPhotos = computed(() => {
  const query = stockSearchQuery.value.trim().toLowerCase();
  if (!query) return stockPhotos.value;

  return stockPhotos.value.filter((photo: any) => {
    const title = String(
      photo?.titre || photo?.nom_original || "",
    ).toLowerCase();
    return title.includes(query);
  });
});

const stockPhotosCount = computed(() => stockPhotos.value.length);

const isOwner = computed(() => {
  const galerie = galerieStore.currentGalerie as any;
  const ownerId = galerie?.photographe_id || galerie?.photographeId;
  return Boolean(authStore.user && ownerId && ownerId === authStore.user.id);
});

const toPublicationBool = (value: unknown): boolean => {
  if (typeof value === "boolean") return value;
  if (typeof value === "number") return value === 1;
  if (typeof value === "string") {
    const normalized = value.trim().toLowerCase();
    return ["1", "true", "oui", "yes"].includes(normalized);
  }
  return false;
};

const getPublicationValue = (galerie: any): boolean => {
  const rawValue = galerie?.estPubliee ?? galerie?.est_publiee;
  return toPublicationBool(rawValue);
};

const syncEditForm = () => {
  const galerie = galerieStore.currentGalerie as any;
  if (!galerie) return;
  editForm.value = {
    titre: galerie.titre || "",
    description: galerie.description || "",
    estPubliee: getPublicationValue(galerie),
    modeMiseEnPage:
      galerie.modeMiseEnPage || galerie.mode_mise_en_page || "grille",
  };
};

const resolvePhotoSrc = (photo: any) => {
  if (photo?.url) return photo.url;
  if (photo?.id) {
    const photoBase =
      import.meta.env.VITE_STORAGE_PHOTO_URL ||
      `${import.meta.env.VITE_API_BACK_URL || "http://localhost:6081/api/back"}/storage/photos`;
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

const saveGalerieInfos = async () => {
  const galerie = galerieStore.currentGalerie;
  if (!galerie) return;

  const titre = editForm.value.titre.trim();
  const description = editForm.value.description.trim();

  if (!titre) {
    saveError.value = "Le nom de la galerie est obligatoire.";
    saveSuccess.value = "";
    return;
  }

  const titreActuel = (galerie.titre || "").trim();
  const descriptionActuelle = (galerie.description || "").trim();
  if (titre === titreActuel && description === descriptionActuelle) {
    return;
  }

  saveError.value = "";
  saveSuccess.value = "";
  isSavingInfos.value = true;

  try {
    await galerieStore.modifierInfosGalerie(galerie.id, {
      titre,
      description,
    });
    saveSuccess.value = "Nom et description enregistrés.";
  } catch (error) {
    saveError.value = "Impossible d'enregistrer le nom/la description.";
  } finally {
    isSavingInfos.value = false;
  }
};

const handlePublicationToggle = async () => {
  const galerie = galerieStore.currentGalerie;
  if (!galerie) return;

  const nouvelleValeur = editForm.value.estPubliee;
  saveError.value = "";
  saveSuccess.value = "";
  isSavingPublication.value = true;

  try {
    await galerieStore.modifierPublicationGalerie(galerie.id, nouvelleValeur);
    saveSuccess.value = nouvelleValeur
      ? "Galerie publiée."
      : "Galerie dépubliée.";
  } catch (error) {
    editForm.value.estPubliee = !nouvelleValeur;
    saveError.value = "Impossible de modifier la publication.";
  } finally {
    isSavingPublication.value = false;
  }
};

const handleLayoutModeChange = async () => {
  const galerie = galerieStore.currentGalerie;
  if (!galerie) return;

  const selectedMode = editForm.value.modeMiseEnPage || "grille";
  const currentMode = currentLayoutMode.value;
  if (selectedMode === currentMode) {
    return;
  }

  saveError.value = "";
  saveSuccess.value = "";

  try {
    await galerieStore.modifierMiseEnPage(galerie.id, selectedMode);
    saveSuccess.value =
      selectedMode === "slideshow"
        ? "Mode slideshow enregistré."
        : "Mode galerie enregistré.";
  } catch (error) {
    editForm.value.modeMiseEnPage = currentMode;
    saveError.value = "Impossible de modifier le mode d'affichage.";
  }
};

const addClientAccess = async () => {
  const galerie = galerieStore.currentGalerie;
  if (!galerie) return;

  const email = newClientEmail.value.trim().toLowerCase();
  if (!email) {
    saveError.value = "Veuillez renseigner une adresse email client.";
    return;
  }

  isAddingClientEmail.value = true;
  saveError.value = "";
  saveSuccess.value = "";
  try {
    await galerieStore.ajouterEmailClient(galerie.id, email);
    await fetchGalerieContent();
    newClientEmail.value = "";
    saveSuccess.value = "Accès client ajouté.";
  } catch (error) {
    saveError.value = "Impossible d'ajouter cet email client.";
  } finally {
    isAddingClientEmail.value = false;
  }
};

const copyAccessCode = async () => {
  const codeAcces =
    galerieStore.currentGalerie?.code_acces ||
    galerieStore.currentGalerie?.codeAcces;
  if (!codeAcces) return;

  try {
    await navigator.clipboard.writeText(codeAcces);
    saveSuccess.value = "Code d'accès copié dans le presse-papiers !";
    setTimeout(() => {
      saveSuccess.value = "";
    }, 3000);
  } catch (error) {
    saveError.value = "Impossible de copier le code.";
  }
};

const choisirPhotoEntete = async (photoId: string) => {
  const galerie = galerieStore.currentGalerie;
  if (!galerie) return;

  isSavingCoverPhoto.value = true;
  saveError.value = "";
  saveSuccess.value = "";
  try {
    await galerieStore.definirPhotoEntete(galerie.id, photoId);
    await fetchGalerieContent();
    saveSuccess.value = "Photo d'entête enregistrée.";
  } catch (error) {
    saveError.value = "Impossible de définir la photo d'entête.";
  } finally {
    isSavingCoverPhoto.value = false;
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

watch(
  () => galerieStore.currentGalerie,
  (galerie: Galerie | null) => {
    if (galerie) {
      syncEditForm();
    }
  },
  { immediate: true },
);

watch(
  () => [editForm.value.titre, editForm.value.description],
  () => {
    if (!isOwner.value) return;

    if (infosSaveDebounce.value) {
      clearTimeout(infosSaveDebounce.value);
    }

    infosSaveDebounce.value = setTimeout(() => {
      saveGalerieInfos();
    }, 700);
  },
);
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
              v-if="isOwner"
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
              galerieStore.currentGalerie?.dateCreation
                ? new Date(
                    galerieStore.currentGalerie.dateCreation,
                  ).toLocaleDateString("fr-FR")
                : "Date inconnue"
            }}
          </p>

          <div v-if="isOwner" class="edit-panel">
            <h2>Modifier la galerie</h2>
            <label class="field-label" for="galerie-title">Nom</label>
            <input
              id="galerie-title"
              v-model="editForm.titre"
              @blur="
                () => {
                  clearTimeout(infosSaveDebounce);
                  saveGalerieInfos();
                }
              "
              class="edit-input"
              type="text"
              placeholder="Nom de la galerie"
            />

            <label class="field-label" for="galerie-description"
              >Description</label
            >
            <textarea
              id="galerie-description"
              v-model="editForm.description"
              @blur="
                () => {
                  clearTimeout(infosSaveDebounce);
                  saveGalerieInfos();
                }
              "
              class="edit-textarea"
              rows="4"
              placeholder="Décrivez cette galerie"
            ></textarea>

            <div class="publish-row">
              <span class="field-label">Publication</span>
              <label class="toggle-label">
                <span>{{ editForm.estPubliee ? "Publiée" : "Dépubliée" }}</span>
                <span class="switch">
                  <input
                    v-model="editForm.estPubliee"
                    type="checkbox"
                    :disabled="isSavingPublication"
                    @change="handlePublicationToggle"
                  />
                  <span class="slider"></span>
                </span>
              </label>
            </div>

            <div class="mode-panel">
              <label class="field-label" for="layout-mode"
                >Mode d'affichage</label
              >
              <select
                id="layout-mode"
                v-model="editForm.modeMiseEnPage"
                class="edit-input mode-select"
                @change="handleLayoutModeChange"
              >
                <option value="grille">Galerie classique</option>
                <option value="slideshow">Slideshow</option>
              </select>
            </div>

            <div class="access-panel">
              <h3>Accès client</h3>
              <div class="access-row">
                <input
                  v-model="newClientEmail"
                  class="edit-input"
                  type="email"
                  placeholder="email.client@exemple.com"
                />
                <button
                  class="add-access-btn"
                  :disabled="isAddingClientEmail"
                  @click="addClientAccess"
                >
                  {{ isAddingClientEmail ? "Ajout..." : "Ajouter" }}
                </button>
              </div>
              <div class="access-list">
                <p v-if="invitedEmails.length === 0" class="access-empty">
                  Aucun client n'a encore accès à cette galerie.
                </p>
                <ul v-else>
                  <li v-for="email in invitedEmails" :key="email">
                    {{ email }}
                  </li>
                </ul>
              </div>
            </div>

            <div
              class="access-code-panel"
              v-if="
                galerieStore.currentGalerie?.code_acces ||
                galerieStore.currentGalerie?.codeAcces
              "
            >
              <h3>Code d'accès de la galerie</h3>
              <div class="code-display">
                <code class="access-code">{{
                  galerieStore.currentGalerie?.code_acces ||
                  galerieStore.currentGalerie?.codeAcces
                }}</code>
                <button
                  class="copy-code-btn"
                  @click="copyAccessCode"
                  title="Copier le code d'accès"
                >
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
                    <rect
                      x="9"
                      y="9"
                      width="13"
                      height="13"
                      rx="2"
                      ry="2"
                    ></rect>
                    <path
                      d="M5 15H4a2 2 0 0 1-2-2V4a2 2 0 0 1 2-2h9a2 2 0 0 1 2 2v1"
                    ></path>
                  </svg>
                </button>
              </div>
              <p class="code-hint">
                Partagez ce code avec vos clients pour accéder à la galerie
                privée.
              </p>
            </div>

            <div
              v-if="
                isSavingInfos ||
                isSavingPublication ||
                isAddingClientEmail ||
                isSavingCoverPhoto
              "
              class="save-message"
            >
              Enregistrement en base de données...
            </div>

            <div v-if="saveError" class="save-message save-error">
              {{ saveError }}
            </div>
            <div v-else-if="saveSuccess" class="save-message save-success">
              {{ saveSuccess }}
            </div>
          </div>

          <p
            v-else-if="galerieStore.currentGalerie.description"
            class="description-longue"
          >
            {{ galerieStore.currentGalerie.description }}
          </p>

          <div v-if="authStore.user" class="stock-container">
            <div class="stock-header">
              <div class="stock-header-copy">
                <h2>Stock photo</h2>
                <p>
                  {{ stockPhotosCount }} photo(s) déjà uploadée(s), prêtes à
                  être ajoutées à une galerie.
                </p>
              </div>
              <button
                type="button"
                class="stock-toggle-btn"
                :aria-expanded="!stockPanelCollapsed"
                aria-controls="stock-panel-content"
                @click="stockPanelCollapsed = !stockPanelCollapsed"
              >
                {{
                  stockPanelCollapsed ? "Afficher le stock" : "Replier le stock"
                }}
              </button>
            </div>

            <div
              id="stock-panel-content"
              :class="[
                'stock-panel-content',
                { collapsed: stockPanelCollapsed },
              ]"
            >
              <PhotoSearchField
                v-model="stockSearchQuery"
                id="stock-search"
                label="Rechercher par titre"
                placeholder="Ex: portrait, mer, famille..."
                class="stock-search"
              />

              <div v-if="stockLoading" class="no-photos stock-state">
                <div class="spinner"></div>
                Chargement du stock...
              </div>

              <div
                v-else-if="stockPhotos.length === 0"
                class="no-photos stock-state"
              >
                <div class="empty-icon">🗂️</div>
                <p>Aucune photo dans le stock pour le moment.</p>
              </div>

              <div
                v-else-if="filteredStockPhotos.length === 0"
                class="no-photos stock-state"
              >
                <div class="empty-icon">🔎</div>
                <p>Aucune photo ne correspond à votre recherche.</p>
              </div>

              <div v-else class="stock-grid">
                <PhotoThumbnailCard
                  v-for="photo in filteredStockPhotos"
                  :key="photo.id"
                  :photo="photo"
                  :show-title="true"
                  variant="compact"
                  class="stock-photo-card"
                >
                  <template #actions="{ photo: stockPhoto }">
                    <button
                      class="cover-stock-btn"
                      :class="{
                        selected: currentCoverPhotoId === stockPhoto.id,
                      }"
                      :disabled="isSavingCoverPhoto"
                      @click="choisirPhotoEntete(stockPhoto.id)"
                    >
                      {{
                        currentCoverPhotoId === stockPhoto.id
                          ? "Photo d'entête"
                          : "Choisir comme entête"
                      }}
                    </button>
                    <button
                      class="add-stock-btn"
                      @click="addStockPhotoToGalerie(stockPhoto.id)"
                    >
                      Ajouter à cette galerie
                    </button>
                  </template>
                </PhotoThumbnailCard>
              </div>
            </div>
          </div>
        </div>

        <!-- Section des photos -->
        <div class="photos-container">
          <GallerySlideshow
            v-if="currentLayoutMode === 'slideshow'"
            :photos="galerieStore.currentGalerie.photos || []"
            :fallback-src="photoFallback"
          />

          <template v-else>
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
          </template>
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

.edit-panel {
  margin: 0 auto 2rem;
  width: 100%;
  max-width: 680px;
  min-width: 0;
  text-align: left;
  background: rgba(17, 24, 39, 0.65);
  border: 1px solid rgba(255, 255, 255, 0.08);
  border-radius: 16px;
  padding: 1.25rem;
  box-sizing: border-box;
  overflow: hidden;
}

.edit-panel h2 {
  margin: 0 0 1rem;
  font-size: 1.1rem;
}

.field-label {
  display: block;
  margin-bottom: 0.4rem;
  color: #cbd5e1;
  font-weight: 600;
  font-size: 0.9rem;
}

.edit-input,
.edit-textarea {
  width: 100%;
  box-sizing: border-box;
  border: 1px solid rgba(148, 163, 184, 0.35);
  background: rgba(15, 23, 42, 0.75);
  color: #f8fafc;
  border-radius: 10px;
  padding: 0.7rem 0.85rem;
  font-size: 0.95rem;
  margin-bottom: 1rem;
}

.edit-textarea {
  resize: vertical;
  min-height: 96px;
}

.mode-panel {
  margin-bottom: 1rem;
}

.mode-select {
  appearance: none;
}

.publish-row {
  display: flex;
  justify-content: space-between;
  align-items: center;
  gap: 1rem;
  margin-bottom: 1rem;
  flex-wrap: wrap;
}

.toggle-label {
  display: flex;
  align-items: center;
  gap: 0.75rem;
  color: #e2e8f0;
  flex-wrap: wrap;
}

.switch {
  position: relative;
  display: inline-block;
  width: 52px;
  height: 30px;
}

.switch input {
  opacity: 0;
  width: 0;
  height: 0;
}

.slider {
  position: absolute;
  cursor: pointer;
  inset: 0;
  background-color: rgba(239, 68, 68, 0.35);
  border: 1px solid rgba(239, 68, 68, 0.5);
  transition: 0.25s;
  border-radius: 999px;
}

.slider:before {
  position: absolute;
  content: "";
  height: 22px;
  width: 22px;
  left: 3px;
  top: 3px;
  background-color: #f8fafc;
  transition: 0.25s;
  border-radius: 50%;
}

.switch input:checked + .slider {
  background-color: rgba(34, 197, 94, 0.35);
  border-color: rgba(34, 197, 94, 0.55);
}

.switch input:checked + .slider:before {
  transform: translateX(22px);
}

.switch input:disabled + .slider {
  opacity: 0.65;
  cursor: wait;
}

.save-message {
  margin-bottom: 0.8rem;
  font-size: 0.9rem;
}

.save-error {
  color: #fda4af;
}

.save-success {
  color: #86efac;
}

.access-panel {
  margin-top: 1rem;
  padding-top: 1rem;
  border-top: 1px solid rgba(255, 255, 255, 0.08);
}

.access-panel h3 {
  margin: 0 0 0.7rem;
  font-size: 0.95rem;
}

.access-row {
  display: flex;
  gap: 0.6rem;
  align-items: center;
  flex-wrap: wrap;
}

.access-row .edit-input {
  margin-bottom: 0;
  flex: 1 1 240px;
}

.add-access-btn {
  flex: 0 0 auto;
  border: 1px solid rgba(59, 130, 246, 0.4);
  background: rgba(59, 130, 246, 0.2);
  color: #dbeafe;
  border-radius: 10px;
  padding: 0.65rem 0.9rem;
  font-weight: 600;
  cursor: pointer;
}

.add-access-btn:disabled {
  opacity: 0.65;
  cursor: wait;
}

.access-list {
  margin-top: 0.8rem;
  max-height: 120px;
  overflow-y: auto;
}

.access-list ul {
  margin: 0;
  padding-left: 1rem;
}

.access-empty {
  margin: 0;
  color: #94a3b8;
  font-size: 0.9rem;
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
  align-items: center;
  gap: 1rem;
  margin-bottom: 1.5rem;
  flex-wrap: wrap;
}

.stock-header-copy {
  min-width: 0;
}

.stock-header h2 {
  margin: 0;
  font-size: 1.6rem;
}

.stock-header p {
  margin: 0;
  color: #9ca3af;
}

.stock-toggle-btn {
  flex: 0 0 auto;
  border: 1px solid rgba(148, 163, 184, 0.35);
  background: rgba(15, 23, 42, 0.8);
  color: #e2e8f0;
  border-radius: 999px;
  padding: 0.55rem 0.95rem;
  font-weight: 600;
  cursor: pointer;
  transition: all 0.2s ease;
}

.stock-toggle-btn:hover {
  border-color: rgba(59, 130, 246, 0.5);
  color: #fff;
}

.stock-panel-content {
  display: grid;
  gap: 1rem;
}

.stock-panel-content.collapsed {
  display: none;
}

.stock-search {
  min-width: 220px;
  max-width: 320px;
  width: 100%;
}

.stock-grid {
  display: grid;
  grid-template-columns: repeat(auto-fill, minmax(140px, 1fr));
  gap: 0.7rem;
  align-items: start;
}

.stock-photo-card :deep(.photo-image.compact) {
  height: 96px;
}

.stock-photo-card :deep(.photo-meta.compact) {
  padding: 0.4rem 0.55rem;
  font-size: 0.76rem;
}

.stock-photo-card :deep(.photo-actions.compact) {
  padding: 0.4rem 0.55rem 0.55rem;
}

.stock-state {
  margin-top: 0.25rem;
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

.stock-actions {
  display: flex;
  flex-direction: column;
  justify-content: center;
  gap: 0.35rem;
  padding: 0.55rem;
  background: rgba(17, 24, 39, 0.95);
}

.cover-stock-btn {
  padding: 0.3rem 0.55rem;
  border: 1px solid rgba(16, 185, 129, 0.35);
  background: rgba(16, 185, 129, 0.12);
  color: #bbf7d0;
  border-radius: 999px;
  font-weight: 600;
  font-size: 0.72rem;
  cursor: pointer;
}

.cover-stock-btn.selected {
  background: rgba(16, 185, 129, 0.25);
  border-color: rgba(16, 185, 129, 0.6);
}

.add-stock-btn {
  padding: 0.3rem 0.55rem;
  border: 1px solid rgba(59, 130, 246, 0.35);
  background: rgba(59, 130, 246, 0.12);
  color: #bfdbfe;
  border-radius: 999px;
  font-weight: 600;
  font-size: 0.72rem;
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

.access-code-panel {
  margin-top: 1rem;
  padding-top: 1rem;
  border-top: 1px solid rgba(255, 255, 255, 0.08);
}

.access-code-panel h3 {
  margin: 0 0 0.7rem;
  font-size: 0.95rem;
}

.code-display {
  display: flex;
  align-items: center;
  gap: 0.6rem;
  margin-bottom: 0.8rem;
}

.access-code {
  flex: 1;
  background: rgba(15, 23, 42, 0.8);
  border: 1px solid rgba(148, 163, 184, 0.35);
  color: #dbeafe;
  padding: 0.75rem 0.9rem;
  border-radius: 8px;
  font-family: "Courier New", monospace;
  font-size: 1rem;
  font-weight: 600;
  letter-spacing: 1px;
  word-break: break-all;
}

.copy-code-btn {
  flex: 0 0 auto;
  display: flex;
  align-items: center;
  justify-content: center;
  width: 40px;
  height: 40px;
  border: 1px solid rgba(59, 130, 246, 0.4);
  background: rgba(59, 130, 246, 0.15);
  color: #dbeafe;
  border-radius: 8px;
  cursor: pointer;
  transition: all 0.2s ease;
}

.copy-code-btn:hover {
  background: rgba(59, 130, 246, 0.25);
  border-color: rgba(59, 130, 246, 0.6);
  transform: translateY(-2px);
}

.code-hint {
  margin: 0;
  font-size: 0.85rem;
  color: #94a3b8;
  font-style: italic;
}
</style>
