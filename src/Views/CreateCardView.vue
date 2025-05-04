<template>
  <header>
    <img class="banner" :src="CreateCardBanner" alt="Create Card Banner"/>
  </header>
  <div class="create-card-container">
    <section class="card-customization">
      <div class="customization-wrapper">

        <div class="left">
          <div class="info-card">
            <h3>Important Information</h3>
            <ul>
              <li>Please enter a Pokémon themed name.</li>
              <li>Please use an image of an actual Pokémon. (custom images are allowed as long as it contains a Pokémon)</li>
              <li>Images might be getting cropped, to refrain your custom image of being cropped please use size <strong>260x300</strong> or smaller.</li>
              <li>Please refrain from using offensive language. This will result in a ban.</li>
              <li>Please refrain from using offensive images. This will result in a ban.</li>
            </ul>
          </div>
          <div class="preview-card">
            <h3>Card Preview</h3>
            <div class="preview-card-content" :style="cardStyle" :class="{ 'legendary-card': selectedRarity === 'legendary' }">
              <div v-if="selectedRarity === 'legendary' || selectedRarity === 'epic'" class="shimmer-overlay" :style="shimmerStyle"></div>

              <h4 :style="{ fontFamily: rarityFonts.fontFamily, fontSize: rarityFonts.fontSize, fontWeight: rarityFonts.fontWeight, color: typeColors.text }">
                {{ cardName }}
              </h4>

              <p class="hp-display" :style="{ color: typeColors.text }">
                HP {{ hp }}
              </p>

              <img :src="cardImage || DefaultCardImage" alt="Card Image" class="card-image" />

              <div class="card-info">
                <p :style="{ color: typeColors.text }">
                  Attack: {{ attack }} | Defense: {{ defense }} | Speed: {{ speed }}
                </p>

                <p :style="{ color: typeColors.text }">
                  Type: {{ cardType }}
                </p>
              </div>
            </div>
          </div>
        </div>

        <div class="right">
          <div class="details-card">
            <h3>Card Details</h3>

            <label>Rarity</label>
            <div class="rarity-options">
              <div
                class="rarity-option common"
                :class="{ selected: selectedRarity === 'common' }"
                @click="selectRarity('common')"
              >
                <input type="radio" :checked="selectedRarity === 'common'" />
                <label>Common (<img src="@/assets/icons/coin.png" class="coin-icon" /> 250)</label>
              </div>
              <div
                class="rarity-option rare"
                :class="{ selected: selectedRarity === 'rare' }"
                @click="selectRarity('rare')"
              >
                <input type="radio" :checked="selectedRarity === 'rare'" />
                <label>Rare (<img src="@/assets/icons/coin.png" class="coin-icon" /> 500)</label>
              </div>
              <div
                class="rarity-option epic"
                :class="{ selected: selectedRarity === 'epic' }"
                @click="selectRarity('epic')"
              >
                <input type="radio" :checked="selectedRarity === 'epic'" />
                <label>Epic (<img src="@/assets/icons/coin.png" class="coin-icon" /> 1000)</label>
              </div>
              <div
                class="rarity-option legendary"
                :class="{ selected: selectedRarity === 'legendary' }"
                @click="selectRarity('legendary')"
              >
                <input type="radio" :checked="selectedRarity === 'legendary'" />
                <label>Legendary (<img src="@/assets/icons/coin.png" class="coin-icon" /> 2000)</label>
              </div>
            </div>

            <label>Card Name</label>
            <input v-model="cardName" type="text" placeholder="Enter card name" class="input-small" />

            <label>Card Type</label>
            <select v-model="cardType" class="input-small">
              <option value="Normal">Normal</option>
              <option value="Fire">Fire</option>
              <option value="Water">Water</option>
              <option value="Electric">Electric</option>
              <option value="Grass">Grass</option>
              <option value="Ice">Ice</option>
              <option value="Fighting">Fighting</option>
              <option value="Poison">Poison</option>
              <option value="Ground">Ground</option>
              <option value="Flying">Flying</option>
              <option value="Psychic">Psychic</option>
              <option value="Bug">Bug</option>
              <option value="Rock">Rock</option>
              <option value="Ghost">Ghost</option>
              <option value="Dragon">Dragon</option>
              <option value="Dark">Dark</option>
              <option value="Steel">Steel</option>
              <option value="Fairy">Fairy</option>
              <option value="Stellar">Stellar</option>
            </select>

            <label>Card Image</label>
            <input type="file" @change="uploadImage" class="input-small" />
            <p v-if="fileError" style="color: red;">{{ fileError }}</p>

            <label>Card Stats</label>
            <div class="stats-input">
              <div class="stat-input">
                <label>HP</label>
                <input v-model="hp" type="number" placeholder="HP" class="input-small" min="1" max="350" @input="validateStat('hp')" />
              </div>
              <div class="stat-input">
                <label>Attack</label>
                <input v-model="attack" type="number" placeholder="Attack" class="input-small" min="1" max="350" @input="validateStat('attack')" />
              </div>
              <div class="stat-input">
                <label>Defense</label>
                <input v-model="defense" type="number" placeholder="Defense" class="input-small" min="1" max="350" @input="validateStat('defense')" />
              </div>
              <div class="stat-input">
                <label>Speed</label>
                <input v-model="speed" type="number" placeholder="Speed" class="input-small" min="1" max="350" @input="validateStat('speed')" />
              </div>
            </div>
            <button @click="createCard" :disabled="!cardName || !cardType || !cardImage || !enoughBalance || isSubmitting" class="create-button">
              {{ isSubmitting ? "Creating Card..." : "Create Card" }}
            </button>
            <p v-if="successMessage" class="succes">{{ successMessage }}</p>
            <p v-if="errorMessage" class="error">{{ errorMessage }}</p>
          </div>
        </div>
      </div>
    </section>
  </div>
