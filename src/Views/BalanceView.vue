<template>
  <header>
    <img class="banner" :src="BalanceBanner" alt="Inventory Banner"/>
  </header>

  <div class="balance-page">
    <div class="balance-card">
      <div class="balance-header">
      </div>
      <p class="subtitle">Your current balance</p>
      <h2 class="balance-amount">{{ animatedBalance }} <img src="@/assets/icons/coin.png" alt="CuboCoin" class="coin-icon" /></h2>

      <button
        class="claim-button"
        :disabled="hasClaimedToday || isLoading"
        @click="claimDaily"
      >
        {{ hasClaimedToday ? '✅ Claimed Today' : '🎁 Claim 500 Daily' }}
      </button>

      <transition name="fade">
        <p v-if="successMessage" class="success">{{ successMessage }}</p>
      </transition>
      <transition name="fade">
        <p v-if="errorMessage" class="error">{{ errorMessage }}</p>
      </transition>
    </div>
  </div>
</template>

<script setup>
import { ref, onMounted, watch } from 'vue'
import axios from 'axios'
import BalanceBanner from '@/assets/images/Balance_Banner.png';
import { useUserStore } from '@/Store/UserStore';
const userStore = useUserStore();

const balance = ref(0)
const animatedBalance = ref(0)
const hasClaimedToday = ref(false)
const successMessage = ref('')
const errorMessage = ref('')
const isLoading = ref(false)

const fetchBalance = async () => {
  try {
    const token = localStorage.getItem('token')
    const response = await axios.get('/user/balance', {
      headers: { Authorization: `Bearer ${token}` },
    })
    console.log('Full response:', JSON.stringify(response.data, null, 2))
    balance.value = response.data.balance
    hasClaimedToday.value = response.data.claimed_today
  } catch (error) {
    errorMessage.value = 'Failed to load balance.'
    console.error(error)
  }
}

const claimDaily = async () => {
  isLoading.value = true
  successMessage.value = ''
  errorMessage.value = ''

  try {
    const token = localStorage.getItem('token')
    const response = await axios.post('/user/claim-daily', {}, {
      headers: { Authorization: `Bearer ${token}` },
    })
    console.log('Full response:', JSON.stringify(response.data, null, 2))
    balance.value = response.data.balance
    hasClaimedToday.value = response.data.claimed_today
    userStore.updateBalance(response.data.balance);
    successMessage.value = 'You claimed 500 CuboCoins!'
  } catch (error) {
    errorMessage.value = error.response?.data?.message || 'Something went wrong.'
    console.error(error)
  } finally {
    isLoading.value = false
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
  margin-top: 10px;
}

.balance-page {
  display: flex;
  justify-content: center;
  align-items: center;
  background: linear-gradient(145deg, #f0f4ff, #ffffff);
  padding: 3vw;
  min-height: 95%;
}

.balance-card {
  background: #fff;
  padding: 3vw;
  border-radius: 20px;
  box-shadow: 0 20px 50px rgba(0, 0, 0, 0.1);
  max-width: 480px;
  width: 100%;
  text-align: center;
}

.balance-header {
  display: flex;
  align-items: center;
  justify-content: center;
  gap: 1vw;
  margin-bottom: 1.5vw;
}

.coin-icon {
  width: 45px;
  height: 45px;
}

.subtitle {
  font-size: 1vw;
  color: #777;
}

.balance-amount {
  font-size: 3vw;
  margin: 1vw 0 2vw;
  color: #222;
  font-weight: bold;
}

.claim-button {
  background: linear-gradient(135deg, #ffc107, #ff9800);
  color: #fff;
  padding: 1vw 2vw;
  border: none;
  border-radius: 12px;
  font-size: 1vw;
  font-weight: bold;
  cursor: pointer;
  transition: all 0.3s ease;
  box-shadow: 0 0 12px rgba(255, 152, 0, 0.4);
}

.claim-button:hover {
  background: linear-gradient(135deg, #ffb300, #fb8c00);
}

.claim-button:disabled {
  background: #ccc;
  cursor: not-allowed;
  box-shadow: none;
}

.success {
  color: green;
  margin-top: 1vw;
}

.error {
  color: red;
  margin-top: 1vw;
}

.fade-enter-active,
.fade-leave-active {
  transition: opacity 0.5s;
}
.fade-enter-from,
.fade-leave-to {
  opacity: 0;
}
</style>