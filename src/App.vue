<script setup lang="ts">
import { ref, onMounted, onUnmounted, computed } from 'vue'

// ─── Mobile Nav ───
const mobileMenuOpen = ref(false)
const toggleMobileMenu = () => {
  mobileMenuOpen.value = !mobileMenuOpen.value
}

// ─── Sticky Header ───
const scrolled = ref(false)
const handleScroll = () => {
  scrolled.value = window.scrollY > 50
}

// ─── Hero Carousel ───
const currentSlide = ref(0)
const heroSlides = [
  {
    image: 'https://images.unsplash.com/photo-1537996194471-e657df975ab4?auto=format&fit=crop&w=1920&q=80',
    alt: 'Lush rice terraces of Bali at golden hour',
  },
  {
    image: 'https://images.unsplash.com/photo-1570789210967-2cac24afeb00?auto=format&fit=crop&w=1920&q=80',
    alt: 'Tropical Balinese villa pool with frangipani',
  },
  {
    image: 'https://images.unsplash.com/photo-1545389336-cf090694435e?auto=format&fit=crop&w=1920&q=80',
    alt: 'Traditional Balinese temple gate at sunrise',
  },
  {
    image: 'https://images.unsplash.com/photo-1573790387438-4da905039392?auto=format&fit=crop&w=1920&q=80',
    alt: 'Oceanfront sunset view from Bali clifftop',
  },
]

let carouselTimer: ReturnType<typeof setInterval> | null = null
const nextSlide = () => {
  currentSlide.value = (currentSlide.value + 1) % heroSlides.length
}
const goToSlide = (index: number) => {
  currentSlide.value = index
  resetCarouselTimer()
}
const resetCarouselTimer = () => {
  if (carouselTimer) clearInterval(carouselTimer)
  carouselTimer = setInterval(nextSlide, 5000)
}

// ─── Rooms ───
const rooms = [
  {
    name: 'Deluxe Garden Suite',
    image: 'https://images.unsplash.com/photo-1590490360182-c33d955e4c47?auto=format&fit=crop&w=800&q=80',
    description: 'Wake up to the symphony of tropical birdsong in our spacious Garden Suite. Featuring hand-carved teak furnishings, a private terrace overlooking our curated gardens, and a rain-shower bathroom adorned with natural stone.',
    priceDay: '$65',
    priceMonth: '$1,200',
  },
  {
    name: 'Premium Pool Villa',
    image: 'https://images.unsplash.com/photo-1582719478250-c89cae4dc85b?auto=format&fit=crop&w=800&q=80',
    description: 'Our signature villa experience with a private plunge pool, open-air living pavilion, and king-sized canopy bed draped in fine Balinese textiles. Perfect for honeymooners and those seeking an intimate tropical escape.',
    priceDay: 'IDR 300.000',
    priceMonth: 'IDR 6.500.000',
  },
  {
    name: 'Classic Comfort Room',
    image: 'https://images.unsplash.com/photo-1631049307264-da0ec9d70304?auto=format&fit=crop&w=800&q=80',
    description: 'Thoughtfully designed for the mindful traveler. Our Classic Comfort Room blends modern amenities with authentic Balinese charm handwoven rattan accents, crisp linens, and a cozy reading nook with garden views.',
    priceDay: '$40',
    priceMonth: '$750',
  },
]

// ─── Scroll-to-section ───
const scrollTo = (id: string) => {
  mobileMenuOpen.value = false
  const el = document.getElementById(id)
  if (el) {
    el.scrollIntoView({ behavior: 'smooth', block: 'start' })
  }
}

// ─── Intersection Observer for Animations ───
const observerCallback = (entries: IntersectionObserverEntry[]) => {
  entries.forEach((entry) => {
    if (entry.isIntersecting) {
      entry.target.classList.add('animate-visible')
    }
  })
}
let observer: IntersectionObserver | null = null

onMounted(() => {
  window.addEventListener('scroll', handleScroll)
  resetCarouselTimer()

  observer = new IntersectionObserver(observerCallback, {
    threshold: 0.15,
    rootMargin: '0px 0px -50px 0px',
  })
  document.querySelectorAll('.animate-on-scroll').forEach((el) => {
    observer?.observe(el)
  })
})

onUnmounted(() => {
  window.removeEventListener('scroll', handleScroll)
  if (carouselTimer) clearInterval(carouselTimer)
  observer?.disconnect()
})


const navLinks = [
  { label: 'Home', target: 'home' },
  { label: 'About', target: 'about' },
  { label: 'Room Types', target: 'rooms' },
  { label: 'Contact', target: 'contact' },
]