</template>

<script setup>
  import axios from "axios";
  import { ref, computed } from 'vue';
  import { useUserStore } from '@/Store/UserStore';
  import CreateCardBanner from '@/assets/images/Create_Card_Banner.png';
  import { useRouter } from 'vue-router';
  import DefaultCardImage from '/images/DefaultPokemon.png';

  defineEmits(['profileUpdated'])
  const router = useRouter();
  const userStore = useUserStore();
  const selectedRarity = ref('common');
  const cardName = ref('[name]');
  const cardType = ref('Normal');
  const cardImage = ref(null);
  const fileError = ref(null);
  const hp = ref(10);
  const attack = ref(10);
  const defense = ref(10);
  const speed = ref(10);
  const userBalance = computed(() => userStore.user?.balance ?? 0);
  const isSubmitting = ref(false);
  const successMessage = ref("");
  const errorMessage = ref("");

  const rarityCosts = {
    common: 200, 
    rare: 500, 
    epic: 1000, 
    legendary: 2000
  };

  const requiredBalance = computed(() => rarityCosts[selectedRarity.value]);
  const enoughBalance = computed(() => userBalance.value >= requiredBalance.value);

  const selectRarity = (rarity) => {
    selectedRarity.value = rarity;
  };
  
  const uploadImage = (event) => {
    const file = event.target.files[0];
  
    if (!file) {
      fileError.value = "Please select a card image before creating.";
      return;
    }

    /*
    const img = new Image();
    img.onload = () => {
      if (img.width > 300 || img.height > 350) {
        fileError.value = 'Please upload an image with dimensions 300x350.';
      } else {
        fileError.value = null;
        cardImage.value = URL.createObjectURL(file);
      }
    };*/

    const maxSize = 5 * 1024 * 1024;
    if (file.size > maxSize) {
      fileError.value = 'The file is too large. Please select an image smaller than 5MB.';
      return;
    }

    if (file) {
      if (file.type === 'image/png' || file.type === 'image/jpeg' || file.type === 'image/jpg') {
        cardImage.value = URL.createObjectURL(file);
      } else {
        fileError.value = 'Please upload an image of type .png, .jpeg or .jpg';
        event.target.value = '';
      }
    }
  };

  const validateStat = (stat) => {
    if (stat === 'hp') {
      hp.value = Math.max(1, Math.min(350, hp.value));
    } else if (stat === 'attack') {
      attack.value = Math.max(1, Math.min(350, attack.value));
    } else if (stat === 'defense') {
      defense.value = Math.max(1, Math.min(350, defense.value));
    } else if (stat === 'speed') {
      speed.value = Math.max(1, Math.min(350, speed.value));
    }
  };
  
  const cardStyle = computed(() => {
    const baseStyle = {
      position: "relative",
      border: `4px solid ${typeColors.value.border}`,
      borderRadius: "15px",
      fontFamily: '"Kanit", sans-serif',
      fontWeight: "500",
      fontStyle: "normal",
      transition: "all 0.3s ease-in-out",
      boxShadow: `0 0 20px 5px ${typeColors.value.glow}`,
      overflow: "hidden",
    };

    const rarityStyles = {
      common: {
        background: "white",
      },
      rare: {
        background: `linear-gradient(to bottom, ${typeColors.value.glow} 0%, rgba(255,255,255,0.1) 100%)`,
      },
      epic: {
        // Epic styles
      },
      legendary: { 
        // Legendary styles
      },
    };

    return { ...baseStyle, ...rarityStyles[selectedRarity.value] };
  });

  const typeColors = computed(() => {
    const colors = {
      Normal: { border: "#A8A77A", text: "#6D6D4E", glow: "rgba(168, 167, 122, 0.5)" },
      Fire: { border: "#EE8130", text: "#9C531F", glow: "rgba(238, 129, 48, 0.5)" },
      Water: { border: "#6390F0", text: "#445E9C", glow: "rgba(99, 144, 240, 0.5)" },
      Electric: { border: "#F7D02C", text: "#A1871F", glow: "rgba(247, 208, 44, 0.5)" },
      Grass: { border: "#7AC74C", text: "#4E8234", glow: "rgba(122, 199, 76, 0.5)" },
      Ice: { border: "#96D9D6", text: "#638D8D", glow: "rgba(150, 217, 214, 0.5)" },
      Fighting: { border: "#C22E28", text: "#7D1F1A", glow: "rgba(194, 46, 40, 0.5)" },
      Poison: { border: "#A33EA1", text: "#682A68", glow: "rgba(163, 62, 161, 0.5)" },
      Ground: { border: "#E2BF65", text: "#927D44", glow: "rgba(226, 191, 101, 0.5)" },
      Flying: { border: "#7A98D1", text: "#4B6A9A", glow: "rgba(122, 152, 209, 0.5)" },
      Psychic: { border: "#F95587", text: "#A13959", glow: "rgba(249, 85, 135, 0.5)" },
      Bug: { border: "#A6B91A", text: "#6D7815", glow: "rgba(166, 185, 26, 0.5)" },
      Rock: { border: "#B6A136", text: "#786824", glow: "rgba(182, 161, 54, 0.5)" },
      Ghost: { border: "#735797", text: "#493963", glow: "rgba(115, 87, 151, 0.5)" },
      Dragon: { border: "#6F35FC", text: "#4C00B0", glow: "rgba(111, 53, 252, 0.5)" },
      Dark: { border: "#705746", text: "#49392F", glow: "rgba(112, 87, 70, 0.5)" },
      Steel: { border: "#B7B7CE", text: "#787887", glow: "rgba(183, 183, 206, 0.5)" },
      Fairy: { border: "#D685AD", text: "#9B6470", glow: "rgba(214, 133, 173, 0.5)" },
      Stellar: { border: "#1E90FF", text: "#1C64C4", glow: "rgba(30, 144, 255, 0.5)" },
    };
    return colors[cardType.value] || colors["Normal"];
  });

  const shimmerGradient = computed(() => {
    return `linear-gradient(110deg, ${typeColors.value.glow} 20%, ${typeColors.value.border} 40%, ${typeColors.value.glow} 80%)`;
  });

  const shimmerStyle = computed(() => {
    return {
      '--shimmer-background': shimmerGradient.value,
    };
  });

  const rarityFonts = computed(() => {
    return {
      common: { fontFamily: '"Raleway", sans-serif', fontSize: "1.8rem", fontWeight: "400" },
      rare: { fontFamily: '"Quicksand", sans-serif', fontSize: "1.8rem", fontWeight: "500" },
      epic: { fontFamily: '"Fredoka", sans-serif', fontSize: "2rem", fontWeight: "600" },
      legendary: { fontFamily: '"Marcellus", serif', fontSize: "2.2remm", fontWeight: "bold" }
    }[selectedRarity.value] || { fontFamily: '"Kanit", sans-serif', fontSize: "1.8rem", fontWeight: "500" };
  });

  const createCard = async () => {
    if (!enoughBalance.value || isSubmitting.value) {
      return;
    }
    isSubmitting.value = true
    const fileInput = document.querySelector('input[type="file"]');
    const file = fileInput.files[0];

    const formData = new FormData();
    formData.append('user_id', userStore.user.id);
    formData.append('name', cardName.value);
    formData.append('type', cardType.value);
    formData.append('hp', hp.value);
    formData.append('attack', attack.value);
    formData.append('defense', defense.value);
    formData.append('speed', speed.value);
    formData.append('image', file); 
    formData.append('rarity', selectedRarity.value);
    formData.append('required_balance', requiredBalance.value);

    try {
      const token = localStorage.getItem("token");
      if (!token) {
        router.push("/");
      }
      const response = await axios.post('/cards', formData, {
        headers: {
          'Content-Type': 'multipart/form-data',
          Authorization: `Bearer ${token}`
        }
      });
      if (response.status === 201 || response.data.success) {
        const newBalance = userBalance.value - requiredBalance.value;
        userStore.updateBalance(newBalance);
        successMessage.value = "Card created successfully! It was added to your inventory.";
        setTimeout(() => { successMessage.value = "";}, 3000);
      }
    } catch (error) {
      this.errorMessage.value = error.response?.data?.message || error.message || "Something went wrong.";
      setTimeout(() => { errorMessage.value = "";}, 3000);
    } finally {
      isSubmitting.value = false;
    }
  };
