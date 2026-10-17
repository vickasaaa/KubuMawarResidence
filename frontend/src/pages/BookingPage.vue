<script setup lang="ts">
import { ref, computed, onMounted, onUnmounted, watch, nextTick } from 'vue'
import { useRouter } from 'vue-router'

const router = useRouter()

// ─── Mouse (custom cursor) ───
const mouse = ref({ x: 0, y: 0, hover: false })
const handleMouseMove = (e: MouseEvent) => {
  mouse.value.x = e.clientX
  mouse.value.y = e.clientY
}

// ─── Room Data ───
const rooms = [
  {
    id: 1,
    name: 'Exclusive Suite',
    image: 'https://images.unsplash.com/photo-1590490360182-c33d955e4c47?auto=format&fit=crop&w=1200&q=80',
    description: 'Suite mewah dengan interior premium, king bed canopy, teras pribadi menghadap taman tropis, dan layanan eksklusif.',
    pricePerNight: 500000,
    priceFormatted: 'IDR 500.000',
    maxGuests: 2,
    features: ['King Bed Canopy', 'Teras Taman Pribadi', 'Smart TV 55"', 'AC & Water Heater', 'Butler Service'],
  },
  {
    id: 2,
    name: 'Mediteranian Suite',
    image: 'https://images.unsplash.com/photo-1631049307264-da0ec9d70304?auto=format&fit=crop&w=1200&q=80',
    description: 'Desain mediterania klasik dengan aksen batu alam, rotan, dan pencahayaan hangat. Nyaman dan estetik.',
    pricePerNight: 300000,
    priceFormatted: 'IDR 300.000',
    maxGuests: 2,
    features: ['Queen Bed Nyaman', 'Balkon Asri', 'Meja Kerja', 'AC & Water Heater', 'Smart TV 42"'],
  },
]

// ─── Booking Form State ───
const currentStep = ref(1)
const totalSteps = 3

const selectedRoomId = ref<number | null>(null)
const checkIn = ref('')
const checkOut = ref('')
const numGuests = ref(1)
const guestName = ref('')
const guestEmail = ref('')
const guestPhone = ref('')
const specialRequests = ref('')
const isSubmitting = ref(false)
const isSubmitted = ref(false)

// ─── Computed ───
const selectedRoom = computed(() => rooms.find(r => r.id === selectedRoomId.value))

const numberOfNights = computed(() => {
  if (!checkIn.value || !checkOut.value) return 0
  const start = new Date(checkIn.value)
  const end = new Date(checkOut.value)
  const diff = Math.ceil((end.getTime() - start.getTime()) / (1000 * 60 * 60 * 24))
  return diff > 0 ? diff : 0
})

const totalPrice = computed(() => {
  if (!selectedRoom.value || numberOfNights.value <= 0) return 0
  return selectedRoom.value.pricePerNight * numberOfNights.value
})

const formattedTotal = computed(() => {
  return 'IDR ' + totalPrice.value.toLocaleString('id-ID')
})

const todayDate = computed(() => {
  const d = new Date()
  return d.toISOString().split('T')[0]
})

const minCheckOut = computed(() => {
  if (!checkIn.value) return todayDate.value
  const d = new Date(checkIn.value)
  d.setDate(d.getDate() + 1)
  return d.toISOString().split('T')[0]
})

const canProceedStep1 = computed(() => selectedRoomId.value !== null)
const canProceedStep2 = computed(() => checkIn.value && checkOut.value && numberOfNights.value > 0 && numGuests.value > 0)
const canProceedStep3 = computed(() => guestName.value.trim() && guestEmail.value.trim() && guestPhone.value.trim())

// ─── Methods ───
const selectRoom = (id: number) => {
  selectedRoomId.value = id
}

const nextStep = () => {
  if (currentStep.value < totalSteps) {
    currentStep.value++
  }
}

const prevStep = () => {
  if (currentStep.value > 1) {
    currentStep.value--
  }
}

