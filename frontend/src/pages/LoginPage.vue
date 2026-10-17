<script setup lang="ts">
import { ref, onMounted } from 'vue'
import { useRouter, useRoute } from 'vue-router'

const router = useRouter()
const route = useRoute()

// ─── Form State ───
const email = ref('')
const password = ref('')
const rememberMe = ref(false)
const isLoading = ref(false)
const errorMessage = ref('')
const successMessage = ref('')
const showPassword = ref(false)

// ─── API Base ───
const API_BASE = 'http://localhost:8000/api'

const handleLogin = async () => {
  errorMessage.value = ''
  successMessage.value = ''

  if (!email.value.trim() || !password.value.trim()) {
    errorMessage.value = 'Please fill in all fields.'
    return
  }

  isLoading.value = true

  try {
    const response = await fetch(`${API_BASE}/login`, {
      method: 'POST',
      headers: {
        'Content-Type': 'application/json',
        'Accept': 'application/json',
      },
      body: JSON.stringify({
        email: email.value,
        password: password.value,
      }),
    })

    const data = await response.json()

    if (!response.ok) {
      errorMessage.value = data.message || 'Invalid credentials. Please try again.'
      return
    }

    localStorage.setItem('auth_token', data.token)
    localStorage.setItem('user', JSON.stringify(data.user))

    if (data.user.role === 'admin') {
      router.push('/admin')
    } else {
      router.push('/dashboard')
    }
  } catch (err) {
    errorMessage.value = 'Unable to connect to server. Please try again later.'
  } finally {
    isLoading.value = false
  }
}

const handleGoogleLogin = () => {
  window.location.href = `${API_BASE}/auth/google/redirect`
}

const goToRegister = () => {
  router.push('/register')
}

const isReady = ref(false)

onMounted(() => {
  setTimeout(() => { isReady.value = true }, 50)

  // Show success message after registration
  if (route.query.registered === 'true') {
    successMessage.value = 'Registration successful! Please sign in with your new account.'
  }
})
</script>