</script>

<style scoped>
.banner {
  width: 100%;
  height: 11vh;
  margin-top: 0.6rem;
}

.create-card-container {
  padding: 2rem 1.5rem;
  display: flex;
  flex-direction: column;
  gap: 2vw;
}

.card-customization {
  display: flex;
  flex-wrap: wrap;
}

h1 {
  font-size: 2rem;
  margin-bottom: 1rem;
}

p {
  font-size: 1rem;
  color: #666;
}

.customization-wrapper {
  display: flex;
  flex-wrap: wrap;
  width: 100%;
  justify-content: space-between;
  gap: 1rem;
}

.left,
.right {
  flex: 1;
  min-width: 300px;
  display: flex;
  flex-direction: column;
  gap: 1rem;
}

.left {
  align-items: center;
  padding: 0;
}

.details-card,
.preview-card {
  width: 100%;
  padding: 1rem;
  border-radius: 0.625rem;
  border: 0.0625rem solid #ccc;
}

.details-card h3,
.preview-card h3 {
  font-size: 1rem;
  margin-bottom: 1.5rem;
}

.details-card input,
.details-card select {
  width: 100%;
  padding: 0.4rem;
  margin: 0.5rem 0;
  font-size: 1rem;
  border: 0.0625rem solid #ddd;
  border-radius: 0.3125rem;
}

