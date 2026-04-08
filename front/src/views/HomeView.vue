<script setup lang="ts">
import { ref, onMounted, watch } from "vue";
import { useAuthStore } from "../stores/auth";
import { useGalerieStore } from "../stores/galerie";
import { useRouter } from "vue-router";

const authStore = useAuthStore();
const galerieStore = useGalerieStore();
const router = useRouter();

const recentGaleries = ref<any[]>([]);
const isLoading = ref(false);

const handleLogout = () => {
  authStore.logout();
  router.push({ name: "login" });
};

const getPhotoEnteteUrl = (galerie: any): string => {
  const photoId = galerie?.photoEnteteId || galerie?.photo_entete_id;
  if (!photoId) {
    return "";
  }
  const photoBase =
    import.meta.env.VITE_STORAGE_PHOTO_URL ||
    `${import.meta.env.VITE_API_BACK_URL || "http://localhost:6081/api/back"}/storage/photos`;
  return `${photoBase}/${photoId}`;
};

const fetchRecentGaleries = async () => {
  if (!authStore.user?.id) return;
  isLoading.value = true;
  try {
    const result = await galerieStore.loadUserGaleries(authStore.user.id);
    recentGaleries.value = (result || []).slice(0, 3);
  } catch (error) {
    console.error("Error loading summary:", error);
  } finally {
    isLoading.value = false;
  }
};

watch(
  () => authStore.user,
  (user: { id?: string } | null) => {
    if (user?.id) {
      fetchRecentGaleries();
    }
  },
  { immediate: true },
);

const confirmDelete = async (id: string, title: string) => {
  if (confirm(`Supprimer la galerie "${title}" ?`)) {
    try {
      await galerieStore.supprimerGalerie(id);
      recentGaleries.value = recentGaleries.value.filter(
        (g: any) => g.id !== id,
      );
    } catch (error) {
      alert("Erreur lors de la suppression");
    }
  }
};

onMounted(() => {
  if (authStore.isAuthenticated && !authStore.user) {
    authStore.fetchUserProfile();
  }
});
</script>

<template>
  <div class="dashboard-wrapper">
    <nav class="glass-nav">
      <div class="nav-brand">Photo<span class="highlight">Pro</span></div>

      <div class="nav-user">
        <div class="user-info">
          <span class="greeting"
            >Hello,
            <strong>{{ authStore.user?.email || "Photographer" }}</strong></span
          >
          <span v-if="authStore.user?.role" class="user-role">{{
            authStore.user.role
          }}</span>
        </div>
        <button @click="handleLogout" class="logout-btn">
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
            <path d="M9 21H5a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h4"></path>
            <polyline points="16 17 21 12 16 7"></polyline>
            <line x1="21" y1="12" x2="9" y2="12"></line>
          </svg>
          Logout
        </button>
      </div>
    </nav>

    <main class="dashboard-content">
      <header class="page-header">
        <div class="header-info">
          <h2>Welcome to your studio</h2>
          <p>Manage your photos, galleries, and clients all from one place.</p>
        </div>
        <div class="header-actions">
          <RouterLink :to="{ name: 'my-galeries' }" class="secondary-btn">
            Mes Galeries
          </RouterLink>
          <RouterLink :to="{ name: 'my-photos' }" class="secondary-btn">
            Mes Photos
          </RouterLink>
        </div>
      </header>

      <!-- Section Stats -->
      <section class="stats-grid">
        <div class="stat-card">
          <div class="stat-icon-simple galleries">📸</div>
          <div class="stat-data">
            <h3>Mes Galeries</h3>
            <span class="number">{{
              recentGaleries.length < 3 ? recentGaleries.length : "12"
            }}</span>
          </div>
        </div>
      </section>

      <!-- Section Recent -->
      <section class="recent-section">
        <div class="section-header">
          <h3>Galeries récentes</h3>
          <RouterLink :to="{ name: 'my-galeries' }" class="text-link"
            >Tout voir →</RouterLink
          >
        </div>

        <div v-if="isLoading" class="loading-state">Chargement...</div>
        <div v-else-if="recentGaleries.length === 0" class="empty-recent">
          Aucune galerie à afficher.
        </div>
        <div v-else class="recent-grid">
          <div
            v-for="galerie in recentGaleries"
            :key="galerie.id"
            class="recent-card"
            @click="
              router.push({
                name: 'galerie-detail',
                params: { id: galerie.id },
              })
            "
          >
            <div class="card-mini-img">
              <img
                v-if="getPhotoEnteteUrl(galerie)"
                :src="getPhotoEnteteUrl(galerie)"
                :alt="galerie.titre"
                class="entete-photo"
              />
              <div v-else class="placeholder-dots"></div>
              <button
                @click.stop="confirmDelete(galerie.id, galerie.titre)"
                class="delete-mini-btn"
              >
                <svg
                  xmlns="http://www.w3.org/2000/svg"
                  width="14"
                  height="14"
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
            <div class="card-mini-info">
              <h4>{{ galerie.titre }}</h4>
              <span class="card-mini-date">{{
                new Date(galerie.date_creation).toLocaleDateString("fr-FR", {
                  day: "numeric",
                  month: "short",
                })
              }}</span>
            </div>
          </div>
        </div>
      </section>
    </main>
  </div>
