<template>
  <div
    :class="['inventory-card', { 'legendary-card': card.rarity === 'Legendary' }]"
    :style="getCardStyle(card)"
  >
    <!-- Shimmer effect for Epic and Legendary cards -->
    <div
      v-if="card.rarity === 'Legendary' || card.rarity === 'Epic'"
      class="shimmer-overlay"
      :style="getShimmerStyle(card)"
    ></div>

    <!-- HP Display -->
    <div class="hp-display" :style="{ color: getTypeColors(card.type).text }">
      HP {{ card.hp }}
    </div>

    <!-- Card Title -->
    <h4
      :style="{
        fontFamily: getFontStyle(card).fontFamily,
        fontSize: getFontStyle(card).fontSize,
        fontWeight: getFontStyle(card).fontWeight,
        color: getTypeColors(card.type).text
      }"
    >
      {{ card.name }}
    </h4>

    <!-- Card Image -->
    <img :src="getCardImage(card.image_url)" alt="Card Image" class="card-image" />

    <!-- Card Stats -->
    <div class="card-info">
      <p :style="{ color: getTypeColors(card.type).text }">
        Attack: {{ card.attack }} | Defense: {{ card.defense }} | Speed: {{ card.speed }}
      </p>
      <p :style="{ color: getTypeColors(card.type).text }">Type: {{ card.type }}</p>
    </div>
  </div>
</template>

<script setup>
defineProps({
  card: {
    type: Object,
    required: true
  }
});

// Style of the Card
const getCardStyle = (card) => {
  const typeColors = getTypeColors(card.type);
  return {
    position: "relative",
    border: `0.25rem solid ${typeColors.border}`,
    borderRadius: "1rem",
    fontFamily: '"Kanit", sans-serif',
    fontWeight: "500",
    boxShadow: `0 0 1.25rem 0.3rem ${typeColors.glow}`,
    overflow: "hidden",
    background:
      card.rarity === "Rare"
        ? `linear-gradient(to bottom, ${typeColors.glow} 0%, rgba(255,255,255,0.1) 100%)`
        : "white",
    padding: "1rem",
    width: "100%",
    height: "27vw",
    maxWidth: "18rem",
    maxHeight: "26rem",
    minWidth: "16rem",
    minHeight: "26rem"
  };
};

// Get colored shimmer
const getShimmerStyle = (card) => {
  const typeColors = getTypeColors(card.type);
  const shimmerGradient = `linear-gradient(110deg, ${typeColors.glow} 20%, ${typeColors.border} 40%, ${typeColors.glow} 80%)`;

  return {
    "--shimmer-background": shimmerGradient
  };
};

// Get color based on Card Type
const getTypeColors = (type) => {
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
    Psychic: { border: "#F95587", text: "#A13959", glow: "rgba(249, 85, 135, 0.5)" },
    Dragon: { border: "#6F35FC", text: "#4C00B0", glow: "rgba(111, 53, 252, 0.5)" },
    Dark: { border: "#705746", text: "#49392F", glow: "rgba(112, 87, 70, 0.5)" },
    Steel: { border: "#B7B7CE", text: "#787887", glow: "rgba(183, 183, 206, 0.5)" },
    Fairy: { border: "#D685AD", text: "#9B6470", glow: "rgba(214, 133, 173, 0.5)" },
    Stellar: { border: "#1E90FF", text: "#1C64C4", glow: "rgba(30, 144, 255, 0.5)" }
  };
  return colors[type] || colors["Normal"];
};

// Get font based on Card Rarity
const getFontStyle = (card) => {
  const style = {
    Common: { fontFamily: '"Raleway", sans-serif', fontSize: "1.8rem", fontWeight: "400" },
    Rare: { fontFamily: '"Quicksand", sans-serif', fontSize: "1.8rem", fontWeight: "500" },
    Epic: { fontFamily: '"Fredoka", sans-serif', fontSize: "2rem", fontWeight: "600" },
    Legendary: { fontFamily: '"Marcellus", serif', fontSize: "2.2rem", fontWeight: "bold" }
  }[card.rarity] || {
    fontFamily: '"Kanit", sans-serif',
    fontSize: "1.8rem",
    fontWeight: "500"
  };
  return style;
};

// Set correct imagepath
const getCardImage = (imageUrl) => {
  if (!imageUrl) return "/default-card.png";
  return `http://localhost:8000/uploads/${imageUrl}`;
};
</script>

<style scoped>
.inventory-card {
  display: flex;
  flex-direction: column;
  justify-content: flex-start;
  position: relative;
  border-radius: 1rem;
  overflow: hidden;
  text-align: center;
  box-shadow: 0 0 1.25rem 0.3rem rgba(0, 0, 0, 0.1);
  width: 100%;
  height: 27vw;
  max-width: 18rem;
  min-width: 16rem;
  min-height: 26rem;
  max-height: 26rem;
  padding: 1rem;
}

.inventory-card h4 {
  color: #333;
  margin-top: 1rem;
  margin-bottom: 0.6rem;
}

.card-image {
  width: 100%;
  height: auto;
  object-fit: cover;
  border-radius: 0.6rem;
  margin-bottom: 0.6rem;
  flex-shrink: 1;
}

.card-info {
  margin-top: auto;
  margin-bottom: 0.2rem;
}

.card-info p {
  font-size: 0.9rem;
  margin: 0.2rem 0;
  color: #666;
}

.hp-display {
  font-size: 0.9rem;
  font-weight: bold;
  font-style: italic;
  color: #555;
  position: absolute;
  top: 0.6rem;
  right: 0.6rem;
}

.shimmer-overlay {
  position: absolute;
  top: 0;
  left: 0;
  width: 100%;
  height: 100%;
  background: var(--shimmer-background);
  background-size: 200% 100%;
  animation: shimmer 2.5s infinite linear;
  border-radius: 0.6rem;
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
  animation: borderAnimation 2s infinite;
}

@keyframes borderAnimation {
  0% {
    border-color: #ffd700;
    box-shadow: 0 0 1.25rem 0.6rem rgba(255, 215, 0, 0.6);
  }
  50% {
    border-color: #ff6347;
    box-shadow: 0 0 1.25rem 0.6rem rgba(255, 99, 71, 0.6);
  }
  100% {
    border-color: #ffd700;
    box-shadow: 0 0 1.25rem 0.6rem rgba(255, 215, 0, 0.6);
  }
}

@media (max-width: 768px) {
  .inventory-card {
    width: 80vw;
    height: auto;
    padding: 1rem;
  }

  .inventory-card h4 {
    font-size: 1.2rem;
  }

  .card-info {
    margin-top: 0.5rem;
  }

  .card-info p {
    font-size: 0.9rem;
  }

  .hp-display {
    font-size: 1rem;
  }
}

@media (max-width: 480px) {
  .inventory-card {
    width: 90vw;
    height: auto;
    padding: 1rem;
  }

  .inventory-card h4 {
    font-size: 1.1rem;
  }

  .card-info p {
    font-size: 0.9rem;
  }

  .hp-display {
    font-size: 0.95rem;
  }
}
</style>