.details-card .stats-input {
  display: flex;
  gap: 1vw;
}

.stat-input {
  flex: 1;
}

.rarity-options {
  display: flex;
  flex-direction: column;
  gap: 0.2rem;
  align-items: flex-start;
}

.rarity-option {
  display: flex;
  align-items: center;
  white-space: nowrap;
}

.rarity-option input[type="radio"] {
  margin-right: 0.5rem;
}

.rarity-option label {
  font-size: 1rem;
  cursor: pointer;
  font-weight: bold;
}

.rarity-option.common label {
  color: gray;
}

.rarity-option.rare label {
  color: rgb(0, 60, 255);
}

.rarity-option.epic label {
  color: rgb(173, 0, 173);
}

.rarity-option.legendary label {
  color: gold;
}

.rarity-option.selected label {
  text-decoration: underline;
}

.coin-icon {
  width: 1rem;
  vertical-align: middle;
  border-radius: 1rem;
}

.preview-card-content {
  position: relative;
  display: flex;
  flex-direction: column;
  justify-content: center;
  align-items: center;
  width: 100%;
  min-height: 28rem;
  max-height: 29rem;
  min-width: 15rem;
  max-width: 20rem;
  padding: 0.8vw;
  text-align: center;
  border-radius: 0.625rem;
  overflow: hidden;
  margin: 0 auto;
}

.card-image {
  width: 80%;
  height: auto;
  object-fit: cover;
  border-radius: 0.6rem;
  margin-bottom: 0.6rem;
  flex-shrink: 1;
}

.preview-card-content h4,
.preview-card-content p {
  margin: 0.3125rem 0;
}

.hp-display {
  position: absolute;
  top: 0.1rem;
  right: 0.6rem;
  font-style: italic;
  font-size: 1rem;
  font-weight: bold;
  padding: 0.3125rem 0.625rem;
  border-radius: 0.3125rem;
}

.glow-effect {
  position: absolute;
  top: 50%;
  left: 50%;
  width: 100%;
  height: 100%;
  background: rgba(255, 255, 0, 0.1);
  box-shadow: 0 0 1.25rem 0.625rem rgba(255, 255, 0, 0.3);
  transform: translate(-50%, -50%);
  pointer-events: none;
  z-index: 1;
  animation: glow-animation 1.5s infinite ease-in-out;
}

