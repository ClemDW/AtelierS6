<script setup lang="ts">
import { onMounted, ref } from "vue";
import { useRouter } from "vue-router";
import { useAuthStore } from "../stores/auth";
import { useGalerieStore, type Photo } from "../stores/galerie";

const router = useRouter();
const authStore = useAuthStore();
const galerieStore = useGalerieStore();

const isLoading = ref(false);
const isUploading = ref(false);
const isDragging = ref(false);
const uploadProgress = ref(0);
const uploadTotal = ref(0);
const uploadSuccessCount = ref(0);
const photos = ref<Photo[]>([]);
const errorMessage = ref("");
const pendingUploads = ref<Array<{ file: File; titre: string }>>([]);
const fileInput = ref<HTMLInputElement | null>(null);

const titleFromFileName = (fileName: string) => {
  return fileName.trim();
};

const isImageFile = (file: File) => file.type.startsWith("image/");

const appendFiles = (files: File[]) => {
  const validFiles = files.filter(isImageFile);
  const existingKeys = new Set(
    pendingUploads.value.map(
      (item: { file: File; titre: string }) =>
        `${item.file.name}:${item.file.size}:${item.file.lastModified}`,
    ),
  );

  const newItems = validFiles
    .filter(
      (file) =>
        !existingKeys.has(`${file.name}:${file.size}:${file.lastModified}`),
    )
    .map((file) => ({
      file,
      titre: titleFromFileName(file.name),
    }));

  pendingUploads.value = [...pendingUploads.value, ...newItems];
};

const resolvePhotoSrc = (photo: any) => {
  if (photo?.url) return photo.url;
  if (photo?.id) {
    const photoBase =
      import.meta.env.VITE_STORAGE_PHOTO_URL ||
      `${import.meta.env.VITE_API_BACK_URL || "http://localhost:6081/api/back"}/storage/photos`;
    return `${photoBase}/${photo.id}`;
  }
  return "/img-placeholder.svg";
};

const loadMyPhotos = async () => {
  const userId = authStore.user?.id;
  if (!userId) {
    router.push({ name: "login" });
    return;
  }

  isLoading.value = true;
  errorMessage.value = "";
  try {
    const response = await galerieStore.loadUserPhotos(userId);
    photos.value = Array.isArray(response) ? response : [];
  } catch (error) {
    errorMessage.value = "Impossible de charger votre stock de photos.";
  } finally {
    isLoading.value = false;
  }
};

const queueFiles = (files: File[]) => {
  appendFiles(files);
};

const processPendingUploads = async () => {
  const userId = authStore.user?.id;
  if (!userId) return;
  if (pendingUploads.value.length === 0) return;

  isUploading.value = true;
  uploadTotal.value = pendingUploads.value.length;
  uploadSuccessCount.value = 0;
  uploadProgress.value = 0;

  try {
    for (const item of pendingUploads.value) {
      const titre = item.titre.trim() || item.file.name;
      await galerieStore.uploadPhoto(userId, item.file, titre);
      uploadSuccessCount.value++;
      uploadProgress.value = Math.round(
        (uploadSuccessCount.value / uploadTotal.value) * 100,
      );
    }
    pendingUploads.value = [];
    await loadMyPhotos();
  } catch (error) {
    alert("Erreur lors de l'upload des photos.");
  } finally {
    isUploading.value = false;
  }
};

const onFileSelect = (e: Event) => {
  const input = e.target as HTMLInputElement;
  if (input.files && input.files.length > 0) {
    queueFiles(Array.from(input.files));
    input.value = "";
  }
};

const handleDrop = (e: DragEvent) => {
  isDragging.value = false;
  const files = e.dataTransfer?.files;
  if (files && files.length > 0) {
    queueFiles(Array.from(files));
  }
};

const openFilePicker = () => {
  fileInput.value?.click();
};

const handleLogout = () => {
  authStore.logout();
  router.push({ name: "login" });
};

onMounted(() => {
  loadMyPhotos();
});
</script>