<template>
  <div class="auth-page-root" :class="{ 'is-ready': isReady }">
    <!-- Ambient Background Image (Exact Landing Page Hero Image) -->
    <div class="auth-bg-layer">
      <img 
        src="https://images.unsplash.com/photo-1537996194471-e657df975ab4?auto=format&fit=crop&w=1920&q=80" 
        alt="Lush rice terraces of Bali" 
        class="auth-bg-image" 
      />
      <!-- Transparent Overlay so background is clearly visible -->
      <div class="auth-bg-overlay"></div>
    </div>

    <!-- ═══ DESKTOP LAYOUT (Hidden on mobile) ═══ -->
    <main class="desktop-auth-container" aria-label="Login">
      <div class="transparent-glass-card">
        <!-- LEFT COLUMN: Soulful Escape Typography -->
        <div class="card-left-col">
          <div class="hero-quote-block">
            <h1 class="balinese-heading">
              A <span class="italic-accent">Soulful</span><br/>
              Balinese<br/>
              Escape
            </h1>
          </div>
          <div class="hero-footer-block">
            <p class="tagline-primary">Login to begin your journey.</p>
            <p class="tagline-secondary">Welcome back to your Balinese retreat.</p>
          </div>
        </div>

        <!-- VERTICAL DIVIDER -->
        <div class="card-col-divider"></div>

        <!-- RIGHT COLUMN: Form -->
        <div class="card-right-col">
          <!-- Top Brand Header (Clean logo & text) -->
          <div 
            class="brand-crest-header cursor-pointer" 
            @click="router.push('/')"
          >
            <div class="brand-crest-row">
              <img src="/images/logo.png" alt="Logo" class="brand-crest-icon" />
              <span class="brand-crest-text">Kubu Mawar Residence</span>
            </div>
          </div>

          <!-- Location (No SVG map / gray-white dots) -->
          <div class="location-map-row">
            <span class="location-text">Bali, Indonesia</span>
          </div>

          <!-- Success Alert Banner -->
          <div v-if="successMessage" class="success-glass-alert">
            <svg width="15" height="15" viewBox="0 0 16 16" fill="currentColor">
              <path d="M8 1a7 7 0 100 14A7 7 0 008 1zm3.22 5.97l-3.75 3.75a.75.75 0 01-1.06 0l-1.88-1.88a.75.75 0 111.06-1.06L6.97 9.16l3.22-3.22a.75.75 0 111.06 1.06z"/>
            </svg>
            <span>{{ successMessage }}</span>
          </div>

          <!-- Error Alert Banner -->
          <div v-if="errorMessage" class="error-glass-alert">
            <svg width="15" height="15" viewBox="0 0 16 16" fill="currentColor">
              <path d="M8 1a7 7 0 100 14A7 7 0 008 1zm-.75 4a.75.75 0 011.5 0v3.5a.75.75 0 01-1.5 0V5zm.75 6.25a.75.75 0 110-1.5.75.75 0 010 1.5z"/>
            </svg>
            <span>{{ errorMessage }}</span>
          </div>

          <!-- Login Form (Clean, borderless inputs) -->
          <form class="auth-fields-form" @submit.prevent="handleLogin">
            <!-- Email Input -->
            <div class="field-container">
              <input 
                id="login-email-desktop"
                v-model="email"
                type="email" 
                placeholder="Email Address"
                autocomplete="email"
                required
              />
            </div>

            <!-- Password Input -->
            <div class="field-container field-container--password">
              <input 
                id="login-password-desktop"
                v-model="password"
                :type="showPassword ? 'text' : 'password'" 
                placeholder="Password"
                autocomplete="current-password"
                required
              />
              <button 
                type="button" 
                class="password-eye-btn" 
                @click="showPassword = !showPassword"
                :aria-label="showPassword ? 'Hide password' : 'Show password'"
              >
                <svg v-if="!showPassword" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.6" stroke-linecap="round" stroke-linejoin="round">
                  <path d="M1 12s4-8 11-8 11 8 11 8-4 8-11 8-11-8-11-8z"/>
                  <circle cx="12" cy="12" r="3"/>
                </svg>
                <svg v-else width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.6" stroke-linecap="round" stroke-linejoin="round">
                  <path d="M17.94 17.94A10.07 10.07 0 0112 20c-7 0-11-8-11-8a18.45 18.45 0 015.06-5.94M9.9 4.24A9.12 9.12 0 0112 4c7 0 11 8 11 8a18.5 18.5 0 01-2.16 3.19m-6.72-1.07a3 3 0 11-4.24-4.24"/>
                  <line x1="1" y1="1" x2="23" y2="23"/>
                </svg>
              </button>
            </div>

            <!-- Remember Me -->
            <div class="field-options-row">
              <label class="custom-checkbox-label">
                <input type="checkbox" v-model="rememberMe" />
                <span class="checkbox-visual">
                  <svg class="check-svg" viewBox="0 0 12 10" fill="none" stroke="currentColor" stroke-width="2">
                    <polyline points="1.5 5 4.5 8 10.5 2"/>
                  </svg>
                </span>
                <span class="checkbox-text">Remember Me</span>
              </label>
            </div>

            <!-- Sign In Red Button -->
            <button 
              id="btn-signin-desktop"
              type="submit" 
              class="primary-red-btn"
              :disabled="isLoading"
            >
              <span v-if="isLoading" class="btn-spinner-row">
                <span class="inline-spinner"></span>
                Signing In...
              </span>
              <span v-else>Sign In</span>
            </button>

            <!-- Sign In with Google Button -->
            <button 
              id="btn-google-desktop"
              type="button" 
              class="google-white-btn"
              @click="handleGoogleLogin"
            >
              <svg class="google-svg-icon" viewBox="0 0 24 24" width="18" height="18">
                <path fill="#4285F4" d="M23.745 12.27c0-.7-.06-1.4-.19-2.07H12v4.51h6.6c-.29 1.52-1.14 2.82-2.4 3.68v3.05h3.88c2.27-2.09 3.665-5.17 3.665-9.17z"/>
                <path fill="#34A853" d="M12 24c3.24 0 5.95-1.08 7.93-2.91l-3.88-3.05c-1.08.72-2.45 1.16-4.05 1.16-3.12 0-5.77-2.1-6.72-4.93H1.25v3.15C3.26 21.36 7.33 24 12 24z"/>
                <path fill="#FBBC05" d="M5.28 14.27c-.25-.72-.38-1.49-.38-2.27s.13-1.55.38-2.27V6.58H1.25C.45 8.18 0 9.99 0 12s.45 3.82 1.25 5.42l4.03-3.15z"/>
                <path fill="#EA4335" d="M12 4.75c1.77 0 3.35.61 4.6 1.8l3.42-3.42C17.95 1.19 15.24 0 12 0 7.33 0 3.26 2.64 1.25 6.58l4.03 3.15c.95-2.83 3.6-4.98 6.72-4.98z"/>
              </svg>
              <span>Sign In with Google</span>
            </button>

            <!-- Bottom Prompt -->
            <div class="auth-bottom-caption">
              <span>Don't have an account? </span>
              <a 
                href="#" 
                @click.prevent="goToRegister" 
                class="auth-link-bold"
              >
                Sign Up.
              </a>
            </div>
          </form>
        </div>
      </div>
    </main>

    <!-- ═══ MOBILE LAYOUT (Responsive: Automatically active on mobile screens) ═══ -->
    <main class="mobile-auth-container" aria-label="Mobile Login">
      <div class="mobile-transparent-card">
        <!-- Brand Crest Header (No red dot) -->
        <div 
          class="mobile-brand-crest cursor-pointer" 
          @click="router.push('/')"
        >
          <div class="mobile-crest-row">
            <img src="/images/logo.png" alt="Logo" class="mobile-crest-icon" />
            <span class="mobile-crest-text">Kubu Mawar Residence</span>
          </div>
        </div>

        <!-- Title Block -->
        <div class="mobile-title-block">
          <h2 class="mobile-heading">
            A <span class="italic-accent">Soulful</span><br/>
            Balinese Escape
          </h2>
          <p class="mobile-location-tag">Bali, Indonesia</p>
        </div>

        <!-- Error Alert -->
        <div v-if="errorMessage" class="error-glass-alert mobile-error">
          <span>{{ errorMessage }}</span>
        </div>

        <!-- Mobile Form (Clean borderless inputs) -->
        <form class="mobile-auth-form" @submit.prevent="handleLogin">
          <!-- Email -->
          <div class="field-container">
            <input 
              id="login-email-mobile"
              v-model="email"
              type="email" 
              placeholder="Email Address"
              autocomplete="email"
              required
            />
          </div>

          <!-- Password -->
          <div class="field-container field-container--password">
            <input 
              id="login-password-mobile"
              v-model="password"
              :type="showPassword ? 'text' : 'password'" 
              placeholder="Password"
              autocomplete="current-password"
              required
            />
            <button 
              type="button" 
              class="password-eye-btn" 
              @click="showPassword = !showPassword"
              :aria-label="showPassword ? 'Hide password' : 'Show password'"
            >
              <svg v-if="!showPassword" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.6">
                <path d="M1 12s4-8 11-8 11 8 11 8-4 8-11 8-11-8-11-8z"/>
                <circle cx="12" cy="12" r="3"/>
              </svg>
              <svg v-else width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.6">
                <path d="M17.94 17.94A10.07 10.07 0 0112 20c-7 0-11-8-11-8a18.45 18.45 0 015.06-5.94M9.9 4.24A9.12 9.12 0 0112 4c7 0 11 8 11 8a18.5 18.5 0 01-2.16 3.19m-6.72-1.07a3 3 0 11-4.24-4.24"/>
                <line x1="1" y1="1" x2="23" y2="23"/>
              </svg>
            </button>
          </div>

          <!-- Sign In Button -->
          <button 
            id="btn-signin-mobile"
            type="submit" 
            class="primary-red-btn"
            :disabled="isLoading"
          >
            <span v-if="isLoading">Signing In...</span>
            <span v-else>Sign In</span>
          </button>

          <!-- Dual Sublinks: Forgot Password? & Sign Up -->
          <div class="mobile-dual-links-row">
            <a href="#" class="mobile-sublink">Forgot Password?</a>
          </div>

          <!-- Google Button -->
          <button 
            id="btn-google-mobile"
            type="button" 
            class="google-white-btn"
            @click="handleGoogleLogin"
          >
            <svg class="google-svg-icon" viewBox="0 0 24 24" width="18" height="18">
              <path fill="#4285F4" d="M23.745 12.27c0-.7-.06-1.4-.19-2.07H12v4.51h6.6c-.29 1.52-1.14 2.82-2.4 3.68v3.05h3.88c2.27-2.09 3.665-5.17 3.665-9.17z"/>
              <path fill="#34A853" d="M12 24c3.24 0 5.95-1.08 7.93-2.91l-3.88-3.05c-1.08.72-2.45 1.16-4.05 1.16-3.12 0-5.77-2.1-6.72-4.93H1.25v3.15C3.26 21.36 7.33 24 12 24z"/>
              <path fill="#FBBC05" d="M5.28 14.27c-.25-.72-.38-1.49-.38-2.27s.13-1.55.38-2.27V6.58H1.25C.45 8.18 0 9.99 0 12s.45 3.82 1.25 5.42l4.03-3.15z"/>
              <path fill="#EA4335" d="M12 4.75c1.77 0 3.35.61 4.6 1.8l3.42-3.42C17.95 1.19 15.24 0 12 0 7.33 0 3.26 2.64 1.25 6.58l4.03 3.15c.95-2.83 3.6-4.98 6.72-4.98z"/>
            </svg>
            <span>Sign In with Google</span>
          </button>

          <!-- Bottom Prompt -->
          <div class="mobile-bottom-prompt">
            <span>Don't have an account? </span>
            <a href="#" @click.prevent="goToRegister" class="auth-link-bold">Sign Up.</a>
          </div>
        </form>

        <div class="mobile-footer-text">
          <span>Powered by Antigravity IDE</span>
        </div>
      </div>
    </main>
  </div>
