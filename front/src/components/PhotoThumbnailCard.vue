<script setup lang="ts">
import { computed, toRefs } from "vue";

type PhotoLike = {
  id: string;
  url?: string;
  titre?: string;
  nom_original?: string;
};

const props = withDefaults(
  defineProps<{
    photo: PhotoLike;
    fallbackSrc?: string;
    showTitle?: boolean;
    imgClass?: string;
  }>(),
  {
    fallbackSrc: "/img-placeholder.svg",
    showTitle: true,
    imgClass: "",
  },
);

const { photo, fallbackSrc, showTitle, imgClass } = toRefs(props);

const resolvePhotoSrc = (photo: PhotoLike, fallbackSrc: string) => {
  if (photo?.url) return photo.url;
  if (photo?.id) {
    const photoBase =
      import.meta.env.VITE_STORAGE_PHOTO_URL ||
      `${import.meta.env.VITE_API_BACK_URL || "http://localhost:6081/api/back"}/storage/photos`;
    return `${photoBase}/${photo.id}`;
  }
  return fallbackSrc;
};

const imageSrc = computed(() =>
  resolvePhotoSrc(photo.value, fallbackSrc.value),
);

const displayTitle = computed(
  () => photo.value.titre || photo.value.nom_original || "Sans titre",
);
</script>

<template>
  <div class="photo-card">
    <img
      :src="imageSrc"
      :alt="displayTitle"
      :class="['photo-image', imgClass]"
      loading="lazy"
    />
    <div v-if="showTitle" class="photo-meta">
      {{ displayTitle }}
    </div>
    <div v-if="$slots.actions" class="photo-actions">
      <slot name="actions" :photo="photo" />
    </div>
  </div>
</template>

<style scoped>
.photo-card {
  border-radius: 12px;
  overflow: hidden;
  border: 1px solid rgba(255, 255, 255, 0.08);
  background: rgba(255, 255, 255, 0.03);
}

.photo-image {
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

.photo-actions {
  display: flex;
  flex-direction: column;
  gap: 0.35rem;
  padding: 0.75rem 0.9rem 0.9rem;
  background: rgba(17, 24, 39, 0.95);
}
</style>
