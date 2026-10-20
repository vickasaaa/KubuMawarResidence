<script setup lang="ts">
import { ref, onMounted, onUnmounted, computed, nextTick } from 'vue'
import { useRouter } from 'vue-router'

const router = useRouter()

// ─── State ───
const isScrolled = ref(false)
const scrollY = ref(0)
const mouse = ref({ x: 0, y: 0, hover: false })
const selectedProperty = ref<'residence' | 'suwung' | null>(null)

const selectProperty = (id: 'residence' | 'suwung') => {
  selectedProperty.value = id
  nextTick(() => {
    const el = document.getElementById('rooms-list-section')
    if (el) {
      el.scrollIntoView({ behavior: 'smooth', block: 'start' })
    }
  })
}

const goToLogin = () => {
  router.push('/login')
}

const goToBooking = (roomId?: number) => {
  if (roomId) {
    router.push({ path: '/booking', query: { room: roomId.toString() } })
  } else {
    router.push('/booking')
  }
}

// ─── Hero Slides ───
const heroSlides = [
  {
    image: 'https://images.unsplash.com/photo-1537996194471-e657df975ab4?auto=format&fit=crop&w=1920&q=80',
    alt: 'Lush rice terraces of Bali',
  },
  {
    image: 'https://images.unsplash.com/photo-1570789210967-2cac24afeb00?auto=format&fit=crop&w=1920&q=80',
    alt: 'Tropical Balinese villa pool',
  },
]
const currentSlide = ref(0)

// ─── 2 Pilihan Hunian Utama ───
const properties = [
  {
    id: 'residence',
    name: 'Kubu Mawar Residence',
    category: 'Home Stay',
    location: 'Seminyak, Bali',
    image: 'https://images.unsplash.com/photo-1582719478250-c89cae4dc85b?auto=format&fit=crop&w=1200&q=80',
    description: 'Boutique homestay bernuansa villa tropis privat di Seminyak. Menawarkan ketenangan dan fasilitas lengkap dengan pilihan sewa mingguan & bulanan.',
    rentalNote: 'Pilihan Sewa: Mingguan & Bulanan',
  },
  {
    id: 'suwung',
    name: 'Kubu Mawar Suwung',
    category: 'Kost-Kostan',
    location: 'Suwung, Denpasar Selatan, Bali',
    image: 'https://images.unsplash.com/photo-1554995207-c18c203602cb?auto=format&fit=crop&w=1200&q=80',
    description: 'Kost-kostan modern, aman, dan nyaman di Suwung Denpasar Selatan. Lingkungan strategis dekat Bypass Ngurah Rai khusus untuk sewa bulanan.',
    rentalNote: 'Pilihan Sewa: Khusus Bulanan',
  },
]