const contactInfo = {
  email: 'hello@kubumawar.com',
  instagram: '@kubumaawarproperti',
  whatsapp: '081991888008',
  mapsUrl: 'https://www.google.com/maps/place/Kubu+Mawar+Residence/@-8.7117138,115.187795,17z/data=!3m1!4b1!4m6!3m5!1s0x2dd2415f41cd6def:0xe0fa45086709b5f8!8m2!3d-8.7117191!4d115.1903699!16s%2Fg%2F11kl3gj2vc?entry=ttu&g_ep=EgoyMDI2MDkyMy4wIKXMDSoASAFQAw%3D%3D',
}

const currentYear = computed(() => new Date().getFullYear())
</script>

<template>
  <header
    :class="[
      'fixed top-0 left-0 right-0 z-50 transition-all duration-500',
      scrolled
        ? 'bg-white/95 backdrop-blur-md shadow-lg'
        : 'bg-white/80 backdrop-blur-sm',
    ]"
  >
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
      <div class="flex items-center justify-between h-20">
        <button
          @click="scrollTo('home')"
          class="flex items-center gap-2 group cursor-pointer"
        >
          <div class="w-10 h-10 bg-kubu-red rounded-lg flex items-center justify-center group-hover:scale-110 transition-transform duration-300">
            <svg class="w-6 h-6 text-white" viewBox="0 0 24 24" fill="currentColor">
              <path d="M12 2C9.38 2 7.25 4.13 7.25 6.75c0 1.85 1.06 3.45 2.6 4.23L8.5 22h7l-1.35-11.02c1.54-.78 2.6-2.38 2.6-4.23C16.75 4.13 14.62 2 12 2zm0 2.5c1.52 0 2.75 1.23 2.75 2.75S13.52 10 12 10 9.25 8.77 9.25 7.25 10.48 4.5 12 4.5z"/>
            </svg>
          </div>
          <div>
            <span class="text-xl font-display font-bold tracking-tight text-kubu-black">
              Kubu<span class="text-kubu-red">Mawar</span>Residence
            </span>
            <span class="block text-[10px] uppercase tracking-[0.25em] text-gray-400 -mt-1 font-body">
              Bali · Indonesia
            </span>
          </div>
        </button>

        <nav class="hidden md:flex items-center gap-1">
          <button
            v-for="link in navLinks"
            :key="link.target"
            @click="scrollTo(link.target)"
            class="px-4 py-2 text-sm font-medium text-gray-600 hover:text-kubu-red rounded-full hover:bg-red-50 transition-all duration-300 cursor-pointer"
          >
            {{ link.label }}
          </button>
          <button
            @click="scrollTo('contact')"
            class="ml-2 px-6 py-2.5 bg-kubu-red text-white text-sm font-semibold rounded-full hover:bg-kubu-red-dark transition-all duration-300 hover:shadow-lg hover:shadow-red-200 cursor-pointer"
          >
            Book Now
          </button>
        </nav>

        <!-- Mobile Toggle -->
        <button
          @click="toggleMobileMenu"
          class="md:hidden w-10 h-10 flex items-center justify-center rounded-lg hover:bg-gray-100 transition cursor-pointer"
          aria-label="Toggle menu"
        >
          <svg v-if="!mobileMenuOpen" class="w-6 h-6 text-kubu-black" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" d="M4 6h16M4 12h16M4 18h16"/>
          </svg>
          <svg v-else class="w-6 h-6 text-kubu-black" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12"/>
          </svg>
        </button>
      </div>
    </div>

    <!-- Mobile Menu -->
    <transition
      enter-active-class="transition duration-300 ease-out"
      enter-from-class="opacity-0 -translate-y-4"
      enter-to-class="opacity-100 translate-y-0"
      leave-active-class="transition duration-200 ease-in"
      leave-from-class="opacity-100 translate-y-0"
      leave-to-class="opacity-0 -translate-y-4"
    >
      <div v-if="mobileMenuOpen" class="md:hidden bg-white border-t border-gray-100 shadow-xl">
        <div class="px-6 py-4 space-y-1">
          <button
            v-for="link in navLinks"
            :key="link.target"
            @click="scrollTo(link.target)"
            class="block w-full text-left px-4 py-3 text-gray-700 hover:text-kubu-red hover:bg-red-50 rounded-lg transition font-medium cursor-pointer"
          >
            {{ link.label }}
          </button>
          <button
            @click="scrollTo('contact')"
            class="block w-full mt-2 px-4 py-3 bg-kubu-red text-white text-center rounded-lg font-semibold hover:bg-kubu-red-dark transition cursor-pointer"
          >
            Book Now
          </button>
        </div>
      </div>
    </transition>
  </header>

  <main>
    <!-- ═══════════════════════ HERO ═══════════════════════ -->
    <section id="home" class="relative h-screen overflow-hidden">
      <!-- Carousel Images -->
      <div class="absolute inset-0">
        <div
          v-for="(slide, index) in heroSlides"
          :key="index"
          :class="[
            'absolute inset-0 transition-all duration-[1200ms] ease-in-out',
            index === currentSlide ? 'opacity-100 scale-100' : 'opacity-0 scale-105',
          ]"
        >
          <img
            :src="slide.image"
            :alt="slide.alt"
            class="w-full h-full object-cover"
          />
        </div>
        <!-- Gradient Overlays -->
        <div class="absolute inset-0 bg-gradient-to-b from-black/50 via-black/30 to-black/70"></div>
      </div>

      <!-- Hero Content -->
      <div class="relative z-10 flex flex-col items-center justify-center h-full text-center px-4">
        <span class="inline-block px-5 py-1.5 border border-white/40 rounded-full text-white/90 text-xs sm:text-sm tracking-[0.3em] uppercase font-light mb-6 backdrop-blur-sm bg-white/5">
          Welcome to Paradise
        </span>
        <h1 class="font-display text-4xl sm:text-5xl md:text-7xl lg:text-8xl font-bold text-white leading-tight mb-6 max-w-5xl">
          Your <span class="text-kubu-red-light italic">Serene</span><br />
          <span class="text-kubu-red-light italic">Balinese</span> Escape
        </h1>
        <p class="text-white/80 text-base sm:text-lg md:text-xl max-w-2xl mb-10 font-light leading-relaxed">
          Nestled among lush tropical gardens and ancient temple pathways,
          Kubu Mawar Residence is your intimate gateway to the authentic soul of Bali.
        </p>
        <div class="flex flex-col sm:flex-row gap-4">
          <button
            @click="scrollTo('rooms')"
            class="px-8 py-4 bg-kubu-red text-white font-semibold rounded-full hover:bg-kubu-red-dark transition-all duration-300 hover:shadow-2xl hover:shadow-red-500/30 hover:-translate-y-0.5 cursor-pointer"
          >
            Explore Our Rooms
          </button>
          <button
            @click="scrollTo('about')"
            class="px-8 py-4 border-2 border-white/50 text-white font-semibold rounded-full hover:bg-white hover:text-kubu-black transition-all duration-300 hover:-translate-y-0.5 cursor-pointer"
          >
            Discover Our Story
          </button>
        </div>

        <!-- Carousel Dots -->
        <div class="absolute bottom-10 left-1/2 -translate-x-1/2 flex gap-3">
          <button
            v-for="(_, index) in heroSlides"
            :key="index"
            @click="goToSlide(index)"
            :class="[
              'transition-all duration-500 rounded-full cursor-pointer',
              index === currentSlide
                ? 'w-10 h-2.5 bg-kubu-red'
                : 'w-2.5 h-2.5 bg-white/50 hover:bg-white/80',
            ]"
            :aria-label="`Go to slide ${index + 1}`"
          />
        </div>
      </div>
    </section>

    <!-- ═══════════════════════ ABOUT ═══════════════════════ -->
    <section id="about" class="py-24 md:py-32 bg-white">
      <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        <!-- Section Header -->
        <div class="text-center mb-16 animate-on-scroll opacity-0 translate-y-8 transition-all duration-700">
          <span class="inline-block px-4 py-1 bg-red-50 text-kubu-red text-xs tracking-[0.25em] uppercase rounded-full font-semibold mb-4">
            Our Story
          </span>
          <h2 class="font-display text-3xl sm:text-4xl md:text-5xl font-bold text-kubu-black mb-6">
            Where Tradition Meets <span class="text-kubu-red italic">Tranquility</span>
          </h2>
          <div class="w-20 h-1 bg-kubu-red mx-auto rounded-full"></div>
        </div>

        <!-- History -->
        <div class="grid md:grid-cols-2 gap-12 lg:gap-20 items-center mb-24">
          <div class="animate-on-scroll opacity-0 translate-y-8 transition-all duration-700 relative">
            <img
              src="/images/kelingking-beach.jpg"
              alt="Pemandangan sawah terasering di dekat Kubu Mawar Residence"
              class="rounded-2xl shadow-2xl w-full object-cover aspect-[4/5]"
            />
          </div>
          <div class="animate-on-scroll opacity-0 translate-y-8 transition-all duration-700 delay-200">
            <h3 class="font-display text-2xl md:text-3xl font-bold text-kubu-black mb-6">
              Born from a Love for Bali
            </h3>
            <p class="text-gray-600 leading-relaxed mb-5">
              Kubu Mawar meaning "Rose Cottage" in Bahasa Indonesia was founded in 2018 by a family of
              passionate travelers who fell deeply in love with the Island of the Gods. What began as a single
              renovated traditional Balinese compound has blossomed into a curated collection of boutique
              accommodations, each one designed to immerse our guests in the genuine warmth and artistry of
              Balinese culture.
            </p>
            <p class="text-gray-600 leading-relaxed mb-5">
              Every corner of Kubu Mawar tells a story from the hand-carved stone entrance blessed by a
              local priest, to the frangipani trees that fill the morning air with their sweet perfume.
              We believe that a homestay should be more than just a place to sleep; it should be a doorway
              into the living, breathing heart of a community.
            </p>
            <p class="text-gray-600 leading-relaxed">
              Our staff are all local Balinese, many from the surrounding banjar (village community), who
              bring with them generations of hospitality traditions. When you stay at Kubu Mawar, you become
              part of our extended family welcomed with genuine smiles, offerings of freshly brewed Balinese
              coffee, and insider knowledge of the island's hidden treasures.
            </p>
          </div>
        </div>

        <!-- Color Philosophy + Location — Two Column -->
        <div class="grid md:grid-cols-2 gap-12 lg:gap-16">
          <!-- Color Philosophy -->
          <div class="animate-on-scroll opacity-0 translate-y-8 transition-all duration-700 bg-kubu-gray rounded-3xl p-8 md:p-10">
            <h3 class="font-display text-2xl font-bold text-kubu-black mb-8">
              The Colors of Kubu Mawar
            </h3>
            <p class="text-gray-600 leading-relaxed mb-8">
              Our brand identity draws from the Tri Hita Karana philosophy the Balinese principle of
              harmony between human, nature, and the divine. Each color in our palette carries deep meaning:
            </p>
            <div class="space-y-6">
              <!-- White -->
              <div class="flex items-start gap-4">
                <div class="flex-shrink-0 w-12 h-12 rounded-xl bg-white border-2 border-gray-200 shadow-sm flex items-center justify-center">
                  <svg class="w-5 h-5 text-gray-400" fill="currentColor" viewBox="0 0 20 20">
                    <path d="M10 2a8 8 0 100 16 8 8 0 000-16zm0 14.5a6.5 6.5 0 110-13 6.5 6.5 0 010 13z"/>
                  </svg>
                </div>
                <div>
                  <h4 class="font-semibold text-kubu-black text-lg mb-1">White — Purity & Peace</h4>
                  <p class="text-gray-500 text-sm leading-relaxed">
                    Representing the cleansing rituals of Balinese Hinduism and the blank canvas of a
                    new journey. White is the space where serenity lives the fresh linens, the morning
                    light, the calm that embraces you upon arrival.
                  </p>
                </div>
              </div>
              <!-- Red -->
              <div class="flex items-start gap-4">
                <div class="flex-shrink-0 w-12 h-12 rounded-xl bg-kubu-red shadow-sm flex items-center justify-center">
                  <svg class="w-5 h-5 text-white" fill="currentColor" viewBox="0 0 20 20">
                    <path fill-rule="evenodd" d="M3.172 5.172a4 4 0 015.656 0L10 6.343l1.172-1.171a4 4 0 115.656 5.656L10 17.657l-6.828-6.829a4 4 0 010-5.656z" clip-rule="evenodd"/>
                  </svg>
                </div>
                <div>
                  <h4 class="font-semibold text-kubu-black text-lg mb-1">Red — Passion & Energy</h4>
                  <p class="text-gray-500 text-sm leading-relaxed">
                    The red of our "Mawar" (rose) embodies the vibrancy of Balinese life the fire
                    ceremonies at dusk, the bold spices of local cuisine, and the passion we pour into
                    every detail of your stay. It is the heartbeat of Kubu Mawar.
                  </p>
                </div>
              </div>
              <!-- Black -->
              <div class="flex items-start gap-4">
                <div class="flex-shrink-0 w-12 h-12 rounded-xl bg-kubu-black shadow-sm flex items-center justify-center">
                  <svg class="w-5 h-5 text-white" fill="currentColor" viewBox="0 0 20 20">
                    <path d="M10.394 2.08a1 1 0 00-.788 0l-7 3a1 1 0 000 1.84L5.25 8.051a.999.999 0 01.356-.257l4-1.714a1 1 0 11.788 1.838L7.667 9.088l1.94.831a1 1 0 00.787 0l7-3a1 1 0 000-1.838l-7-3.001zM3.31 9.397L5 10.12v4.102a8.969 8.969 0 00-1.05-.174 1 1 0 01-.89-.89 11.115 11.115 0 01.25-3.762zM9.3 16.573A9.026 9.026 0 007 14.935v-3.957l1.818.78a3 3 0 002.364 0l5.508-2.361a11.026 11.026 0 01.25 3.762 1 1 0 01-.89.89 8.968 8.968 0 00-5.35 2.524 1 1 0 01-1.4 0z"/>
                  </svg>
                </div>
                <div>
                  <h4 class="font-semibold text-kubu-black text-lg mb-1">Black — Grounding & Tradition</h4>
                  <p class="text-gray-500 text-sm leading-relaxed">
                    Inspired by the volcanic black sand beaches and ancient lava stone carvings found
                    across Bali's sacred temples. Black grounds us in tradition, connecting the modern
                    comforts we offer to the timeless Balinese culture that inspires every design choice.
                  </p>
                </div>
              </div>
            </div>
          </div>

          <!-- Location -->
          <div class="animate-on-scroll opacity-0 translate-y-8 transition-all duration-700 delay-200 bg-kubu-black rounded-3xl p-8 md:p-10 text-white">
            <h3 class="font-display text-2xl font-bold mb-6">
              The Perfect Location
            </h3>
            <p class="text-gray-300 leading-relaxed mb-6">
              Kubu Mawar is strategically situated in the cultural heartland of Bali, offering the rare
              combination of peaceful seclusion and convenient access to the island's most celebrated
              attractions.
            </p>
            <ul class="space-y-4 mb-8">
              <li class="flex items-start gap-3">
                <span class="flex-shrink-0 mt-1 w-6 h-6 bg-kubu-red rounded-full flex items-center justify-center">
                  <svg class="w-3.5 h-3.5 text-white" fill="currentColor" viewBox="0 0 20 20">
                    <path fill-rule="evenodd" d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z" clip-rule="evenodd"/>
                  </svg>
                </span>
                <span class="text-gray-300 text-sm"><strong class="text-white">1 hour</strong> from Ubud's iconic Monkey Forest and Royal Palace</span>
              </li>
              <li class="flex items-start gap-3">
                <span class="flex-shrink-0 mt-1 w-6 h-6 bg-kubu-red rounded-full flex items-center justify-center">
                  <svg class="w-3.5 h-3.5 text-white" fill="currentColor" viewBox="0 0 20 20">
                    <path fill-rule="evenodd" d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z" clip-rule="evenodd"/>
                  </svg>
                </span>
                <span class="text-gray-300 text-sm"><strong class="text-white">50 minutes</strong> to the breathtaking Tegallalang Rice Terraces</span>
              </li>
              <li class="flex items-start gap-3">
                <span class="flex-shrink-0 mt-1 w-6 h-6 bg-kubu-red rounded-full flex items-center justify-center">
                  <svg class="w-3.5 h-3.5 text-white" fill="currentColor" viewBox="0 0 20 20">
                    <path fill-rule="evenodd" d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z" clip-rule="evenodd"/>
                  </svg>
                </span>
                <span class="text-gray-300 text-sm"><strong class="text-white">Walking distance</strong> to local warungs, art galleries, and yoga studios</span>
              </li>
              <li class="flex items-start gap-3">
                <span class="flex-shrink-0 mt-1 w-6 h-6 bg-kubu-red rounded-full flex items-center justify-center">
                  <svg class="w-3.5 h-3.5 text-white" fill="currentColor" viewBox="0 0 20 20">
                    <path fill-rule="evenodd" d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z" clip-rule="evenodd"/>
                  </svg>
                </span>
                <span class="text-gray-300 text-sm"><strong class="text-white">25 minutes</strong> from Ngurah Rai International Airport (free pickup available)</span>
              </li>
            </ul>
            <p class="text-gray-400 text-sm leading-relaxed">
              Surrounded by emerald rice paddies and ancient banyan trees, our location offers the serenity
              of rural Bali with none of the isolation. Whether you're here to explore, create, or simply
              breathe you'll find yourself exactly where you need to be.
            </p>
          </div>
        </div>
      </div>
    </section>

    <!-- ═══════════════════════ ROOMS ═══════════════════════ -->
    <section id="rooms" class="py-24 md:py-32 bg-kubu-gray">
      <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        <!-- Section Header -->
        <div class="text-center mb-16 animate-on-scroll opacity-0 translate-y-8 transition-all duration-700">
          <span class="inline-block px-4 py-1 bg-red-50 text-kubu-red text-xs tracking-[0.25em] uppercase rounded-full font-semibold mb-4">
            Accommodations
          </span>
          <h2 class="font-display text-3xl sm:text-4xl md:text-5xl font-bold text-kubu-black mb-6">
            Find Your Perfect <span class="text-kubu-red italic">Retreat</span>
          </h2>
          <p class="text-gray-500 max-w-2xl mx-auto">
            Each room at Kubu Mawar is a unique expression of Balinese craftsmanship, designed for comfort,
            beauty, and the kind of rest that restores your soul.
          </p>
          <div class="w-20 h-1 bg-kubu-red mx-auto rounded-full mt-6"></div>
        </div>

        <!-- Room Cards -->
        <div class="grid md:grid-cols-2 lg:grid-cols-3 gap-8">
          <div
            v-for="(room, index) in rooms"
            :key="room.name"
            class="animate-on-scroll opacity-0 translate-y-8 transition-all duration-700 group bg-white rounded-3xl overflow-hidden shadow-sm hover:shadow-2xl hover:-translate-y-2"
            :style="{ transitionDelay: `${index * 150}ms` }"
          >
            <!-- Room Image -->
            <div class="relative overflow-hidden aspect-[4/3]">
              <img
                :src="room.image"
                :alt="room.name"
                class="w-full h-full object-cover group-hover:scale-110 transition-transform duration-700"
              />
              <div class="absolute inset-0 bg-gradient-to-t from-black/40 to-transparent opacity-0 group-hover:opacity-100 transition-opacity duration-500"></div>
              <div class="absolute top-4 right-4 px-3 py-1 bg-white/90 backdrop-blur-sm rounded-full text-xs font-semibold text-kubu-red">
                Best Seller
              </div>
            </div>

            <!-- Room Info -->
            <div class="p-6 md:p-8">
              <h3 class="font-display text-xl md:text-2xl font-bold text-kubu-black mb-3 group-hover:text-kubu-red transition-colors duration-300">
                {{ room.name }}
              </h3>
              <p class="text-gray-500 text-sm leading-relaxed mb-6">
                {{ room.description }}
              </p>

              <!-- Pricing -->
              <div class="flex items-end justify-between pt-4 border-t border-gray-100">
                <div>
                  <span class="text-2xl font-bold text-kubu-black">{{ room.priceDay }}</span>
                  <span class="text-gray-400 text-sm"> / night</span>
                </div>
                <div class="text-right">
                  <span class="text-lg font-semibold text-gray-400">{{ room.priceMonth }}</span>
                  <span class="text-gray-400 text-sm"> / month</span>
                </div>
              </div>

              <!-- CTA -->
              <button
                @click="scrollTo('contact')"
                class="w-full mt-6 py-3.5 bg-kubu-black text-white font-semibold rounded-xl hover:bg-kubu-red transition-all duration-300 cursor-pointer"
              >
                Reserve This Room
              </button>
            </div>
          </div>
        </div>
      </div>
    </section>

    <!-- ═══════════════════════ CONTACT ═══════════════════════ -->
    <section id="contact" class="py-24 md:py-32 bg-white">
      <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        <!-- Section Header -->
        <div class="text-center mb-16 animate-on-scroll opacity-0 translate-y-8 transition-all duration-700">
          <span class="inline-block px-4 py-1 bg-red-50 text-kubu-red text-xs tracking-[0.25em] uppercase rounded-full font-semibold mb-4">
            Get In Touch
          </span>
          <h2 class="font-display text-3xl sm:text-4xl md:text-5xl font-bold text-kubu-black mb-6">
            We'd Love to <span class="text-kubu-red italic">Hear</span> from You
          </h2>
          <p class="text-gray-500 max-w-2xl mx-auto">
            Have questions about your upcoming Bali adventure? Reach out through any of the channels
            below our team typically responds within 2 hours.
          </p>
          <div class="w-20 h-1 bg-kubu-red mx-auto rounded-full mt-6"></div>
        </div>

        <!-- Contact Cards -->
        <div class="grid sm:grid-cols-2 lg:grid-cols-4 gap-6 mb-12">
          <!-- Email -->
          <a
            :href="`mailto:${contactInfo.email}`"
            class="animate-on-scroll opacity-0 translate-y-8 transition-all duration-700 group block bg-kubu-gray rounded-2xl p-6 text-center hover:bg-red-50 hover:shadow-lg hover:-translate-y-1"
          >
            <div class="w-14 h-14 mx-auto bg-white rounded-2xl shadow-sm flex items-center justify-center mb-4 group-hover:bg-kubu-red group-hover:shadow-red-200 transition-all duration-300">
              <svg class="w-6 h-6 text-kubu-red group-hover:text-white transition-colors duration-300" fill="none" stroke="currentColor" stroke-width="1.5" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" d="M21.75 6.75v10.5a2.25 2.25 0 01-2.25 2.25h-15a2.25 2.25 0 01-2.25-2.25V6.75m19.5 0A2.25 2.25 0 0019.5 4.5h-15a2.25 2.25 0 00-2.25 2.25m19.5 0v.243a2.25 2.25 0 01-1.07 1.916l-7.5 4.615a2.25 2.25 0 01-2.36 0L3.32 8.91a2.25 2.25 0 01-1.07-1.916V6.75"/>
              </svg>
            </div>
            <h4 class="font-semibold text-kubu-black mb-1">Email</h4>
            <p class="text-gray-500 text-sm">{{ contactInfo.email }}</p>
          </a>

          <!-- Instagram -->
          <a
            href="https://www.instagram.com/kubumaawarproperti?stkn=MTg2Z3h6b2k4emZzMw=="
            target="_blank"
            rel="noopener"
            class="animate-on-scroll opacity-0 translate-y-8 transition-all duration-700 delay-100 group block bg-kubu-gray rounded-2xl p-6 text-center hover:bg-red-50 hover:shadow-lg hover:-translate-y-1"
          >
            <div class="w-14 h-14 mx-auto bg-white rounded-2xl shadow-sm flex items-center justify-center mb-4 group-hover:bg-kubu-red group-hover:shadow-red-200 transition-all duration-300">
              <svg class="w-6 h-6 text-kubu-red group-hover:text-white transition-colors duration-300" fill="currentColor" viewBox="0 0 24 24">
                <path d="M12 2.163c3.204 0 3.584.012 4.85.07 3.252.148 4.771 1.691 4.919 4.919.058 1.265.069 1.645.069 4.849 0 3.205-.012 3.584-.069 4.849-.149 3.225-1.664 4.771-4.919 4.919-1.266.058-1.644.07-4.85.07-3.204 0-3.584-.012-4.849-.07-3.26-.149-4.771-1.699-4.919-4.92-.058-1.265-.07-1.644-.07-4.849 0-3.204.013-3.583.07-4.849.149-3.227 1.664-4.771 4.919-4.919 1.266-.057 1.645-.069 4.849-.069zM12 0C8.741 0 8.333.014 7.053.072 2.695.272.273 2.69.073 7.052.014 8.333 0 8.741 0 12c0 3.259.014 3.668.072 4.948.2 4.358 2.618 6.78 6.98 6.98C8.333 23.986 8.741 24 12 24c3.259 0 3.668-.014 4.948-.072 4.354-.2 6.782-2.618 6.979-6.98.059-1.28.073-1.689.073-4.948 0-3.259-.014-3.667-.072-4.947-.196-4.354-2.617-6.78-6.979-6.98C15.668.014 15.259 0 12 0zm0 5.838a6.162 6.162 0 100 12.324 6.162 6.162 0 000-12.324zM12 16a4 4 0 110-8 4 4 0 010 8zm6.406-11.845a1.44 1.44 0 100 2.881 1.44 1.44 0 000-2.881z"/>
              </svg>
            </div>
            <h4 class="font-semibold text-kubu-black mb-1">Instagram</h4>
            <p class="text-gray-500 text-sm">{{ contactInfo.instagram }}</p>
          </a>

          <!-- WhatsApp -->
          <a
            :href="`https://wa.me/${contactInfo.whatsapp.replace(/\D/g, '')}`"
            target="_blank"
            rel="noopener"
            class="animate-on-scroll opacity-0 translate-y-8 transition-all duration-700 delay-200 group block bg-kubu-gray rounded-2xl p-6 text-center hover:bg-red-50 hover:shadow-lg hover:-translate-y-1"
          >
            <div class="w-14 h-14 mx-auto bg-white rounded-2xl shadow-sm flex items-center justify-center mb-4 group-hover:bg-kubu-red group-hover:shadow-red-200 transition-all duration-300">
              <svg class="w-6 h-6 text-kubu-red group-hover:text-white transition-colors duration-300" fill="currentColor" viewBox="0 0 24 24">
                <path d="M17.472 14.382c-.297-.149-1.758-.867-2.03-.967-.273-.099-.471-.148-.67.15-.197.297-.767.966-.94 1.164-.173.199-.347.223-.644.075-.297-.15-1.255-.463-2.39-1.475-.883-.788-1.48-1.761-1.653-2.059-.173-.297-.018-.458.13-.606.134-.133.298-.347.446-.52.149-.174.198-.298.298-.497.099-.198.05-.371-.025-.52-.075-.149-.669-1.612-.916-2.207-.242-.579-.487-.5-.669-.51-.173-.008-.371-.01-.57-.01-.198 0-.52.074-.792.372-.272.297-1.04 1.016-1.04 2.479 0 1.462 1.065 2.875 1.213 3.074.149.198 2.096 3.2 5.077 4.487.709.306 1.262.489 1.694.625.712.227 1.36.195 1.871.118.571-.085 1.758-.719 2.006-1.413.248-.694.248-1.289.173-1.413-.074-.124-.272-.198-.57-.347m-5.421 7.403h-.004a9.87 9.87 0 01-5.031-1.378l-.361-.214-3.741.982.998-3.648-.235-.374a9.86 9.86 0 01-1.51-5.26c.001-5.45 4.436-9.884 9.888-9.884 2.64 0 5.122 1.03 6.988 2.898a9.825 9.825 0 012.893 6.994c-.003 5.45-4.437 9.884-9.885 9.884m8.413-18.297A11.815 11.815 0 0012.05 0C5.495 0 .16 5.335.157 11.892c0 2.096.547 4.142 1.588 5.945L.057 24l6.305-1.654a11.882 11.882 0 005.683 1.448h.005c6.554 0 11.89-5.335 11.893-11.893a11.821 11.821 0 00-3.48-8.413z"/>
              </svg>
            </div>
            <h4 class="font-semibold text-kubu-black mb-1">WhatsApp</h4>
            <p class="text-gray-500 text-sm">{{ contactInfo.whatsapp }}</p>
          </a>

          <!-- Map Toggle -->
          <a
            :href="contactInfo.mapsUrl"
            target="_blank"
            rel="noopener"
            class="animate-on-scroll opacity-0 translate-y-8 transition-all duration-700 delay-300 group block bg-kubu-red rounded-2xl p-6 text-center hover:bg-kubu-red-dark hover:shadow-lg hover:shadow-red-200 hover:-translate-y-1 transition-all duration-300"
          >
            <div class="w-14 h-14 mx-auto bg-white/20 rounded-2xl flex items-center justify-center mb-4 group-hover:bg-white/30 transition-all duration-300">
              <svg class="w-6 h-6 text-white" fill="none" stroke="currentColor" stroke-width="1.5" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" d="M15 10.5a3 3 0 11-6 0 3 3 0 016 0z"/>
                <path stroke-linecap="round" stroke-linejoin="round" d="M19.5 10.5c0 7.142-7.5 11.25-7.5 11.25S4.5 17.642 4.5 10.5a7.5 7.5 0 1115 0z"/>
              </svg>
            </div>
            <h4 class="font-semibold text-white mb-1">Find Us on Map</h4>
            <p class="text-white/70 text-sm">Open in Google Maps →</p>
          </a>
        </div>
      </div>
    </section>
  </main>

  <!-- ═══════════════════════ FOOTER ═══════════════════════ -->
  <footer class="bg-white border-t border-gray-100">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-12">
      <div class="flex flex-col md:flex-row items-center justify-between gap-8">
        <!-- Logo -->
        <div class="flex items-center gap-2">
          <div class="w-8 h-8 bg-kubu-red rounded-lg flex items-center justify-center">
            <svg class="w-5 h-5 text-white" viewBox="0 0 24 24" fill="currentColor">
              <path d="M12 2C9.38 2 7.25 4.13 7.25 6.75c0 1.85 1.06 3.45 2.6 4.23L8.5 22h7l-1.35-11.02c1.54-.78 2.6-2.38 2.6-4.23C16.75 4.13 14.62 2 12 2zm0 2.5c1.52 0 2.75 1.23 2.75 2.75S13.52 10 12 10 9.25 8.77 9.25 7.25 10.48 4.5 12 4.5z"/>
            </svg>
          </div>
          <span class="text-lg font-display font-bold text-kubu-black">
            Kubu<span class="text-kubu-red">Mawar</span>Residence
          </span>
        </div>

        <!-- Footer Nav -->
        <nav class="flex flex-wrap justify-center gap-6">
          <button
            v-for="link in navLinks"
            :key="link.target"
            @click="scrollTo(link.target)"
            class="text-sm text-gray-500 hover:text-kubu-red transition-colors duration-300 cursor-pointer"
          >
            {{ link.label }}
          </button>
        </nav>

        <!-- Copyright -->
        <p class="text-sm text-gray-400">
          &copy; {{ currentYear }} Kubu Mawar Residence. All rights reserved.
        </p>
      </div>
    </div>
  </footer>
</template>

<style>
/* Scroll-triggered animation helper */
.animate-on-scroll {
  transition-property: opacity, transform;
}
.animate-on-scroll.animate-visible {
  opacity: 1 !important;
  transform: translateY(0) !important;
}

/* Smooth scrolling */
html {
  scroll-behavior: smooth;
  scroll-padding-top: 5rem;
}

/* Hide scrollbar on carousel (optional utility) */
.no-scrollbar::-webkit-scrollbar {
  display: none;
}
.no-scrollbar {
  -ms-overflow-style: none;
  scrollbar-width: none;
}
</style>
