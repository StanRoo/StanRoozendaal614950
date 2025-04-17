<template>
  <header>
    <img class="banner" :src="ProfileBanner" alt="Profile Banner"/>
  </header>
  
  <div class="container profile-container">
    <div class="profile-grid">
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

      <!-- Profile Info Section -->
      <div class="card shadow-sm">
        <div class="card-body">
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
</template>

<script>
import { useUserStore } from '@/Store/UserStore';
import axios from "axios";
import { handleApiError } from "@/Utils/errorHandler";
import ProfileBanner from '@/assets/images/Profile_Banner.png'

export default {
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
      if (!token) return this.redirectToLogin();

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
      if (!token) return this.redirectToLogin();
 
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
      if (error.response?.status === 401) this.redirectToLogin();
    },

    redirectToLogin() {
      this.$router.push("/");
    }
  }
};
</script>

<style scoped>
  .banner {
    width: 100%;
    margin-top: 10px;
  }

  .profile-container {
    max-width: 1000px;
    margin: 100px auto 50px;
  }

  .profile-grid {
    display: grid;
    grid-template-columns: 1fr 1fr;
    gap: 20px;
  }

  .card {
    display: flex;
    flex-direction: column;
    justify-content: space-between;
    padding: 20px;
    height: 100%;
  }

  .card-body {
    display: flex;
    flex-direction: column;
    justify-content: space-between;
    height: 100%;
  }

  .user-info {
    padding: 20px;
    text-align: center;
  }

  .profile-picture {
    align-self: center;
    width: 100px;
    height: 100px;
    object-fit: cover;
    border: 3px solid #ddd;
    border-radius: 50%;
  }

  .default-pictures {
    display: flex;
    justify-content: center;
    gap: 10px;
    flex-wrap: wrap;
  }

  .default-pic {
    width: 60px;
    height: 60px;
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
    min-height: 20px;
  }

  .alert {
    text-align: center;
    font-size: 14px;
  }
</style>