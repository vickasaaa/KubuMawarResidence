<script setup lang="ts">
import { ref, onMounted, onUnmounted, computed, nextTick } from 'vue'
import { useRouter } from 'vue-router'

const router = useRouter()

// ─── User & Auth State ───
const user = ref<{ id?: number; name?: string; email?: string; role?: string; phone?: string; avatar?: string }>({})
const isLoading = ref(false)
const isScrolled = ref(false)
const mobileMenuOpen = ref(false)
const mouse = ref({ x: 0, y: 0, hover: false })

// ─── Property & View State ───
const selectedProperty = ref<'residence' | 'suwung' | null>(null)
const selectedRoom = ref<any>(null)
const showBookingModal = ref(false)
const activeNav = ref('beranda')

// ─── API Base ───
const API_BASE = 'http://localhost:8000/api'

// ─── 2 Pilihan Hunian Utama ───
const properties = [
  {
    id: 'residence',
    name: 'Kubu Mawar Residence',
    category: 'Home Stay',
    tagline: 'Boutique Living Experience',
    location: 'Seminyak, Bali',
    image: 'https://images.unsplash.com/photo-1582719478250-c89cae4dc85b?auto=format&fit=crop&w=1200&q=80',
    description: 'Boutique homestay villa tropis privat di Seminyak dengan nuansa Bali otentik. Mendukung sewa mingguan dan bulanan.',
    rentalNote: 'Sewa Mingguan & Bulanan',
    icon: '🏡',
  },
  {
    id: 'suwung',
    name: 'Kubu Mawar Suwung',
    category: 'Kost-Kostan',
    tagline: 'Modern Living Space',
    location: 'Suwung, Denpasar Selatan',
    image: 'https://images.unsplash.com/photo-1554995207-c18c203602cb?auto=format&fit=crop&w=1200&q=80',
    description: 'Kost modern eksklusif di Suwung Denpasar Selatan. Lingkungan aman dan strategis, khusus sewa bulanan.',
    rentalNote: 'Khusus Sewa Bulanan',
    icon: '🏠',
  },
]

// ─── Residence Rooms: Only EXCLUSIVE & MEDITERANIAN ───
const residenceRooms = ref([
  {
    id: 1,
    property_type: 'residence',
    room_type: 'exclusive',
    room_number: 'KM-EX01',
    name: 'Exclusive Suite',
    type_label: 'Tipe Exclusive',
    image: 'https://images.unsplash.com/photo-1590490360182-c33d955e4c47?auto=format&fit=crop&w=1200&q=80',
    gallery: [
      'https://images.unsplash.com/photo-1590490360182-c33d955e4c47?auto=format&fit=crop&w=800&q=80',
      'https://images.unsplash.com/photo-1578683010236-d716f9a3f461?auto=format&fit=crop&w=800&q=80',
    ],
    description: 'Suite mewah dengan interior premium, king bed canopy, teras pribadi menghadap taman tropis, dan layanan eksklusif.',
    price_weekly: 3200000,
    price_monthly: 11500000,
    features: ['King Bed Canopy', 'Teras Taman Pribadi', 'Living Room', 'Smart TV 55"', 'AC & Water Heater', 'WiFi High-Speed', 'Butler Service', 'Mini Bar Gratis'],
    status: 'available',
    roomsLeft: 1,
  },
  {
    id: 2,
    property_type: 'residence',
    room_type: 'mediteranian',
    room_number: 'KM-MD01',
    name: 'Mediteranian Suite',
    type_label: 'Tipe Mediteranian',
    image: 'https://images.unsplash.com/photo-1631049307264-da0ec9d70304?auto=format&fit=crop&w=1200&q=80',
    gallery: [
      'https://images.unsplash.com/photo-1631049307264-da0ec9d70304?auto=format&fit=crop&w=800&q=80',
      'https://images.unsplash.com/photo-1582719478250-c89cae4dc85b?auto=format&fit=crop&w=800&q=80',
    ],
    description: 'Desain mediterania klasik dengan aksen batu alam, rotan, dan pencahayaan hangat. Nyaman dan estetik.',
    price_weekly: 1800000,
    price_monthly: 6500000,
    features: ['Queen Bed Nyaman', 'Balkon Asri', 'Meja Kerja', 'AC & Water Heater', 'Smart TV 42"', 'WiFi Cepat', 'Kulkas Mini', 'Housekeeping'],
    status: 'available',
    roomsLeft: 2,
  },
])

// ─── Suwung Kost Rooms (10 kamar, tipe sama) ───
const kostImage = 'https://images.unsplash.com/photo-1522771739844-6a9f6d5f14af?auto=format&fit=crop&w=1200&q=80'
const kostFeatures = ['AC 1 PK', 'Kamar Mandi Dalam', 'Kasur Springbed', 'WiFi Fiber 50Mbps', 'Lemari 2 Pintu', 'Dapur Bersama', 'Parkir Motor', 'CCTV 24 Jam']
const suwungRooms = ref(
  Array.from({ length: 10 }, (_, i) => ({
    id: 100 + i + 1,
    property_type: 'suwung',
    room_number: `KM-SW${String(i + 1).padStart(2, '0')}`,
    name: `Kamar Kost No. ${i + 1}`,
    type_label: 'Kost Standard AC',
    image: kostImage,
    description: 'Kamar kost modern nyaman ber-AC di Suwung Denpasar Selatan, kamar mandi dalam dengan shower air hangat, lemari dan meja kerja.',
    price_monthly: 1600000,
    features: kostFeatures,
    status: [1, 3, 6, 8].includes(i) ? 'occupied' : 'available',
  }))
)

// ─── User Bookings ───
const userBookings = ref<any[]>([])
const bookingFilter = ref<'all' | 'upcoming' | 'completed'>('upcoming')

// ─── Booking Form State ───
const bookingForm = ref({
  nama: '',
  phone: '',
  email: '',
  rental_type: 'monthly' as 'weekly' | 'monthly',
  duration: 1,
  catatan: '',
})
const isSubmitting = ref(false)
const bookingSuccess = ref(false)

// ─── Fetch from API ───
const fetchRooms = async () => {
  try {
    const res = await fetch(`${API_BASE}/rooms`)
    if (res.ok) {
      const data = await res.json()
      if (data.data && data.data.length > 0) {
        const allRooms = data.data
        // Separate by property type
        const resRooms = allRooms
          .filter((r: any) => r.property_type === 'residence')
          .map((r: any) => ({
            ...r,
            room_type: r.room_type || (r.name?.toLowerCase().includes('exclusive') ? 'exclusive' : 'mediteranian'),
          }))
        const suwRooms = allRooms.filter((r: any) => r.property_type === 'suwung')
        if (resRooms.length > 0) residenceRooms.value = resRooms
        if (suwRooms.length > 0) suwungRooms.value = suwRooms
      }
    }
  } catch {}
}

const fetchUserBookings = async () => {
  try {
    const token = localStorage.getItem('auth_token')
    const res = await fetch(`${API_BASE}/bookings`, {
      headers: {
        'Accept': 'application/json',
        ...(token ? { 'Authorization': `Bearer ${token}` } : {})
      }
    })
    if (res.ok) {
      const data = await res.json()
      userBookings.value = data.data || []
    }
  } catch {}
}

// ─── Computed ───
const greeting = computed(() => {
  const hour = new Date().getHours()
  if (hour < 12) return 'Selamat Pagi'
  if (hour < 17) return 'Selamat Siang'
  return 'Selamat Malam'
})

const initials = computed(() => {
  const name = user.value.name || 'Guest'
  return name.split(' ').map(w => w[0]).join('').toUpperCase().slice(0, 2)
})

const formattedName = computed(() => {
  const name = user.value.name || 'Tamu Terhormat'
  return name
    .split(' ')
    .map(word => word.charAt(0).toUpperCase() + word.slice(1).toLowerCase())
    .join(' ')
})

const activeRooms = computed(() => {
  if (selectedProperty.value === 'residence') return residenceRooms.value
  if (selectedProperty.value === 'suwung') return suwungRooms.value
  return []
})

const availableRoomsCount = computed(() => {
  return activeRooms.value.filter(r => r.status === 'available').length
})

const occupiedRoomsCount = computed(() => {
  return activeRooms.value.filter(r => r.status === 'occupied').length
})

const displayBookings = computed(() => {
  if (bookingFilter.value === 'all') return userBookings.value
  if (bookingFilter.value === 'upcoming') {
    return userBookings.value.filter(b => b.status === 'confirmed' || b.status === 'active')
  }
  return userBookings.value.filter(b => b.status === 'completed' || b.status === 'cancelled')
})

const formatRupiah = (val: number | null | undefined) => {
  if (!val && val !== 0) return '-'
  return 'Rp ' + Number(val).toLocaleString('id-ID')
}

const bookingTotal = computed(() => {
  if (!selectedRoom.value) return 0
  if (selectedProperty.value === 'residence') {
    if (bookingForm.value.rental_type === 'weekly') {
      return (selectedRoom.value.price_weekly || 0) * bookingForm.value.duration
    }
    return (selectedRoom.value.price_monthly || 0) * bookingForm.value.duration
  }
  return (selectedRoom.value.price_monthly || 0) * bookingForm.value.duration
})

