<script setup lang="ts">
import { ref, computed } from "vue";
import { useRouter } from "vue-router";
import { useAuthStore } from "../stores/auth";
import { useGalerieStore } from "../stores/galerie";

const router = useRouter();
const authStore = useAuthStore();
const galerieStore = useGalerieStore();

// --- Form state ---
const titre = ref("");
const description = ref("");
const type_visibilite = ref("public"); // 'public' or 'private'
const estPubliee = ref(false);
const emailClient = ref("");

const isCreating = ref(false);
const errorMessage = ref("");
const successMessage = ref("");

type ApiErrorPayload = {
  message?: string;
  error?: string;
};

type ApiError = {
  response?: {
    status?: number;
    _data?: ApiErrorPayload;
  };
  data?: ApiErrorPayload;
  message?: string;
};

const canSubmit = computed(() => {
  return titre.value.trim() !== "" && !isCreating.value;
});

function isValidEmail(email: string): boolean {
  if (!email.trim()) {
    return true;
  }

  return /^[^\s@]+@[^\s@]+\.[^\s@]+$/.test(email.trim());
}

function getCreateGalerieErrorMessage(err: unknown): string {
  const apiError = err as ApiError;
  const status = apiError?.response?.status;
  const backendMessage =
    apiError?.response?._data?.message ||
    apiError?.response?._data?.error ||
    apiError?.data?.message ||
    apiError?.data?.error;

  if (status === 400) {
    return (
      backendMessage ||
      "Les informations saisies sont invalides. Vérifiez le titre et l'email client."
    );
  }

  if (status === 401) {
    return "Votre session a expiré. Reconnectez-vous puis réessayez.";
  }

  if (status === 403) {
    return "Vous n'avez pas les droits nécessaires pour créer une galerie.";
  }

  if (status === 404) {
    return "Le service de création de galerie est introuvable pour le moment.";
  }

  if (status === 409) {
    return backendMessage || "Une galerie avec ces informations existe déjà.";
  }

  if (status === 413) {
    return "La requête est trop volumineuse pour être traitée.";
  }

  if (status === 422) {
    return (
      backendMessage || "Certaines données ne respectent pas le format attendu."
    );
  }

  if (status === 429) {
    return "Trop de tentatives de création. Patientez quelques instants puis réessayez.";
  }

  if (status && status >= 500) {
    return "Le serveur rencontre un problème temporaire. Réessayez dans quelques instants.";
  }

  if (apiError?.message?.toLowerCase().includes("fetch")) {
    return "Connexion impossible au serveur. Vérifiez votre réseau puis réessayez.";
  }

  return "Impossible de créer la galerie pour le moment. Veuillez réessayer.";
}

function validateCreateForm(): string | null {
  if (isCreating.value) {
    return "La création est déjà en cours. Veuillez patienter.";
  }

  if (!titre.value.trim()) {
    return "Le titre de la galerie est obligatoire.";
  }

  if (!["public", "private"].includes(type_visibilite.value)) {
    return "Le type de confidentialité sélectionné est invalide.";
  }

  if (!isValidEmail(emailClient.value)) {
    return "L'email client n'est pas valide.";
  }

  return null;
}

// --- Submit ---
async function handleSubmit() {
  const validationError = validateCreateForm();
  if (validationError) {
    errorMessage.value = validationError;
    successMessage.value = "";
    return;
  }

  isCreating.value = true;
  errorMessage.value = "";
  successMessage.value = "";

  const userId = authStore.user?.id;
  if (!userId) {
    errorMessage.value =
      "Action impossible : votre profil utilisateur est introuvable. Reconnectez-vous.";
    isCreating.value = false;
    return;
  }

  try {
    // On crée la galerie vide
    const galerie = await galerieStore.createGalerie({
      photographeId: userId,
      typeGalerie: type_visibilite.value,
      titre: titre.value,
      description: description.value,
      estPubliee: estPubliee.value,
      modeMiseEnPage: "grille", // Valeur par défaut
      emailsClients: emailClient.value.trim()
        ? [emailClient.value.trim().toLowerCase()]
        : [],
      photos: [], // Pas de photos à la création initiale
    });

    successMessage.value = "Galerie créée avec succès !";

    // Redirection vers le détail de la galerie pour ajouter des photos
    const galerieId = galerie?.id;
    setTimeout(() => {
      if (galerieId) {
        router.push({ name: "galerie-detail", params: { id: galerieId } });
      } else {
        router.push({ name: "home" });
      }
    }, 1200);
  } catch (err) {
    errorMessage.value = getCreateGalerieErrorMessage(err);
  } finally {
    isCreating.value = false;
  }
}

function handleLogout() {
  authStore.logout();
  router.push({ name: "login" });
}
</script>