</template>

<style scoped>
/* ─── Root Container: Standard Cursor ─── */
.auth-page-root {
  min-height: 100vh;
  width: 100%;
  position: relative;
  display: flex;
  align-items: center;
  justify-content: center;
  overflow-x: hidden;
  font-family: 'Inter', system-ui, -apple-system, sans-serif;
  color: #ffffff;
  padding: 40px 20px;
  cursor: default;
}

/* ─── Background Layer (Landing Page Hero Image) ─── */
.auth-bg-layer {
  position: fixed;
  inset: 0;
  z-index: 0;
  overflow: hidden;
}

.auth-bg-image {
  width: 100%;
  height: 100%;
  object-fit: cover;
  object-position: center 40%;
  transform: scale(1.02);
  transition: transform 1.2s cubic-bezier(0.16, 1, 0.3, 1);
}

.auth-page-root.is-ready .auth-bg-image {
  transform: scale(1);
}

.auth-bg-overlay {
  position: absolute;
  inset: 0;
  background: rgba(10, 15, 12, 0.32);
}

/* ═════════════════════════════════════════════════════════════
   DESKTOP VIEW: Clear Transparent Glassmorphism Card
   ═════════════════════════════════════════════════════════════ */
.desktop-auth-container {
  position: relative;
  z-index: 10;
  width: 100%;
  max-width: 900px;
  margin: auto;
  display: block;
}

