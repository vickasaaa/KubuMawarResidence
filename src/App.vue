<script setup lang="ts">
import { ref, onMounted, onUnmounted, computed } from 'vue'

// ─── State ───
const isScrolled = ref(false)
const scrollY = ref(0)
const mouse = ref({ x: 0, y: 0, hover: false })
const activeRoom = ref(0)
const selectedRoom = ref<number | null>(null)

const openRoomModal = (index: number) => {
  selectedRoom.value = index
  document.body.style.overflow = 'hidden'
}
const closeRoomModal = () => {
  selectedRoom.value = null
  document.body.style.overflow = ''
}

// ─── Data ───
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

const rooms = [
  {
    name: 'Deluxe Garden Suite',
    image: 'https://images.unsplash.com/photo-1590490360182-c33d955e4c47?auto=format&fit=crop&w=1200&q=80',
    description: 'A sanctuary of peace with hand-carved teak furnishings and a private terrace opening to curated tropical gardens.',
    price: 'IDR 300.000',
  },
  {
    name: 'Premium Pool Villa',
    image: 'https://images.unsplash.com/photo-1582719478250-c89cae4dc85b?auto=format&fit=crop&w=1200&q=80',
    description: 'Our signature experience featuring a private plunge pool, open-air living pavilion, and fine Balinese textiles.',
    price: 'IDR 500.000',
  },
  {
    name: 'Classic Comfort',
    image: 'https://images.unsplash.com/photo-1631049307264-da0ec9d70304?auto=format&fit=crop&w=1200&q=80',
    description: 'Thoughtfully designed for the mindful traveler, blending modern amenities with authentic handwoven rattan accents.',
    price: 'IDR 200.000',
  },
]

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

// Parallax computations
const heroParallax = computed(() => `translateY(${scrollY.value * 0.4}px) scale(${1 + scrollY.value * 0.0005})`)
</script>

