<template>
  <div class="create-card-container">
    <header>
      <h1>Create Your Own Card</h1>
      <p>Choose a rarity, fill in the details, and preview your card!</p>
    </header>
    <section class="card-customization">
      <div class="customization-wrapper">
        <!--Left Side-->
        <div class="left-side">
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
                <label>Common</label>
              </div>
              <div
                class="rarity-option rare"
                :class="{ selected: selectedRarity === 'rare' }"
                @click="selectRarity('rare')"
              >
                <input type="radio" :checked="selectedRarity === 'rare'" />
                <label>Rare</label>
              </div>
              <div
                class="rarity-option epic"
                :class="{ selected: selectedRarity === 'epic' }"
                @click="selectRarity('epic')"
              >
                <input type="radio" :checked="selectedRarity === 'epic'" />
                <label>Epic</label>
              </div>
              <div
                class="rarity-option legendary"
                :class="{ selected: selectedRarity === 'legendary' }"
                @click="selectRarity('legendary')"
              >
                <input type="radio" :checked="selectedRarity === 'legendary'" />
                <label>Legendary</label>
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
          </div>
        </div>

        <!--Middle-->
        <div class="middle">
          <div class="preview-card">
            <h3>Card Preview</h3>
            <div class="preview-card-content" :style="cardStyle" :class="{ 'legendary-card': selectedRarity === 'legendary' }">
              <!-- Effects -->
              <div v-if="selectedRarity === 'legendary' || selectedRarity === 'epic'" class="shimmer-overlay" :style="shimmerStyle"></div>
              <div v-if="applyGlow" class="glow-effect"></div>
              <div v-if="applyShine" class="shine-effect"></div>

              <h4 :style="{ fontFamily: rarityFonts.fontFamily, fontSize: rarityFonts.fontSize, fontWeight: rarityFonts.fontWeight, color: typeColors.text }">
                {{ cardName }}
              </h4>

              <p class="hp-display" :style="{ color: typeColors.text }">
                HP {{ hp }}
              </p>

              <img :src="cardImage" alt="Card Image" class="card-image" />

              <p :style="{ color: typeColors.text }">
                Attack: {{ attack }} | Defense: {{ defense }} | Speed: {{ speed }}
              </p>

              <p :style="{ color: typeColors.text }">
                Type: {{ cardType }}
              </p>

            </div>
          </div>
        </div>

        <!--Right Side-->
        <div class="right-side">
          <div class="info-card">
            <h3>Important Information</h3>
              <ul>
                <li>Please enter an official Pokémon name.</li>
                <li>Please use images of size <strong>300x300</strong> for optimum quality.</li>
                <li>Please refrain from using offensive language. This will result in a ban.</li>
                <li>Please refrain from offensive images. This will result in a ban.</li>
              </ul>
          </div>

          <div class="balance-card">
            <h3>Balance</h3>
            <p>Available: {{ userBalance }} CuboCoins</p>
            <p>Cost: {{ requiredBalance }} CuboCoins</p>
            <p v-if="!enoughBalance" style="color: red;">Not enough balance!</p>
          </div>
          <button @click="createCard" :disabled="!cardName || !cardType || !cardImage || !enoughBalance" class="next-button">
            Next
          </button>
        </div>
      </div>
    </section>
  </div>
</template>

  
  <script setup>
  import { ref, computed } from 'vue';
  import { useUserStore } from '@/Store/UserStore';
  
  const userStore = useUserStore();
  const baseUrl = "http://localhost:8000/";
  const selectedRarity = ref('common');
  const cardName = ref('[name]');
  const cardType = ref('Normal');
  const cardImage = ref(null);
  const hp = ref(10);
  const attack = ref(10);
  const defense = ref(10);
  const speed = ref(10);
  const userBalance = computed(() => userStore.user?.balance ?? 0);

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
    if (file) {
      cardImage.value = URL.createObjectURL(file);
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
      common: { fontFamily: '"Raleway", sans-serif', fontSize: "1.4rem", fontWeight: "400" },
      rare: { fontFamily: '"Quicksand", sans-serif', fontSize: "1.4rem", fontWeight: "500" },
      epic: { fontFamily: '"Fredoka", sans-serif', fontSize: "1.6rem", fontWeight: "600" },
      legendary: { fontFamily: '"Marcellus", serif', fontSize: "1.8rem", fontWeight: "bold" }
    }[selectedRarity.value] || { fontFamily: '"Kanit", sans-serif', fontSize: "1.2rem", fontWeight: "500" };
  });

  const createCard = () => {
    //finalizeCreation;
  };
  
  const finalizeCreation = () => {
    if (enoughBalance.value) {
      console.log("Card Created!", {
        cardName: cardName.value,
        cardType: cardType.value,
        hp: hp.value,
        attack: attack.value,
        defense: defense.value,
        speed: speed.value,
      });
      userStore.updateBalance(userBalance.value - requiredBalance.value);
    } else {
      console.log("Not enough balance to create this card.");
    }
  };
</script>
  
<style scoped>
.create-card-container {
  padding: 40px;
  display: flex;
  flex-direction: column;
  gap: 40px;
}

header {
  text-align: center;
}

h1 {
  font-size: 2.5rem;
  margin-bottom: 10px;
}

p {
  font-size: 1rem;
  color: #666;
}

