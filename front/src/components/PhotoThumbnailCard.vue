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
    variant?: "default" | "compact" | "thumb";
  }>(),
  {
    fallbackSrc: "/img-placeholder.svg",
    showTitle: true,
    imgClass: "",
    variant: "default",
  },
);

const { photo, fallbackSrc, showTitle, imgClass, variant } = toRefs(props);

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
      :class="['photo-image', imgClass, { compact: variant === 'compact' }]"
      loading="lazy"
    />
    <div
      v-if="showTitle"
      :class="['photo-meta', { compact: variant === 'compact' }]"
    >
      {{ displayTitle }}
    </div>
    <div
      v-if="$slots.actions"
      :class="['photo-actions', { compact: variant === 'compact' }]"
    >
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

.photo-image.compact {
  height: 120px;
}

.photo-image.thumb {
  height: 80px;
}

.photo-image.slideshow-image {
  height: min(72vh, 680px);
  object-fit: contain;
  background: rgba(3, 7, 18, 0.95);
  box-sizing: border-box;
  padding: 0.5rem;
}

.photo-meta {
  padding: 0.7rem 0.9rem;
  color: #cbd5e1;
  font-size: 0.9rem;
}

.photo-meta.compact {
  padding: 0.5rem 0.65rem;
  font-size: 0.8rem;
}

.photo-meta.thumb {
  display: none;
}

.photo-actions {
  display: flex;
  flex-direction: column;
  gap: 0.35rem;
  padding: 0.75rem 0.9rem 0.9rem;
  background: rgba(17, 24, 39, 0.95);
}

.photo-actions.compact {
  padding: 0.5rem 0.65rem 0.65rem;
  gap: 0.25rem;
}

.photo-actions.thumb {
  display: none;
}
</style>
