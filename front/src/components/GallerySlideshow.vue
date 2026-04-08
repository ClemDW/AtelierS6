<script setup lang="ts">
import { computed, ref, watch } from "vue";
import type { Photo } from "../stores/galerie";
import PhotoThumbnailCard from "./PhotoThumbnailCard.vue";

const props = withDefaults(
  defineProps<{
    photos: Photo[];
    fallbackSrc?: string;
  }>(),
  {
    fallbackSrc: "/img-placeholder.svg",
  },
);

const currentIndex = ref(0);

const hasPhotos = computed(() => props.photos.length > 0);
const currentPhoto = computed(() => props.photos[currentIndex.value] ?? null);

const goPrevious = () => {
  if (!hasPhotos.value) return;
  currentIndex.value =
    currentIndex.value === 0 ? props.photos.length - 1 : currentIndex.value - 1;
};

const goNext = () => {
  if (!hasPhotos.value) return;
  currentIndex.value = (currentIndex.value + 1) % props.photos.length;
};

const selectPhoto = (index: number) => {
  currentIndex.value = index;
};

watch(
  () => props.photos,
  () => {
    currentIndex.value = 0;
  },
  { deep: true },
);
</script>

<template>
  <div class="slideshow-shell">
    <div v-if="!hasPhotos" class="slideshow-empty">
      <div class="empty-icon">📷</div>
      <p>Cette galerie ne contient pas encore de photos.</p>
    </div>

    <template v-else>
      <div class="slideshow-stage">
        <button class="nav-button prev" type="button" @click="goPrevious">
          ‹
        </button>

        <PhotoThumbnailCard
          :photo="currentPhoto as Photo"
          :fallback-src="fallbackSrc"
          :show-title="true"
          img-class="slideshow-image"
          class="slideshow-card"
        />

        <button class="nav-button next" type="button" @click="goNext">›</button>
      </div>

      <div class="slideshow-counter">
        {{ currentIndex + 1 }} / {{ photos.length }}
      </div>

      <div class="slideshow-thumbs">
        <button
          v-for="(photo, index) in photos"
          :key="photo.id"
          type="button"
          class="thumb-button"
          :class="{ active: index === currentIndex }"
          @click="selectPhoto(index)"
        >
          <PhotoThumbnailCard
            :photo="photo"
            :fallback-src="fallbackSrc"
            :show-title="false"
            variant="thumb"
          />
        </button>
      </div>
    </template>
  </div>
</template>

<style scoped>
.slideshow-shell {
  display: flex;
  flex-direction: column;
  gap: 1rem;
}

.slideshow-empty {
  text-align: center;
  padding: 5rem 2rem;
  background: rgba(255, 255, 255, 0.02);
  border-radius: 20px;
  border: 1px dashed rgba(255, 255, 255, 0.1);
  color: #9ca3af;
}

.slideshow-stage {
  display: grid;
  grid-template-columns: auto minmax(0, 1fr) auto;
  align-items: center;
  gap: 1rem;
}

.slideshow-card {
  width: 100%;
}

.nav-button {
  width: 46px;
  height: 46px;
  border: 1px solid rgba(255, 255, 255, 0.15);
  border-radius: 999px;
  background: rgba(17, 24, 39, 0.8);
  color: #fff;
  font-size: 1.8rem;
  line-height: 1;
  cursor: pointer;
}

.nav-button:hover {
  background: rgba(59, 130, 246, 0.2);
}

.slideshow-counter {
  text-align: center;
  color: #94a3b8;
  font-size: 0.9rem;
}

.slideshow-thumbs {
  display: grid;
  grid-template-columns: repeat(auto-fill, minmax(90px, 1fr));
  gap: 0.5rem;
}

.thumb-button {
  border: 2px solid transparent;
  border-radius: 12px;
  padding: 0;
  background: transparent;
  cursor: pointer;
  overflow: hidden;
}

.thumb-button.active {
  border-color: #3b82f6;
}
</style>