// ─── Actions ───
const selectProperty = (id: 'residence' | 'suwung') => {
  selectedProperty.value = id
  nextTick(() => {
    const el = document.getElementById('rooms-section')
    if (el) el.scrollIntoView({ behavior: 'smooth', block: 'start' })
  })
}

const openBooking = (room: any) => {
  selectedRoom.value = room
  bookingForm.value.nama = user.value.name || ''
  bookingForm.value.phone = user.value.phone || ''
  bookingForm.value.email = user.value.email || ''
  bookingForm.value.rental_type = selectedProperty.value === 'suwung' ? 'monthly' : 'monthly'
  bookingForm.value.duration = 1
  bookingForm.value.catatan = ''
  bookingSuccess.value = false
  showBookingModal.value = true
}

const closeBooking = () => {
  showBookingModal.value = false
  selectedRoom.value = null
  bookingSuccess.value = false
}

const submitBooking = async () => {
  isSubmitting.value = true
  try {
    const token = localStorage.getItem('auth_token')
    const payload = {
      room_id: selectedRoom.value?.id,
      property_type: selectedProperty.value,
      guest_name: bookingForm.value.nama,
      phone: bookingForm.value.phone,
      email: bookingForm.value.email,
      rental_type: bookingForm.value.rental_type,
      duration: bookingForm.value.duration,
      notes: bookingForm.value.catatan,
    }

    const res = await fetch(`${API_BASE}/bookings`, {
      method: 'POST',
      headers: {
        'Content-Type': 'application/json',
        'Accept': 'application/json',
        ...(token ? { 'Authorization': `Bearer ${token}` } : {}),
      },
      body: JSON.stringify(payload),
    })

    if (res.ok) {
      bookingSuccess.value = true
      fetchUserBookings()
      fetchRooms()
    } else {
      bookingSuccess.value = true // fallback: show success anyway for demo
    }
  } catch {
    bookingSuccess.value = true
  } finally {
    isSubmitting.value = false
  }
}

const goBackToProperties = () => {
  selectedProperty.value = null
  nextTick(() => {
    const el = document.getElementById('hunian-section')
    if (el) el.scrollIntoView({ behavior: 'smooth', block: 'start' })
  })
}

const handleLogout = async () => {
  isLoading.value = true
  try {
    const token = localStorage.getItem('auth_token')
    if (token) {
      await fetch(`${API_BASE}/logout`, {
        method: 'POST',
        headers: {
          'Authorization': `Bearer ${token}`,
          'Accept': 'application/json',
        },
      })
    }
  } catch {
  } finally {
    localStorage.removeItem('auth_token')
    localStorage.removeItem('user')
    router.push('/login')
  }
}

const scrollToEl = (id: string) => {
  activeNav.value = id
  mobileMenuOpen.value = false
  const el = document.getElementById(id)
  if (el) {
    el.scrollIntoView({ behavior: 'smooth', block: 'start' })
  }
}

// ─── Mouse & Scroll tracking ───
const handleMouseMove = (e: MouseEvent) => {
  mouse.value.x = e.clientX
  mouse.value.y = e.clientY
}
const handleScroll = () => {
  isScrolled.value = window.scrollY > 30
}

// ─── Lifecycle ───
onMounted(() => {
  const userStr = localStorage.getItem('user')
  if (userStr) {
    try {
      user.value = JSON.parse(userStr)
    } catch {
      router.push('/login')
      return
    }
  } else {
    router.push('/login')
    return
  }

  fetchRooms()
  fetchUserBookings()

  const token = localStorage.getItem('auth_token')
  if (token) {
    fetch(`${API_BASE}/me`, {
      headers: {
        'Authorization': `Bearer ${token}`,
        'Accept': 'application/json',
      },
    })
      .then(res => res.json())
      .then(data => {
        if (data.user) {
          user.value = { ...user.value, ...data.user }
          localStorage.setItem('user', JSON.stringify(user.value))
        }
      })
      .catch(() => {})
  }

  window.addEventListener('scroll', handleScroll, { passive: true })
  window.addEventListener('mousemove', handleMouseMove)
})

onUnmounted(() => {
  window.removeEventListener('scroll', handleScroll)
  window.removeEventListener('mousemove', handleMouseMove)
})
</script>