// ─── Rooms Data (Initial fallback & updated from API) ───
const rooms = ref<any[]>([
  // Residence Units: Only Exclusive & Mediteranian
  {
    id: 1,
    property_type: 'residence',
    property_name: 'Kubu Mawar Residence',
    room_number: 'KM-EX01',
    name: 'Exclusive Suite',
    type_label: 'Tipe Exclusive',
    image: 'https://images.unsplash.com/photo-1590490360182-c33d955e4c47?auto=format&fit=crop&w=1200&q=80',
    description: 'Suite mewah dengan interior premium, king bed canopy, teras pribadi menghadap taman tropis, dan layanan eksklusif.',
    price_weekly: 3200000,
    price_monthly: 11500000,
    features: ['King Bed Canopy', 'Teras Taman Pribadi', 'Living Room', 'Smart TV 55"', 'AC & Water Heater', 'WiFi High-Speed', 'Butler Service', 'Mini Bar Gratis'],
    status: 'available',
  },
  {
    id: 2,
    property_type: 'residence',
    property_name: 'Kubu Mawar Residence',
    room_number: 'KM-MD01',
    name: 'Mediteranian Suite',
    type_label: 'Tipe Mediteranian',
    image: 'https://images.unsplash.com/photo-1631049307264-da0ec9d70304?auto=format&fit=crop&w=1200&q=80',
    description: 'Desain mediterania klasik dengan aksen batu alam, rotan, dan pencahayaan hangat. Nyaman dan estetik.',
    price_weekly: 1800000,
    price_monthly: 6500000,
    features: ['Queen Bed Nyaman', 'Balkon Asri', 'Meja Kerja', 'AC & Water Heater', 'Smart TV 42"', 'WiFi Cepat', 'Kulkas Mini', 'Housekeeping'],
    status: 'available',
  },

  // Suwung Units: 10 Kamar Kost Standard AC (sebagian terisi & sebagian kosong)
  ...Array.from({ length: 10 }, (_, i) => ({
    id: 10 + i + 1,
    property_type: 'suwung',
    property_name: 'Kubu Mawar Suwung',
    room_number: `KM-SW${String(i + 1).padStart(2, '0')}`,
    name: `Kamar Kost No. ${i + 1}`,
    type_label: 'Kost Standard AC',
    image: 'https://images.unsplash.com/photo-1522771739844-6a9f6d5f14af?auto=format&fit=crop&w=1200&q=80',
    description: 'Kamar kost modern nyaman ber-AC di Suwung Denpasar Selatan, kamar mandi dalam dengan shower air hangat, lemari dan meja kerja.',
    price_weekly: null,
    price_monthly: 1600000,
    features: ['AC 1 PK', 'Kamar Mandi Dalam', 'Kasur Springbed', 'WiFi Fiber 50Mbps', 'Lemari 2 Pintu', 'Dapur Bersama', 'Parkir Motor', 'CCTV 24 Jam'],
    status: [1, 3, 6, 8].includes(i) ? 'occupied' : 'available',
  }))
])

// Fetch live rooms from backend API to ensure rooms stay synced with Admin
const fetchRoomsFromApi = async () => {
  try {
    const res = await fetch('http://localhost:8000/api/rooms')
    if (res.ok) {
      const json = await res.json()
      if (json.data && json.data.length > 0) {
        rooms.value = json.data
      }
    }
  } catch {}
}

// Active rooms based on selected property
const activeRooms = computed(() => {
  if (!selectedProperty.value) return []
  return rooms.value.filter(r => r.property_type === selectedProperty.value)
})

const formatRupiah = (val: number | null | undefined) => {
  if (!val && val !== 0) return '-'
  return 'Rp ' + Number(val).toLocaleString('id-ID')
}

const contactInfo = {
  email: 'hello@kubumawar.com',
  instagram: '@kubumaawarproperti',
  whatsapp: '081991888008',
  maps: 'Google Maps'
}

// ─── Logic ───
const handleScroll = () => {
  scrollY.value = window.scrollY
  isScrolled.value = window.scrollY > 50
}

const handleMouseMove = (e: MouseEvent) => {
  mouse.value.x = e.clientX
  mouse.value.y = e.clientY
}

const scrollTo = (id: string) => {
  const el = document.getElementById(id)
  if (el) {
    el.scrollIntoView({ behavior: 'smooth' })
  }
}

let observer: IntersectionObserver
onMounted(() => {
  window.addEventListener('scroll', handleScroll)
  window.addEventListener('mousemove', handleMouseMove)
  fetchRoomsFromApi()
  
  // Auto-advance hero
  setInterval(() => {
    currentSlide.value = (currentSlide.value + 1) % heroSlides.length
  }, 6000)

  // Intersection Observer for staggered reveals
  observer = new IntersectionObserver((entries) => {
    entries.forEach(entry => {
      if (entry.isIntersecting) {
        entry.target.classList.add('is-revealed')
      }
    })
  }, { threshold: 0.15 })

  document.querySelectorAll('.reveal-el, .reveal-image-container').forEach(el => observer.observe(el))
})

onUnmounted(() => {
  window.removeEventListener('scroll', handleScroll)
  window.removeEventListener('mousemove', handleMouseMove)
  observer?.disconnect()
})