<template>
  <div class="my-photos-wrapper">
    <nav class="glass-nav">
      <RouterLink :to="{ name: 'home' }" class="nav-brand"
        >Photo<span class="highlight">Pro</span></RouterLink
      >
      <div class="nav-actions">
        <RouterLink :to="{ name: 'my-galeries' }" class="nav-link"
          >Mes Galeries</RouterLink
        >
        <button @click="handleLogout" class="logout-btn">Déconnexion</button>
      </div>
    </nav>

    <main class="main-content">
      <header class="page-header">
        <div>
          <h1>Mes Photos</h1>
          <p>
            Uploadez et gérez votre stock photo avant de l'ajouter aux galeries.
          </p>
        </div>
      </header>

      <section class="upload-section">
        <div
          class="dropzone"
          :class="{ 'is-dragging': isDragging, 'is-uploading': isUploading }"
          @dragover.prevent="isDragging = true"
          @dragleave.prevent="isDragging = false"
          @drop.prevent="handleDrop"
          @click="openFilePicker"
        >
          <div class="dropzone-content">
            <strong v-if="!isUploading">Uploader de nouvelles photos</strong>
            <strong v-else>Upload en cours...</strong>
            <span v-if="!isUploading"
              >Glissez-déposez des images ou cliquez ici</span
            >
            <span v-else
              >{{ uploadSuccessCount }} / {{ uploadTotal }} fichiers</span
            >
            <button
              type="button"
              class="picker-btn"
              @click.stop="openFilePicker"
              :disabled="isUploading"
            >
              Choisir plusieurs photos
            </button>
          </div>
          <div v-if="isUploading" class="progress-bar">
            <div
              class="progress-fill"
              :style="{ width: uploadProgress + '%' }"
            ></div>
          </div>
        </div>
        <input
          ref="fileInput"
          type="file"
          multiple
          accept="image/*"
          @change="onFileSelect"
          class="hidden-input"
        />

        <div v-if="pendingUploads.length > 0" class="pending-panel">
          <h3>Titres des photos à uploader</h3>
          <div class="pending-list">
            <div
              v-for="(item, index) in pendingUploads"
              :key="item.file.name + index"
              class="pending-item"
            >
              <div class="pending-file">{{ item.file.name }}</div>
              <input
                v-model="item.titre"
                type="text"
                class="title-input"
                placeholder="Titre de la photo"
              />
            </div>
          </div>
          <div class="pending-actions">
            <button
              class="ghost-btn"
              @click="pendingUploads = []"
              :disabled="isUploading"
            >
              Annuler
            </button>
            <button
              class="primary-btn"
              @click="processPendingUploads"
              :disabled="isUploading"
            >
              Lancer l'upload
            </button>
          </div>
        </div>
      </section>

      <section class="photos-section">
        <div v-if="isLoading" class="state">Chargement du stock...</div>
        <div v-else-if="errorMessage" class="state error">
          {{ errorMessage }}
        </div>
        <div v-else-if="photos.length === 0" class="state">
          Aucune photo dans votre stock.
        </div>

        <div v-else class="photos-grid">
          <div v-for="photo in photos" :key="photo.id" class="photo-card">
            <img
              :src="resolvePhotoSrc(photo)"
              :alt="photo.titre || 'Photo'"
              loading="lazy"
            />
            <div class="photo-meta">
              {{ photo.titre || (photo as any).nom_original || "Sans titre" }}
            </div>
          </div>
        </div>
      </section>
    </main>
  </div>
</template>

<style scoped>
.my-photos-wrapper {
  min-height: 100vh;
  background: #0b0f19;
  color: #f1f5f9;
}

.glass-nav {
  display: flex;
  justify-content: space-between;
  align-items: center;
  padding: 1rem 2rem;
  background: rgba(17, 24, 39, 0.4);
  border-bottom: 1px solid rgba(255, 255, 255, 0.06);
}

.nav-brand {
  color: #fff;
  text-decoration: none;
  font-weight: 700;
  font-size: 1.4rem;
}

.highlight {
  color: #3b82f6;
}

.nav-actions {
  display: flex;
  align-items: center;
  gap: 1rem;
}

.nav-link {
  color: #cbd5e1;
  text-decoration: none;
}