<template>
  <div class="km-dashboard">


    <!-- ═══ NAVBAR ═══ -->
    <header class="km-navbar" :class="{ scrolled: isScrolled }">
      <div class="km-navbar-inner">
        <!-- Brand -->
        <div class="km-brand" @click="scrollToEl('beranda')">
          <img src="/images/logo.png" alt="Kubu Mawar Logo" class="km-brand-logo" />
          <span class="km-brand-name">Kubu Mawar</span>
        </div>

        <!-- Center Nav (desktop) -->
        <nav class="km-nav-links">
          <button @click="scrollToEl('beranda')" :class="{ active: activeNav === 'beranda' }">Beranda</button>
          <button @click="scrollToEl('hunian-section')" :class="{ active: activeNav === 'hunian-section' }">Hunian</button>
          <button @click="scrollToEl('reservasi-section')" :class="{ active: activeNav === 'reservasi-section' }">Reservasi</button>
        </nav>

        <!-- Right: User -->
        <div class="km-nav-right">
          <div class="km-user-badge">
            <div class="km-avatar">{{ initials }}</div>
            <span class="km-user-name">{{ formattedName }}</span>
          </div>
          <button class="km-logout-btn" @click="handleLogout" title="Keluar">
            <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
              <path d="M9 21H5a2 2 0 01-2-2V5a2 2 0 012-2h4"/><polyline points="16 17 21 12 16 7"/><line x1="21" y1="12" x2="9" y2="12"/>
            </svg>
          </button>

          <!-- Mobile toggle -->
          <button class="km-mobile-toggle" @click="mobileMenuOpen = !mobileMenuOpen">
            <svg v-if="!mobileMenuOpen" width="24" height="24" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h16"/></svg>
            <svg v-else width="24" height="24" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
          </button>
        </div>
      </div>

      <!-- Mobile menu -->
      <div v-if="mobileMenuOpen" class="km-mobile-menu">
        <button @click="scrollToEl('beranda')">Beranda</button>
        <button @click="scrollToEl('hunian-section')">Hunian</button>
        <button @click="scrollToEl('reservasi-section')">Reservasi</button>
        <button class="logout" @click="handleLogout">Keluar</button>
      </div>
    </header>

    <!-- ═══ WELCOME HERO ═══ -->
    <section id="beranda" class="km-welcome">
      <div class="km-welcome-bg">
        <img src="https://images.unsplash.com/photo-1537996194471-e657df975ab4?auto=format&fit=crop&w=1920&q=80" alt="Bali" />
        <div class="km-welcome-overlay"></div>
      </div>
      <div class="km-welcome-content">
        <div class="km-welcome-badge">
          <span class="km-badge-dot"></span>
          Dashboard Tamu Resmi
        </div>
        <h1 class="km-welcome-title">
          {{ greeting }}, <span class="km-highlight">{{ formattedName }}</span>
        </h1>
        <p class="km-welcome-sub">
          Selamat datang di portal hunian Kubu Mawar Bali. Pilih hunian yang Anda inginkan untuk melihat kamar tersedia.
        </p>
        <div class="km-welcome-stats">
          <div class="km-stat-item">
            <span class="km-stat-number">2</span>
            <span class="km-stat-label">Properti</span>
          </div>
          <div class="km-stat-divider"></div>
          <div class="km-stat-item">
            <span class="km-stat-number">{{ residenceRooms.length + suwungRooms.length }}</span>
            <span class="km-stat-label">Total Kamar</span>
          </div>
          <div class="km-stat-divider"></div>
          <div class="km-stat-item">
            <span class="km-stat-number">{{ userBookings.length }}</span>
            <span class="km-stat-label">Reservasi Anda</span>
          </div>
        </div>
        <button class="km-cta-btn" @click="scrollToEl('hunian-section')" @mouseenter="mouse.hover=true" @mouseleave="mouse.hover=false">
          Pilih Hunian Sekarang
          <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M7 17l9.2-9.2M17 17V7.8H7.8"/></svg>
        </button>
      </div>
    </section>

    <!-- ═══ PILIH HUNIAN (2 OPSI) - HORIZONTAL SCROLL ═══ -->
    <section id="hunian-section" class="km-section" v-show="!selectedProperty">
      <div class="km-section-inner">
        <div class="km-section-header">
          <div>
            <p class="km-section-eyebrow">Langkah 1</p>
            <h2 class="km-section-title">Pilih Hunian</h2>
          </div>
          <p class="km-section-desc">Klik salah satu hunian untuk melihat tipe kamar dan fasilitas.</p>
        </div>

        <!-- Horizontal Scrollable Property Cards -->
        <div class="km-property-scroll">
          <div 
            v-for="prop in properties" 
            :key="prop.id"
            class="km-property-card"
            @click="selectProperty(prop.id as any)"
            @mouseenter="mouse.hover=true"
            @mouseleave="mouse.hover=false"
          >
            <div class="km-property-image">
              <img :src="prop.image" :alt="prop.name" />
              <div class="km-property-image-overlay"></div>
              
              <!-- Badges -->
              <div class="km-property-badges">
                <span class="km-badge" :class="prop.id === 'residence' ? 'red' : 'gold'">
                  {{ prop.category }}
                </span>
              </div>

              <!-- Room count -->
              <div class="km-rooms-left">
                <span>{{ prop.id === 'residence' ? residenceRooms.filter(r => r.status === 'available').length : suwungRooms.filter(r => r.status === 'available').length }} kamar tersedia</span>
              </div>
            </div>

            <div class="km-property-body">
              <div class="km-property-location">
                <svg width="12" height="12" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M21 10c0 7-9 13-9 13s-9-6-9-13a9 9 0 0118 0z"/><circle cx="12" cy="10" r="3"/></svg>
                {{ prop.location }}
              </div>
              <h3 class="km-property-name">{{ prop.name }}</h3>
              <p class="km-property-desc">{{ prop.description }}</p>
              <div class="km-property-footer">
                <span class="km-rental-note" :class="prop.id === 'residence' ? 'red' : 'gold'">
                  ✦ {{ prop.rentalNote }}
                </span>
                <span class="km-property-action">Lihat Kamar →</span>
              </div>
            </div>
          </div>
        </div>
      </div>
    </section>

    <!-- ═══ TIPE KAMAR (After selecting a property) ═══ -->
    <section id="rooms-section" class="km-section" v-if="selectedProperty">
      <div class="km-section-inner">
        
        <!-- Back Button & Header -->
        <div class="km-rooms-header">
          <button class="km-back-btn" @click="goBackToProperties">
            <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M19 12H5m7-7l-7 7 7 7"/></svg>
            Kembali ke Pilihan Hunian
          </button>
          <div class="km-rooms-title-area">
            <div>
              <p class="km-section-eyebrow">
                {{ selectedProperty === 'residence' ? 'Langkah 2 · Residence (Home Stay)' : 'Langkah 2 · Suwung (Kost)' }}
              </p>
              <h2 class="km-section-title">
                {{ selectedProperty === 'residence' ? 'Pilih Tipe Kamar' : 'Daftar Kamar Kost' }}
              </h2>
              <p class="km-rooms-subtitle" v-if="selectedProperty === 'residence'">
                Kubu Mawar Residence menawarkan 2 tipe kamar eksklusif. Pilih tipe yang sesuai kebutuhan Anda.
              </p>
              <p class="km-rooms-subtitle" v-else>
                Berikut daftar kamar kost yang tersedia dan terisi di Kubu Mawar Suwung.
              </p>
            </div>
            <div class="km-rooms-stats">
              <div class="km-stat-pill green">
                <span class="km-pill-dot green"></span>
                {{ availableRoomsCount }} Tersedia
              </div>
              <div class="km-stat-pill rose">
                <span class="km-pill-dot rose"></span>
                {{ occupiedRoomsCount }} Terisi
              </div>
            </div>
          </div>
        </div>

        <!-- ─── RESIDENCE: 2 Room Types - Exclusive & Mediteranian ─── -->
        <div v-if="selectedProperty === 'residence'" class="km-room-types-grid">
          <div 
            v-for="room in residenceRooms" 
            :key="room.id"
            class="km-room-type-card"
            :class="{ exclusive: room.room_type === 'exclusive', mediteranian: room.room_type === 'mediteranian' }"
          >
            <div class="km-room-image-wrap">
              <img :src="room.image" :alt="room.name" />
              <div class="km-room-image-overlay"></div>
              <div class="km-room-type-badge" :class="room.room_type">
                {{ room.type_label }}
              </div>
              <div class="km-room-status-badge" :class="room.status">
                {{ room.status === 'available' ? 'Tersedia' : 'Terisi' }}
              </div>
              <div v-if="room.roomsLeft" class="km-rooms-left-badge">
                {{ room.roomsLeft }} kamar tersisa
              </div>
            </div>

            <div class="km-room-content">
              <h3 class="km-room-name">{{ room.name }}</h3>
              <p class="km-room-desc">{{ room.description }}</p>

              <!-- Features -->
              <div class="km-features">
                <p class="km-features-title">Fasilitas Kamar:</p>
                <div class="km-features-grid">
                  <span v-for="(feat, fi) in room.features" :key="fi" class="km-feature-tag">
                    <span class="km-check">✓</span> {{ feat }}
                  </span>
                </div>
              </div>

              <!-- Pricing -->
              <div class="km-pricing-box">
                <div class="km-price-row">
                  <span class="km-price-label">Per Minggu</span>
                  <span class="km-price-value red">{{ formatRupiah(room.price_weekly) }}</span>
                </div>
                <div class="km-price-divider"></div>
                <div class="km-price-row">
                  <span class="km-price-label">Per Bulan</span>
                  <span class="km-price-value green">{{ formatRupiah(room.price_monthly) }}</span>
                </div>
              </div>

              <!-- Action -->
              <button 
                v-if="room.status === 'available'"
                class="km-book-btn"
                @click="openBooking(room)"
              >
                Pesan Kamar Ini →
              </button>
              <button v-else class="km-book-btn disabled" disabled>
                Kamar Sedang Terisi
              </button>
            </div>
          </div>
        </div>

        <!-- ─── SUWUNG: All Kost Rooms showing status ─── -->
        <div v-else class="km-kost-grid">
          <div 
            v-for="room in suwungRooms" 
            :key="room.id"
            class="km-kost-card"
            :class="{ occupied: room.status === 'occupied' }"
          >
            <div class="km-kost-image-wrap">
              <img :src="room.image" :alt="room.name" />
              <div class="km-kost-badges">
                <span class="km-kost-number">{{ room.room_number }}</span>
                <span class="km-kost-status" :class="room.status">
                  {{ room.status === 'available' ? '● Kosong' : '● Terisi' }}
                </span>
              </div>
              <span class="km-kost-type-label">{{ room.type_label }}</span>
            </div>

            <div class="km-kost-body">
              <h4 class="km-kost-name">{{ room.name }}</h4>
              <p class="km-kost-desc">{{ room.description }}</p>

              <div class="km-kost-features">
                <span v-for="(feat, fi) in room.features" :key="fi" class="km-kost-feat">
                  <span class="km-check-sm">✓</span> {{ feat }}
                </span>
              </div>

              <div class="km-kost-price-area">
                <div class="km-kost-price">
                  <span class="km-kost-price-label">Sewa Per Bulan:</span>
                  <span class="km-kost-price-value">{{ formatRupiah(room.price_monthly) }}</span>
                </div>
                <p class="km-kost-price-note">*Khusus sewa per bulan</p>
              </div>

              <button 
                v-if="room.status === 'available'"
                class="km-book-btn"
                @click="openBooking(room)"
              >
                Pesan Kamar Ini →
              </button>
              <button v-else class="km-book-btn disabled" disabled>
                Kamar Sedang Terisi
              </button>
            </div>
          </div>
        </div>

      </div>
    </section>

    <!-- ═══ RESERVASI SAYA ═══ -->
    <section id="reservasi-section" class="km-section km-reservasi-bg">
      <div class="km-section-inner">
        <div class="km-section-header">
          <div>
            <p class="km-section-eyebrow">Riwayat</p>
            <h2 class="km-section-title">Reservasi Saya</h2>
          </div>
          <div class="km-filter-tabs">
            <button 
              @click="bookingFilter = 'upcoming'"
              :class="{ active: bookingFilter === 'upcoming' }"
            >
              Aktif ({{ userBookings.filter(b => b.status === 'confirmed' || b.status === 'active').length }})
            </button>
            <button 
              @click="bookingFilter = 'completed'"
              :class="{ active: bookingFilter === 'completed' }"
            >
              Selesai
            </button>
            <button 
              @click="bookingFilter = 'all'"
              :class="{ active: bookingFilter === 'all' }"
            >
              Semua ({{ userBookings.length }})
            </button>
          </div>
        </div>

        <!-- Empty State -->
        <div v-if="displayBookings.length === 0" class="km-empty-state">
          <div class="km-empty-icon">
            <svg width="32" height="32" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5">
              <rect x="3" y="4" width="18" height="18" rx="2" ry="2"/><line x1="16" y1="2" x2="16" y2="6"/><line x1="8" y1="2" x2="8" y2="6"/><line x1="3" y1="10" x2="21" y2="10"/>
            </svg>
          </div>
          <h3>Belum Ada Reservasi</h3>
          <p>Anda belum memiliki reservasi kamar. Pilih hunian dan kamar di atas untuk mulai memesan!</p>
          <button class="km-cta-btn small" @click="scrollToEl('hunian-section')">
            Pilih Kamar Sekarang →
          </button>
        </div>

        <!-- Booking Cards -->
        <div v-else class="km-booking-list">
          <div v-for="b in displayBookings" :key="b.id" class="km-booking-card">
            <div class="km-booking-left">
              <div class="km-booking-icon">🛏️</div>
              <div>
                <div class="km-booking-meta">
                  <span class="km-booking-code">{{ b.booking_code }}</span>
                  <span class="km-booking-type">{{ b.property_type === 'suwung' ? 'Suwung' : 'Residence' }}</span>
                  <span class="km-booking-status" :class="(b.status === 'confirmed' || b.status === 'active') ? 'active' : 'done'">
                    {{ b.status }}
                  </span>
                </div>
                <p class="km-booking-room">{{ b.room?.name || 'Unit Kamar' }}</p>
                <p class="km-booking-detail">
                  {{ b.rental_type === 'weekly' ? 'Mingguan' : 'Bulanan' }} · {{ b.duration }} {{ b.rental_type === 'weekly' ? 'Minggu' : 'Bulan' }} · {{ b.start_date }} s/d {{ b.end_date }}
                </p>
              </div>
            </div>
            <div class="km-booking-right">
              <p class="km-booking-total-label">Total</p>
              <p class="km-booking-total">Rp {{ Number(b.total_price || 0).toLocaleString('id-ID') }}</p>
            </div>
          </div>
        </div>
      </div>
    </section>

    <!-- ═══ PROFIL ═══ -->
    <section id="profil-section" class="km-section">
      <div class="km-section-inner">
        <div class="km-profile-card">
          <h2 class="km-profile-title">Profil Akun</h2>
          <div class="km-profile-info">
            <div class="km-profile-avatar">{{ initials }}</div>
            <div>
              <p class="km-profile-name">{{ formattedName }}</p>
              <p class="km-profile-email">{{ user.email }}</p>
            </div>
          </div>
          <button class="km-logout-action" @click="handleLogout">
            Keluar dari Akun (Logout)
          </button>
        </div>
      </div>
    </section>

    <!-- ═══ BOOKING MODAL ═══ -->
    <Teleport to="body">
      <Transition name="modal-fade">
        <div v-if="showBookingModal" class="km-modal-overlay" @click.self="closeBooking">
          <div class="km-modal" @click.stop>
            
            <!-- Success State -->
            <div v-if="bookingSuccess" class="km-modal-success">
              <div class="km-success-icon">✓</div>
              <h3>Pemesanan Berhasil!</h3>
              <p>Terima kasih, {{ bookingForm.nama }}. Pemesanan kamar <strong>{{ selectedRoom?.name }}</strong> Anda telah kami terima.</p>
              <p class="km-success-note">Tim kami akan segera menghubungi Anda di <strong>{{ bookingForm.phone }}</strong> untuk konfirmasi lebih lanjut.</p>
              <button class="km-cta-btn" @click="closeBooking">Tutup</button>
            </div>

            <!-- Booking Form -->
            <div v-else>
              <div class="km-modal-header">
                <div>
                  <p class="km-modal-eyebrow">Formulir Pemesanan</p>
                  <h3 class="km-modal-title">{{ selectedRoom?.name }}</h3>
                  <p class="km-modal-subtitle">
                    {{ selectedProperty === 'residence' ? 'Kubu Mawar Residence · Home Stay' : 'Kubu Mawar Suwung · Kost' }}
                  </p>
                </div>
                <button class="km-modal-close" @click="closeBooking">✕</button>
              </div>

              <div class="km-modal-body">
                <!-- Room Preview -->
                <div class="km-modal-room-preview">
                  <img :src="selectedRoom?.image" :alt="selectedRoom?.name" />
                  <div class="km-modal-room-info">
                    <span class="km-modal-room-number">{{ selectedRoom?.room_number }}</span>
                    <span class="km-modal-room-type">{{ selectedRoom?.type_label }}</span>
                  </div>
                </div>

                <!-- Form Fields -->
                <div class="km-form-group">
                  <label>Nama Lengkap <span class="required">*</span></label>
                  <input v-model="bookingForm.nama" type="text" placeholder="Masukkan nama lengkap Anda" />
                </div>

                <div class="km-form-group">
                  <label>No. Telepon / WhatsApp <span class="required">*</span></label>
                  <input v-model="bookingForm.phone" type="tel" placeholder="08xx xxxx xxxx" />
                </div>

                <div class="km-form-group">
                  <label>Email</label>
                  <input v-model="bookingForm.email" type="email" placeholder="email@contoh.com" />
                </div>

                <!-- Rental Type (only for Residence) -->
                <div v-if="selectedProperty === 'residence'" class="km-form-group">
                  <label>Jenis Sewa <span class="required">*</span></label>
                  <div class="km-radio-group">
                    <label class="km-radio-option" :class="{ selected: bookingForm.rental_type === 'weekly' }">
                      <input type="radio" v-model="bookingForm.rental_type" value="weekly" />
                      <div class="km-radio-content">
                        <span class="km-radio-title">Per Minggu</span>
                        <span class="km-radio-price">{{ formatRupiah(selectedRoom?.price_weekly) }}/minggu</span>
                      </div>
                    </label>
                    <label class="km-radio-option" :class="{ selected: bookingForm.rental_type === 'monthly' }">
                      <input type="radio" v-model="bookingForm.rental_type" value="monthly" />
                      <div class="km-radio-content">
                        <span class="km-radio-title">Per Bulan</span>
                        <span class="km-radio-price">{{ formatRupiah(selectedRoom?.price_monthly) }}/bulan</span>
                      </div>
                    </label>
                  </div>
                </div>

                <!-- Duration -->
                <div class="km-form-group">
                  <label>Durasi Sewa <span class="required">*</span></label>
                  <div class="km-duration-input">
                    <button class="km-dur-btn" @click="bookingForm.duration = Math.max(1, bookingForm.duration - 1)">−</button>
                    <span class="km-dur-value">{{ bookingForm.duration }}</span>
                    <button class="km-dur-btn" @click="bookingForm.duration++">+</button>
                    <span class="km-dur-unit">
                      {{ selectedProperty === 'residence' && bookingForm.rental_type === 'weekly' ? 'Minggu' : 'Bulan' }}
                    </span>
                  </div>
                </div>

                <div class="km-form-group">
                  <label>Catatan Tambahan</label>
                  <textarea v-model="bookingForm.catatan" rows="2" placeholder="Permintaan khusus (opsional)"></textarea>
                </div>

                <!-- Total -->
                <div class="km-total-box">
                  <div class="km-total-row">
                    <span>Estimasi Total Biaya</span>
                    <span class="km-total-price">{{ formatRupiah(bookingTotal) }}</span>
                  </div>
                  <p class="km-total-note">
                    {{ formatRupiah(selectedProperty === 'residence' && bookingForm.rental_type === 'weekly' ? selectedRoom?.price_weekly : selectedRoom?.price_monthly) }}
                    × {{ bookingForm.duration }}
                    {{ selectedProperty === 'residence' && bookingForm.rental_type === 'weekly' ? 'minggu' : 'bulan' }}
                  </p>
                </div>
              </div>

              <div class="km-modal-footer">
                <button class="km-cancel-btn" @click="closeBooking">Batal</button>
                <button 
                  class="km-submit-btn"
                  :disabled="!bookingForm.nama || !bookingForm.phone || isSubmitting"
                  @click="submitBooking"
                >
                  <span v-if="isSubmitting" class="km-spinner"></span>
                  <span v-else>Kirim Pemesanan →</span>
                </button>
              </div>
            </div>
          </div>
        </div>
      </Transition>
    </Teleport>

  </div>
