<template>
  <header>
    <img class="banner" :src="ProfileBanner" alt="Profile Banner"/>
  </header>
  
  <div class="container profile-container">
    <div class="profile-grid">
      <div class="card-wrapper">
        <!-- Profile Picture Section -->
        <div class="card shadow-sm">
          <div class="card-body text-center">
            <img
              :src="previewImage || user.profile_picture_url"
              alt="Profile Picture"
              class="rounded-circle profile-picture"
            />
            <h5 class="mt-3">Choose a Default Profile Picture:</h5>
            <div class="default-pictures">
              <img
                v-for="pic in defaultPictures"
                :key="pic"
                :src="pic"
                @click="selectProfilePicture(pic)"
                class="default-pic"
                :class="{ selected: user.profile_picture_url === pic }"
              />
            </div>

            <h5 class="mt-3">Or Upload a Custom Picture:</h5>
            <input type="file" class="form-control" @change="previewFile" />

            <div class="profile-actions">
              <button @click="updateProfilePicture" class="btn btn-primary w-100 mt-3">Save Profile Picture</button>
            </div>

            <div class="profile-feedback">
              <p v-if="messagePicture" :class="messagePictureClass">{{ messagePicture }}</p>
            </div>
          </div>
        </div>
      </div>

      <div class="card-wrapper">
        <!-- Profile Info Section -->
        <div class="card shadow-sm">
          <div class="card-body user-information">
            <div class="user-info">
              <h2 class="text-center">{{ user.username }}</h2>
              <p class="text-muted text-center">{{ user.email }}</p>
              <p><strong>Status:</strong> <span class="badge bg-info">{{ user.status }}</span></p>
            </div>

            <form @submit.prevent="updateProfileInfo">
              <label class="form-label">Username</label>
              <input type="text" class="form-control" v-model="user.username" required />

              <label class="form-label">Email</label>
              <input type="email" class="form-control" v-model="user.email" required />

              <label class="form-label">Bio</label>
              <textarea class="form-control" v-model="user.bio"></textarea>

              <div class="profile-actions">
                <button type="submit" class="btn btn-primary w-100 mt-3">Save Profile Info</button>
              </div>

              <div class="profile-feedback">
                <p v-if="messageInfo" :class="messageInfoClass">{{ messageInfo }}</p>
              </div>
            </form>
          </div>
        </div>
      </div>
    </div>
  </div>
</template>

<script>
import { useUserStore } from '@/Store/UserStore';
import axios from "axios";
import { handleApiError } from "@/Utils/errorHandler";
import ProfileBanner from '@/assets/images/Profile_Banner.png'