<template>
  <div class="bg-[#F7F5F0] text-kubu-black selection:bg-kubu-red selection:text-white font-body min-h-screen overflow-x-hidden">
    
    <!-- Custom Cursor -->
    <div 
      class="fixed w-4 h-4 bg-kubu-red rounded-full pointer-events-none z-[9999] mix-blend-difference transition-transform duration-100 ease-out hidden lg:block"
      :style="{ transform: `translate3d(${mouse.x - 8}px, ${mouse.y - 8}px, 0) scale(${mouse.hover ? 3 : 1})` }"
    ></div>

    <!-- Navigation -->
    <nav :class="[
      'fixed w-full z-50 transition-all duration-700 px-6 md:px-12 py-6 flex justify-between items-center mix-blend-difference text-white',
      isScrolled ? 'py-4 bg-black/5 backdrop-blur-md' : ''
    ]">
      <div class="flex items-center gap-3 cursor-pointer" @click="scrollTo('home')" @mouseenter="mouse.hover=true" @mouseleave="mouse.hover=false">
        <img src="/images/logo.png" alt="Logo" class="w-10 h-10 object-contain filter brightness-0 invert" />
        <span class="font-display text-xl tracking-wide font-medium">Kubu Mawar Residence</span>
      </div>
      
      <div class="hidden md:flex gap-10 text-sm tracking-[0.15em] uppercase">
        <button @click="scrollTo('about')" class="hover:text-kubu-red-light transition-colors cursor-pointer" @mouseenter="mouse.hover=true" @mouseleave="mouse.hover=false">Story</button>
        <button @click="scrollTo('rooms')" class="hover:text-kubu-red-light transition-colors cursor-pointer" @mouseenter="mouse.hover=true" @mouseleave="mouse.hover=false">Rooms</button>
        <button @click="scrollTo('contact')" class="hover:text-kubu-red-light transition-colors cursor-pointer" @mouseenter="mouse.hover=true" @mouseleave="mouse.hover=false">Contact</button>
      </div>
    </nav>

    <!-- 1. Hero Section -->
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

    <!-- 2. Editorial About Section -->
    <section id="about" class="py-32 lg:py-48 px-6 md:px-12 max-w-[1400px] mx-auto">
      <div class="grid lg:grid-cols-12 gap-16 lg:gap-8 items-center">
        <!-- Text Content -->
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
            <a href="#contact" class="inline-flex items-center gap-4 text-sm uppercase tracking-[0.2em] font-semibold hover:text-kubu-red transition-colors group cursor-pointer" @mouseenter="mouse.hover=true" @mouseleave="mouse.hover=false">
              Discover Our Roots
              <span class="w-8 h-[1px] bg-currentColor group-hover:w-12 transition-all duration-300"></span>
            </a>
          </div>
        </div>

        <!-- Mascot Character -->
        <div class="lg:col-span-5 lg:col-start-8 relative flex items-center justify-center">
          <div class="relative reveal-el" style="transition-delay: 350ms;">
            <div 
              class="relative z-10 w-full max-w-[500px] lg:max-w-[800px] mx-auto mascot-float cursor-pointer group" 
              @mouseenter="mouse.hover=true" 
              @mouseleave="mouse.hover=false"
            >
              <!-- Character Image Default -->
              <img 
                src="/images/mask-mascot.png" 
                class="relative z-10 w-full h-auto object-contain drop-shadow-2xl transition-opacity duration-500 group-hover:opacity-0" 
                alt="Kubu Mawar Balinese Mask" 
              />
              <!-- Character Image Hover (Red) -->
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

    <!-- 3. Immersive Rooms Section -->
    <section id="rooms" class="py-32 bg-kubu-black text-white px-6 md:px-12">
      <div class="max-w-[1400px] mx-auto">
        <div class="flex flex-col md:flex-row justify-between items-end mb-24 reveal-el">
          <h2 class="font-display text-5xl lg:text-7xl">Our Spaces</h2>
          <p class="text-white/60 max-w-sm text-lg font-light mt-6 md:mt-0 pb-3">
            Intimate sanctuaries designed for deep rest and natural connection.
          </p>
        </div>

        <!-- Room Accordion/Stack -->
        <div class="border-t border-white/20">
          <div 
            v-for="(room, index) in rooms" :key="index"
            class="group border-b border-white/20 py-8 md:py-12 cursor-pointer relative overflow-hidden"
            @click="openRoomModal(index)"
            @mouseenter="activeRoom = index; mouse.hover=true"
            @mouseleave="mouse.hover=false"
          >
            <!-- Background Image Reveal on Hover -->
            <div 
              class="absolute inset-0 z-0 opacity-0 group-hover:opacity-30 transition-opacity duration-700 ease-out pointer-events-none"
            >
              <img :src="room.image" class="w-full h-full object-cover scale-110 group-hover:scale-100 transition-transform duration-[10s] ease-out" />
            </div>

            <!-- Content -->
            <div class="relative z-10 flex flex-col md:flex-row md:items-center justify-between gap-6 reveal-el" :style="{ transitionDelay: `${index * 100}ms` }">
              <div class="flex items-center gap-8 md:gap-16">
                <span class="font-mono text-white/40 text-sm hidden md:block">0{{ index + 1 }}</span>
                <h3 class="font-display text-3xl md:text-5xl group-hover:translate-x-4 transition-transform duration-500">{{ room.name }}</h3>
              </div>
              <div class="flex flex-col md:items-end gap-2 group-hover:-translate-x-4 transition-transform duration-500">
                <p class="font-mono text-sm tracking-widest text-kubu-red-light">{{ room.price }} <span class="text-white/50 text-xs">/ NIGHT</span></p>
                <p class="text-white/60 max-w-xs md:text-right text-sm font-light leading-relaxed hidden md:block opacity-0 group-hover:opacity-100 transition-opacity duration-500 delay-100">
                  {{ room.description }}
                </p>
              </div>
            </div>
          </div>
        </div>
      </div>
    </section>

    <!-- 4. Candi Bentar Contact Section -->
    <section id="contact" class="relative overflow-hidden min-h-screen flex flex-col pt-24 pb-6 bg-[#F7F5F0]">
      <!-- Cloud Ornaments (subtle background) -->
      <img src="/images/ornament-left.png" class="absolute left-0 top-0 h-full w-auto object-contain object-left pointer-events-none opacity-20 hidden lg:block" alt="" />
      <img src="/images/ornament-right.png" class="absolute right-0 top-0 h-full w-auto object-contain object-right pointer-events-none opacity-20 hidden lg:block" alt="" />

      <!-- Gate Container -->
      <div class="relative z-10 flex-1 flex items-stretch justify-center w-full max-w-[1200px] mx-auto px-4">
        <!-- Left Gate Pillar -->
        <div class="hidden md:flex items-stretch flex-shrink-0 w-1/4 max-w-[280px]">
          <img src="/images/gate-left.png" class="h-full w-full object-contain object-right" alt="Candi Bentar Left" />
        </div>

        <!-- Inner Content (inside the gate) -->
        <div class="flex-1 flex flex-col items-center justify-center px-4 md:px-8">
          <!-- Let's Talk -->
          <h2 class="font-display text-5xl md:text-[5rem] lg:text-[7rem] leading-none mb-8 lg:mb-12 reveal-el text-center">
            Let's <i class="text-kubu-red">talk.</i>
          </h2>

          <!-- Contact Links -->
          <div class="flex flex-col md:flex-row justify-center items-center gap-6 md:gap-10 font-mono text-xs lg:text-sm tracking-widest uppercase reveal-el" style="transition-delay: 150ms;">
            <a :href="`https://wa.me/${contactInfo.whatsapp.replace(/\D/g, '')}`" class="hover:text-kubu-red transition-colors relative after:content-[''] after:absolute after:bottom-[-4px] after:left-0 after:w-0 after:h-[1px] after:bg-kubu-red hover:after:w-full after:transition-all after:duration-300 cursor-pointer" @mouseenter="mouse.hover=true" @mouseleave="mouse.hover=false">
              WhatsApp
            </a>
            <a :href="`mailto:${contactInfo.email}`" class="hover:text-kubu-red transition-colors relative after:content-[''] after:absolute after:bottom-[-4px] after:left-0 after:w-0 after:h-[1px] after:bg-kubu-red hover:after:w-full after:transition-all after:duration-300 cursor-pointer" @mouseenter="mouse.hover=true" @mouseleave="mouse.hover=false">
              Email Us
            </a>
            <a href="https://instagram.com/kubumaawarproperti" target="_blank" class="hover:text-kubu-red transition-colors relative after:content-[''] after:absolute after:bottom-[-4px] after:left-0 after:w-0 after:h-[1px] after:bg-kubu-red hover:after:w-full after:transition-all after:duration-300 cursor-pointer" @mouseenter="mouse.hover=true" @mouseleave="mouse.hover=false">
              Instagram
            </a>
            <a href="https://maps.google.com/?q=Kubu+Mawar+Residence+Bali" target="_blank" class="hover:text-kubu-red transition-colors relative after:content-[''] after:absolute after:bottom-[-4px] after:left-0 after:w-0 after:h-[1px] after:bg-kubu-red hover:after:w-full after:transition-all after:duration-300 cursor-pointer" @mouseenter="mouse.hover=true" @mouseleave="mouse.hover=false">
              Location
            </a>
          </div>
        </div>

        <!-- Right Gate Pillar -->
        <div class="hidden md:flex items-stretch flex-shrink-0 w-1/4 max-w-[280px]">
          <img src="/images/gate-right.png" class="h-full w-full object-contain object-left" alt="Candi Bentar Right" />
        </div>
      </div>

      <!-- Footer (outside gate) -->
      <div class="w-full max-w-[1400px] mx-auto px-6 md:px-12 mt-4 relative z-10">
        <div class="border-t border-[#1A1A1A]/10 pt-6 flex flex-col md:flex-row justify-between items-center gap-4 reveal-el">
          <div class="flex items-center gap-3">
            <img src="/images/logo.png" class="w-8 h-8 object-contain" />
            <span class="font-display font-semibold text-sm">Kubu Mawar Residence</span>
          </div>
          <p class="text-xs text-[#1A1A1A]/50 tracking-widest uppercase">
            &copy; {{ new Date().getFullYear() }} All Rights Reserved.
          </p>
        </div>
      </div>
    </section>

    <!-- Room Modal Popup -->
    <Transition name="modal-fade">
      <div v-if="selectedRoom !== null" class="fixed inset-0 z-[100] flex items-center justify-center bg-kubu-black text-white">
        <!-- Close Button -->
        <button 
          @click="closeRoomModal"
          class="absolute top-8 left-8 md:top-12 md:left-12 z-[110] flex items-center gap-4 group cursor-pointer"
          @mouseenter="mouse.hover=true" 
          @mouseleave="mouse.hover=false"
        >
          <div class="relative w-10 h-10 flex items-center justify-center rounded-full border border-white/30 group-hover:bg-white group-hover:text-black transition-colors duration-300">
            <span class="text-lg">✕</span>
          </div>
          <span class="font-mono text-sm tracking-widest uppercase opacity-0 group-hover:opacity-100 transition-opacity duration-300 translate-x-[-10px] group-hover:translate-x-0 hidden md:block">Close</span>
        </button>

        <!-- Image Background -->
        <div class="absolute inset-0 z-0">
          <img :src="rooms[selectedRoom].image" class="w-full h-full object-cover opacity-40" />
          <div class="absolute inset-0 bg-gradient-to-t from-kubu-black via-kubu-black/80 to-transparent"></div>
        </div>

        <!-- Content -->
        <div class="relative z-10 px-6 md:px-12 w-full max-w-[1200px] mx-auto flex flex-col md:flex-row items-end justify-between gap-12 pt-32">
          <div class="max-w-2xl">
            <span class="font-mono text-kubu-red-light text-sm tracking-widest block mb-4">0{{ selectedRoom + 1 }}</span>
            <h2 class="font-display text-5xl md:text-7xl lg:text-[6rem] leading-none mb-8">{{ rooms[selectedRoom].name }}</h2>
            <p class="text-white/80 text-lg md:text-xl font-light leading-relaxed max-w-xl">
              {{ rooms[selectedRoom].description }}
            </p>
          </div>
          <div class="flex flex-col gap-2">
            <span class="text-white/50 text-xs tracking-widest uppercase font-mono">Starting from</span>
            <span class="font-display text-3xl md:text-4xl text-kubu-red-light">{{ rooms[selectedRoom].price }}</span>
            <span class="text-white/50 text-xs tracking-widest uppercase font-mono mt-1">Per Night</span>
          </div>
        </div>
      </div>
    </Transition>

  </div>
