<template>
  <header>
    <img class="banner" :src="ProfileBanner" alt="Profile Banner"/>
  </header>
  
  <!--Profile Grid-->
  <div class="container profile-container" v-if="user">
    <div class="profile-grid">
      <div class="card-wrapper">

        <!-- Profile Picture Section -->
        <div class="card shadow-sm">
          <div class="card-body text-center">
            <img
              :src="previewImage || user.profile_picture_url || DefaultPFP"
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
              <button @click="updateProfilePicture" class="btn btn-primary w-100 mt-3" :disabled="isSubmittingPFP">
                {{ isSubmittingPFP ? "Saving Profile Picture..." : "Save Profile Picture" }}
              </button>
            </div>

            <div class="profile-feedback">
              <p v-if="errorMessagePicture" class="error">{{ errorMessagePicture }}</p>
              <p v-if="successMessagePicture" class="succes">{{ successMessagePicture }}</p>
            </div>
          </div>
        </div>
      </div>

      <!-- Profile Info Section -->
      <div class="card-wrapper"> 
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
                <button type="submit" class="btn btn-primary w-100 mt-3" :disabled="isSubmittingInfo">
                  {{ isSubmittingInfo ? "Saving Profile Information..." : "Save Profile Information" }}
                </button>
              </div>

              <!--User Feedback-->
              <div class="profile-feedback">
                <p v-if="errorMessageProfile" class="error">{{ errorMessageProfile }}</p>
                <p v-if="successMessageProfile" class="succes">{{ successMessageProfile }}</p>
              </div>
            </form>
          </div>
        </div>
      </div>
    </div>
  </div>

  <!--Loading State-->
  <div v-else class="loading">Loading profile details...</div>
</template>

<script>
import { useUserStore } from '@/Store/UserStore';
import axios from "axios";
import ProfileBanner from '@/assets/images/Profile_Banner.png'
import DefaultPFP from '/images/profile.png'

export default {
  emits: ['profileUpdated'],
  data() {
    return {
      user: { ...useUserStore().user },
      ProfileBanner,
      selectedFile: null,
      previewImage: null,
      DefaultPFP,
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
      isSubmittingPFP: false,
      isSubmittingInfo: false,
      successMessagePicture: "",
      errorMessagePicture: "",
      successMessageProfile: "",
      errorMessageProfile: "",
      isUsernameValid: true,
      isEmailValid: true,
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
        this.$router.push("/login");
      }

      try {
        const response = await axios.get("/user", {
          headers: { Authorization: `Bearer ${token}` },
        });
        this.user = response.data.user;
      } catch (error) {
        this.errorMessageProfile = error.response?.data?.message || error.message || "Something went wrong.";
        setTimeout(() => { this.errorMessageProfile = ''; }, 3000);
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

    async validateUsername() {
      const response = await axios.get(`/user/username?username=${this.user.username}`);
      return response.data.exists;
    },

    async validateEmail() {
      const response = await axios.get(`/user/email?email=${this.user.email}`);
      return response.data.exists;
    },

    async updateProfileInfo() {
      if (this.isSubmittingInfo) return;
      this.isSubmittingInfo = true;
      const token = localStorage.getItem("token");
      if (!token) {
        this.$router.push("/login");
      }

      try {
        let usernameChanged = this.user.username !== useUserStore().user.username;
        let emailChanged = this.user.email !== useUserStore().user.email;

        if (usernameChanged) {
          this.isUsernameValid = !(await this.validateUsername());
        }

        if (emailChanged) {
          this.isEmailValid = !(await this.validateEmail());
        }

        if ((!usernameChanged || this.isUsernameValid) && (!emailChanged || this.isEmailValid)) {
          const response = await axios.put("/user", this.user, {
            headers: {
              "Content-Type": "application/json",
              Authorization: `Bearer ${token}`
            }
          });

          this.successMessageProfile = response.data.message;
          setTimeout(() => { this.successMessageProfile = ''; }, 3000);
        } else {
          this.errorMessageProfile = "Username or email already exists.";
          setTimeout(() => { this.errorMessageProfile = ''; }, 3000);
        }
      } catch (error) {
        this.errorMessageProfile = error.response?.data?.message || error.message || "Something went wrong.";
        setTimeout(() => { this.errorMessageProfile = ''; }, 3000);
      } finally {
        this.isSubmittingInfo = false;
      }
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

.succes {
  text-align: center;
  color: green !important;
  margin-top: 5px;
}

.error {
  text-align: center;
  color: red !important;
  margin-top: 5px;
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