export default {
  emits: ['profileUpdated'],
  data() {
    return {
      user: { ...useUserStore().user },
      ProfileBanner,
      selectedFile: null,
      previewImage: null,
      defaultPictures: [
        "/uploads/Defaults/Bulbasaur_PFP.png",
        "/uploads/Defaults/Charmander_PFP.png",
        "/uploads/Defaults/Squirtle_PFP.png",
        "/uploads/Defaults/Cubone_PFP.png",
        "/uploads/Defaults/Pikachu_PFP.png",
        "/uploads/Defaults/Eevee_PFP.png",
        "/uploads/Defaults/Gengar_PFP.png",
        "/uploads/Defaults/Jigglypuff_PFP.png",
        "/uploads/Defaults/Magikarp_PFP.png",
        "/uploads/Defaults/Meowth_PFP.png",
        "/uploads/Defaults/Mew_PFP.png",
        "/uploads/Defaults/Psyduck_PFP.png",
        "/uploads/Defaults/Riolu_PFP.png",
        "/uploads/Defaults/Shinx_PFP.png",
        "/uploads/Defaults/Snorlax_PFP.png",
        "/uploads/Defaults/Umbreon_PFP.png",
        "/uploads/Defaults/Metagross_PFP.png",
        "/uploads/Defaults/Absol_PFP.png",
        "/uploads/Defaults/Scizor_PFP.png",
        "/uploads/Defaults/Rayquaza_PFP.png"
      ],
      messagePicture: "",
      messageInfo: "",
      messagePictureClass: "",
      messageInfoClass: "",
    };
  },
  computed: {
    profilePicture() {
      return this.user.profile_picture_url || '/images/profile.png';
    }
  },
  async created() {
    this.fetchUserProfile();
  },
  methods: {
    async fetchUserProfile() {
      const token = localStorage.getItem("token");
      if (!token) {
        this.$router.push("/");
      }

      try {
        const response = await axios.get("/user", {
          headers: { Authorization: `Bearer ${token}` },
        });
        this.user = response.data.user;
      } catch (error) {
        this.messageInfo = handleApiError(error);
      }
    },

    previewFile(event) {
      const file = event.target.files[0];
      if (file && file.type.startsWith("image/")) {
        this.selectedFile = file;
        const reader = new FileReader();
        reader.onload = (e) => (this.previewImage = e.target.result);
        reader.readAsDataURL(file);
      }
    },

    selectProfilePicture(pic) {
      this.user.profile_picture_url = pic;
      this.previewImage = pic;
      this.selectedFile = null;
    },

    async updateProfilePicture() {
      const token = localStorage.getItem("token");
      if (!token) {
        this.$router.push("/");
      }
      const headers = { Authorization: `Bearer ${token}` };
      let payload;

      if (this.selectedFile) {
        payload = new FormData();
        payload.append("profile_picture", this.selectedFile);
        headers["Content-Type"] = "multipart/form-data";
      } else {
        payload = { profile_picture_url: this.user.profile_picture_url };
        headers["Content-Type"] = "application/json";
      }

      try {
        const response = await axios.post("/user/upload-profile-picture", payload, { headers });
        if (response.status === 200) {
          const updatedProfilePicture = this.previewImage || this.user.profile_picture_url;
          useUserStore().updateProfilePicture(updatedProfilePicture);
          this.$emit("profileUpdated", updatedProfilePicture);
          this.showMessage("Profile picture updated successfully!", "success", "messagePicture");
        } else {
          this.showMessage("Unexpected response. Please try again.", "error", "messagePicture");
        }
      } catch (error) {
        this.showMessage(handleApiError(error), "error", "messagePicture");
      }
    },

    async updateProfileInfo() {
      const token = localStorage.getItem("token");
      if (!token) {
        this.$router.push("/");
      }
 
      try {
        const response = await axios.put("/user", this.user, {
          headers: {
            "Content-Type": "application/json",
            Authorization: `Bearer ${token}`
          }
        });
 
        this.showMessage("Profile updated successfully!", "success", "messageInfo");
      } catch (error) {
        this.handleError(error, "messageInfo");
      }
    },

    showMessage(message, type, field) {
      this[field] = message;
      this[field + "Class"] = type === "success" ? "alert alert-success" : "alert alert-danger";
      setTimeout(() => (this[field] = ""), 1000);
    },

    handleError(error, field) {
      this[field] = error.response?.data?.error || "An error occurred.";
      this[field + "Class"] = "alert alert-danger";
    },
  }
};
</script>

<style scoped>
.banner {
  width: 100%;
  height: 11vh;
  margin-top: 0.6rem;
}

.profile-container {
  max-width: 90vw;
  margin: 3rem auto;
}

.profile-grid {
  display: grid;
  grid-template-columns: repeat(auto-fit, minmax(280px, 1fr));
  gap: 1rem;
  justify-content: center;
}

.card-wrapper {
  display: flex;
  justify-content: center;
}

.card {
  display: flex;
  flex-direction: column;
  justify-content: space-between;
  padding: 1.5rem;
  height: 100%;
  max-width: 65vh;
  min-width: 40vh;
  width: 100%;
}

.card-body {
  display: flex;
  flex-direction: column;
  justify-content: space-between;
  height: 100%;
  padding: 1.5rem;
}

.user-info {
  padding: 1.5rem;
  text-align: center;
}

.form-label {
  margin-top: 2rem;
}

.profile-picture {
  align-self: center;
  width: 6rem;
  height: 6rem;
  object-fit: cover;
  border: 3px solid #ddd;
  border-radius: 50%;
}

.default-pictures {
  display: flex;
  justify-content: center;
  gap: 1rem;
  flex-wrap: wrap;
}

.default-pic {
  width: 8vh;
  height: 8vh;
  border-radius: 50%;
  cursor: pointer;
  transition: transform 0.2s;
}

.default-pic:hover {
  transform: scale(1.1);
}

.selected {
  border: 2px solid #3366af;
}

.profile-actions {
  display: flex;
  flex-direction: column;
  justify-content: center;
  margin-top: auto;
  text-align: center;
}

.profile-feedback {
  min-height: 1.5rem;
}

@media (max-width: 1050px) {
  .default-pic {
    width: 6.8vh;
    height: 6.8vh;
  }

  .profile-picture {
    width: 6.8vh;
    height: 6.8vh;
  }
}

@media (max-width: 1024px) {
  .banner {
    object-fit: cover;
  }
}

@media (max-width: 940px) {
  .profile-grid {
    grid-template-columns: 1fr;
  }
}

@media (max-width: 768px) {
  .profile-grid {
    grid-template-columns: 1fr;
  }
}

@media (max-width: 480px) {
  .banner {
    height: 10vh;
  }

  .card {
    padding: 1.5rem;
  }

  .default-pic {
    width: 5.5vh;
    height: 5.5vh;
  }

  .profile-picture {
    width: 5.5vh;
    height: 5.5vh;
  }
}
</style>