.transparent-glass-card {
  display: grid;
  grid-template-columns: 1fr 1px 1fr;
  min-height: 520px;
  background: rgba(255, 255, 255, 0.07);
  backdrop-filter: blur(14px) saturate(120%);
  -webkit-backdrop-filter: blur(14px) saturate(120%);
  border: 1px solid rgba(255, 255, 255, 0.18);
  border-radius: 20px;
  box-shadow: 
    0 25px 50px -10px rgba(0, 0, 0, 0.4),
    inset 0 1px 1px rgba(255, 255, 255, 0.2);
  overflow: hidden;
  animation: slideUpFade 0.6s cubic-bezier(0.16, 1, 0.3, 1);
}

/* ─── Left Column ─── */
.card-left-col {
  padding: 48px 42px;
  display: flex;
  flex-direction: column;
  justify-content: space-between;
}

.balinese-heading {
  font-family: 'Playfair Display', Georgia, serif;
  font-size: 44px;
  line-height: 1.15;
  font-weight: 400;
  color: #ffffff;
  letter-spacing: -0.01em;
  margin: 0;
  text-shadow: 0 2px 10px rgba(0, 0, 0, 0.5);
}

.italic-accent {
  font-style: italic;
  color: #D9383A;
  font-weight: 500;
  font-family: 'Playfair Display', Georgia, serif;
}