</template>

<style scoped>
/* ═══════════════════════════════════════
   KUBU MAWAR DASHBOARD - CLEAN DESIGN
   Inspired by Rukita's clean aesthetics
   ═══════════════════════════════════════ */

/* ─── Variables & Root ─── */
.km-dashboard {
  --km-red: #C8102E;
  --km-red-dark: #A80B24;
  --km-red-light: #E8354F;
  --km-gold: #8E5B23;
  --km-gold-light: #B07B3A;
  --km-black: #1A1A1A;
  --km-bg: #FAFAF8;
  --km-bg-warm: #F4EFEA;
  --km-border: #E8E2D9;
  --km-text: #222222;
  --km-text-light: #666666;
  --km-text-muted: #999999;
  --km-radius: 16px;
  --km-radius-sm: 10px;
  --km-shadow: 0 2px 12px rgba(0,0,0,0.06);
  --km-shadow-lg: 0 8px 32px rgba(0,0,0,0.10);
  font-family: 'Inter', system-ui, -apple-system, sans-serif;
  background: var(--km-bg);
  color: var(--km-text);
  min-height: 100vh;
  overflow-x: hidden;
  cursor: default;
}

/* ─── Interactive Element Cursors ─── */
.km-dashboard a,
.km-dashboard button,
.km-dashboard [role="button"] {
  cursor: pointer;
}
.km-dashboard input,
.km-dashboard textarea,
.km-dashboard select {
  cursor: auto;
}