const heroParallax = computed(() => `translateY(${scrollY.value * 0.4}px) scale(${1 + scrollY.value * 0.0005})`)
</script>

<template>
  <div class="bg-[#F7F5F0] text-kubu-black selection:bg-kubu-red selection:text-white font-body min-h-screen overflow-x-hidden">
    
    <!-- Custom Cursor -->
    <div 
      class="fixed w-4 h-4 bg-kubu-red rounded-full pointer-events-none z-[9999] mix-blend-difference transition-transform duration-100 ease-out hidden lg:block"
      :style="{ transform: `translate3d(${mouse.x - 8}px, ${mouse.y - 8}px, 0) scale(${mouse.hover ? 3 : 1})` }"
    ></div>

    <!-- Navigation (Original) -->
    <nav :class="[
      'fixed w-full z-50 transition-all duration-700 px-6 md:px-12 py-6 flex justify-between items-center mix-blend-difference text-white',
      isScrolled ? 'py-4 bg-black/5 backdrop-blur-md' : ''
    ]">
      <div class="flex items-center gap-3 cursor-pointer" @click="scrollTo('home')" @mouseenter="mouse.hover=true" @mouseleave="mouse.hover=false">
        <img src="/images/logo.png" alt="Logo" class="w-10 h-10 object-contain filter brightness-0 invert" />
        <span class="font-display text-xl tracking-wide font-medium"></span>
      </div>
      
      <div class="hidden md:flex gap-10 text-sm tracking-[0.15em] uppercase">
        <button @click="scrollTo('about')" class="hover:text-kubu-red-light transition-colors cursor-pointer" @mouseenter="mouse.hover=true" @mouseleave="mouse.hover=false">Story</button>
        <button @click="scrollTo('rooms')" class="hover:text-kubu-red-light transition-colors cursor-pointer" @mouseenter="mouse.hover=true" @mouseleave="mouse.hover=false">Rooms</button>
        <button @click="scrollTo('contact')" class="hover:text-kubu-red-light transition-colors cursor-pointer" @mouseenter="mouse.hover=true" @mouseleave="mouse.hover=false">Book Now</button>
      </div>
    </nav>

    <!-- 1. Hero Section (Clean & Simple, exactly as original) -->
    <section id="home" class="relative h-screen w-full overflow-hidden flex items-center justify-center bg-kubu-black">
      <div class="absolute inset-0 z-0" :style="{ transform: heroParallax }">
        <div 
          v-for="(slide, idx) in heroSlides" :key="idx"
          class="absolute inset-0 transition-opacity duration-[2000ms] ease-in-out"
          :class="idx === currentSlide ? 'opacity-100' : 'opacity-0'"
        >
          <img :src="slide.image" class="w-full h-full object-cover scale-105" />
          <div class="absolute inset-0 bg-black/40"></div>
        </div>
      </div>
      
      <div class="relative z-10 text-center flex flex-col items-center mt-20 px-4">
        <span class="text-white/80 uppercase tracking-[0.4em] text-xs md:text-sm mb-6 block reveal-el">Bali, Indonesia</span>
        <h1 class="font-display text-5xl md:text-7xl lg:text-[7rem] text-white leading-[1.1] font-normal reveal-el" style="transition-delay: 200ms;">
          A <i class="text-kubu-red-light">Soulful</i><br>Balinese Escape
        </h1>
        <button 
          @click="scrollTo('about')"
          @mouseenter="mouse.hover=true" @mouseleave="mouse.hover=false"
          class="mt-16 w-12 h-12 border border-white/30 rounded-full flex items-center justify-center text-white hover:bg-white hover:text-kubu-black transition-colors duration-500 reveal-el cursor-pointer"
          style="transition-delay: 400ms;"
        >
          ↓
        </button>
      </div>
    </section>

    <!-- 2. Editorial About Section (Original) -->
    <section id="about" class="py-32 lg:py-48 px-6 md:px-12 max-w-[1400px] mx-auto">
      <div class="grid lg:grid-cols-12 gap-16 lg:gap-8 items-center">
        <div class="lg:col-span-5 lg:col-start-2">
          <p class="text-kubu-red uppercase tracking-[0.2em] text-xs font-bold mb-8 reveal-el">The Philosophy</p>
          <h2 class="font-display text-4xl lg:text-6xl leading-[1.1] text-kubu-black mb-10 reveal-el" style="transition-delay: 100ms;">
            Where tradition meets <i>tranquility.</i>
          </h2>
          <div class="text-[#555] space-y-6 text-lg leading-relaxed font-light reveal-el" style="transition-delay: 200ms;">
            <p>
              Kubu Mawar meaning "Rose Cottage" was founded by passionate travelers who fell deeply in love with the Island of the Gods. 
            </p>
            <p>
              We discarded the generic hotel experience to create something authentic. Every carved stone, every woven rattan, and every smile from our local staff is an invitation into the living, breathing heart of a Balinese community.
            </p>
          </div>
          
          <div class="mt-12 reveal-el" style="transition-delay: 300ms;">
            <a href="#rooms" class="inline-flex items-center gap-4 text-sm uppercase tracking-[0.2em] font-semibold hover:text-kubu-red transition-colors group cursor-pointer" @mouseenter="mouse.hover=true" @mouseleave="mouse.hover=false">
              Discover Our Spaces
              <span class="w-8 h-[1px] bg-currentColor group-hover:w-12 transition-all duration-300"></span>
            </a>
          </div>
        </div>

        <div class="lg:col-span-5 lg:col-start-8 relative flex items-center justify-center">
          <div class="relative reveal-el" style="transition-delay: 350ms;">
            <div 
              class="relative z-10 w-full max-w-[500px] lg:max-w-[800px] mx-auto mascot-float cursor-pointer group" 
              @mouseenter="mouse.hover=true" 
              @mouseleave="mouse.hover=false"
            >
              <img 
                src="/images/mask-mascot.png" 
                class="relative z-10 w-full h-auto object-contain drop-shadow-2xl transition-opacity duration-500 group-hover:opacity-0" 
                alt="Kubu Mawar Balinese Mask" 
              />
              <img 
                src="/images/red-mask.png" 
                class="absolute inset-0 z-20 w-full h-full object-contain drop-shadow-2xl transition-opacity duration-500 opacity-0 group-hover:opacity-100" 
                alt="Kubu Mawar Balinese Mask Red" 
              />
            </div>
          </div>
        </div>
      </div>
    </section>

    <!-- 3. PEMILIHAN HUNIAN (2 OPSI HUNIAN BERGAMBAR -> KLIK UNTUK MELIHAT TIPE KAMAR & FASILITAS) -->
    <section id="rooms" class="py-28 bg-[#F4EFEA] text-[#1A1A1A] px-6 md:px-12 border-t border-[#E8E2D9]">
      <div class="max-w-[1400px] mx-auto space-y-16">
        
        <!-- Section Title -->
        <div class="flex flex-col md:flex-row justify-between items-start md:items-end gap-6 reveal-el">
          <div>
            <h2 class="font-display text-4xl sm:text-5xl lg:text-6xl text-[#1A1A1A]">
              Our Spaces
            </h2>
          </div>
          <p class="text-[#1A1A1A]/70 text-sm font-light max-w-md leading-relaxed">
            Pilih salah satu hunian di bawah ini untuk melihat detail tipe kamar, fasilitas lengkap, dan tarif sewa.
          </p>
        </div>

        <!-- ─── DUA OPSI HUNIAN (2 CARDS DENGAN GAMBAR) ─── -->
        <div class="grid md:grid-cols-2 gap-8 reveal-el">
          <div 
            v-for="prop in properties" 
            :key="prop.id"
            @click="selectProperty(prop.id as any)"
            class="group relative rounded-2xl overflow-hidden border cursor-pointer transition-all duration-500 flex flex-col bg-white shadow-[0_4px_24px_rgba(0,0,0,0.04)]"
            :class="[
              selectedProperty === prop.id 
                ? 'border-kubu-red ring-2 ring-kubu-red/40 shadow-xl shadow-kubu-red/10 scale-[1.01]' 
                : 'border-[#E8E2D9] hover:border-[#C4B7A6] hover:shadow-lg'
            ]"
            @mouseenter="mouse.hover=true" @mouseleave="mouse.hover=false"
          >
            <!-- Property Cover Image -->
            <div class="relative h-64 sm:h-72 overflow-hidden bg-[#EFEBE4]">
              <img 
                :src="prop.image" 
                :alt="prop.name" 
                class="w-full h-full object-cover transition-transform duration-700 group-hover:scale-105"
              />
              <div class="absolute inset-0 bg-gradient-to-t from-black/50 via-transparent to-transparent"></div>

              <!-- Top Badges -->
              <div class="absolute top-4 left-4 flex items-center gap-2">
                <span class="px-3.5 py-1.5 rounded-full text-xs font-mono font-bold uppercase tracking-wider text-white shadow-sm" :class="prop.id === 'residence' ? 'bg-kubu-red' : 'bg-[#8E5B23]'">
                  {{ prop.category }}
                </span>
                <span class="px-3 py-1 bg-black/60 backdrop-blur-sm rounded-full text-[11px] font-mono text-white">
                  {{ prop.location }}
                </span>
              </div>

              <!-- Selected Active Indicator -->
              <div v-if="selectedProperty === prop.id" class="absolute top-4 right-4 px-3 py-1 bg-kubu-red text-white text-xs font-mono uppercase font-bold rounded-full flex items-center gap-1.5 shadow-md">
                <span>✓</span>
                <span>Hunian Terpilih</span>
              </div>
            </div>

            <!-- Property Card Body -->
            <div class="p-6 sm:p-8 bg-white flex-1 flex flex-col justify-between space-y-4">
              <div>
                <h3 class="font-display text-2xl sm:text-3xl text-[#1A1A1A] group-hover:text-kubu-red transition-colors mb-2">
                  {{ prop.name }}
                </h3>
                <p class="text-[#1A1A1A]/70 text-sm font-light leading-relaxed">
                  {{ prop.description }}
                </p>
              </div>

              <!-- Rental Note Pill -->
              <div class="pt-4 border-t border-[#E8E2D9] flex items-center justify-between">
                <span class="text-xs font-mono font-semibold" :class="prop.id === 'residence' ? 'text-kubu-red' : 'text-[#8E5B23]'">
                  ✦ {{ prop.rentalNote }}
                </span>
                <span class="text-xs font-mono uppercase tracking-widest text-[#1A1A1A]/50 group-hover:text-kubu-red transition-colors font-medium">
                  {{ selectedProperty === prop.id ? 'Sedang Ditampilkan ↓' : 'Klik Untuk Memilih →' }}
                </span>
              </div>
            </div>
          </div>
        </div>

        <!-- ─── DAFTAR TIPE KAMAR DARI HUNIAN TERPILIH (Hanya Muncul Jika Hunian Diklik) ─── -->
        <div v-if="selectedProperty" id="rooms-list-section" class="space-y-8 pt-8 border-t border-[#E8E2D9] mt-8">
          <div class="flex items-center justify-between border-b border-[#E8E2D9] pb-4">
            <div>
              <p class="text-xs font-mono text-kubu-red uppercase tracking-widest font-semibold">
                Daftar Tipe Kamar
              </p>
              <h3 class="font-display text-2xl sm:text-3xl text-[#1A1A1A] mt-1">
                Kamar di {{ selectedProperty === 'residence' ? 'Kubu Mawar Residence (Home Stay)' : 'Kubu Mawar Suwung (Kost-Kostan)' }}
              </h3>
            </div>
            <span class="text-xs font-mono text-[#1A1A1A]/60 px-3.5 py-1.5 bg-white rounded-full border border-[#E8E2D9] shadow-sm">
              {{ activeRooms.length }} Unit Tersedia
            </span>
          </div>

          <!-- Room Cards Grid -->
          <div class="grid md:grid-cols-2 lg:grid-cols-3 gap-8">
            <div 
              v-for="room in activeRooms" 
              :key="room.id"
              class="bg-white border border-[#E8E2D9] rounded-2xl overflow-hidden hover:border-kubu-red/50 hover:shadow-xl transition-all duration-300 flex flex-col justify-between group shadow-sm"
            >
              <div>
                <!-- Room Image -->
                <div class="relative h-56 overflow-hidden bg-[#EFEBE4]">
                  <img 
                    :src="room.image" 
                    :alt="room.name" 
                    class="w-full h-full object-cover transition-transform duration-500 group-hover:scale-105"
                  />
                  <div class="absolute inset-0 bg-gradient-to-t from-black/40 via-transparent to-transparent"></div>

                  <!-- Room Number & Status -->
                  <div class="absolute top-3 left-3 px-2.5 py-1 bg-white/95 backdrop-blur-sm rounded-md text-xs font-mono text-[#1A1A1A] font-semibold shadow-sm border border-[#E8E2D9]">
                    {{ room.room_number }}
                  </div>

                  <div class="absolute top-3 right-3">
                    <span 
                      v-if="room.status === 'available'"
                      class="px-2.5 py-1 bg-emerald-600 text-white text-[11px] font-mono font-bold rounded-md uppercase shadow-sm"
                    >
                      Tersedia
                    </span>
                    <span 
                      v-else
                      class="px-2.5 py-1 bg-rose-600 text-white text-[11px] font-mono font-bold rounded-md uppercase shadow-sm"
                    >
                      Terisi
                    </span>
                  </div>
                </div>

                <!-- Room Details -->
                <div class="p-6 space-y-4">
                  <div>
                    <span class="text-[11px] font-mono uppercase tracking-wider text-kubu-red font-semibold block mb-1">
                      {{ room.type_label }}
                    </span>
                    <h4 class="font-display text-xl text-[#1A1A1A] group-hover:text-kubu-red transition-colors">
                      {{ room.name }}
                    </h4>
                    <p class="text-[#1A1A1A]/70 text-xs font-light mt-2 line-clamp-2 leading-relaxed">
                      {{ room.description }}
                    </p>
                  </div>

                  <!-- FASILITAS LENGKAP DIJABARKAN -->
                  <div class="space-y-2 pt-3 border-t border-[#E8E2D9]">
                    <p class="text-[11px] font-mono uppercase tracking-wider text-[#1A1A1A]/60 font-semibold">
                      Fasilitas Kamar:
                    </p>
                    <ul class="space-y-1.5 text-xs text-[#1A1A1A]/80 font-light">
                      <li v-for="(feat, fi) in room.features" :key="fi" class="flex items-center gap-2">
                        <span class="text-kubu-red font-bold">✓</span>
                        <span>{{ feat }}</span>
                      </li>
                    </ul>
                  </div>
                </div>
              </div>

              <!-- PRICING & BOOKING ACTION -->
              <div class="p-6 pt-0 space-y-4">
                <div class="p-4 bg-[#FBF9F6] rounded-xl border border-[#E8E2D9] space-y-2">
                  <!-- IF RESIDENCE: JABARKAN BISA MINGGUAN BISA BULANAN -->
                  <template v-if="room.property_type === 'residence'">
                    <div class="flex items-center justify-between text-xs">
                      <span class="text-[#1A1A1A]/60 font-mono">Tarif Per Minggu:</span>
                      <span class="font-mono text-kubu-red font-bold text-sm">
                        {{ formatRupiah(room.price_weekly) }}
                      </span>
                    </div>
                    <div class="flex items-center justify-between text-xs pt-1.5 border-t border-[#E8E2D9]">
                      <span class="text-[#1A1A1A]/60 font-mono">Tarif Per Bulan:</span>
                      <span class="font-mono text-emerald-700 font-bold text-sm">
                        {{ formatRupiah(room.price_monthly) }}
                      </span>
                    </div>
                  </template>

                  <!-- IF SUWUNG: JABARKAN HANYA PER BULAN -->
                  <template v-else>
                    <div class="flex items-center justify-between text-xs">
                      <span class="text-[#8E5B23] font-mono uppercase text-[11px] font-semibold">
                        Khusus Per Bulan:
                      </span>
                      <span class="font-mono text-[#8E5B23] font-bold text-base">
                        {{ formatRupiah(room.price_monthly) }} <span class="text-xs text-[#1A1A1A]/50 font-normal">/ bln</span>
                      </span>
                    </div>
                    <p class="text-[10px] text-[#1A1A1A]/50 italic">
                      *Kubu Mawar Suwung hanya melayani sistem sewa per bulan.
                    </p>
                  </template>
                </div>

                <!-- Action Button -->
                <div>
                  <button 
                    v-if="room.status === 'available'"
                    @click="goToBooking(room.id)"
                    class="w-full py-3 bg-kubu-red hover:bg-kubu-red-dark text-white rounded-xl font-mono text-xs uppercase tracking-widest font-semibold transition-all cursor-pointer flex items-center justify-center gap-2 shadow-sm hover:shadow-md"
                  >
                    <span>Pesan Kamar Ini</span>
                    <span>→</span>
                  </button>
                  <button 
                    v-else
                    disabled
                    class="w-full py-3 bg-[#EFEBE4] text-[#1A1A1A]/40 rounded-xl font-mono text-xs uppercase tracking-widest cursor-not-allowed text-center border border-[#E8E2D9]"
                  >
                    Kamar Sedang Terisi
                  </button>
                </div>
              </div>

            </div>
          </div>
        </div>

      </div>
    </section>

    <!-- 4. Candi Bentar Contact Section (Original) -->
    <section id="contact" class="relative overflow-hidden min-h-screen flex flex-col pt-0 pb-6 bg-[#F7F5F0]">
      <!-- Ornamen Tradisional Kiri & Kanan (Menyambung penuh dari atas sampai footer, dengan efek soft blur & opacity agar tidak terlalu merah menyala) -->
      <img 
        src="/images/ornament-left.png" 
        class="absolute left-0 top-0 h-full w-14 sm:w-20 md:w-28 lg:w-auto max-w-[56px] sm:max-w-[80px] md:max-w-[120px] lg:max-w-[180px] object-cover lg:object-contain object-left pointer-events-none z-0 select-none" 
        style="filter: blur(1px); opacity: 0.75;"
        alt="Balinese Ornament Left" 
      />
      <img 
        src="/images/ornament-right.png" 
        class="absolute right-0 top-0 h-full w-14 sm:w-20 md:w-28 lg:w-auto max-w-[56px] sm:max-w-[80px] md:max-w-[120px] lg:max-w-[180px] object-cover lg:object-contain object-right pointer-events-none z-0 select-none" 
        style="filter: blur(1px); opacity: 0.75;"
        alt="Balinese Ornament Right" 
      />

      <!-- Contact Main Area -->
      <div class="relative z-10 flex-1 flex flex-col items-center justify-center w-full pt-16 md:pt-24 pb-4">
        <div class="flex-1 flex items-stretch justify-center w-full max-w-[1200px] mx-auto px-4">
          <div class="hidden md:flex items-stretch flex-shrink-0 w-1/4 max-w-[280px]">
            <img src="/images/gate-left.png" class="h-full w-full object-contain object-right" alt="Candi Bentar Left" />
          </div>

          <div class="flex-1 flex flex-col items-center justify-center px-8 sm:px-12 md:px-8">
            <h2 class="font-display text-4xl sm:text-5xl md:text-[5rem] lg:text-[7rem] leading-none mb-8 lg:mb-12 reveal-el text-center">
              Let's <i class="text-kubu-red">talk.</i>
            </h2>

            <div class="flex flex-col items-center gap-8 reveal-el" style="transition-delay: 150ms;">
              <button 
                @click="goToLogin"
                @mouseenter="mouse.hover=true" 
                @mouseleave="mouse.hover=false"
                class="group relative inline-flex items-center gap-4 px-8 sm:px-12 py-4 sm:py-5 border-2 border-kubu-black overflow-hidden cursor-pointer transition-all duration-500 hover:border-kubu-red"
              >
                <span class="absolute inset-0 bg-kubu-red transform transition-transform duration-500 ease-out translate-y-full group-hover:translate-y-0"></span>
                <span class="relative z-10 font-mono text-sm tracking-[0.3em] uppercase transition-colors duration-500 group-hover:text-white">Book Now</span>
                <span class="relative z-10 transition-all duration-500 group-hover:text-white group-hover:translate-x-1">→</span>
              </button>
            </div>
          </div>

          <div class="hidden md:flex items-stretch flex-shrink-0 w-1/4 max-w-[280px]">
            <img src="/images/gate-right.png" class="h-full w-full object-contain object-left" alt="Candi Bentar Right" />
          </div>
        </div>
      </div>

      <!-- Footer Area (100% Clean, No Ornaments) -->
      <footer class="w-full max-w-[1400px] mx-auto px-6 md:px-12 mt-4 relative z-10">
        <div class="border-t border-[#1A1A1A]/10 pt-6 flex flex-col md:flex-row justify-between items-center gap-4 reveal-el">
          <div class="flex items-center gap-3">
            <img src="/images/logo.png" class="w-8 h-8 object-contain" />
            <span class="font-display font-semibold text-sm">Kubu Mawar</span>
          </div>
          <div class="flex flex-wrap justify-center items-center gap-6 md:gap-8 font-mono text-xs tracking-widest uppercase">
            <a :href="`https://wa.me/${contactInfo.whatsapp.replace(/\D/g, '')}`" target="_blank" class="hover:text-kubu-red transition-colors cursor-pointer" @mouseenter="mouse.hover=true" @mouseleave="mouse.hover=false">
              WhatsApp
            </a>
            <a :href="`mailto:${contactInfo.email}`" class="hover:text-kubu-red transition-colors cursor-pointer" @mouseenter="mouse.hover=true" @mouseleave="mouse.hover=false">
              Email Us
            </a>
            <a href="https://instagram.com/kubumaawarproperti" target="_blank" class="hover:text-kubu-red transition-colors cursor-pointer" @mouseenter="mouse.hover=true" @mouseleave="mouse.hover=false">
              Instagram
            </a>
            <a href="https://maps.google.com/?q=Kubu+Mawar+Residence+Bali" target="_blank" class="hover:text-kubu-red transition-colors cursor-pointer" @mouseenter="mouse.hover=true" @mouseleave="mouse.hover=false">
              Location
            </a>
            <router-link to="/privacy" class="hover:text-kubu-red transition-colors cursor-pointer" @mouseenter="mouse.hover=true" @mouseleave="mouse.hover=false">
              Privacy Policy
            </router-link>
          </div>
          <p class="text-xs text-[#1A1A1A]/50 tracking-widest uppercase">
            &copy; {{ new Date().getFullYear() }} All Rights Reserved.
          </p>
        </div>
      </footer>
    </section>

  </div>
</template>

<style scoped>
@keyframes mascotFloat {
  0%, 100% { transform: translateY(0); }
  50% { transform: translateY(-12px); }
}
.mascot-float {
  animation: mascotFloat 4s ease-in-out infinite;
  transition: filter 0.3s ease, transform 0.3s ease;
}
.mascot-float:hover {
  filter: drop-shadow(0 20px 30px rgba(0,0,0,0.15));
  animation-play-state: paused;
  transform: scale(1.03);
}
</style>