const submitBooking = async () => {
  isSubmitting.value = true
  
  // Simulate API call — will be replaced with real Laravel API later
  await new Promise(resolve => setTimeout(resolve, 2000))
  
  isSubmitted.value = true
  isSubmitting.value = false
}

const goHome = () => {
  router.push('/')
}

// Reset checkOut when checkIn changes
watch(checkIn, () => {
  if (checkOut.value && checkOut.value <= checkIn.value) {
    checkOut.value = ''
  }
})

// ─── Intersection Observer ───
let observer: IntersectionObserver
onMounted(() => {
  window.addEventListener('mousemove', handleMouseMove)
  
  observer = new IntersectionObserver((entries) => {
    entries.forEach(entry => {
      if (entry.isIntersecting) {
        entry.target.classList.add('is-revealed')
      }
    })
  }, { threshold: 0.1 })

  nextTick(() => {
    document.querySelectorAll('.reveal-el').forEach(el => observer.observe(el))
  })
})

onUnmounted(() => {
  window.removeEventListener('mousemove', handleMouseMove)
  observer?.disconnect()
})

// Re-observe when step changes
watch(currentStep, () => {
  nextTick(() => {
    document.querySelectorAll('.reveal-el:not(.is-revealed)').forEach(el => observer?.observe(el))
  })
})
</script>