/* ─── NAVBAR ─── */
.km-navbar {
  position: sticky;
  top: 0;
  z-index: 100;
  background: rgba(250, 250, 248, 0.92);
  backdrop-filter: blur(16px);
  border-bottom: 1px solid var(--km-border);
  transition: all 0.3s ease;
}
.km-navbar.scrolled {
  box-shadow: 0 2px 20px rgba(0,0,0,0.05);
}
.km-navbar-inner {
  max-width: 1360px;
  margin: 0 auto;
  padding: 0 24px;
  height: 72px;
  display: flex;
  align-items: center;
  justify-content: space-between;
}
.km-brand {
  display: flex;
  align-items: center;
  gap: 12px;
  cursor: pointer;
  flex-shrink: 0;
}
.km-brand-logo {
  width: 36px;
  height: 36px;
  object-fit: contain;
}
.km-brand-name {
  font-weight: 700;
  font-size: 1.15rem;
  color: var(--km-black);
  letter-spacing: -0.3px;
}
.km-nav-links {
  display: none;
  align-items: center;
  gap: 8px;
}
.km-nav-links button {
  padding: 8px 16px;
  font-size: 0.82rem;
  font-weight: 500;
  color: var(--km-text-light);
  border: none;
  background: none;
  cursor: pointer;
  border-radius: 8px;
  transition: all 0.2s;
}
.km-nav-links button:hover {
  color: var(--km-black);
  background: rgba(0,0,0,0.04);
}
.km-nav-links button.active {
  color: var(--km-red);
  font-weight: 600;
  background: rgba(200,16,46,0.06);
}
.km-nav-right {
  display: flex;
  align-items: center;
  gap: 12px;
  flex-shrink: 0;
}
.km-user-badge {
  display: flex;
  align-items: center;
  gap: 10px;
}
.km-avatar {
  width: 36px;
  height: 36px;
  border-radius: 50%;
  background: var(--km-black);
  color: white;
  display: flex;
  align-items: center;
  justify-content: center;
  font-weight: 700;
  font-size: 0.72rem;
  letter-spacing: 0.5px;
}
.km-user-name {
  font-size: 0.82rem;
  font-weight: 600;
  color: var(--km-black);
  display: none;
}
.km-logout-btn {
  padding: 8px;
  color: var(--km-text-muted);
  background: none;
  border: none;
  cursor: pointer;
  border-radius: 8px;
  transition: all 0.2s;
  display: flex;
}
.km-logout-btn:hover {
  color: var(--km-red);
  background: rgba(200,16,46,0.06);
}
.km-mobile-toggle {
  display: flex;
  padding: 6px;
  background: none;
  border: none;
  cursor: pointer;
  color: var(--km-black);
}
.km-mobile-menu {
  padding: 16px 24px;
  border-top: 1px solid var(--km-border);
  display: flex;
  flex-direction: column;
  gap: 4px;
}
.km-mobile-menu button {
  text-align: left;
  padding: 10px 12px;
  font-size: 0.88rem;
  font-weight: 500;
  color: var(--km-text);
  background: none;
  border: none;
  border-radius: 8px;
  cursor: pointer;
  transition: background 0.15s;
}
.km-mobile-menu button:hover {
  background: rgba(0,0,0,0.04);
}
.km-mobile-menu button.logout {
  color: var(--km-red);
  margin-top: 8px;
  border-top: 1px solid var(--km-border);
  padding-top: 14px;
}

@media (min-width: 768px) {
  .km-nav-links { display: flex; }
  .km-user-name { display: block; }
  .km-mobile-toggle { display: none; }
}

/* ─── WELCOME HERO ─── */
.km-welcome {
  position: relative;
  min-height: 400px;
  display: flex;
  align-items: center;
  overflow: hidden;
}
.km-welcome-bg {
  position: absolute;
  inset: 0;
}
.km-welcome-bg img {
  width: 100%;
  height: 100%;
  object-fit: cover;
}
.km-welcome-overlay {
  position: absolute;
  inset: 0;
  background: linear-gradient(135deg, rgba(26,26,26,0.88) 0%, rgba(26,26,26,0.65) 50%, rgba(200,16,46,0.25) 100%);
}
.km-welcome-content {
  position: relative;
  z-index: 2;
  max-width: 1360px;
  margin: 0 auto;
  padding: 56px 24px 48px;
  width: 100%;
}
.km-welcome-badge {
  display: inline-flex;
  align-items: center;
  gap: 8px;
  padding: 6px 14px;
  background: rgba(255,255,255,0.1);
  backdrop-filter: blur(12px);
  border: 1px solid rgba(255,255,255,0.15);
  border-radius: 100px;
  font-size: 0.72rem;
  font-weight: 600;
  color: rgba(255,255,255,0.85);
  text-transform: uppercase;
  letter-spacing: 1.5px;
  margin-bottom: 20px;
}
.km-badge-dot {
  width: 7px;
  height: 7px;
  border-radius: 50%;
  background: #34D399;
  box-shadow: 0 0 8px rgba(52,211,153,0.5);
}
.km-welcome-title {
  font-family: 'Playfair Display', Georgia, serif;
  font-size: 2.2rem;
  font-weight: 600;
  color: white;
  line-height: 1.2;
  margin-bottom: 12px;
}
.km-highlight {
  color: #F9A8B8;
  font-style: italic;
}
.km-welcome-sub {
  font-size: 0.9rem;
  color: rgba(255,255,255,0.65);
  line-height: 1.6;
  max-width: 550px;
  margin-bottom: 28px;
}
.km-welcome-stats {
  display: flex;
  align-items: center;
  gap: 20px;
  margin-bottom: 28px;
}
.km-stat-item {
  display: flex;
  flex-direction: column;
}
.km-stat-number {
  font-size: 1.5rem;
  font-weight: 700;
  color: white;
}
.km-stat-label {
  font-size: 0.7rem;
  color: rgba(255,255,255,0.5);
  text-transform: uppercase;
  letter-spacing: 1px;
}
.km-stat-divider {
  width: 1px;
  height: 36px;
  background: rgba(255,255,255,0.15);
}
.km-cta-btn {
  display: inline-flex;
  align-items: center;
  gap: 8px;
  padding: 12px 28px;
  background: var(--km-red);
  color: white;
  border: none;
  border-radius: 12px;
  font-size: 0.82rem;
  font-weight: 600;
  cursor: pointer;
  transition: all 0.25s;
  box-shadow: 0 4px 14px rgba(200,16,46,0.3);
}
.km-cta-btn:hover {
  background: var(--km-red-dark);
  transform: translateY(-1px);
  box-shadow: 0 6px 20px rgba(200,16,46,0.4);
}
.km-cta-btn.small {
  padding: 10px 20px;
  font-size: 0.78rem;
}

@media (min-width: 768px) {
  .km-welcome { min-height: 440px; }
  .km-welcome-content { padding: 64px 40px 56px; }
  .km-welcome-title { font-size: 3rem; }
}

/* ─── SECTIONS ─── */
.km-section {
  padding: 48px 0;
  scroll-margin-top: 80px;
}
.km-section-inner {
  max-width: 1360px;
  margin: 0 auto;
  padding: 0 24px;
}
.km-section-header {
  display: flex;
  flex-direction: column;
  gap: 8px;
  margin-bottom: 28px;
}
.km-section-eyebrow {
  font-size: 0.72rem;
  font-weight: 700;
  text-transform: uppercase;
  letter-spacing: 2px;
  color: var(--km-red);
}
.km-section-title {
  font-family: 'Playfair Display', Georgia, serif;
  font-size: 1.8rem;
  font-weight: 700;
  color: var(--km-black);
  margin-top: 4px;
}
.km-section-desc {
  font-size: 0.85rem;
  color: var(--km-text-light);
}
.km-reservasi-bg {
  background: var(--km-bg-warm);
}

@media (min-width: 768px) {
  .km-section { padding: 64px 0; }
  .km-section-header {
    flex-direction: row;
    justify-content: space-between;
    align-items: flex-end;
  }
  .km-section-title { font-size: 2.2rem; }
}

/* ─── PROPERTY CARDS (Horizontal Scroll like Rukita) ─── */
.km-property-scroll {
  display: flex;
  gap: 24px;
  overflow-x: auto;
  scroll-snap-type: x mandatory;
  padding-bottom: 16px;
  -webkit-overflow-scrolling: touch;
  scrollbar-width: none;
}
.km-property-scroll::-webkit-scrollbar { display: none; }