<template>
  <div class="create-wrapper">
    <!-- Navigation -->
    <nav class="glass-nav">
      <RouterLink :to="{ name: 'home' }" class="nav-brand"
        >Photo<span class="highlight">Pro</span></RouterLink
      >
      <div class="nav-actions">
        <RouterLink :to="{ name: 'home' }" class="nav-link"
          >← Tableau de bord</RouterLink
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
      <div class="form-container-centered">
        <header class="page-header">
          <h1>Nouvelle galerie</h1>
          <p>Configurez les paramètres de base de votre galerie.</p>
        </header>

        <form @submit.prevent="handleSubmit" class="create-form">
          <section class="form-section glass-card">
            <h2 class="section-title">
              <span class="section-icon">📋</span>
              Informations générales
            </h2>

            <div class="input-group">
              <label for="titre">Titre de la galerie *</label>
              <input
                type="text"
                id="titre"
                v-model="titre"
                placeholder="Ex: Voyage en Islande"
                required
                :disabled="isCreating"
              />
            </div>

            <div class="input-group">
              <label for="description">Description</label>
              <textarea
                id="description"
                v-model="description"
                placeholder="Quelques mots sur cette série de photos..."
                rows="3"
                :disabled="isCreating"
              ></textarea>
            </div>

            <div class="input-group">
              <label for="visibilite">Confidentialité</label>
              <select
                id="visibilite"
                v-model="type_visibilite"
                :disabled="isCreating"
              >
                <option value="public">Publique (Visible par tous)</option>
                <option value="private">
                  Privée (Accès par lien sécurisé)
                </option>
              </select>
            </div>

            <div class="toggle-group">
              <label class="toggle-label" for="estPubliee">
                <span>Rendre visible immédiatement (Publiée)</span>
                <div class="toggle-switch" :class="{ active: estPubliee }">
                  <input
                    type="checkbox"
                    id="estPubliee"
                    v-model="estPubliee"
                    :disabled="isCreating"
                  />
                  <span class="toggle-slider"></span>
                </div>
              </label>
            </div>

            <div class="input-group">
              <label for="email-client">Email client </label>
              <input
                type="email"
                id="email-client"
                v-model="emailClient"
                placeholder="client@exemple.com"
                :disabled="isCreating"
              />
            </div>
          </section>

          <!-- Messages and submit -->
          <div v-if="errorMessage" class="message error-message">
            <span>⚠️</span> {{ errorMessage }}
          </div>
          <div v-if="successMessage" class="message success-message">
            <span>✅</span> {{ successMessage }}
          </div>

          <div class="form-actions">
            <RouterLink :to="{ name: 'home' }" class="cancel-btn"
              >Annuler</RouterLink
            >
            <button type="submit" class="submit-btn" :disabled="!canSubmit">
              <template v-if="isCreating">
                <div class="spinner-small"></div>
                Création en cours...
              </template>
              <template v-else> Créer la galerie </template>
            </button>
          </div>
        </form>
      </div>
    </main>
  </div>
</template>

<style scoped>
.create-wrapper {
  min-height: 100vh;
  background-color: #0b0f19;
  background-image:
    radial-gradient(at 0% 0%, rgba(16, 185, 129, 0.1) 0px, transparent 50%),
    radial-gradient(at 100% 80%, rgba(59, 130, 246, 0.1) 0px, transparent 50%);
  color: #f1f5f9;
  font-family: inherit;
}

/* --- NAV --- */
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
  text-decoration: none;
  color: #f1f5f9;
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
  transition: all 0.2s;
}
.logout-btn:hover {
  background: rgba(239, 68, 68, 0.2);
}

.main-content {
  max-width: 800px;
  margin: 0 auto;
  padding: 4rem 1.5rem;
}

.form-container-centered {
  max-width: 550px;
  margin: 0 auto;
}

.page-header {
  text-align: center;
  margin-bottom: 2.5rem;
  animation: fadeIn 0.5s ease;
}

.page-header h1 {
  font-size: 2.2rem;
  margin: 0 0 0.5rem 0;
}

.page-header p {
  color: #94a3b8;
  font-size: 1.05rem;
  margin: 0;
}

.glass-card {
  background: rgba(17, 24, 39, 0.5);
  backdrop-filter: blur(10px);
  border: 1px solid rgba(255, 255, 255, 0.06);
  border-radius: 20px;
  padding: 2rem;
  animation: slideUp 0.6s cubic-bezier(0.16, 1, 0.3, 1) backwards;
}

.section-title {
  display: flex;
  align-items: center;
  gap: 0.6rem;
  font-size: 1.15rem;
  margin: 0 0 1.5rem 0;
  font-weight: 600;
}

.section-icon {
  font-size: 1.3rem;
}

/* --- INPUTS --- */
.input-group {
  display: flex;
  flex-direction: column;
  gap: 0.45rem;
  margin-bottom: 1.5rem;
}