<template>
  <div class="bg-[#F7F5F0] text-kubu-black selection:bg-kubu-red selection:text-white font-body min-h-screen overflow-x-hidden">
    
    <!-- Custom Cursor -->
    <div 
      class="fixed w-4 h-4 bg-kubu-red rounded-full pointer-events-none z-[9999] mix-blend-difference transition-transform duration-100 ease-out hidden lg:block"
      :style="{ transform: `translate3d(${mouse.x - 8}px, ${mouse.y - 8}px, 0) scale(${mouse.hover ? 3 : 1})` }"
    ></div>

    <!-- Top Navigation Bar -->
    <nav class="fixed w-full z-50 transition-all duration-700 px-6 md:px-12 py-6 flex justify-between items-center bg-[#F7F5F0]/90 backdrop-blur-md border-b border-kubu-black/5">
      <div class="flex items-center gap-3 cursor-pointer" @click="goHome" @mouseenter="mouse.hover=true" @mouseleave="mouse.hover=false">
        <img src="/images/logo.png" alt="Logo" class="w-10 h-10 object-contain" />
        <span class="font-display text-xl tracking-wide font-medium">Kubu Mawar</span>
      </div>
      
      <div class="flex items-center gap-6">
        <button 
          @click="goHome" 
          class="hidden md:flex items-center gap-2 font-mono text-xs tracking-widest uppercase hover:text-kubu-red transition-colors cursor-pointer"
          @mouseenter="mouse.hover=true" @mouseleave="mouse.hover=false"
        >
          <span>←</span> Back to Home
        </button>
      </div>
    </nav>

    <!-- Main Content -->
    <div class="pt-28 pb-16 px-4 md:px-8 lg:px-12 max-w-[1400px] mx-auto">
      
      <!-- Page Header -->
      <div class="text-center mb-16 reveal-el">
        <p class="text-kubu-red uppercase tracking-[0.3em] text-xs font-bold mb-4">Reservation</p>
        <h1 class="font-display text-4xl md:text-6xl lg:text-7xl leading-[1.1]">
          Book Your <i class="text-kubu-red">Stay</i>
        </h1>
        <p class="text-[#888] text-lg font-light mt-6 max-w-lg mx-auto">
          Select your perfect sanctuary and let us prepare everything for your arrival.
        </p>
      </div>

      <!-- Step Indicator -->
      <div v-if="!isSubmitted" class="flex items-center justify-center gap-4 mb-16 reveal-el" style="transition-delay: 100ms;">
        <template v-for="step in totalSteps" :key="step">
          <div class="flex items-center gap-3">
            <div 
              class="w-10 h-10 rounded-full flex items-center justify-center font-mono text-sm transition-all duration-500 border-2"
              :class="currentStep >= step 
                ? 'bg-kubu-red border-kubu-red text-white' 
                : 'border-[#1A1A1A]/20 text-[#1A1A1A]/40'"
            >
              {{ step }}
            </div>
            <span 
              class="font-mono text-xs tracking-widest uppercase hidden md:block transition-colors duration-300"
              :class="currentStep >= step ? 'text-kubu-black' : 'text-[#1A1A1A]/30'"
            >
              {{ step === 1 ? 'Select Room' : step === 2 ? 'Choose Dates' : 'Guest Details' }}
            </span>
          </div>
          <div 
            v-if="step < totalSteps" 
            class="w-12 lg:w-20 h-[2px] transition-colors duration-500"
            :class="currentStep > step ? 'bg-kubu-red' : 'bg-[#1A1A1A]/10'"
          ></div>
        </template>
      </div>

      <!-- ═══════════ STEP 1: Room Selection ═══════════ -->
      <Transition name="step-slide" mode="out-in">
        <div v-if="currentStep === 1 && !isSubmitted" key="step1">
          <div class="grid md:grid-cols-3 gap-6 lg:gap-8">
            <div 
              v-for="room in rooms" :key="room.id"
              class="group relative overflow-hidden cursor-pointer transition-all duration-500 reveal-el"
              :class="selectedRoomId === room.id 
                ? 'ring-2 ring-kubu-red shadow-xl shadow-kubu-red/10' 
                : 'hover:shadow-lg'"
              :style="{ transitionDelay: `${(room.id - 1) * 100}ms` }"
              @click="selectRoom(room.id)"
              @mouseenter="mouse.hover=true"
              @mouseleave="mouse.hover=false"
            >
              <!-- Image -->
              <div class="relative h-64 lg:h-72 overflow-hidden">
                <img 
                  :src="room.image" 
                  :alt="room.name" 
                  class="w-full h-full object-cover transition-transform duration-700 group-hover:scale-110"
                />
                <div class="absolute inset-0 bg-gradient-to-t from-black/60 via-transparent to-transparent"></div>
                
                <!-- Selected Badge -->
                <Transition name="badge-pop">
                  <div v-if="selectedRoomId === room.id" class="absolute top-4 right-4 bg-kubu-red text-white w-8 h-8 rounded-full flex items-center justify-center">
                    <span class="text-sm">✓</span>
                  </div>
                </Transition>

                <!-- Price Tag -->
                <div class="absolute bottom-4 left-4">
                  <span class="font-mono text-sm text-white tracking-widest">{{ room.priceFormatted }}</span>
                  <span class="text-white/60 text-xs ml-1">/ night</span>
                </div>
              </div>

              <!-- Info -->
              <div class="p-6 bg-white">
                <h3 class="font-display text-xl lg:text-2xl mb-2">{{ room.name }}</h3>
                <p class="text-[#888] text-sm font-light leading-relaxed mb-4">{{ room.description }}</p>
                
                <!-- Features -->
                <div class="flex flex-wrap gap-2">
                  <span 
                    v-for="feat in room.features" :key="feat"
                    class="px-3 py-1 text-xs font-mono tracking-wider uppercase border border-[#1A1A1A]/10 text-[#888]"
                  >
                    {{ feat }}
                  </span>
                </div>
                
                <div class="mt-4 pt-4 border-t border-[#1A1A1A]/5 flex items-center justify-between">
                  <span class="text-xs font-mono tracking-wider text-[#888] uppercase">Max {{ room.maxGuests }} guests</span>
                  <span 
                    class="text-xs font-mono tracking-wider uppercase transition-colors"
                    :class="selectedRoomId === room.id ? 'text-kubu-red font-bold' : 'text-[#888]'"
                  >
                    {{ selectedRoomId === room.id ? 'Selected' : 'Select' }}
                  </span>
                </div>
              </div>
            </div>
          </div>

          <!-- Next Button -->
          <div class="flex justify-center mt-12 reveal-el" style="transition-delay: 400ms;">
            <button 
              @click="nextStep"
              :disabled="!canProceedStep1"
              class="group relative inline-flex items-center gap-4 px-12 py-5 border-2 overflow-hidden cursor-pointer transition-all duration-500"
              :class="canProceedStep1 
                ? 'border-kubu-black hover:border-kubu-red' 
                : 'border-[#1A1A1A]/20 opacity-50 cursor-not-allowed'"
              @mouseenter="mouse.hover=true" @mouseleave="mouse.hover=false"
            >
              <span v-if="canProceedStep1" class="absolute inset-0 bg-kubu-red transform transition-transform duration-500 ease-out translate-y-full group-hover:translate-y-0"></span>
              <span class="relative z-10 font-mono text-sm tracking-[0.3em] uppercase transition-colors duration-500" :class="canProceedStep1 ? 'group-hover:text-white' : ''">Continue</span>
              <span class="relative z-10 transition-all duration-500" :class="canProceedStep1 ? 'group-hover:text-white group-hover:translate-x-1' : ''">→</span>
            </button>
          </div>
        </div>
      </Transition>

      <!-- ═══════════ STEP 2: Date & Guests ═══════════ -->
      <Transition name="step-slide" mode="out-in">
        <div v-if="currentStep === 2 && !isSubmitted" key="step2">
          <div class="max-w-2xl mx-auto">
            <!-- Selected Room Summary -->
            <div v-if="selectedRoom" class="flex items-center gap-6 p-6 bg-white border border-[#1A1A1A]/5 mb-10 reveal-el">
              <img :src="selectedRoom.image" class="w-20 h-20 object-cover flex-shrink-0" />
              <div>
                <h3 class="font-display text-xl">{{ selectedRoom.name }}</h3>
                <p class="font-mono text-sm text-kubu-red tracking-widest">{{ selectedRoom.priceFormatted }} <span class="text-[#888]">/ night</span></p>
              </div>
              <button 
                @click="prevStep" 
                class="ml-auto text-xs font-mono tracking-widest uppercase text-[#888] hover:text-kubu-red transition-colors cursor-pointer"
                @mouseenter="mouse.hover=true" @mouseleave="mouse.hover=false"
              >
                Change
              </button>
            </div>

            <!-- Date Selection -->
            <div class="bg-white border border-[#1A1A1A]/5 p-8 md:p-10 space-y-8 reveal-el" style="transition-delay: 100ms;">
              <h3 class="font-display text-2xl">Choose Your <i class="text-kubu-red">Dates</i></h3>
              
              <div class="grid md:grid-cols-2 gap-6">
                <div class="space-y-2">
                  <label class="font-mono text-xs tracking-widest uppercase text-[#888] block">Check-in</label>
                  <input 
                    v-model="checkIn"
                    type="date" 
                    :min="todayDate"
                    class="w-full px-4 py-4 border border-[#1A1A1A]/10 bg-transparent font-body text-kubu-black focus:border-kubu-red focus:outline-none transition-colors"
                  />
                </div>
                <div class="space-y-2">
                  <label class="font-mono text-xs tracking-widest uppercase text-[#888] block">Check-out</label>
                  <input 
                    v-model="checkOut"
                    type="date" 
                    :min="minCheckOut"
                    class="w-full px-4 py-4 border border-[#1A1A1A]/10 bg-transparent font-body text-kubu-black focus:border-kubu-red focus:outline-none transition-colors"
                  />
                </div>
              </div>

              <div class="space-y-2">
                <label class="font-mono text-xs tracking-widest uppercase text-[#888] block">Number of Guests</label>
                <div class="flex items-center gap-4">
                  <button 
                    @click="numGuests = Math.max(1, numGuests - 1)"
                    class="w-10 h-10 border border-[#1A1A1A]/10 flex items-center justify-center hover:border-kubu-red hover:text-kubu-red transition-colors cursor-pointer"
                    @mouseenter="mouse.hover=true" @mouseleave="mouse.hover=false"
                  >−</button>
                  <span class="font-mono text-xl w-8 text-center">{{ numGuests }}</span>
                  <button 
                    @click="numGuests = Math.min(selectedRoom?.maxGuests || 4, numGuests + 1)"
                    class="w-10 h-10 border border-[#1A1A1A]/10 flex items-center justify-center hover:border-kubu-red hover:text-kubu-red transition-colors cursor-pointer"
                    @mouseenter="mouse.hover=true" @mouseleave="mouse.hover=false"
                  >+</button>
                  <span class="text-xs text-[#888] font-mono">max {{ selectedRoom?.maxGuests || 4 }}</span>
                </div>
              </div>

              <!-- Price Summary -->
              <Transition name="fade-up">
                <div v-if="numberOfNights > 0" class="border-t border-[#1A1A1A]/5 pt-6 space-y-3">
                  <div class="flex justify-between items-center text-sm">
                    <span class="text-[#888]">{{ selectedRoom?.priceFormatted }} × {{ numberOfNights }} night{{ numberOfNights > 1 ? 's' : '' }}</span>
                    <span class="font-mono tracking-wider">{{ formattedTotal }}</span>
                  </div>
                  <div class="flex justify-between items-center">
                    <span class="font-mono text-xs tracking-widest uppercase text-[#888]">Total</span>
                    <span class="font-display text-2xl text-kubu-red">{{ formattedTotal }}</span>
                  </div>
                </div>
              </Transition>
            </div>

            <!-- Navigation Buttons -->
            <div class="flex justify-between mt-10 reveal-el" style="transition-delay: 200ms;">
              <button 
                @click="prevStep"
                class="inline-flex items-center gap-3 font-mono text-sm tracking-[0.2em] uppercase hover:text-kubu-red transition-colors cursor-pointer"
                @mouseenter="mouse.hover=true" @mouseleave="mouse.hover=false"
              >
                <span>←</span> Back
              </button>
              <button 
                @click="nextStep"
                :disabled="!canProceedStep2"
                class="group relative inline-flex items-center gap-4 px-10 py-4 border-2 overflow-hidden cursor-pointer transition-all duration-500"
                :class="canProceedStep2 
                  ? 'border-kubu-black hover:border-kubu-red' 
                  : 'border-[#1A1A1A]/20 opacity-50 cursor-not-allowed'"
                @mouseenter="mouse.hover=true" @mouseleave="mouse.hover=false"
              >
                <span v-if="canProceedStep2" class="absolute inset-0 bg-kubu-red transform transition-transform duration-500 ease-out translate-y-full group-hover:translate-y-0"></span>
                <span class="relative z-10 font-mono text-sm tracking-[0.3em] uppercase transition-colors duration-500" :class="canProceedStep2 ? 'group-hover:text-white' : ''">Continue</span>
                <span class="relative z-10 transition-all duration-500" :class="canProceedStep2 ? 'group-hover:text-white group-hover:translate-x-1' : ''">→</span>
              </button>
            </div>
          </div>
        </div>
      </Transition>

      <!-- ═══════════ STEP 3: Guest Details ═══════════ -->
      <Transition name="step-slide" mode="out-in">
        <div v-if="currentStep === 3 && !isSubmitted" key="step3">
          <div class="max-w-3xl mx-auto grid lg:grid-cols-5 gap-8">
            <!-- Form -->
            <div class="lg:col-span-3 bg-white border border-[#1A1A1A]/5 p-8 md:p-10 space-y-6 reveal-el">
              <h3 class="font-display text-2xl">Guest <i class="text-kubu-red">Information</i></h3>
              
              <div class="space-y-2">
                <label class="font-mono text-xs tracking-widest uppercase text-[#888] block">Full Name *</label>
                <input 
                  v-model="guestName"
                  type="text" 
                  placeholder="Enter your full name"
                  class="w-full px-4 py-4 border border-[#1A1A1A]/10 bg-transparent font-body text-kubu-black focus:border-kubu-red focus:outline-none transition-colors placeholder:text-[#ccc]"
                />
              </div>

              <div class="space-y-2">
                <label class="font-mono text-xs tracking-widest uppercase text-[#888] block">Email Address *</label>
                <input 
                  v-model="guestEmail"
                  type="email" 
                  placeholder="your@email.com"
                  class="w-full px-4 py-4 border border-[#1A1A1A]/10 bg-transparent font-body text-kubu-black focus:border-kubu-red focus:outline-none transition-colors placeholder:text-[#ccc]"
                />
              </div>

              <div class="space-y-2">
                <label class="font-mono text-xs tracking-widest uppercase text-[#888] block">Phone / WhatsApp *</label>
                <input 
                  v-model="guestPhone"
                  type="tel" 
                  placeholder="+62 812 3456 7890"
                  class="w-full px-4 py-4 border border-[#1A1A1A]/10 bg-transparent font-body text-kubu-black focus:border-kubu-red focus:outline-none transition-colors placeholder:text-[#ccc]"
                />
              </div>

              <div class="space-y-2">
                <label class="font-mono text-xs tracking-widest uppercase text-[#888] block">Special Requests <span class="normal-case">(optional)</span></label>
                <textarea 
                  v-model="specialRequests"
                  rows="3"
                  placeholder="Any special arrangements, dietary needs, or requests..."
                  class="w-full px-4 py-4 border border-[#1A1A1A]/10 bg-transparent font-body text-kubu-black focus:border-kubu-red focus:outline-none transition-colors resize-none placeholder:text-[#ccc]"
                ></textarea>
              </div>
            </div>

            <!-- Booking Summary -->
            <div class="lg:col-span-2 reveal-el" style="transition-delay: 150ms;">
              <div class="bg-kubu-black text-white p-8 sticky top-28">
                <h3 class="font-display text-xl mb-6">Booking <i class="text-kubu-red-light">Summary</i></h3>
                
                <div v-if="selectedRoom" class="space-y-4">
                  <img :src="selectedRoom.image" class="w-full h-32 object-cover" />
                  
                  <div class="space-y-3 text-sm">
                    <div class="flex justify-between">
                      <span class="text-white/60">Room</span>
                      <span class="font-mono tracking-wider text-right">{{ selectedRoom.name }}</span>
                    </div>
                    <div class="flex justify-between">
                      <span class="text-white/60">Check-in</span>
                      <span class="font-mono tracking-wider">{{ checkIn }}</span>
                    </div>
                    <div class="flex justify-between">
                      <span class="text-white/60">Check-out</span>
                      <span class="font-mono tracking-wider">{{ checkOut }}</span>
                    </div>
                    <div class="flex justify-between">
                      <span class="text-white/60">Nights</span>
                      <span class="font-mono tracking-wider">{{ numberOfNights }}</span>
                    </div>
                    <div class="flex justify-between">
                      <span class="text-white/60">Guests</span>
                      <span class="font-mono tracking-wider">{{ numGuests }}</span>
                    </div>
                  </div>

                  <div class="border-t border-white/10 pt-4 mt-4">
                    <div class="flex justify-between items-center">
                      <span class="font-mono text-xs tracking-widest uppercase text-white/60">Total</span>
                      <span class="font-display text-2xl text-kubu-red-light">{{ formattedTotal }}</span>
                    </div>
                  </div>
                </div>
              </div>
            </div>

            <!-- Buttons (full width) -->
            <div class="lg:col-span-5 flex justify-between reveal-el" style="transition-delay: 300ms;">
              <button 
                @click="prevStep"
                class="inline-flex items-center gap-3 font-mono text-sm tracking-[0.2em] uppercase hover:text-kubu-red transition-colors cursor-pointer"
                @mouseenter="mouse.hover=true" @mouseleave="mouse.hover=false"
              >
                <span>←</span> Back
              </button>
              <button 
                @click="submitBooking"
                :disabled="!canProceedStep3 || isSubmitting"
                class="group relative inline-flex items-center gap-4 px-10 py-4 overflow-hidden cursor-pointer transition-all duration-500"
                :class="canProceedStep3 && !isSubmitting
                  ? 'bg-kubu-red text-white hover:bg-kubu-red-dark' 
                  : 'bg-[#1A1A1A]/20 text-[#1A1A1A]/40 cursor-not-allowed'"
                @mouseenter="mouse.hover=true" @mouseleave="mouse.hover=false"
              >
                <span v-if="isSubmitting" class="inline-block w-4 h-4 border-2 border-white/30 border-t-white rounded-full animate-spin"></span>
                <span class="font-mono text-sm tracking-[0.3em] uppercase">
                  {{ isSubmitting ? 'Submitting...' : 'Confirm Booking' }}
                </span>
              </button>
            </div>
          </div>
        </div>
      </Transition>

      <!-- ═══════════ SUCCESS STATE ═══════════ -->
      <Transition name="step-slide" mode="out-in">
        <div v-if="isSubmitted" key="success" class="max-w-xl mx-auto text-center py-16">
          <!-- Success Animation -->
          <div class="mb-8 reveal-el">
            <div class="w-24 h-24 rounded-full bg-kubu-red/10 flex items-center justify-center mx-auto mb-6 success-pulse">
              <div class="w-16 h-16 rounded-full bg-kubu-red flex items-center justify-center text-white text-2xl">
                ✓
              </div>
            </div>
          </div>

          <h2 class="font-display text-4xl md:text-5xl mb-4 reveal-el" style="transition-delay: 200ms;">
            Booking <i class="text-kubu-red">Confirmed!</i>
          </h2>
          <p class="text-[#888] text-lg font-light leading-relaxed mb-4 reveal-el" style="transition-delay: 300ms;">
            Thank you, <strong class="text-kubu-black">{{ guestName }}</strong>. Your reservation has been received.
          </p>
          <p class="text-[#888] text-sm font-light mb-10 reveal-el" style="transition-delay: 350ms;">
            A confirmation will be sent to <strong class="text-kubu-black">{{ guestEmail }}</strong>
          </p>

          <!-- Summary Card -->
          <div class="bg-white border border-[#1A1A1A]/5 p-8 text-left mb-10 reveal-el" style="transition-delay: 400ms;">
            <div class="space-y-3 text-sm">
              <div class="flex justify-between">
                <span class="text-[#888]">Room</span>
                <span class="font-mono tracking-wider">{{ selectedRoom?.name }}</span>
              </div>
              <div class="flex justify-between">
                <span class="text-[#888]">Dates</span>
                <span class="font-mono tracking-wider">{{ checkIn }} → {{ checkOut }}</span>
              </div>
              <div class="flex justify-between">
                <span class="text-[#888]">Guests</span>
                <span class="font-mono tracking-wider">{{ numGuests }}</span>
              </div>
              <div class="border-t border-[#1A1A1A]/5 pt-3 flex justify-between items-center">
                <span class="font-mono text-xs tracking-widest uppercase text-[#888]">Total</span>
                <span class="font-display text-xl text-kubu-red">{{ formattedTotal }}</span>
              </div>
            </div>
          </div>

          <button 
            @click="goHome"
            class="group relative inline-flex items-center gap-4 px-10 py-4 border-2 border-kubu-black overflow-hidden cursor-pointer transition-all duration-500 hover:border-kubu-red reveal-el"
            style="transition-delay: 500ms;"
            @mouseenter="mouse.hover=true" @mouseleave="mouse.hover=false"
          >
            <span class="absolute inset-0 bg-kubu-red transform transition-transform duration-500 ease-out translate-y-full group-hover:translate-y-0"></span>
            <span class="relative z-10 font-mono text-sm tracking-[0.3em] uppercase transition-colors duration-500 group-hover:text-white">Back to Home</span>
            <span class="relative z-10 transition-all duration-500 group-hover:text-white group-hover:translate-x-1">→</span>
          </button>
        </div>
      </Transition>

    </div>

    <!-- Footer -->
    <div class="w-full max-w-[1400px] mx-auto px-6 md:px-12 mt-auto">
      <div class="border-t border-[#1A1A1A]/10 py-6 flex flex-col md:flex-row justify-between items-center gap-4">
        <div class="flex items-center gap-3">
          <img src="/images/logo.png" class="w-8 h-8 object-contain" />
          <span class="font-display font-semibold text-sm">Kubu Mawar Residence</span>
        </div>
        <div class="flex flex-wrap justify-center items-center gap-6 md:gap-8 font-mono text-xs tracking-widest uppercase">
          <a href="https://wa.me/081991888008" target="_blank" class="hover:text-kubu-red transition-colors cursor-pointer" @mouseenter="mouse.hover=true" @mouseleave="mouse.hover=false">WhatsApp</a>
          <a href="mailto:hello@kubumawar.com" class="hover:text-kubu-red transition-colors cursor-pointer" @mouseenter="mouse.hover=true" @mouseleave="mouse.hover=false">Email Us</a>
          <a href="https://instagram.com/kubumaawarproperti" target="_blank" class="hover:text-kubu-red transition-colors cursor-pointer" @mouseenter="mouse.hover=true" @mouseleave="mouse.hover=false">Instagram</a>
          <a href="https://maps.google.com/?q=Kubu+Mawar+Residence+Bali" target="_blank" class="hover:text-kubu-red transition-colors cursor-pointer" @mouseenter="mouse.hover=true" @mouseleave="mouse.hover=false">Location</a>
        </div>
        <p class="text-xs text-[#1A1A1A]/50 tracking-widest uppercase">
          &copy; {{ new Date().getFullYear() }} All Rights Reserved.
        </p>
      </div>
    </div>

  </div>
