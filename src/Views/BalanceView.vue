<template>
<header>
  <img class="banner" :src="BalanceBanner" alt="Balance Banner" />
</header>

  <div class="balance-page" v-if="balance">
    <!--Balance Card-->
    <div class="balance-card">
      <div class="balance-header">
      </div>
      <p class="subtitle">Your current balance</p>
      <h2 class="balance-amount">{{ animatedBalance }} <img src="@/assets/icons/coin.png" alt="CuboCoin" class="coin-icon" /></h2>

      <button
        class="claim-button"
        :disabled="hasClaimedToday || isSubmitting"
        @click="claimDaily"
      >
        {{ hasClaimedToday ? '✅ Claimed Today' : '🎁 Claim 500 Daily' }}
      </button>

      <!--User Feedback-->
      <p v-if="successMessage" class="succes">{{ successMessage }}</p>
      <p v-if="errorMessage" class="error">{{ errorMessage }}</p>
    </div>
  </div>

  <!--Loading State-->
  <div v-else class="loading">Loading balance details...</div>
</template>

<script setup>
import { ref, onMounted, watch } from 'vue'
import axios from 'axios'
import BalanceBanner from '@/assets/images/Balance_Banner.png';
import { useUserStore } from '@/Store/UserStore';
import { useRouter } from 'vue-router';

const router = useRouter();
const userStore = useUserStore();

const balance = ref()
const animatedBalance = ref(0)
const hasClaimedToday = ref(false)
const successMessage = ref('')
const errorMessage = ref('')
const isSubmitting = ref(false)

defineEmits(['profileUpdated'])

const fetchBalance = async () => {
  try {
    const token = localStorage.getItem('token')
    if (!token) {
      router.push("/");
    }
    const response = await axios.get('/user/balance', {
      headers: { Authorization: `Bearer ${token}` },
    })
    balance.value = response.data.balance.balance
    hasClaimedToday.value = response.data.balance.claimed_today
  } catch (error) {
    errorMessage.value = error.response?.data?.message || error.message || "Something went wrong.";
    setTimeout(() => { errorMessage.value = ''; }, 3000);
  }
}

const claimDaily = async () => {
  isSubmitting.value = true

  try {
    const token = localStorage.getItem('token')
    if (!token) {
      router.push("/");
    }
    const response = await axios.post('/user/claim-daily', {}, {
      headers: { Authorization: `Bearer ${token}` },
    })
    balance.value = response.data.balance;
    hasClaimedToday.value = response.data.claimed_today;
    userStore.updateBalance(response.data.balance);
    animateBalance();
    successMessage.value = 'You claimed 500 CuboCoins!'
    setTimeout(() => { successMessage.value = ''; }, 3000);
  } catch (error) {
    errorMessage.value = error.response?.data?.message || error.message || "Something went wrong.";
    setTimeout(() => { errorMessage.value = ''; }, 3000);
  } finally {
    isSubmitting.value = false
  }
}

const animateBalance = () => {
  let start = 0
  const end = balance.value
  const duration = 700
  const increment = end / (duration / 16)

  const step = () => {
    start += increment
    if (start < end) {
      animatedBalance.value = Math.floor(start)
      requestAnimationFrame(step)
    } else {
      animatedBalance.value = end
    }
  }

  step()
}

onMounted(async () => {
  await fetchBalance()
  animateBalance()
})

watch(balance, () => {
  animateBalance()
})
</script>

<style scoped>
.banner {
  width: 100%;
  height: 11vh;
  margin-top: 0.6rem;
}

.balance-page {
  display: flex;
  justify-content: center;
  align-items: center;
  padding: 2rem 1rem;
  min-height: 70vh;
}

.balance-card {
  background: #fff;
  padding: 2rem;
  border-radius: 1.25rem;
  box-shadow: 0 1.25rem 3.125rem rgba(0, 0, 0, 0.1);
  max-width: 30rem;
  width: 100%;
  text-align: center;
}

.balance-header {
  display: flex;
  align-items: center;
  justify-content: center;
  gap: 1rem;
  margin-bottom: 1.5rem;
}

.coin-icon {
  width: 2.5rem;
  height: 2.5rem;
}

.subtitle {
  font-size: 1.1rem;
  color: #777;
  margin-bottom: 0.5rem;
}

.balance-amount {
  font-size: 2.5rem;
  margin: 1rem 0 1.5rem;
  color: #222;
  font-weight: bold;
  display: flex;
  align-items: center;
  justify-content: center;
  gap: 0.5rem;
}

.claim-button {
  background: linear-gradient(135deg, #ffc107, #ff9800);
  color: #fff;
  padding: 0.75rem 1.5rem;
  border: none;
  border-radius: 0.75rem;
  font-size: 1rem;
  font-weight: bold;
  cursor: pointer;
  transition: all 0.3s ease;
  box-shadow: 0 0 0.75rem rgba(255, 152, 0, 0.4);
  width: 100%;
  max-width: 18.75rem;
  margin: 0 auto;
}

.claim-button:hover {
  background: linear-gradient(135deg, #ffb300, #fb8c00);
}

.claim-button:disabled {
  background: #ccc;
  cursor: not-allowed;
  box-shadow: none;
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

.loading {
  font-size: 1.5rem;
  color: gray;
}

@media (max-width: 1024px) {
  .banner {
    object-fit: cover;
  }
}
 
@media (max-width: 768px) {
  .balance-card {
    padding: 1.5rem 1rem;
  }

  .balance-amount {
    font-size: 2rem;
  }

  .claim-button {
    font-size: 1rem;
    padding: 0.75rem 1rem;
  }

  .subtitle {
    font-size: 1rem;
  }

  .coin-icon {
    width: 1.875rem;
    height: 1.875rem;
  }
}

@media (max-width: 480px) {
  .banner {
    height: 10vh;
  }

  .balance-card {
    padding: 1.5rem 1rem;
  }

  .balance-amount {
    font-size: 1.8rem;
  }

  .claim-button {
    font-size: 0.95rem;
    padding: 0.75rem 1rem;
  }

  .subtitle {
    font-size: 1rem;
  }

  .coin-icon {
    width: 1.875rem;
    height: 1.875rem;
  }
}
</style>