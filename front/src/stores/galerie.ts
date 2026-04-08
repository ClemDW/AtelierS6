import { ref } from "vue";
import { defineStore } from "pinia";
import { ofetch } from "ofetch";

export interface Photo {
  id: string;
  url: string;
  titre?: string;
}

export interface Galerie {
  id: string;
  titre: string;
  description: string;
  dateCreation: string;
  date_creation?: string;
  url: string;
  estPubliee?: boolean;
  est_publiee?: boolean;
  photographeId?: string;
  photographe_id?: string;
  emailsClients?: string[];
  emails_clients?: string[];
  photoEnteteId?: string | null;
  photo_entete_id?: string | null;
  modeMiseEnPage?: string;
  mode_mise_en_page?: string;
  codeAcces?: string | null;
  code_acces?: string | null;
  urlAcces?: string | null;
  url_acces?: string | null;
  photos?: Photo[];
}

export const useGalerieStore = defineStore("galerie", () => {
  const galeriesPubliques = ref<Galerie[]>([]);
  const currentGalerie = ref<Galerie | null>(null);

  // --- LE CLIENT API (Gateway Back) ---
  const api = ofetch.create({
    baseURL:
      import.meta.env.VITE_API_BACK_URL || "http://localhost:6081/api/back",
    onRequest({ options }) {
      const token = localStorage.getItem("auth_token");
      if (token) {
        options.headers = {
          ...options.headers,
          Authorization: `Bearer ${token}`,
        };
      }
    },
  });

  // --- LE CLIENT API AUTHENTIFIÉ (Gateway Back) ---
  const authApi = ofetch.create({
    baseURL:
      import.meta.env.VITE_API_BACK_URL || "http://localhost:6081/api/back",
    onRequest({ options }) {
      const token = localStorage.getItem("auth_token");
      if (token) {
        options.headers = {
          ...options.headers,
          Authorization: `Bearer ${token}`,
        };
        console.log(
          "Sending token to CUSTOM back gateway:",
          token.substring(0, 10) + "...",
        );
      } else {
        console.warn("No authentication token found in localStorage");
      }
    },
  });

  // --- ACTIONS ---
  async function loadPublicGaleries() {
    try {
      const response = await api("/galeries", { method: "GET" });
      galeriesPubliques.value = response;
      return response;
    } catch (error) {
      console.error(
        "Erreur ofetch : Impossible de récupérer les galeries",
        error,
      );
      throw error;
    }
  }

  async function loadGalerieById(id: string) {
    try {
      // Nettoyage avant chargement (éviter d'afficher une ancienne galerie)
      currentGalerie.value = null;

      const response = await api(`/galeries/${id}`, { method: "GET" });
      currentGalerie.value = response;
      return response;
    } catch (error) {
      console.error(
        "Erreur ofetch : Impossible de récupérer la galerie",
        error,
      );
      throw error;
    }
  }

  async function createGalerie(data: {
    photographeId: string;
    typeGalerie: string;
    titre: string;
    description: string;
    estPubliee: boolean;
    modeMiseEnPage: string;
    emailsClients?: string[];
    photos?: string[];
  }) {
    try {
      const response = await authApi("/galeries", {
        method: "POST",
        body: {
          photographeId: data.photographeId,
          typeGalerie: data.typeGalerie,
          titre: data.titre,
          description: data.description,
          estPubliee: data.estPubliee,
          modeMiseEnPage: data.modeMiseEnPage,
          emailsClients: data.emailsClients || [],
          photos: data.photos || [],
        },
      });
      return response;
    } catch (error) {
      console.error("Erreur : Impossible de créer la galerie", error);
      throw error;
    }
  }

  async function uploadPhoto(userId: string, file: File, titre?: string) {
    try {
      const formData = new FormData();
      formData.append("photo", file);
      if (titre) {
        formData.append("titre", titre);
      }

      const response = await authApi(`/photos/upload/${userId}`, {
        method: "POST",
        body: formData,
      });
      return response;
    } catch (error) {
      console.error("Erreur : Impossible d'uploader la photo", error);
      throw error;
    }
  }

  async function loadUserPhotos(userId: string) {
    try {
      const response = await authApi(`/storage/users/${userId}/photos`, {
        method: "GET",
      });
      return response;
    } catch (error) {
      console.error(
        "Erreur : Impossible de récupérer vos photos stockées",
        error,
      );
      throw error;
    }
  }

  async function ajouterPhotoToGalerie(galerieId: string, photoId: string) {
    try {
      await authApi(`/galeries/${galerieId}/photos`, {
        method: "POST",
        body: { photoId },
      });
    } catch (error) {
      console.error(
        "Erreur : Impossible d'ajouter la photo à la galerie",
        error,
      );
      throw error;
    }
  }

  async function loadUserGaleries(userId: string) {
    try {
      const response = await authApi(`/photographes/${userId}/galeries`, {
        method: "GET",
      });
      return response;
    } catch (error) {
      console.error("Erreur : Impossible de récupérer vos galeries", error);
      throw error;
    }
  }

  async function supprimerGalerie(id: string) {
    try {
      await authApi(`/galeries/${id}`, { method: "DELETE" });
    } catch (error) {
      console.error("Erreur : Impossible de supprimer la galerie", error);
      throw error;
    }
  }

  async function modifierInfosGalerie(
    galerieId: string,
    data: { titre: string; description: string },
  ) {
    try {
      const response = await authApi(`/galeries/${galerieId}`, {
        method: "PATCH",
        body: {
          titre: data.titre,
          description: data.description,
        },
      });

      if (currentGalerie.value && currentGalerie.value.id === galerieId) {
        currentGalerie.value = {
          ...currentGalerie.value,
          titre: data.titre,
          description: data.description,
        };
      }

      return response;
    } catch (error) {
      console.error(
        "Erreur : Impossible de modifier les informations de la galerie",
        error,
      );
      throw error;
    }
  }

  async function modifierPublicationGalerie(
    galerieId: string,
    estPubliee: boolean,
  ) {
    try {
      const response = await authApi(`/galeries/${galerieId}/publication`, {
        method: "PATCH",
        body: { estPubliee },
      });

      if (currentGalerie.value && currentGalerie.value.id === galerieId) {
        currentGalerie.value = {
          ...currentGalerie.value,
          estPubliee,
          est_publiee: estPubliee,
        };
      }

      return response;
    } catch (error) {
      console.error(
        "Erreur : Impossible de modifier la publication de la galerie",
        error,
      );
      throw error;
    }
  }

  async function modifierMiseEnPage(galerieId: string, modeMiseEnPage: string) {
    try {
      const response = await authApi(`/galeries/${galerieId}/mise-en-page`, {
        method: "PATCH",
        body: { modeMiseEnPage },
      });

      if (currentGalerie.value && currentGalerie.value.id === galerieId) {
        currentGalerie.value = {
          ...currentGalerie.value,
          modeMiseEnPage,
          mode_mise_en_page: modeMiseEnPage,
        };
      }

      return response;
    } catch (error) {
      console.error(
        "Erreur : Impossible de modifier le mode de mise en page de la galerie",
        error,
      );
      throw error;
    }
  }

  async function ajouterEmailClient(galerieId: string, email: string) {
    try {
      const response = await authApi(`/galeries/${galerieId}/invitations`, {
        method: "POST",
        body: { email },
      });

      if (currentGalerie.value && currentGalerie.value.id === galerieId) {
        const currentEmails =
          currentGalerie.value.emailsClients ||
          currentGalerie.value.emails_clients ||
          [];
        const normalized = email.trim().toLowerCase();
        if (!currentEmails.includes(normalized)) {
          currentGalerie.value = {
            ...currentGalerie.value,
            emailsClients: [...currentEmails, normalized],
          };
        }
      }

      return response;
    } catch (error) {
      console.error("Erreur : Impossible d'ajouter l'email client", error);
      throw error;
    }
  }

  async function definirPhotoEntete(galerieId: string, photoId: string | null) {
    try {
      const response = await authApi(`/galeries/${galerieId}/photo-entete`, {
        method: "PATCH",
        body: { photoId },
      });

      if (currentGalerie.value && currentGalerie.value.id === galerieId) {
        currentGalerie.value = {
          ...currentGalerie.value,
          photoEnteteId: photoId,
          photo_entete_id: photoId,
        };
      }

      return response;
    } catch (error) {
      console.error(
        "Erreur : Impossible de définir la photo d'entête de la galerie",
        error,
      );
      throw error;
    }
  }

  return {
    galeriesPubliques,
    currentGalerie,
    loadPublicGaleries,
    loadUserGaleries,
    loadGalerieById,
    createGalerie,
    uploadPhoto,
    loadUserPhotos,
    ajouterPhotoToGalerie,
    supprimerGalerie,
    modifierInfosGalerie,
    modifierPublicationGalerie,
    modifierMiseEnPage,
    ajouterEmailClient,
    definirPhotoEntete,
  };
});