</template>

<style scoped>
.dashboard-wrapper {
  min-height: 100vh;
  background-color: #0b0f19;
  background-image:
    radial-gradient(at 0% 0%, rgba(16, 185, 129, 0.15) 0px, transparent 50%),
    radial-gradient(at 100% 100%, rgba(59, 130, 246, 0.15) 0px, transparent 50%);
  display: flex;
  flex-direction: column;
}

.glass-nav {
  display: flex;
  justify-content: space-between;
  align-items: center;
  padding: 1rem 2rem;
  background: rgba(17, 24, 39, 0.4);
  backdrop-filter: blur(12px);
  -webkit-backdrop-filter: blur(12px);
  border-bottom: 1px solid rgba(255, 255, 255, 0.05);
  position: sticky;
  top: 0;
  z-index: 10;
}

.nav-brand {
  font-size: 1.5rem;
  font-weight: 700;
  letter-spacing: -0.5px;
}

.highlight {
  color: #3b82f6;
}

.nav-user {
  display: flex;
  align-items: center;
  gap: 1.5rem;
}

.greeting {
  color: #94a3b8;
}

.greeting strong {
  color: #f8fafc;
}

.user-info {
  display: flex;
  flex-direction: column;
  align-items: flex-end;
  line-height: 1.2;
}

.user-role {
  font-size: 0.7rem;
  background: rgba(59, 130, 246, 0.2);
  color: #60a5fa;
  padding: 0.15rem 0.5rem;
  border-radius: 4px;
  text-transform: uppercase;
  font-weight: 700;
  letter-spacing: 0.5px;
  margin-top: 4px;
}

.logout-btn {
  display: flex;
  align-items: center;
  gap: 0.5rem;
  background: rgba(239, 68, 68, 0.1);
  color: #ef4444;
  border: 1px solid rgba(239, 68, 68, 0.2);
  padding: 0.6rem 1rem;
  border-radius: 8px;
  cursor: pointer;
  font-family: inherit;
  font-weight: 600;
  transition: all 0.2s;
}

.logout-btn:hover {
  background: rgba(239, 68, 68, 0.2);
  transform: translateY(-1px);
}

.dashboard-content {
  padding: 3rem 2rem;
  max-width: 1200px;
  margin: 0 auto;
  width: 100%;
}

.page-header {
  margin-bottom: 3rem;
  animation: fadeIn 0.5s ease;
  display: flex;
  justify-content: space-between;
  align-items: center;
  flex-wrap: wrap;
  gap: 1rem;
}

.page-header h2 {
  font-size: 2.2rem;
  margin: 0 0 0.5rem 0;
}

.page-header p {
  color: #94a3b8;
  font-size: 1.1rem;
  margin: 0;
}

.header-actions {
  display: flex;
  gap: 1rem;
}

.secondary-btn {
  display: flex;
  align-items: center;
  gap: 0.5rem;
  padding: 0.75rem 1.5rem;
  border-radius: 12px;
  background: rgba(255, 255, 255, 0.05);
  border: 1px solid rgba(255, 255, 255, 0.1);
  color: #ffffff;
  text-decoration: none;
  font-size: 0.95rem;
  font-weight: 600;
  transition: all 0.2s;
  white-space: nowrap;
}