.hero-footer-block {
  margin-top: 40px;
}

.tagline-primary {
  font-size: 15px;
  font-weight: 500;
  color: #ffffff;
  margin: 0 0 4px 0;
  text-shadow: 0 1px 4px rgba(0, 0, 0, 0.6);
}

.tagline-secondary {
  font-size: 13px;
  color: rgba(255, 255, 255, 0.75);
  margin: 0;
  text-shadow: 0 1px 3px rgba(0, 0, 0, 0.6);
}

/* ─── Center Divider ─── */
.card-col-divider {
  background: rgba(255, 255, 255, 0.14);
  width: 1px;
  height: 100%;
}

/* ─── Right Column ─── */
.card-right-col {
  padding: 38px 42px;
  display: flex;
  flex-direction: column;
}

/* Brand Header */
.brand-crest-header {
  display: flex;
  flex-direction: column;
  align-items: center;
  margin-bottom: 14px;
}

.brand-crest-row {
  display: flex;
  align-items: center;
  gap: 8px;
  user-select: none;
}

.brand-crest-icon {
  width: 18px;
  height: 18px;
  object-fit: contain;
  filter: drop-shadow(0 1px 2px rgba(0, 0, 0, 0.6));
}

.brand-crest-text {
  font-size: 13.5px;
  font-weight: 500;
  letter-spacing: 0.03em;
  color: #ffffff;
  text-shadow: 0 1px 3px rgba(0, 0, 0, 0.6);
}

/* Location Row (No SVG) */
.location-map-row {
  display: flex;
  align-items: center;
  margin-bottom: 16px;
  padding: 0 2px;
}

.location-text {
  font-size: 15px;
  font-weight: 400;
  color: #ffffff;
  letter-spacing: 0.01em;
  text-shadow: 0 1px 3px rgba(0, 0, 0, 0.5);
}

/* ─── Form Inputs: Clean, NO GRAY BORDERS ─── */
.auth-fields-form {
  display: flex;
  flex-direction: column;
  gap: 12px;
}

.field-container {
  position: relative;
  width: 100%;
}

.field-container input {
  width: 100%;
  height: 44px;
  background: rgba(0, 0, 0, 0.28);
  border: none;
  outline: none;
  border-radius: 9px;
  padding: 0 14px;
  font-size: 13.5px;
  color: #ffffff;
  transition: all 0.2s ease;
  backdrop-filter: blur(8px);
}

.field-container input::placeholder {
  color: rgba(255, 255, 255, 0.5);
}

.field-container input:focus {
  background: rgba(0, 0, 0, 0.42);
  box-shadow: 0 0 0 2px rgba(217, 56, 58, 0.4);
}

.field-container--password input {
  padding-right: 42px;
}