@keyframes glow-animation {
  0% { box-shadow: 0 0 1.25rem 0.625rem rgba(255, 255, 0, 1); }
  50% { box-shadow: 0 0 2.5rem 1.25rem rgba(255, 255, 0, 1); }
  100% { box-shadow: 0 0 1.25rem 0.625rem rgba(255, 255, 0, 1); }
}

.shimmer-overlay {
  position: absolute;
  top: 0;
  left: 0;
  width: 100%;
  height: 100%;
  background: var(--shimmer-background);
  background-size: 200% 100%;
  animation: shimmer 2.5s infinite linear !important;
  border-radius: 0.625rem;
  pointer-events: none;
  z-index: 2;
  opacity: 0.6;
}

@keyframes shimmer {
  0% {
    background-position: -200% 0;
  }
  100% {
    background-position: 200% 0;
  }
}

.legendary-card {
  animation: borderAnimation 2s infinite !important;
}

@keyframes borderAnimation {
  0% {
    border-color: #ffd700;
    box-shadow: 0 0 1.25rem 0.625rem rgba(255, 215, 0, 0.6);
  }
  50% {
    border-color: #ff6347;
    box-shadow: 0 0 1.25rem 0.625rem rgba(255, 99, 71, 0.6);
  }
  100% {
    border-color: #ffd700;
    box-shadow: 0 0 1.25rem 0.625rem rgba(255, 215, 0, 0.6);
  }
}

.right {
  width: 30%;
  padding-left: 1rem;
}

.info-card {
  width: 100%;
  padding: 0.9375rem;
  border-radius: 0.5rem;
  box-shadow: 0 0.125rem 0.25rem rgba(0, 0, 0, 0.1);
  border: 0.0625rem solid #ddd;
  text-align: left;
  font-size: 0.8rem;
  margin-bottom: 0.6rem;
}

.info-card h3 {
  font-size: 1rem;
  font-weight: bold;
  margin-bottom: 0.625rem;
  color: #333;
}

.info-card ul {
  list-style-type: disc;
  padding-left: 1.25rem;
}

.info-card li {
  margin-bottom: 0.3125rem;
  color: #666;
}

.info-card strong {
  color: #222;
}

.create-button {
  background-color: #4992f8;
  color: white;
  border: none;
  border-radius: 0.3125rem;
  cursor: pointer;
  margin-top: 0.9375rem;
  width: 100%;
  padding: 0.8rem 1.2rem;
  font-size: 1rem;
  margin-top: 1rem;
  align-self: center;
}

.create-button:disabled {
  background-color: #ccc;
  cursor: not-allowed;
}

.succes {
  text-align: center;
  color: green;
  margin-top: 5px;
}

.error {
  text-align: center;
  color: red;
  margin-top: 5px;
}

@media (max-width: 1024px) {
  .banner {
    object-fit: cover;
  }

  .customization-wrapper {
    flex-direction: row;
    flex-wrap: wrap;
  }

  .left,
  .right {
    width: 100%;
    padding: 0;
  }

  .right {
    width: 100%;
    padding: 0;
    display: flex;
    flex-direction: column;
    align-items: flex-start;
    gap: 2rem;
    margin-bottom: 0.5rem;
  }

  .right {
    width: 100%;
  }

  .info-card {
    width: 100%;
    margin-bottom: 0 !important;
  }

  .create-button {
    width: 100%;
    margin-top: 2rem;
  }

  .middle {
    align-items: center;
    padding-top: 1rem;
    padding-bottom: 1rem;
  }

  .rarity-options {
    align-items: flex-start !important;
    text-align: left;
  }

  .rarity-option {
    justify-content: flex-start !important;
  }

  .rarity-option label {
    text-align: left;
  }
}

@media (max-width: 768px) {
  .customization-wrapper {
    flex-direction: column;
    gap: 1rem;
    width: 100%;
    margin: 0 auto;
    align-items: center;
  }

  .left,
  .right {
    width: 100%;
    padding: 0;
    margin-bottom: 1rem;
  }

  .preview-card-content {
    width: 100%;
    height: auto;
  }

  .preview-card-content img {
    width: 80%;
    height: auto;
  }

  .info-card {
    width: 100%;
  }
}

@media (max-width: 600px) {
  .banner {
    height: 10vh;
  }

  .create-card-container {
    padding: 2rem;
  }

  .card-customization {
    flex-direction: column;
  }

  .next-button {
    font-size: 1rem;
    width: 100%;
  }

  .details-card label,
  .details-card input,
  .details-card select {
    font-size: 1rem;
  }
}
</style>