</template>

<style scoped>
/* Step Slide Transition */
.step-slide-enter-active {
  transition: all 0.5s cubic-bezier(0.19, 1, 0.22, 1);
}
.step-slide-leave-active {
  transition: all 0.3s cubic-bezier(0.55, 0, 1, 0.45);
}
.step-slide-enter-from {
  opacity: 0;
  transform: translateX(40px);
}
.step-slide-leave-to {
  opacity: 0;
  transform: translateX(-40px);
}

/* Fade Up */
.fade-up-enter-active {
  transition: all 0.5s cubic-bezier(0.19, 1, 0.22, 1);
}
.fade-up-leave-active {
  transition: all 0.3s ease;
}
.fade-up-enter-from {
  opacity: 0;
  transform: translateY(15px);
}
.fade-up-leave-to {
  opacity: 0;
}

/* Badge Pop */
.badge-pop-enter-active {
  transition: all 0.4s cubic-bezier(0.34, 1.56, 0.64, 1);
}
.badge-pop-leave-active {
  transition: all 0.2s ease;
}
.badge-pop-enter-from {
  opacity: 0;
  transform: scale(0);
}
.badge-pop-leave-to {
  opacity: 0;
  transform: scale(0);
}

/* Success Pulse */
@keyframes successPulse {
  0%, 100% { box-shadow: 0 0 0 0 rgba(200, 16, 46, 0.2); }
  50% { box-shadow: 0 0 0 20px rgba(200, 16, 46, 0); }
}
.success-pulse {
  animation: successPulse 2s ease-in-out infinite;
}

/* Date input styling */
input[type="date"] {
  color-scheme: light;
}
input[type="date"]::-webkit-calendar-picker-indicator {
  cursor: pointer;
  opacity: 0.5;
}
input[type="date"]::-webkit-calendar-picker-indicator:hover {
  opacity: 1;
}
</style>
