<script setup lang="ts">
import { onMounted } from 'vue'
import { useRouter, useRoute } from 'vue-router'

const router = useRouter()
const route = useRoute()

onMounted(() => {
  const token = route.query.token as string
  const userParam = route.query.user as string
  const error = route.query.error as string

  if (error) {
    router.push({ name: 'login', query: { error } })
    return
  }

  if (token && userParam) {
    try {
      const user = JSON.parse(decodeURIComponent(userParam))
      localStorage.setItem('auth_token', token)
      localStorage.setItem('user', JSON.stringify(user))

      // Redirect based on role
      if (user.role === 'admin') {
        router.push('/admin')
      } else {
        router.push('/dashboard')
      }
    } catch {
      router.push('/login')
    }
  } else {
    router.push('/login')
  }
})
</script>

<template>
  <div class="callback-page">
    <div class="callback-loader">
      <div class="callback-spinner"></div>
      <p>Signing you in...</p>
    </div>
  </div>
</template>

<style scoped>
.callback-page {
  min-height: 100vh;
  display: flex;
  align-items: center;
  justify-content: center;
  background: #f7f5f0;
  font-family: 'Inter', sans-serif;
}

.callback-loader {
  text-align: center;
}

.callback-spinner {
  width: 40px;
  height: 40px;
  border: 3px solid rgba(200, 16, 46, 0.15);
  border-top-color: #C8102E;
  border-radius: 50%;
  animation: spin 0.8s linear infinite;
  margin: 0 auto 16px;
}

.callback-loader p {
  color: #888;
  font-size: 0.9rem;
}

@keyframes spin {
  to { transform: rotate(360deg); }
}
</style>