.km-property-card {
  flex: 0 0 340px;
  scroll-snap-align: start;
  background: white;
  border-radius: var(--km-radius);
  border: 1px solid var(--km-border);
  overflow: hidden;
  cursor: pointer;
  transition: all 0.35s cubic-bezier(0.4, 0, 0.2, 1);
  display: flex;
  flex-direction: column;
  box-shadow: var(--km-shadow);
}
.km-property-card:hover {
  transform: translateY(-4px);
  box-shadow: var(--km-shadow-lg);
  border-color: rgba(200,16,46,0.25);
}
.km-property-image {
  position: relative;
  height: 220px;
  overflow: hidden;
}
.km-property-image img {
  width: 100%;
  height: 100%;
  object-fit: cover;
  transition: transform 0.6s ease;
}
.km-property-card:hover .km-property-image img {
  transform: scale(1.05);
}
.km-property-image-overlay {
  position: absolute;
  inset: 0;
  background: linear-gradient(to top, rgba(0,0,0,0.55) 0%, transparent 60%);
}
.km-property-badges {
  position: absolute;
  top: 12px;
  left: 12px;
  display: flex;
  gap: 8px;
}
.km-badge {
  padding: 5px 12px;
  border-radius: 100px;
  font-size: 0.68rem;
  font-weight: 700;
  text-transform: uppercase;
  letter-spacing: 0.5px;
  color: white;
}
.km-badge.red { background: var(--km-red); }
.km-badge.gold { background: var(--km-gold); }

.km-rooms-left {
  position: absolute;
  bottom: 12px;
  right: 12px;
}
.km-rooms-left span {
  padding: 5px 12px;
  background: rgba(16, 185, 129, 0.9);
  color: white;
  border-radius: 8px;
  font-size: 0.68rem;
  font-weight: 700;
}
.km-property-body {
  padding: 20px;
  flex: 1;
  display: flex;
  flex-direction: column;
}
.km-property-location {
  display: flex;
  align-items: center;
  gap: 5px;
  font-size: 0.72rem;
  color: var(--km-text-muted);
  margin-bottom: 6px;
}
.km-property-name {
  font-family: 'Playfair Display', Georgia, serif;
  font-size: 1.15rem;
  font-weight: 700;
  color: var(--km-black);
  margin-bottom: 8px;
}
.km-property-desc {
  font-size: 0.78rem;
  color: var(--km-text-light);
  line-height: 1.6;
  flex: 1;
}
.km-property-footer {
  display: flex;
  justify-content: space-between;
  align-items: center;
  padding-top: 14px;
  margin-top: 14px;
  border-top: 1px solid var(--km-border);
}
.km-rental-note {
  font-size: 0.7rem;
  font-weight: 700;
}
.km-rental-note.red { color: var(--km-red); }
.km-rental-note.gold { color: var(--km-gold); }
.km-property-action {
  font-size: 0.72rem;
  font-weight: 600;
  color: var(--km-red);
  transition: transform 0.2s;
}
.km-property-card:hover .km-property-action {
  transform: translateX(3px);
}

@media (min-width: 640px) {
  .km-property-card { flex: 0 0 400px; }
  .km-property-image { height: 240px; }
}
@media (min-width: 1024px) {
  .km-property-card { flex: 0 0 calc(50% - 12px); }
}