.password-eye-btn {
  position: absolute;
  right: 12px;
  top: 50%;
  transform: translateY(-50%);
  background: none;
  border: none;
  color: rgba(255, 255, 255, 0.6);
  cursor: pointer;
  display: flex;
  align-items: center;
  justify-content: center;
  padding: 4px;
  transition: color 0.15s ease;
}

.password-eye-btn:hover {
  color: #ffffff;
}

/* Options / Remember Me */
.field-options-row {
  display: flex;
  align-items: center;
  justify-content: space-between;
  margin-top: -2px;
  margin-bottom: 2px;
}

.custom-checkbox-label {
  display: inline-flex;
  align-items: center;
  gap: 8px;
  cursor: pointer;
  user-select: none;
}

.custom-checkbox-label input {
  position: absolute;
  opacity: 0;
  width: 0;
  height: 0;
}

.checkbox-visual {
  width: 16px;
  height: 16px;
  border-radius: 4px;
  background: rgba(0, 0, 0, 0.35);
  border: 1px solid rgba(255, 255, 255, 0.2);
  display: flex;
  align-items: center;
  justify-content: center;
  transition: all 0.2s ease;
}

.check-svg {
  width: 10px;
  height: 10px;
  opacity: 0;
  transition: opacity 0.15s ease;
}

.custom-checkbox-label input:checked + .checkbox-visual {
  background: #D9383A;
  border-color: #D9383A;
}

.custom-checkbox-label input:checked + .checkbox-visual .check-svg {
  opacity: 1;
}

.checkbox-text {
  font-size: 12.5px;
  color: rgba(255, 255, 255, 0.85);
}

/* Buttons */
.primary-red-btn {
  width: 100%;
  height: 44px;
  background: #D9383A;
  color: #ffffff;
  border: none;
  border-radius: 9px;
  font-size: 14px;
  font-weight: 600;
  letter-spacing: 0.01em;
  cursor: pointer;
  display: flex;
  align-items: center;
  justify-content: center;
  transition: all 0.2s cubic-bezier(0.16, 1, 0.3, 1);
  box-shadow: 0 4px 14px rgba(217, 56, 58, 0.35);
}

.primary-red-btn:hover:not(:disabled) {
  background: #e24244;
  transform: translateY(-1px);
  box-shadow: 0 6px 18px rgba(217, 56, 58, 0.55);
}

.primary-red-btn:disabled {
  opacity: 0.7;
  cursor: not-allowed;
}

.btn-spinner-row {
  display: flex;
  align-items: center;
  gap: 8px;
}

.inline-spinner {
  width: 16px;
  height: 16px;
  border: 2px solid rgba(255, 255, 255, 0.3);
  border-top-color: #ffffff;
  border-radius: 50%;
  animation: spin 0.8s linear infinite;
}

.google-white-btn {
  width: 100%;
  height: 44px;
  background: #ffffff;
  color: #202124;
  border: none;
  border-radius: 9px;
  font-size: 13.5px;
  font-weight: 600;
  display: flex;
  align-items: center;
  justify-content: center;
  gap: 10px;
  cursor: pointer;
  transition: all 0.2s cubic-bezier(0.16, 1, 0.3, 1);
  box-shadow: 0 3px 10px rgba(0, 0, 0, 0.2);
}

.google-white-btn:hover {
  background: #f8fafc;
  transform: translateY(-1px);
  box-shadow: 0 6px 16px rgba(0, 0, 0, 0.3);
}

.google-svg-icon {
  flex-shrink: 0;
}

.auth-bottom-caption {
  text-align: center;
  font-size: 12.5px;
  color: rgba(255, 255, 255, 0.8);
  margin-top: 4px;
}

.auth-link-bold {
  color: #ffffff;
  font-weight: 600;
  text-decoration: underline;
  text-underline-offset: 3px;
  transition: color 0.15s ease;
}

.auth-link-bold:hover {
  color: #ff8082;
}

.error-glass-alert {
  background: rgba(217, 56, 58, 0.28);
  border: 1px solid rgba(217, 56, 58, 0.6);
  color: #ffcccc;
  border-radius: 8px;
  padding: 8px 12px;
  font-size: 12px;
  display: flex;
  align-items: center;
  gap: 8px;
  backdrop-filter: blur(6px);
}