.logout-btn {
  background: rgba(239, 68, 68, 0.12);
  border: 1px solid rgba(239, 68, 68, 0.35);
  color: #ef4444;
  border-radius: 10px;
  padding: 0.5rem 0.9rem;
  cursor: pointer;
}

.main-content {
  max-width: 1200px;
  margin: 0 auto;
  padding: 2rem 1.5rem 3rem;
}

.page-header h1 {
  margin: 0;
  font-size: 2.2rem;
}

.page-header p {
  color: #94a3b8;
}

.upload-section {
  margin: 1.5rem 0 2rem;
}

.dropzone {
  border: 2px dashed rgba(255, 255, 255, 0.18);
  border-radius: 14px;
  background: rgba(255, 255, 255, 0.03);
  padding: 2rem;
  cursor: pointer;
  position: relative;
  overflow: hidden;
}

.dropzone.is-dragging {
  border-color: #3b82f6;
  background: rgba(59, 130, 246, 0.1);
}

.dropzone-content {
  text-align: center;
  display: flex;
  flex-direction: column;
  gap: 0.4rem;
  align-items: center;
}

.dropzone-content span {
  color: #94a3b8;
}

.progress-bar {
  position: absolute;
  left: 0;
  right: 0;
  bottom: 0;
  height: 4px;
  background: rgba(255, 255, 255, 0.08);
}

.progress-fill {
  height: 100%;
  background: linear-gradient(90deg, #3b82f6, #60a5fa);
}

.hidden-input {
  display: none;
}

.picker-btn {
  margin-top: 0.5rem;
  border: 1px solid rgba(59, 130, 246, 0.45);
  background: rgba(59, 130, 246, 0.18);
  color: #dbeafe;
  border-radius: 999px;
  padding: 0.55rem 0.9rem;
  font-weight: 600;
  cursor: pointer;
}

.picker-btn:disabled {
  opacity: 0.7;
  cursor: wait;
}

.pending-panel {
  margin-top: 1rem;
  background: rgba(255, 255, 255, 0.03);
  border: 1px solid rgba(255, 255, 255, 0.08);
  border-radius: 12px;
  padding: 1rem;
}

.pending-panel h3 {
  margin: 0 0 0.75rem 0;
  font-size: 1rem;
}

.pending-list {
  display: flex;
  flex-direction: column;
  gap: 0.6rem;
}

.pending-item {
  display: grid;
  grid-template-columns: 1fr 1.2fr;
  gap: 0.6rem;
  align-items: center;
}

.pending-file {
  color: #cbd5e1;
  font-size: 0.9rem;
  overflow: hidden;
  text-overflow: ellipsis;
  white-space: nowrap;
}

.title-input {
  border: 1px solid rgba(255, 255, 255, 0.15);
  border-radius: 8px;
  background: rgba(15, 23, 42, 0.7);
  color: #f1f5f9;
  padding: 0.5rem 0.65rem;
}

.pending-actions {
  margin-top: 0.8rem;
  display: flex;
  justify-content: flex-end;
  gap: 0.6rem;
}

.ghost-btn,
.primary-btn {
  border-radius: 8px;
  padding: 0.5rem 0.85rem;
  cursor: pointer;
  font-weight: 600;
}

.ghost-btn {
  background: transparent;
  border: 1px solid rgba(255, 255, 255, 0.2);
  color: #cbd5e1;
}

.primary-btn {
  background: #3b82f6;
  border: 1px solid #3b82f6;
  color: #fff;
}

.photos-grid {
  display: grid;
  grid-template-columns: repeat(auto-fill, minmax(240px, 1fr));
  gap: 1rem;
}

.photo-card {
  border-radius: 12px;
  overflow: hidden;
  border: 1px solid rgba(255, 255, 255, 0.08);
  background: rgba(255, 255, 255, 0.03);
}

.photo-card img {
  width: 100%;
  height: 200px;
  object-fit: cover;
  display: block;
}

.photo-meta {
  padding: 0.7rem 0.9rem;
  color: #cbd5e1;
  font-size: 0.9rem;
}

.state {
  text-align: center;
  padding: 3rem 1rem;
  color: #94a3b8;
}

.state.error {
  color: #ef4444;
}
</style>