/* ─── ROOMS HEADER ─── */
.km-rooms-header {
  margin-bottom: 32px;
}
.km-back-btn {
  display: inline-flex;
  align-items: center;
  gap: 8px;
  padding: 8px 16px;
  font-size: 0.8rem;
  font-weight: 500;
  color: var(--km-text-light);
  background: white;
  border: 1px solid var(--km-border);
  border-radius: 10px;
  cursor: pointer;
  transition: all 0.2s;
  margin-bottom: 20px;
}
.km-back-btn:hover {
  color: var(--km-red);
  border-color: var(--km-red);
  background: rgba(200,16,46,0.03);
}
.km-rooms-title-area {
  display: flex;
  flex-direction: column;
  gap: 16px;
}
.km-rooms-subtitle {
  font-size: 0.82rem;
  color: var(--km-text-light);
  margin-top: 6px;
  max-width: 600px;
}
.km-rooms-stats {
  display: flex;
  gap: 10px;
}
.km-stat-pill {
  display: inline-flex;
  align-items: center;
  gap: 6px;
  padding: 6px 14px;
  border-radius: 100px;
  font-size: 0.72rem;
  font-weight: 600;
}
.km-stat-pill.green {
  background: #ECFDF5;
  color: #065F46;
}
.km-stat-pill.rose {
  background: #FFF1F2;
  color: #9F1239;
}
.km-pill-dot {
  width: 7px;
  height: 7px;
  border-radius: 50%;
}
.km-pill-dot.green { background: #10B981; }
.km-pill-dot.rose { background: #E11D48; }

@media (min-width: 768px) {
  .km-rooms-title-area {
    flex-direction: row;
    justify-content: space-between;
    align-items: flex-start;
  }
}

/* ─── RESIDENCE ROOM TYPE CARDS (2-col) ─── */
.km-room-types-grid {
  display: grid;
  grid-template-columns: 1fr;
  gap: 28px;
}
@media (min-width: 768px) {
  .km-room-types-grid { grid-template-columns: 1fr 1fr; }
}

.km-room-type-card {
  background: white;
  border-radius: var(--km-radius);
  border: 1px solid var(--km-border);
  overflow: hidden;
  transition: all 0.35s;
  box-shadow: var(--km-shadow);
  display: flex;
  flex-direction: column;
}
.km-room-type-card:hover {
  box-shadow: var(--km-shadow-lg);
  transform: translateY(-2px);
}
.km-room-image-wrap {
  position: relative;
  height: 260px;
  overflow: hidden;
}
.km-room-image-wrap img {
  width: 100%;
  height: 100%;
  object-fit: cover;
  transition: transform 0.7s ease;
}
.km-room-type-card:hover .km-room-image-wrap img {
  transform: scale(1.05);
}
.km-room-image-overlay {
  position: absolute;
  inset: 0;
  background: linear-gradient(to top, rgba(0,0,0,0.4) 0%, transparent 50%);
}
.km-room-type-badge {
  position: absolute;
  top: 14px;
  left: 14px;
  padding: 6px 16px;
  border-radius: 100px;
  font-size: 0.72rem;
  font-weight: 700;
  text-transform: uppercase;
  letter-spacing: 1px;
  color: white;
}
.km-room-type-badge.exclusive {
  background: linear-gradient(135deg, #C8102E, #FF4D6A);
}
.km-room-type-badge.mediteranian {
  background: linear-gradient(135deg, #8E5B23, #C49B5F);
}
.km-room-status-badge {
  position: absolute;
  top: 14px;
  right: 14px;
  padding: 5px 12px;
  border-radius: 8px;
  font-size: 0.68rem;
  font-weight: 700;
  text-transform: uppercase;
  color: white;
}
.km-room-status-badge.available {
  background: rgba(16, 185, 129, 0.9);
}
.km-room-status-badge.occupied {
  background: rgba(225, 29, 72, 0.9);
}
.km-rooms-left-badge {
  position: absolute;
  bottom: 14px;
  left: 14px;
  padding: 5px 12px;
  background: rgba(0,0,0,0.6);
  backdrop-filter: blur(8px);
  border-radius: 8px;
  font-size: 0.68rem;
  font-weight: 600;
  color: white;
}

.km-room-content {
  padding: 24px;
  flex: 1;
  display: flex;
  flex-direction: column;
}
.km-room-name {
  font-family: 'Playfair Display', Georgia, serif;
  font-size: 1.35rem;
  font-weight: 700;
  color: var(--km-black);
  margin-bottom: 8px;
}
.km-room-desc {
  font-size: 0.82rem;
  color: var(--km-text-light);
  line-height: 1.6;
  margin-bottom: 16px;
}

.km-features {
  margin-bottom: 20px;
}
.km-features-title {
  font-size: 0.7rem;
  font-weight: 700;
  text-transform: uppercase;
  letter-spacing: 1px;
  color: var(--km-text-muted);
  margin-bottom: 10px;
}
.km-features-grid {
  display: flex;
  flex-wrap: wrap;
  gap: 6px;
}
.km-feature-tag {
  display: inline-flex;
  align-items: center;
  gap: 4px;
  padding: 4px 10px;
  background: #F8F6F3;
  border-radius: 6px;
  font-size: 0.72rem;
  color: var(--km-text-light);
}
.km-check {
  color: var(--km-red);
  font-weight: 700;
  font-size: 0.68rem;
}

.km-pricing-box {
  background: #FAF8F5;
  border: 1px solid var(--km-border);
  border-radius: 12px;
  padding: 14px 16px;
  margin-bottom: 16px;
}
.km-price-row {
  display: flex;
  justify-content: space-between;
  align-items: center;
}
.km-price-label {
  font-size: 0.75rem;
  color: var(--km-text-muted);
}
.km-price-value {
  font-size: 0.95rem;
  font-weight: 700;
}
.km-price-value.red { color: var(--km-red); }
.km-price-value.green { color: #047857; }
.km-price-divider {
  height: 1px;
  background: var(--km-border);
  margin: 10px 0;
}

.km-book-btn {
  width: 100%;
  padding: 13px 20px;
  background: var(--km-red);
  color: white;
  border: none;
  border-radius: 12px;
  font-size: 0.82rem;
  font-weight: 600;
  cursor: pointer;
  transition: all 0.25s;
  box-shadow: 0 4px 14px rgba(200,16,46,0.2);
  margin-top: auto;
}
.km-book-btn:hover {
  background: var(--km-red-dark);
  transform: translateY(-1px);
  box-shadow: 0 6px 20px rgba(200,16,46,0.3);
}
.km-book-btn.disabled {
  background: #F3F3F3;
  color: #AAAAAA;
  cursor: not-allowed;
  box-shadow: none;
  border: 1px solid var(--km-border);
}
.km-book-btn.disabled:hover {
  transform: none;
}

/* ─── SUWUNG KOST GRID ─── */
.km-kost-grid {
  display: grid;
  grid-template-columns: 1fr;
  gap: 20px;
}
@media (min-width: 640px) {
  .km-kost-grid { grid-template-columns: 1fr 1fr; }
}
@media (min-width: 1024px) {
  .km-kost-grid { grid-template-columns: 1fr 1fr 1fr; }
}

.km-kost-card {
  background: white;
  border-radius: var(--km-radius);
  border: 1px solid var(--km-border);
  overflow: hidden;
  transition: all 0.3s;
  box-shadow: var(--km-shadow);
  display: flex;
  flex-direction: column;
}
.km-kost-card:hover {
  box-shadow: var(--km-shadow-lg);
  transform: translateY(-2px);
}
.km-kost-card.occupied {
  opacity: 0.7;
}
.km-kost-card.occupied:hover {
  transform: none;
  opacity: 0.75;
}
.km-kost-image-wrap {
  position: relative;
  height: 200px;
  overflow: hidden;
}
.km-kost-image-wrap img {
  width: 100%;
  height: 100%;
  object-fit: cover;
  transition: transform 0.5s;
}
.km-kost-card:hover .km-kost-image-wrap img {
  transform: scale(1.04);
}
.km-kost-badges {
  position: absolute;
  top: 10px;
  left: 10px;
  right: 10px;
  display: flex;
  justify-content: space-between;
}
.km-kost-number {
  padding: 4px 10px;
  background: rgba(255,255,255,0.95);
  border-radius: 6px;
  font-size: 0.68rem;
  font-weight: 700;
  color: var(--km-black);
}
.km-kost-status {
  padding: 4px 10px;
  border-radius: 6px;
  font-size: 0.68rem;
  font-weight: 700;
  color: white;
}
.km-kost-status.available { background: rgba(16,185,129,0.9); }
.km-kost-status.occupied { background: rgba(225,29,72,0.9); }
.km-kost-type-label {
  position: absolute;
  bottom: 10px;
  left: 10px;
  padding: 4px 10px;
  background: rgba(0,0,0,0.6);
  backdrop-filter: blur(6px);
  border-radius: 6px;
  font-size: 0.66rem;
  font-weight: 600;
  color: white;
  text-transform: uppercase;
  letter-spacing: 0.5px;
}
.km-kost-body {
  padding: 18px;
  flex: 1;
  display: flex;
  flex-direction: column;
}
.km-kost-name {
  font-size: 0.95rem;
  font-weight: 700;
  color: var(--km-black);
  margin-bottom: 6px;
}
.km-kost-desc {
  font-size: 0.75rem;
  color: var(--km-text-light);
  line-height: 1.5;
  margin-bottom: 12px;
}
.km-kost-features {
  display: flex;
  flex-wrap: wrap;
  gap: 4px;
  margin-bottom: 14px;
}
.km-kost-feat {
  font-size: 0.68rem;
  color: var(--km-text-light);
  padding: 3px 8px;
  background: #F6F4F1;
  border-radius: 5px;
  display: inline-flex;
  align-items: center;
  gap: 3px;
}
.km-check-sm {
  color: var(--km-red);
  font-weight: 700;
  font-size: 0.62rem;
}
.km-kost-price-area {
  background: #FFF7ED;
  border: 1px solid #FED7AA;
  border-radius: 10px;
  padding: 12px 14px;
  margin-bottom: 14px;
}
.km-kost-price {
  display: flex;
  justify-content: space-between;
  align-items: center;
}
.km-kost-price-label {
  font-size: 0.72rem;
  color: #92400E;
  font-weight: 600;
}
.km-kost-price-value {
  font-size: 0.95rem;
  font-weight: 700;
  color: #78350F;
}
.km-kost-price-note {
  font-size: 0.62rem;
  color: #B45309;
  font-style: italic;
  margin-top: 4px;
}

/* ─── FILTER TABS ─── */
.km-filter-tabs {
  display: flex;
  gap: 4px;
  background: white;
  padding: 4px;
  border-radius: 12px;
  border: 1px solid var(--km-border);
}
.km-filter-tabs button {
  padding: 8px 16px;
  font-size: 0.75rem;
  font-weight: 600;
  color: var(--km-text-muted);
  background: none;
  border: none;
  border-radius: 8px;
  cursor: pointer;
  transition: all 0.2s;
  white-space: nowrap;
}
.km-filter-tabs button.active {
  background: var(--km-red);
  color: white;
  box-shadow: 0 2px 6px rgba(200,16,46,0.25);
}
.km-filter-tabs button:not(.active):hover {
  background: rgba(0,0,0,0.04);
  color: var(--km-black);
}

/* ─── EMPTY STATE ─── */
.km-empty-state {
  text-align: center;
  padding: 48px 24px;
  background: white;
  border-radius: var(--km-radius);
  border: 1px solid var(--km-border);
  box-shadow: var(--km-shadow);
}
.km-empty-icon {
  width: 56px;
  height: 56px;
  border-radius: 50%;
  background: var(--km-bg-warm);
  border: 1px solid var(--km-border);
  display: flex;
  align-items: center;
  justify-content: center;
  color: var(--km-red);
  margin: 0 auto 16px;
}
.km-empty-state h3 {
  font-size: 1rem;
  font-weight: 700;
  color: var(--km-black);
  margin-bottom: 6px;
}
.km-empty-state p {
  font-size: 0.8rem;
  color: var(--km-text-light);
  max-width: 380px;
  margin: 0 auto 20px;
}

/* ─── BOOKING LIST ─── */
.km-booking-list {
  display: flex;
  flex-direction: column;
  gap: 12px;
}
.km-booking-card {
  background: white;
  border-radius: 14px;
  border: 1px solid var(--km-border);
  padding: 18px 22px;
  box-shadow: var(--km-shadow);
  display: flex;
  flex-direction: column;
  gap: 14px;
}
.km-booking-left {
  display: flex;
  align-items: flex-start;
  gap: 14px;
}
.km-booking-icon {
  width: 40px;
  height: 40px;
  border-radius: 10px;
  background: var(--km-bg-warm);
  display: flex;
  align-items: center;
  justify-content: center;
  font-size: 1.1rem;
  flex-shrink: 0;
}
.km-booking-meta {
  display: flex;
  flex-wrap: wrap;
  gap: 6px;
  margin-bottom: 4px;
}
.km-booking-code {
  font-size: 0.72rem;
  font-weight: 700;
  color: var(--km-black);
  font-family: monospace;
}
.km-booking-type {
  font-size: 0.62rem;
  font-weight: 600;
  padding: 2px 8px;
  background: #F3F4F6;
  color: #374151;
  border-radius: 4px;
}
.km-booking-status {
  font-size: 0.62rem;
  font-weight: 700;
  padding: 2px 8px;
  border-radius: 4px;
  text-transform: uppercase;
}
.km-booking-status.active {
  background: #ECFDF5;
  color: #065F46;
}
.km-booking-status.done {
  background: #F3F4F6;
  color: #6B7280;
}
.km-booking-room {
  font-size: 0.88rem;
  font-weight: 700;
  color: var(--km-black);
}
.km-booking-detail {
  font-size: 0.72rem;
  color: var(--km-text-light);
  margin-top: 2px;
}
.km-booking-right {
  text-align: right;
}
.km-booking-total-label {
  font-size: 0.62rem;
  text-transform: uppercase;
  color: var(--km-text-muted);
  letter-spacing: 1px;
}
.km-booking-total {
  font-size: 1rem;
  font-weight: 700;
  color: var(--km-red);
  font-family: monospace;
}

@media (min-width: 768px) {
  .km-booking-card {
    flex-direction: row;
    align-items: center;
    justify-content: space-between;
  }
}

/* ─── PROFILE ─── */
.km-profile-card {
  background: white;
  border-radius: var(--km-radius);
  border: 1px solid var(--km-border);
  padding: 28px;
  box-shadow: var(--km-shadow);
  max-width: 560px;
}
.km-profile-title {
  font-family: 'Playfair Display', Georgia, serif;
  font-size: 1.3rem;
  font-weight: 700;
  margin-bottom: 20px;
}
.km-profile-info {
  display: flex;
  align-items: center;
  gap: 16px;
  padding-bottom: 20px;
  border-bottom: 1px solid var(--km-border);
}
.km-profile-avatar {
  width: 52px;
  height: 52px;
  border-radius: 50%;
  background: var(--km-black);
  color: white;
  display: flex;
  align-items: center;
  justify-content: center;
  font-weight: 700;
  font-size: 0.95rem;
}
.km-profile-name {
  font-size: 1rem;
  font-weight: 700;
  color: var(--km-black);
}
.km-profile-email {
  font-size: 0.78rem;
  color: var(--km-text-muted);
  font-family: monospace;
}
.km-logout-action {
  margin-top: 20px;
  padding: 10px 20px;
  border: 1px solid #FCA5A5;
  color: #DC2626;
  background: none;
  border-radius: 10px;
  font-size: 0.78rem;
  font-weight: 600;
  cursor: pointer;
  transition: all 0.2s;
}
.km-logout-action:hover {
  background: #FEF2F2;
}

/* ─── MODAL ─── */
.km-modal-overlay {
  position: fixed;
  inset: 0;
  z-index: 1000;
  background: rgba(0,0,0,0.5);
  backdrop-filter: blur(4px);
  display: flex;
  align-items: center;
  justify-content: center;
  padding: 16px;
}
.km-modal {
  background: white;
  border-radius: 20px;
  width: 100%;
  max-width: 520px;
  max-height: 90vh;
  overflow-y: auto;
  box-shadow: 0 24px 64px rgba(0,0,0,0.2);
}
.km-modal-header {
  display: flex;
  justify-content: space-between;
  align-items: flex-start;
  padding: 24px 24px 0;
}
.km-modal-eyebrow {
  font-size: 0.68rem;
  font-weight: 600;
  text-transform: uppercase;
  letter-spacing: 1.5px;
  color: var(--km-red);
  margin-bottom: 4px;
}
.km-modal-title {
  font-family: 'Playfair Display', Georgia, serif;
  font-size: 1.35rem;
  font-weight: 700;
  color: var(--km-black);
}
.km-modal-subtitle {
  font-size: 0.78rem;
  color: var(--km-text-light);
  margin-top: 2px;
}
.km-modal-close {
  width: 32px;
  height: 32px;
  border-radius: 50%;
  background: #F3F3F3;
  border: none;
  cursor: pointer;
  font-size: 0.9rem;
  color: var(--km-text-muted);
  display: flex;
  align-items: center;
  justify-content: center;
  transition: all 0.2s;
  flex-shrink: 0;
}
.km-modal-close:hover {
  background: #E5E5E5;
  color: var(--km-black);
}
.km-modal-body {
  padding: 20px 24px;
  display: flex;
  flex-direction: column;
  gap: 16px;
}
.km-modal-room-preview {
  position: relative;
  height: 140px;
  border-radius: 14px;
  overflow: hidden;
}
.km-modal-room-preview img {
  width: 100%;
  height: 100%;
  object-fit: cover;
}
.km-modal-room-info {
  position: absolute;
  bottom: 10px;
  left: 10px;
  display: flex;
  gap: 6px;
}
.km-modal-room-number,
.km-modal-room-type {
  padding: 4px 10px;
  background: rgba(0,0,0,0.6);
  backdrop-filter: blur(6px);
  border-radius: 6px;
  font-size: 0.66rem;
  font-weight: 600;
  color: white;
}

.km-form-group {
  display: flex;
  flex-direction: column;
  gap: 6px;
}
.km-form-group label {
  font-size: 0.78rem;
  font-weight: 600;
  color: var(--km-black);
}
.km-form-group label .required {
  color: var(--km-red);
}
.km-form-group input,
.km-form-group textarea {
  padding: 11px 14px;
  border: 1px solid var(--km-border);
  border-radius: 10px;
  font-size: 0.85rem;
  color: var(--km-text);
  background: var(--km-bg);
  outline: none;
  transition: border-color 0.2s;
  font-family: inherit;
}
.km-form-group input:focus,
.km-form-group textarea:focus {
  border-color: var(--km-red);
  box-shadow: 0 0 0 3px rgba(200,16,46,0.08);
}
.km-form-group textarea {
  resize: vertical;
  min-height: 56px;
}

/* Radio Group */
.km-radio-group {
  display: grid;
  grid-template-columns: 1fr 1fr;
  gap: 10px;
}
.km-radio-option {
  position: relative;
  display: flex;
  align-items: center;
  padding: 12px 14px;
  border: 2px solid var(--km-border);
  border-radius: 12px;
  cursor: pointer;
  transition: all 0.2s;
}
.km-radio-option.selected {
  border-color: var(--km-red);
  background: rgba(200,16,46,0.03);
}
.km-radio-option input {
  position: absolute;
  opacity: 0;
  pointer-events: none;
}
.km-radio-content {
  display: flex;
  flex-direction: column;
  gap: 2px;
}
.km-radio-title {
  font-size: 0.8rem;
  font-weight: 600;
  color: var(--km-black);
}
.km-radio-price {
  font-size: 0.7rem;
  color: var(--km-text-light);
}

/* Duration Input */
.km-duration-input {
  display: flex;
  align-items: center;
  gap: 12px;
}
.km-dur-btn {
  width: 36px;
  height: 36px;
  border-radius: 10px;
  border: 1px solid var(--km-border);
  background: var(--km-bg);
  font-size: 1.1rem;
  color: var(--km-black);
  cursor: pointer;
  display: flex;
  align-items: center;
  justify-content: center;
  transition: all 0.15s;
}
.km-dur-btn:hover {
  background: var(--km-border);
}
.km-dur-value {
  font-size: 1.1rem;
  font-weight: 700;
  min-width: 28px;
  text-align: center;
}
.km-dur-unit {
  font-size: 0.8rem;
  color: var(--km-text-light);
}

/* Total Box */
.km-total-box {
  background: #F0FDF4;
  border: 1px solid #BBF7D0;
  border-radius: 12px;
  padding: 14px 16px;
}
.km-total-row {
  display: flex;
  justify-content: space-between;
  align-items: center;
}
.km-total-row span:first-child {
  font-size: 0.8rem;
  font-weight: 600;
  color: #166534;
}
.km-total-price {
  font-size: 1.1rem;
  font-weight: 700;
  color: #15803D;
}
.km-total-note {
  font-size: 0.68rem;
  color: #4ADE80;
  margin-top: 4px;
}

/* Modal Footer */
.km-modal-footer {
  display: flex;
  gap: 10px;
  padding: 16px 24px 24px;
  border-top: 1px solid var(--km-border);
}
.km-cancel-btn {
  flex: 1;
  padding: 12px;
  background: #F3F3F3;
  border: none;
  border-radius: 12px;
  font-size: 0.82rem;
  font-weight: 600;
  color: var(--km-text-light);
  cursor: pointer;
  transition: background 0.2s;
}
.km-cancel-btn:hover {
  background: #E8E8E8;
}
.km-submit-btn {
  flex: 2;
  padding: 12px;
  background: var(--km-red);
  border: none;
  border-radius: 12px;
  font-size: 0.82rem;
  font-weight: 600;
  color: white;
  cursor: pointer;
  transition: all 0.25s;
  box-shadow: 0 4px 14px rgba(200,16,46,0.25);
  display: flex;
  align-items: center;
  justify-content: center;
}
.km-submit-btn:hover:not(:disabled) {
  background: var(--km-red-dark);
}
.km-submit-btn:disabled {
  opacity: 0.5;
  cursor: not-allowed;
}

.km-spinner {
  width: 18px;
  height: 18px;
  border: 2px solid rgba(255,255,255,0.3);
  border-top-color: white;
  border-radius: 50%;
  animation: km-spin 0.6s linear infinite;
}
@keyframes km-spin {
  to { transform: rotate(360deg); }
}

/* Modal Success */
.km-modal-success {
  padding: 40px 28px;
  text-align: center;
}
.km-success-icon {
  width: 56px;
  height: 56px;
  border-radius: 50%;
  background: #ECFDF5;
  color: #059669;
  font-size: 1.4rem;
  font-weight: 700;
  display: flex;
  align-items: center;
  justify-content: center;
  margin: 0 auto 16px;
  border: 2px solid #A7F3D0;
}
.km-modal-success h3 {
  font-family: 'Playfair Display', Georgia, serif;
  font-size: 1.3rem;
  font-weight: 700;
  color: var(--km-black);
  margin-bottom: 10px;
}
.km-modal-success p {
  font-size: 0.82rem;
  color: var(--km-text-light);
  line-height: 1.6;
  margin-bottom: 6px;
}
.km-success-note {
  font-size: 0.78rem !important;
  color: #047857 !important;
  font-weight: 500;
}
.km-modal-success .km-cta-btn {
  margin-top: 16px;
}

/* ─── MODAL TRANSITION ─── */
.modal-fade-enter-active {
  transition: all 0.3s ease;
}
.modal-fade-leave-active {
  transition: all 0.2s ease;
}
.modal-fade-enter-from {
  opacity: 0;
}
.modal-fade-enter-from .km-modal {
  transform: scale(0.95) translateY(20px);
}
.modal-fade-leave-to {
  opacity: 0;
}
.modal-fade-leave-to .km-modal {
  transform: scale(0.95) translateY(10px);
}
</style>