.secondary-btn:hover {
  background: rgba(255, 255, 255, 0.1);
  border-color: rgba(255, 255, 255, 0.2);
  transform: translateY(-2px);
}

.create-btn:hover {
  transform: translateY(-2px);
  box-shadow: 0 10px 25px -10px rgba(59, 130, 246, 0.5);
}

.stats-grid {
  display: grid;
  grid-template-columns: repeat(auto-fit, minmax(280px, 1fr));
  gap: 1.5rem;
  margin-bottom: 3rem;
}

.stat-card {
  background: rgba(30, 41, 59, 0.4);
  border: 1px solid rgba(255, 255, 255, 0.05);
  border-radius: 16px;
  padding: 1.5rem;
  display: flex;
  align-items: center;
  gap: 1.25rem;
  transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
  animation: slideUp 0.6s cubic-bezier(0.16, 1, 0.3, 1) backwards;
}

.stat-card:hover {
  transform: scale(1.02);
  background: rgba(30, 41, 59, 0.6);
  border-color: rgba(59, 130, 246, 0.2);
}

.stat-icon-simple {
  width: 48px;
  height: 48px;
  background: rgba(255, 255, 255, 0.03);
  border-radius: 12px;
  display: flex;
  align-items: center;
  justify-content: center;
  font-size: 1.5rem;
}

.stat-data h3 {
  margin: 0;
  font-size: 0.9rem;
  color: #94a3b8;
  font-weight: 500;
}

.stat-data .number {
  font-size: 1.5rem;
  font-weight: 700;
  color: #fff;
}

/* RECENT SECTION */
.recent-section {
  margin-top: 2rem;
  animation: fadeIn 0.8s ease;
}

.section-header {
  display: flex;
  justify-content: space-between;
  align-items: center;
  margin-bottom: 1.5rem;
}

.section-header h3 {
  font-size: 1.25rem;
  font-weight: 600;
  margin: 0;
}

.text-link {
  color: #3b82f6;
  text-decoration: none;
  font-weight: 500;
  font-size: 0.9rem;
}

.recent-grid {
  display: grid;
  grid-template-columns: repeat(auto-fill, minmax(280px, 1fr));
  gap: 1.5rem;
}

.recent-card {
  background: rgba(17, 24, 39, 0.6);
  border-radius: 16px;
  border: 1px solid rgba(255, 255, 255, 0.05);
  overflow: hidden;
  cursor: pointer;
  transition: all 0.3s;
}

.recent-card:hover {
  border-color: rgba(59, 130, 246, 0.3);
  transform: translateY(-4px);
}

.card-mini-img {
  height: 120px;
  background: #1e293b;
  position: relative;
  overflow: hidden;
}

.entete-photo {
  width: 100%;
  height: 100%;
  object-fit: cover;
  display: block;
}

.placeholder-dots {
  width: 100%;
  height: 100%;
  background-image: radial-gradient(
    rgba(255, 255, 255, 0.05) 1px,
    transparent 1px
  );
  background-size: 15px 15px;
}

.delete-mini-btn {
  position: absolute;
  top: 0.75rem;
  right: 0.75rem;
  padding: 0.4rem;
  background: rgba(239, 68, 68, 0.1);
  color: #ef4444;
  border: 1px solid rgba(239, 68, 68, 0.2);
  border-radius: 6px;
  cursor: pointer;
  opacity: 0;
  transition: all 0.2s;
}

.recent-card:hover .delete-mini-btn {
  opacity: 1;
}

.delete-mini-btn:hover {
  background: #ef4444;
  color: white;
}

.card-mini-info {
  padding: 1rem;
}

.card-mini-info h4 {
  margin: 0 0 0.25rem 0;
  font-size: 1rem;
}

.card-mini-date {
  font-size: 0.8rem;
  color: #64748b;
}

@keyframes fadeIn {
  from {
    opacity: 0;
  }
  to {
    opacity: 1;
  }
}
@keyframes slideUp {
  from {
    opacity: 0;
    transform: translateY(20px);
  }
  to {
    opacity: 1;
    transform: translateY(0);
  }
}
</style>