.card-customization {
  display: flex;
  gap: 30px;
}

.customization-wrapper {
  display: flex;
  width: 100%;
  justify-content: space-between;
  max-width: 1200px;
  margin: 0 auto;
}

.left-side,
.right-side,
.middle {
  display: flex;
  flex-direction: column;
  gap: 20px;
  height: 100%;
}

.balance-card,
.details-card,
.preview-card {
  width: 100%;
  padding: 20px;
  border-radius: 10px;
  border: 1px solid #ccc;
  margin-bottom: 20px;
}

.balance-card h3,
.details-card h3,
.preview-card h3 {
  font-size: 1.2rem;
  margin-bottom: 15px;
}

/*Left Side*/
.left-side {
  width: 35%;
  padding-right: 20px;
}

.details-card input,
.details-card select {
  width: 100%;
  padding: 8px;
  margin: 10px 0;
  font-size: 1rem;
  border: 1px solid #ddd;
  border-radius: 5px;
}

.details-card .stats-input {
  display: flex;
  gap: 15px;
}

.details-card label {
  display: block;
  font-weight: bold;
  margin-bottom: 5px;
}

.stat-input {
  flex: 1;
}

/* Rarity Option Styling*/
.rarity-options {
  display: flex;
  flex-direction: column;
  gap: 10px;
  align-items: flex-start;
}

.rarity-option {
  display: flex;
  align-items: center;
  margin-left: 0;
}

.rarity-option input[type="radio"] {
  margin-right: 10px;
  vertical-align: middle;
}

.rarity-option label {
  font-size: 0.9rem;
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
  font-weight: bold;
  text-decoration: underline;
}

/*Middle*/
.middle {
  width: 40%;
  display: flex;
  justify-content: flex-start;
  align-items: center;
  padding-left: 20px;
  padding-right: 20px;
}

.preview-card-content {
  position: relative;
  display: flex;
  flex-direction: column;
  justify-content: center;
  align-items: center;
  width: 330px;
  height: 500px;
  padding: 15px;
  text-align: center;
  box-sizing: border-box;
  margin: 0 auto;
  border-radius: 10px;
  overflow: hidden;
}

.preview-card-content img {
  width: 260px;
  height: 300px;
  object-fit: cover;
  border-radius: 10px;
  margin-bottom: 15px;
}

.preview-card-content h4,
.preview-card-content p {
  margin: 5px 0;
}

.hp-display {
  position: absolute;
  top: 10px;
  right: 10px;
  font-style: italic;
  font-size: 1.2rem;
  font-weight: bold;
  padding: 5px 10px;
  border-radius: 5px;
}

/* Card effects*/
.glow-effect {
  position: absolute;
  top: 50%;
  left: 50%;
  width: 100%;
  height: 100%;
  background: rgba(255, 255, 0, 0.1);
  box-shadow: 0 0 20px 10px rgba(255, 255, 0, 0.3);
  transform: translate(-50%, -50%);
  pointer-events: none;
  z-index: 1;
  animation: glow-animation 1.5s infinite ease-in-out;
}

@keyframes glow-animation {
  0% { box-shadow: 0 0 20px 10px rgba(255, 255, 0, 1); }
  50% { box-shadow: 0 0 40px 20px rgba(255, 255, 0, 1); }
  100% { box-shadow: 0 0 20px 10px rgba(255, 255, 0, 1); }
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
  border-radius: 10px;
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
    box-shadow: 0 0 20px 10px rgba(255, 215, 0, 0.6);
  }
  50% {
    border-color: #ff6347;
    box-shadow: 0 0 20px 10px rgba(255, 99, 71, 0.6);
  }
  100% {
    border-color: #ffd700;
    box-shadow: 0 0 20px 10px rgba(255, 215, 0, 0.6);
  }
}

/*Right Side*/
.right-side {
  width: 30%;
  padding-left: 20px;
}

.info-card {
  padding: 15px;
  border-radius: 8px;
  box-shadow: 0px 2px 4px rgba(0, 0, 0, 0.1);
  border: 1px solid #ddd;
  text-align: left;
  font-size: 14px;
  margin-bottom: 20px;
}

.info-card h3 {
  font-size: 16px;
  font-weight: bold;
  margin-bottom: 10px;
  color: #333;
}

.info-card ul {
  list-style-type: disc;
  padding-left: 20px;
}

.info-card li {
  margin-bottom: 5px;
  color: #666;
}

.info-card strong {
  color: #222;
}

.balance-card {
  padding: 15px;
  border-radius: 8px;
  box-shadow: 0px 2px 4px rgba(0, 0, 0, 0.1);
  font-size: 16px;
}

.balance-card h3 {
  margin-bottom: 10px;
  font-size: 18px;
  font-weight: bold;
}

.balance-card p {
  margin: 5px 0;
}

.balance-card p:nth-child(3) {
  font-weight: bold;
  color: #333;
}

.balance-card p:nth-child(4) {
  font-weight: bold;
  color: red;
}

.next-button {
  padding: 15px;
  background-color: #4CAF50;
  color: white;
  border: none;
  border-radius: 5px;
  cursor: pointer;
  font-size: 1.2rem;
  margin-top: 20px;
}

.next-button:disabled {
  background-color: #ccc;
  cursor: not-allowed;
}
</style>