.input-group label {
  font-size: 0.85rem;
  font-weight: 600;
  color: #94a3b8;
  text-transform: uppercase;
  letter-spacing: 0.5px;
}

.input-group input,
.input-group textarea,
.input-group select {
  padding: 0.8rem 1rem;
  border-radius: 12px;
  border: 1px solid rgba(255, 255, 255, 0.08);
  background: rgba(15, 23, 42, 0.6);
  color: #f1f5f9;
  font-size: 0.95rem;
  font-family: inherit;
  transition: all 0.3s ease;
  outline: none;
}

.input-group input:focus,
.input-group textarea:focus,
.input-group select:focus {
  border-color: #3b82f6;
  background: rgba(15, 23, 42, 0.9);
  box-shadow: 0 0 0 4px rgba(59, 130, 246, 0.12);
}

.input-group textarea {
  resize: vertical;
  min-height: 100px;
}

.input-group select {
  cursor: pointer;
  appearance: none;
  background-image: url("data:image/svg+xml,%3Csvg xmlns='http://www.w3.org/2000/svg' width='12' height='12' viewBox='0 0 24 24' fill='none' stroke='%2394a3b8' stroke-width='2' stroke-linecap='round' stroke-linejoin='round'%3E%3Cpolyline points='6 9 12 15 18 9'%3E%3C/polyline%3E%3C/svg%3E");
  background-repeat: no-repeat;
  background-position: right 1rem center;
  padding-right: 2.5rem;
}

/* --- TOGGLE --- */
.toggle-group {
  margin-top: 0.5rem;
}

.toggle-label {
  display: flex;
  align-items: center;
  justify-content: space-between;
  cursor: pointer;
  padding: 1rem;
  border-radius: 12px;
  background: rgba(15, 23, 42, 0.4);
  border: 1px solid rgba(255, 255, 255, 0.06);
  transition: background 0.2s;
}
.toggle-label:hover {
  background: rgba(15, 23, 42, 0.6);
}

.toggle-switch {
  position: relative;
  width: 48px;
  height: 26px;
  flex-shrink: 0;
}

.toggle-switch input {
  opacity: 0;
  width: 0;
  height: 0;
  position: absolute;
}

.toggle-slider {
  position: absolute;
  inset: 0;
  background: rgba(255, 255, 255, 0.1);
  border-radius: 26px;
  transition: background 0.3s;
  cursor: pointer;
}

.toggle-slider::before {
  content: "";
  position: absolute;
  width: 20px;
  height: 20px;
  background: #94a3b8;
  border-radius: 50%;
  left: 3px;
  top: 3px;
  transition: all 0.3s;
}

.toggle-switch.active .toggle-slider {
  background: rgba(16, 185, 129, 0.3);
}

.toggle-switch.active .toggle-slider::before {
  transform: translateX(22px);
  background: #10b981;
}

/* --- MESSAGES --- */
.message {
  margin-top: 1.5rem;
  padding: 1rem 1.25rem;
  border-radius: 12px;
  display: flex;
  align-items: center;
  gap: 0.6rem;
  font-size: 0.95rem;
  animation: fadeIn 0.3s ease;
}

.error-message {
  background: rgba(239, 68, 68, 0.1);
  border: 1px solid rgba(239, 68, 68, 0.2);
  color: #fca5a5;
}

.success-message {
  background: rgba(16, 185, 129, 0.1);
  border: 1px solid rgba(16, 185, 129, 0.2);
  color: #6ee7b7;
}

/* --- ACTIONS --- */
.form-actions {
  display: flex;
  justify-content: flex-end;
  align-items: center;
  gap: 1.5rem;
  margin-top: 2.5rem;
}

.cancel-btn {
  color: #94a3b8;
  text-decoration: none;
  font-weight: 500;
  transition: color 0.2s;
}

.cancel-btn:hover {
  color: #fff;
}

.submit-btn {
  display: flex;
  align-items: center;
  gap: 0.75rem;
  padding: 0.8rem 2rem;
  background: linear-gradient(135deg, #3b82f6, #2563eb);
  color: #fff;
  border: none;
  border-radius: 12px;
  font-weight: 600;
  font-size: 1rem;
  cursor: pointer;
  transition: all 0.3s;
}

.submit-btn:hover:not(:disabled) {
  transform: translateY(-2px);
  box-shadow: 0 10px 20px -10px rgba(59, 130, 246, 0.5);
}

.submit-btn:disabled {
  opacity: 0.6;
  cursor: not-allowed;
}

/* --- SPINNERS --- */
.spinner-small {
  width: 18px;
  height: 18px;
  border: 2px solid rgba(255, 255, 255, 0.3);
  border-top-color: #fff;
  border-radius: 50%;
  animation: spin 0.8s linear infinite;
}

/* --- ANIMATIONS --- */
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