.success-glass-alert {
  background: rgba(34, 197, 94, 0.22);
  border: 1px solid rgba(34, 197, 94, 0.5);
  color: #bbf7d0;
  border-radius: 8px;
  padding: 8px 12px;
  font-size: 12px;
  display: flex;
  align-items: center;
  gap: 8px;
  backdrop-filter: blur(6px);
}

/* ═════════════════════════════════════════════════════════════
   MOBILE VIEW (Automatically renders on screens <= 768px)
   ═════════════════════════════════════════════════════════════ */
.mobile-auth-container {
  display: none;
  position: relative;
  z-index: 10;
  width: 100%;
  max-width: 420px;
  margin: auto;
}

.mobile-transparent-card {
  width: 100%;
  background: rgba(255, 255, 255, 0.08);
  backdrop-filter: blur(14px) saturate(120%);
  -webkit-backdrop-filter: blur(14px) saturate(120%);
  border: 1px solid rgba(255, 255, 255, 0.18);
  border-radius: 24px;
  padding: 34px 24px 24px;
  box-shadow: 0 20px 45px -10px rgba(0, 0, 0, 0.45);
}

.mobile-brand-crest {
  display: flex;
  flex-direction: column;
  align-items: center;
  margin-bottom: 20px;
}

.mobile-crest-row {
  display: flex;
  align-items: center;
  gap: 7px;
}

.mobile-crest-icon {
  width: 18px;
  height: 18px;
  object-fit: contain;
}

.mobile-crest-text {
  font-size: 13px;
  font-weight: 500;
  letter-spacing: 0.02em;
}

.mobile-title-block {
  text-align: center;
  margin-bottom: 24px;
}

.mobile-heading {
  font-family: 'Playfair Display', Georgia, serif;
  font-size: 30px;
  line-height: 1.2;
  font-weight: 400;
  margin: 0;
  text-shadow: 0 2px 8px rgba(0, 0, 0, 0.5);
}

.mobile-location-tag {
  font-size: 13.5px;
  color: rgba(255, 255, 255, 0.85);
  margin-top: 6px;
  text-shadow: 0 1px 3px rgba(0, 0, 0, 0.5);
}

.mobile-auth-form {
  display: flex;
  flex-direction: column;
  gap: 12px;
}

.mobile-error {
  margin-bottom: 10px;
}

.mobile-dual-links-row {
  display: flex;
  align-items: center;
  justify-content: space-between;
  padding: 0 4px;
  margin-top: 1px;
  margin-bottom: 2px;
}

.mobile-sublink {
  font-size: 12px;
  color: rgba(255, 255, 255, 0.85);
  text-decoration: none;
  transition: color 0.15s ease;
}

.mobile-sublink:hover {
  color: #ffffff;
  text-decoration: underline;
}

.mobile-bottom-prompt {
  text-align: center;
  font-size: 12px;
  color: rgba(255, 255, 255, 0.8);
  margin-top: 4px;
}

.mobile-footer-text {
  text-align: center;
  margin-top: 24px;
  font-size: 11px;
  color: rgba(255, 255, 255, 0.55);
  letter-spacing: 0.02em;
}

/* ═════════════════════════════════════════════════════════════
   MEDIA QUERIES (True Responsive Behavior)
   ═════════════════════════════════════════════════════════════ */
@media (max-width: 768px) {
  .desktop-auth-container {
    display: none;
  }

  .mobile-auth-container {
    display: block;
  }

  .auth-page-root {
    padding: 24px 16px;
    align-items: center;
  }
}

/* ─── Keyframe Animations ─── */
@keyframes spin {
  to { transform: rotate(360deg); }
}

@keyframes slideUpFade {
  from {
    opacity: 0;
    transform: translateY(20px) scale(0.98);
  }
  to {
    opacity: 1;
    transform: translateY(0) scale(1);
  }
}
</style>