</template>

<style>
/* Room Modal Transition */
.modal-fade-enter-active,
.modal-fade-leave-active {
  transition: opacity 0.5s ease;
}
.modal-fade-enter-from,
.modal-fade-leave-to {
  opacity: 0;
}

/* Smooth Scrolling */
html {
  scroll-behavior: smooth;
}

/* Custom Cursor Hide Native */
@media (min-width: 1024px) {
  body {
    cursor: none;
  }
  a, button {
    cursor: none;
  }
}

/* Scroll Reveal Animations */
.reveal-el {
  opacity: 0;
  transform: translateY(40px);
  transition: all 1s cubic-bezier(0.19, 1, 0.22, 1);
}
.reveal-el.is-revealed {
  opacity: 1;
  transform: translateY(0);
}

/* Image Reveal Clip Path */
.reveal-image-container {
  clip-path: polygon(0 100%, 100% 100%, 100% 100%, 0 100%);
  transition: clip-path 1.5s cubic-bezier(0.19, 1, 0.22, 1);
}
.reveal-image-container.is-revealed {
  clip-path: polygon(0 0, 100% 0, 100% 100%, 0 100%);
}
.reveal-image {
  transform: scale(1.2);
  transition: transform 2s cubic-bezier(0.19, 1, 0.22, 1);
}
.reveal-image-container.is-revealed .reveal-image {
  transform: scale(1);
}

/* Mascot Floating Animation */
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
