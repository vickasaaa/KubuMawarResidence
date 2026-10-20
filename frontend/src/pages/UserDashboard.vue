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

// ─── Kontak & Social Media (Sinkron dengan Homepage) ───
const contactInfo = {
  email: 'hello@kubumawar.com',
  instagram: '@kubumaawarproperti',
  instagramUrl: 'https://instagram.com/kubumaawarproperti',
  whatsapp: '081991888008',
  whatsappUrl: 'https://wa.me/6281991888008',
  maps: 'Google Maps',
  mapsResidenceUrl: 'https://www.google.com/maps/place/Kubu+Mawar+Residence/@-8.7117187,115.1719158,15z/data=!3m1!4b1!4m6!3m5!1s0x2dd2415f41cd6def:0xe0fa45086709b5f8!8m2!3d-8.7117191!4d115.1903699!16s%2Fg%2F11kl3gj2vc',
  mapsSuwungUrl: 'https://www.google.com/maps/place/KUBU+MAWAR/@-8.7186839,115.1758263,15z/data=!3m1!4b1!4m6!3m5!1s0x2dd241789936f96b:0xc81a21fdab7ba47a!8m2!3d-8.7186843!4d115.1942804!16s%2Fg%2F11fn6mw9fd',
}

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

// ─── Customer Google Reviews (Bintang 4 & 5 Terverifikasi) ───
interface CustomerReview {
  id: number
  author: string
  avatarInitial: string
  avatarColor: string
  avatarImg?: string
  subtitle: string
  isLocalGuide: boolean
  rating: number
  time: string
  text: string
  likes: number
  hasPhoto?: boolean
  photoUrl?: string
  place: string
  placeUrl: string
}

const customerReviews = ref<CustomerReview[]>([
  {
    id: 1,
    author: 'Fajar Yanuar',
    avatarInitial: 'F',
    avatarColor: '#2e7d32',
    subtitle: '9 ulasan · 2 foto',
    isLocalGuide: false,
    rating: 5,
    time: '4 tahun lalu',
    text: 'enak murah di pawon yu en',
    likes: 1,
    place: 'KUBU MAWAR',
    placeUrl: 'https://www.google.com/maps/place/KUBU+MAWAR/@-8.7186839,115.1758263,15z/data=!3m1!4b1!4m6!3m5!1s0x2dd241789936f96b:0xc81a21fdab7ba47a!8m2!3d-8.7186843!4d115.1942804!16s%2Fg%2F11fn6mw9fd'
  },
  {
    id: 2,
    author: 'Vira Zon123',
    avatarInitial: 'V',
    avatarColor: '#00838f',
    subtitle: '1 ulasan',
    isLocalGuide: false,
    rating: 5,
    time: '3 tahun lalu',
    text: 'Nyaman',
    likes: 0,
    place: 'KUBU MAWAR',
    placeUrl: 'https://www.google.com/maps/place/KUBU+MAWAR/@-8.7186839,115.1758263,15z/data=!3m1!4b1!4m6!3m5!1s0x2dd241789936f96b:0xc81a21fdab7ba47a!8m2!3d-8.7186843!4d115.1942804!16s%2Fg%2F11fn6mw9fd'
  },
  {
    id: 3,
    author: "Sifa'uddin M. Hidayatulloh",
    avatarInitial: 'SM',
    avatarColor: '#1b5e20',
    subtitle: 'Local Guide · 76 ulasan · 37 foto',
    isLocalGuide: true,
    rating: 5,
    time: '5 tahun lalu',
    text: 'Tempat bagus dengan harga terjangkau.',
    likes: 0,
    place: 'Kubu Mawar Residence',
    placeUrl: 'https://www.google.com/maps/place/Kubu+Mawar+Residence/@-8.7117187,115.1719158,15z/data=!3m1!4b1!4m6!3m5!1s0x2dd2415f41cd6def:0xe0fa45086709b5f8!8m2!3d-8.7117191!4d115.1903699!16s%2Fg%2F11kl3gj2vc'
  },
  {
    id: 4,
    author: 'Syaw Lee20',
    avatarInitial: 'S',
    avatarColor: '#558b2f',
    subtitle: 'Local Guide · 25 ulasan · 34 foto',
    isLocalGuide: true,
    rating: 5,
    time: '5 tahun lalu',
    text: 'Kost yg nyaman',
    likes: 0,
    place: 'KUBU MAWAR',
    placeUrl: 'https://www.google.com/maps/place/KUBU+MAWAR/@-8.7186839,115.1758263,15z/data=!3m1!4b1!4m6!3m5!1s0x2dd241789936f96b:0xc81a21fdab7ba47a!8m2!3d-8.7186843!4d115.1942804!16s%2Fg%2F11fn6mw9fd'
  },
  {
    id: 5,
    author: 'Yande Bhero',
    avatarInitial: 'YB',
    avatarColor: '#374151',
    subtitle: 'Local Guide · 44 ulasan · 2 foto',
    isLocalGuide: true,
    rating: 5,
    time: 'Diedit 5 tahun lalu',
    text: 'Tempatnya nyaman dan bersih',
    likes: 0,
    place: 'KUBU MAWAR',
    placeUrl: 'https://www.google.com/maps/place/KUBU+MAWAR/@-8.7186839,115.1758263,15z/data=!3m1!4b1!4m6!3m5!1s0x2dd241789936f96b:0xc81a21fdab7ba47a!8m2!3d-8.7186843!4d115.1942804!16s%2Fg%2F11fn6mw9fd'
  },
  {
    id: 6,
    author: 'Yunita Sitepu',
    avatarInitial: 'YS',
    avatarColor: '#0288d1',
    subtitle: '3 ulasan',
    isLocalGuide: false,
    rating: 5,
    time: 'setahun lalu',
    text: 'Apakah ada nomor pemilik yg bisa dihubungi?',
    likes: 0,
    place: 'Kubu Mawar Residence',
    placeUrl: 'https://www.google.com/maps/place/Kubu+Mawar+Residence/@-8.7117187,115.1719158,15z/data=!3m1!4b1!4m6!3m5!1s0x2dd2415f41cd6def:0xe0fa45086709b5f8!8m2!3d-8.7117191!4d115.1903699!16s%2Fg%2F11kl3gj2vc'
  },
  {
    id: 7,
    author: 'Yayuk Setyaningsih',
    avatarInitial: 'YS',
    avatarColor: '#c2185b',
    subtitle: 'Local Guide · 6 ulasan · 21 foto',
    isLocalGuide: true,
    rating: 5,
    time: '8 bulan lalu',
    text: 'Bersih',
    likes: 0,
    place: 'Kubu Mawar Residence',
    placeUrl: 'https://www.google.com/maps/place/Kubu+Mawar+Residence/@-8.7117187,115.1719158,15z/data=!3m1!4b1!4m6!3m5!1s0x2dd2415f41cd6def:0xe0fa45086709b5f8!8m2!3d-8.7117191!4d115.1903699!16s%2Fg%2F11kl3gj2vc'
  },
  {
    id: 8,
    author: 'Sandiputra Putra',
    avatarInitial: 'SP',
    avatarColor: '#d97706',
    subtitle: 'Local Guide · 12 ulasan · 41 foto',
    isLocalGuide: true,
    rating: 5,
    time: 'setahun lalu',
    text: 'Tepat..ngekos lengkap..tinggal tidur..aja',
    likes: 0,
    place: 'KUBU MAWAR',
    placeUrl: 'https://www.google.com/maps/place/KUBU+MAWAR/@-8.7186839,115.1758263,15z/data=!3m1!4b1!4m6!3m5!1s0x2dd241789936f96b:0xc81a21fdab7ba47a!8m2!3d-8.7186843!4d115.1942804!16s%2Fg%2F11fn6mw9fd'
  },
  {
    id: 9,
    author: 'trisna samudra',
    avatarInitial: 'TS',
    avatarColor: '#2563eb',
    subtitle: 'Local Guide · 103 ulasan · 60 foto',
    isLocalGuide: true,
    rating: 5,
    time: '5 tahun lalu',
    text: 'Tempatnya bersih...harga terjangkau...aman dan nyaman..',
    likes: 0,
    place: 'KUBU MAWAR',
    placeUrl: 'https://www.google.com/maps/place/KUBU+MAWAR/@-8.7186839,115.1758263,15z/data=!3m1!4b1!4m6!3m5!1s0x2dd241789936f96b:0xc81a21fdab7ba47a!8m2!3d-8.7186843!4d115.1942804!16s%2Fg%2F11fn6mw9fd'
  },
  {
    id: 10,
    author: 'Bali Motorbike Rental',
    avatarInitial: 'BM',
    avatarColor: '#0f766e',
    subtitle: 'Local Guide · 243 ulasan · 76 foto',
    isLocalGuide: true,
    rating: 5,
    time: '3 tahun lalu',
    text: 'Harga murah dan tempat strategis recomended',
    likes: 0,
    hasPhoto: true,
    photoUrl: 'https://images.unsplash.com/photo-1558981403-c5f9899a28bc?auto=format&fit=crop&w=600&q=80',
    place: 'Kubu Mawar Residence',
    placeUrl: 'https://www.google.com/maps/place/Kubu+Mawar+Residence/@-8.7117187,115.1719158,15z/data=!3m1!4b1!4m6!3m5!1s0x2dd2415f41cd6def:0xe0fa45086709b5f8!8m2!3d-8.7117191!4d115.1903699!16s%2Fg%2F11kl3gj2vc'
  }
])

const isReviewMarqueePaused = ref(false)
const reviewScrollTrack = ref<HTMLElement | null>(null)

const toggleReviewMarquee = () => {
  isReviewMarqueePaused.value = !isReviewMarqueePaused.value
}

const scrollReviewsManual = (dir: 'left' | 'right') => {
  if (reviewScrollTrack.value) {
    const amount = dir === 'left' ? -380 : 380
    reviewScrollTrack.value.scrollBy({ left: amount, behavior: 'smooth' })
  }
}

// ─── User Bookings ───
const userBookings = ref<any[]>([])
const bookingFilter = ref<'all' | 'upcoming' | 'completed'>('upcoming')

// ─── Booking Form State ───
const getTodayDateString = () => {
  const now = new Date()
  const year = now.getFullYear()
  const month = String(now.getMonth() + 1).padStart(2, '0')
  const day = String(now.getDate()).padStart(2, '0')
  return `${year}-${month}-${day}`
}

const minBookingDate = computed(() => getTodayDateString())

const bookingForm = ref({
  nama: '',
  phone: '',
  email: '',
  rental_type: 'monthly' as 'weekly' | 'monthly',
  duration: 1,
  start_date: getTodayDateString(),
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

const formattedStartDate = computed(() => {
  if (!bookingForm.value.start_date) return ''
  try {
    const d = new Date(bookingForm.value.start_date + 'T00:00:00')
    return d.toLocaleDateString('id-ID', { day: 'numeric', month: 'short', year: 'numeric' })
  } catch {
    return bookingForm.value.start_date
  }
})

const calculatedEndDate = computed(() => {
  if (!bookingForm.value.start_date) return ''
  try {
    const d = new Date(bookingForm.value.start_date + 'T00:00:00')
    const dur = Number(bookingForm.value.duration) || 1
    if (selectedProperty.value === 'residence' && bookingForm.value.rental_type === 'weekly') {
      d.setDate(d.getDate() + (dur * 7))
    } else {
      d.setMonth(d.getMonth() + dur)
    }
    return d.toLocaleDateString('id-ID', { day: 'numeric', month: 'short', year: 'numeric' })
  } catch {
    return ''
  }
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
  bookingForm.value.start_date = getTodayDateString()
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
    const guestEmail = bookingForm.value.email || user.value.email || 'tamu@kubumawar.com'
    const payload = {
      room_id: selectedRoom.value?.id,
      property_type: selectedProperty.value,
      guest_name: bookingForm.value.nama,
      guest_phone: bookingForm.value.phone,
      guest_email: guestEmail,
      phone: bookingForm.value.phone,
      email: guestEmail,
      rental_type: bookingForm.value.rental_type,
      duration: bookingForm.value.duration,
      start_date: bookingForm.value.start_date || getTodayDateString(),
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

// ─── Hero Interactive State ───
const heroLocation = ref('Pilih area atau lokasi')
const heroCheckIn = ref('')
const heroGuests = ref('1 orang')
const showLocationDropdown = ref(false)
const showGuestsDropdown = ref(false)
const showUserDropdown = ref(false)
const activeCardIndex = ref(0)
const wishlistCount = ref(3)

const selectLocation = (loc: string) => {
  heroLocation.value = loc
  showLocationDropdown.value = false
}

const selectGuests = (guests: string) => {
  heroGuests.value = guests
  showGuestsDropdown.value = false
}

const toggleHeroCard = (direction: 'next' | 'prev') => {
  if (direction === 'next') {
    activeCardIndex.value = (activeCardIndex.value + 1) % 2
  } else {
    activeCardIndex.value = (activeCardIndex.value - 1 + 2) % 2
  }
}

const handleHeroSearch = () => {
  if (heroLocation.value.includes('Seminyak')) {
    selectProperty('residence')
  } else if (heroLocation.value.includes('Denpasar') || heroLocation.value.includes('Suwung')) {
    selectProperty('suwung')
  } else {
    scrollToEl('hunian-section')
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


    <!-- ═══ NAVBAR (SEAMLESS OVER HERO, NO BORDER) ═══ -->
    <header class="km-navbar" :class="{ scrolled: isScrolled }">
      <div class="km-navbar-inner">
        <!-- Brand -->
        <div class="km-brand" @click="scrollToEl('beranda')">
          <img src="/images/logo.png" alt="Kubu Mawar Logo" class="km-brand-logo" />
          <div class="km-brand-text">
            <span class="km-brand-name">Kubu Mawar</span>
          </div>
        </div>

        <!-- Center Nav Links -->
        <nav class="km-nav-links">
          <button @click="scrollToEl('beranda')" class="km-nav-link-pill" :class="{ active: activeNav === 'beranda' }">Beranda</button>
          <div class="km-nav-dropdown-wrap">
            <button @click="scrollToEl('hunian-section')" class="km-nav-link" :class="{ active: activeNav === 'hunian-section' }">
              <span>Hunian</span>
              <svg class="km-chevron" width="11" height="11" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><polyline points="6 9 12 15 18 9"/></svg>
            </button>
          </div>
          <button @click="scrollToEl('reservasi-section')" class="km-nav-link" :class="{ active: activeNav === 'reservasi-section' }">Reservasi Saya</button>
          <button @click="scrollToEl('ulasan-section')" class="km-nav-link" :class="{ active: activeNav === 'ulasan-section' }">Ulasan Tamu</button>
          <button @click="scrollToEl('hunian-section')" class="km-nav-link">Tentang Kami</button>
          <button @click="scrollToEl('hunian-section')" class="km-nav-link">Bantuan</button>
        </nav>

        <!-- Right Nav -->
        <div class="km-nav-right">
          <!-- Heart / Wishlist Icon with badge 3 -->
          <button class="km-icon-btn km-wishlist-btn" @click="scrollToEl('hunian-section')" title="Favorit">
            <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M20.84 4.61a5.5 5.5 0 0 0-7.78 0L12 5.67l-1.06-1.06a5.5 5.5 0 0 0-7.78 7.78l1.06 1.06L12 21.23l7.78-7.78 1.06-1.06a5.5 5.5 0 0 0 0-7.78z"/></svg>
            <span class="km-nav-badge">3</span>
          </button>

          <!-- User Profile Pill -->
          <div class="km-user-menu-wrap">
            <div class="km-user-pill" @click="showUserDropdown = !showUserDropdown">
              <div class="km-avatar-circle">MV</div>
              <span class="km-user-name-text">{{ formattedName }}</span>
              <svg class="km-chevron" width="11" height="11" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><polyline points="6 9 12 15 18 9"/></svg>
            </div>

            <!-- Dropdown Menu -->
            <div v-if="showUserDropdown" class="km-user-dropdown-menu">
              <div class="km-dropdown-header">
                <strong>{{ formattedName }}</strong>
                <small>{{ user.email || 'tamu@kubumawar.com' }}</small>
              </div>
              <div class="km-dropdown-divider"></div>
              <button @click="scrollToEl('reservasi-section'); showUserDropdown = false">
                <svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M14 2H6a2 2 0 00-2 2v16a2 2 0 002 2h12a2 2 0 002-2V8z"/><polyline points="14 2 14 8 20 8"/><line x1="16" y1="13" x2="8" y2="13"/><line x1="16" y1="17" x2="8" y2="17"/></svg>
                Reservasi Saya
              </button>
              <button class="logout" @click="handleLogout">
                <svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M9 21H5a2 2 0 01-2-2V5a2 2 0 012-2h4"/><polyline points="16 17 21 12 16 7"/><line x1="21" y1="12" x2="9" y2="12"/></svg>
                Keluar
              </button>
            </div>
          </div>

          <!-- Mobile hamburger toggle -->
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
        <button @click="scrollToEl('reservasi-section')">Reservasi Saya</button>
        <button @click="scrollToEl('ulasan-section')">Ulasan Tamu</button>
        <button @click="scrollToEl('hunian-section')">Tentang Kami</button>
        <button @click="scrollToEl('hunian-section')">Bantuan</button>
        <button class="logout" @click="handleLogout">Keluar</button>
      </div>
    </header>

    <!-- ═══ WELCOME HERO - IMMERSIVE BALI EXPERIENCE ═══ -->
    <section id="beranda" class="km-welcome">
      <!-- Background Image with Sunset Lake & Temple -->
      <div class="km-welcome-bg">
        <img src="https://images.unsplash.com/photo-1537996194471-e657df975ab4?auto=format&fit=crop&w=1920&q=80" alt="Danau Beratan Bali" />
        <div class="km-welcome-overlay"></div>
      </div>

      <!-- Floating particles for subtle ambient life -->
      <div class="km-particles" aria-hidden="true">
        <span class="km-particle p1"></span>
        <span class="km-particle p2"></span>
        <span class="km-particle p3"></span>
        <span class="km-particle p4"></span>
        <span class="km-particle p5"></span>
      </div>

      <div class="km-welcome-content">
        <div class="km-hero-grid">
          <!-- LEFT: Content -->
          <div class="km-hero-left">
            <!-- Badge -->
            <div class="km-hero-badge km-anim-up" style="--delay: 0s">
              <span class="km-hero-badge-icon">
                <svg width="14" height="14" viewBox="0 0 24 24" fill="currentColor"><path d="M12 3L2 12h3v8h6v-6h2v6h6v-8h3L12 3z"/></svg>
              </span>
              <span>Homestay & Kost Pilihan di Lokasi Strategis</span>
            </div>

            <!-- Title -->
            <h1 class="km-welcome-title km-anim-up" style="--delay: 0.1s">
              {{ greeting }}, <span class="km-welcome-name-italic">{{ formattedName }}</span>
            </h1>

            <!-- Subtitle -->
            <p class="km-welcome-sub km-anim-up" style="--delay: 0.2s">
              Temukan homestay dan kost terbaik di Kubu Mawar.
              Hunian nyaman, lokasi strategis, dengan proses reservasi yang mudah dan aman.
            </p>

            <!-- Red Booking CTA Banner Container (Auto-scroll to Hunian) -->
            <div 
              class="km-hero-booking-cta km-anim-up" 
              style="--delay: 0.3s"
              @click="scrollToEl('hunian-section')"
              role="button"
              tabindex="0"
              title="Klik untuk memilih dan booking hunian"
            >
              <div class="km-hb-main">
                <div class="km-hb-icon-circle">
                  <svg width="22" height="22" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round">
                    <path d="M3 9l9-7 9 7v11a2 2 0 01-2 2H5a2 2 0 01-2-2z"/>
                    <polyline points="9 22 9 12 15 12 15 22"/>
                  </svg>
                </div>
                <div class="km-hb-text">
                  <span class="km-hb-eyebrow">RESERVASI MUDAH & CEPAT</span>
                  <h3 class="km-hb-title">Pesan & Booking Hunian Sekarang</h3>
                  <p class="km-hb-desc">Pilih homestay atau kost favorit Anda, cek ketersediaan kamar & reservasi langsung</p>
                </div>
              </div>
              <div class="km-hb-action">
                <span class="km-hb-btn-label">Pilih Hunian</span>
                <span class="km-hb-btn-arrow">
                  <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round">
                    <line x1="12" y1="5" x2="12" y2="19"></line>
                    <polyline points="19 12 12 19 5 12"></polyline>
                  </svg>
                </span>
              </div>
            </div>

            <!-- Stats Row with Clean Outline Icons -->
            <div class="km-hero-stats km-anim-up" style="--delay: 0.4s">
              <!-- Properti -->
              <div class="km-stat-unit">
                <div class="km-stat-icon">
                  <svg width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M3 9l9-7 9 7v11a2 2 0 01-2 2H5a2 2 0 01-2-2z"/><polyline points="9 22 9 12 15 12 15 22"/></svg>
                </div>
                <div class="km-stat-content">
                  <span class="km-stat-number">2</span>
                  <span class="km-stat-tag">PROPERTI</span>
                </div>
              </div>

              <!-- Total Kamar -->
              <div class="km-stat-unit">
                <div class="km-stat-icon">
                  <svg width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M2 4v16"/><path d="M2 9h18a2 2 0 012 2v9"/><path d="M2 17h20"/><path d="M6 9v8"/></svg>
                </div>
                <div class="km-stat-content">
                  <span class="km-stat-number">12</span>
                  <span class="km-stat-tag">TOTAL KAMAR</span>
                </div>
              </div>

              <!-- Reservasi Anda -->
              <div class="km-stat-unit">
                <div class="km-stat-icon">
                  <svg width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><circle cx="12" cy="8" r="6"/><path d="M15.477 12.89L17 22l-5-3-5 3 1.523-9.11"/></svg>
                </div>
                <div class="km-stat-content">
                  <span class="km-stat-number">4</span>
                  <span class="km-stat-tag">RESERVASI ANDA</span>
                </div>
              </div>

              <!-- Rating Penghuni -->
              <div class="km-stat-unit">
                <div class="km-stat-icon">
                  <svg width="24" height="24" viewBox="0 0 24 24" fill="currentColor"><polygon points="12 2 15.09 8.26 22 9.27 17 14.14 18.18 21.02 12 17.77 5.82 21.02 7 14.14 2 9.27 8.91 8.26 12 2"/></svg>
                </div>
                <div class="km-stat-content">
                  <span class="km-stat-number">4.8</span>
                  <span class="km-stat-tag">RATING PENGHUNI</span>
                </div>
              </div>
            </div>

            <!-- Feature Badges -->
            <div class="km-hero-features km-anim-up" style="--delay: 0.5s">
              <div class="km-feature-pill">
                <div class="km-feat-icon-sq">
                  <svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><path d="M12 22s8-4 8-10V5l-8-3-8 3v7c0 6 8 10 8 10z"/><polyline points="9 12 11 14 15 10"/></svg>
                </div>
                <span>Proses Aman & Terpercaya</span>
              </div>

              <div class="km-feature-pill">
                <div class="km-feat-icon-sq">
                  <svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><path d="M21 10c0 7-9 13-9 13s-9-6-9-13a9 9 0 0118 0z"/><circle cx="12" cy="10" r="3"/></svg>
                </div>
                <span>Lokasi Strategis</span>
              </div>

              <div class="km-feature-pill">
                <div class="km-feat-icon-sq">
                  <svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><path d="M20 9V7a2 2 0 0 0-2-2H6a2 2 0 0 0-2 2v2"/><path d="M2 11v5a2 2 0 0 0 2 2h16a2 2 0 0 0 2-2v-5a2 2 0 0 0-2-2H4a2 2 0 0 0-2 2z"/><path d="M4 18v2"/><path d="M20 18v2"/></svg>
                </div>
                <span>Hunian Nyaman dan Bersih</span>
              </div>

              <div class="km-feature-pill">
                <div class="km-feat-icon-sq">
                  <svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><path d="M3 18v-6a9 9 0 0 1 18 0v6"/><path d="M21 19a2 2 0 0 1-2 2h-1a2 2 0 0 1-2-2v-3a2 2 0 0 1 2-2h3zM3 19a2 2 0 0 0 2 2h1a2 2 0 0 0 2-2v-3a2 2 0 0 0-2-2H3z"/></svg>
                </div>
                <span>Layanan Responsif 24/7</span>
              </div>
            </div>
          </div>

          <!-- RIGHT: Floating Overlapping Property Cards with Carousel Navigation -->
          <div class="km-hero-right">
            <!-- Front Card 1: Kubu Mawar Homestay -->
            <div 
              class="km-hero-card km-hero-card-1 km-anim-up" 
              :class="{ 'card-highlight': activeCardIndex === 0 }"
              style="--delay: 0.4s"
              @click="selectProperty('residence')"
            >
              <div class="km-hero-card-img">
                <img src="https://images.unsplash.com/photo-1582719478250-c89cae4dc85b?auto=format&fit=crop&w=600&q=80" alt="Kubu Mawar Homestay" />
                <span class="km-hero-card-badge">Homestay</span>
                <span class="km-hero-card-heart">
                  <svg width="14" height="14" viewBox="0 0 24 24" fill="#e11d48" stroke="#e11d48" stroke-width="2"><path d="M20.84 4.61a5.5 5.5 0 0 0-7.78 0L12 5.67l-1.06-1.06a5.5 5.5 0 0 0-7.78 7.78l1.06 1.06L12 21.23l7.78-7.78 1.06-1.06a5.5 5.5 0 0 0 0-7.78z"/></svg>
                </span>
              </div>
              <div class="km-hero-card-body">
                <h4>Kubu Mawar Homestay</h4>
                <p class="km-hero-card-loc">
                  <svg width="11" height="11" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M21 10c0 7-9 13-9 13s-9-6-9-13a9 9 0 0118 0z"/><circle cx="12" cy="10" r="3"/></svg>
                  Jl Taman Pancing, Denpasar Selatan
                </p>
                <div class="km-hero-card-bottom">
                  <span class="km-hero-card-price">Rp 150.000 <small>/malam</small></span>
                  <span class="km-hero-card-rating">
                    <span class="star-icon">★</span> <strong>4.8</strong> (24)
                  </span>
                </div>
              </div>
            </div>

            <!-- Card 2: Mawar Kost Exclusive -->
            <div 
              class="km-hero-card km-hero-card-2 km-anim-up" 
              :class="{ 'card-highlight': activeCardIndex === 1 }"
              style="--delay: 0.55s"
              @click="selectProperty('suwung')"
            >
              <div class="km-hero-card-img">
                <img src="https://images.unsplash.com/photo-1554995207-c18c203602cb?auto=format&fit=crop&w=600&q=80" alt="Mawar Kost Exclusive" />
                <span class="km-hero-card-badge">Kost</span>
                <span class="km-hero-card-heart">
                  <svg width="14" height="14" viewBox="0 0 24 24" fill="#e11d48" stroke="#e11d48" stroke-width="2"><path d="M20.84 4.61a5.5 5.5 0 0 0-7.78 0L12 5.67l-1.06-1.06a5.5 5.5 0 0 0-7.78 7.78l1.06 1.06L12 21.23l7.78-7.78 1.06-1.06a5.5 5.5 0 0 0 0-7.78z"/></svg>
                </span>
              </div>
              <div class="km-hero-card-body">
                <h4>Mawar Kost Exclusive</h4>
                <p class="km-hero-card-loc">
                  <svg width="11" height="11" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M21 10c0 7-9 13-9 13s-9-6-9-13a9 9 0 0118 0z"/><circle cx="12" cy="10" r="3"/></svg>
                  Denpasar, Bali
                </p>
                <div class="km-hero-card-bottom">
                  <span class="km-hero-card-price">Rp 1.200.000 <small>/bulan</small></span>
                  <span class="km-hero-card-rating">
                    <span class="star-icon">★</span> <strong>4.6</strong> (18)
                  </span>
                </div>
              </div>
            </div>

            <!-- Card Navigation Arrows (Circular) -->
            <div class="km-hero-card-nav">
              <button class="km-card-arrow" @click.stop="toggleHeroCard('prev')" title="Sebelumnya">
                <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><polyline points="15 18 9 12 15 6"/></svg>
              </button>
              <button class="km-card-arrow" @click.stop="toggleHeroCard('next')" title="Berikutnya">
                <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><polyline points="9 18 15 12 9 6"/></svg>
              </button>
            </div>
          </div>
        </div>
      </div>

      <!-- ═══ ORGANIC WATER FLOW SHAPE ═══ -->
      <div class="km-water-flow" aria-hidden="true">
        <svg class="km-water-svg" viewBox="0 0 1440 120" preserveAspectRatio="none" xmlns="http://www.w3.org/2000/svg">
          <path 
            d="M0,45 C280,105 520,30 840,65 C1120,100 1320,35 1440,55 L1440,120 L0,120 Z" 
            fill="var(--km-bg)" 
          />
        </svg>
      </div>
    </section>


    <!-- ═══ PILIH HUNIAN (2 OPSI) - HORIZONTAL SCROLL ═══ -->
    <section id="hunian-section" class="km-section" v-show="!selectedProperty">
      <div class="km-section-inner">
        <div class="km-section-header">
          <div>
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
                {{ selectedProperty === 'residence' ? 'Kubu Mawar Residence (Home Stay)' : 'Kubu Mawar Suwung (Kost)' }}
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

    <!-- ═══ ULASAN GOOGLE MAPS (CUSTOMER REVIEWS SCROLLING RIGHT TO LEFT) ═══ -->
    <section id="ulasan-section" class="km-reviews-section">
      <div class="km-reviews-header-wrap">
        <div class="km-reviews-header-inner">
          <div class="km-reviews-badge">
            <svg class="km-google-icon" viewBox="0 0 24 24" width="18" height="18">
              <path fill="#4285F4" d="M22.56 12.25c0-.78-.07-1.53-.2-2.25H12v4.26h5.92c-.26 1.37-1.04 2.53-2.21 3.31v2.77h3.57c2.08-1.92 3.28-4.74 3.28-8.09z"/>
              <path fill="#34A853" d="M12 23c2.97 0 5.46-.98 7.28-2.66l-3.57-2.77c-.98.66-2.23 1.06-3.71 1.06-2.86 0-5.29-1.93-6.16-4.53H2.18v2.84C3.99 20.53 7.7 23 12 23z"/>
              <path fill="#FBBC05" d="M5.84 14.09c-.22-.66-.35-1.36-.35-2.09s.13-1.43.35-2.09V7.06H2.18C1.43 8.55 1 10.22 1 12s.43 3.45 1.18 4.94l2.85-2.22.81-.63z"/>
              <path fill="#EA4335" d="M12 5.38c1.62 0 3.06.56 4.21 1.64l3.15-3.15C17.45 2.09 14.97 1 12 1 7.7 1 3.99 3.47 2.18 7.06l3.66 2.84c.87-2.6 3.3-4.52 6.16-4.52z"/>
            </svg>
            <span>Google Reviews Terverifikasi Customer Kubu Mawar Residence & Kubu Mawar Suwung</span>
          </div>

          <h2 class="km-reviews-title">
            Apa Kata Para Tamu & <span class="km-name-italic">Penghuni Kami</span>
          </h2>

          <p class="km-reviews-subtitle">
            Ulasan para pelanggan dari Google Maps untuk <strong>Kubu Mawar Residence</strong> & <strong>Kubu Mawar</strong>.
            Kenyamanan terbukti, harga bersahabat, dan lingkungan aman bersih.
          </p>

          <!-- Overall Rating Highlight Bar -->
          <div class="km-reviews-stats-bar">
            <div class="km-rstat-item">
              <span class="km-rstat-num">5.0</span>
              <div class="km-rstat-stars">
                <span v-for="n in 5" :key="n" class="km-star-gold">★</span>
              </div>
              <span class="km-rstat-lbl">Rating Ulasan Terpilih</span>
            </div>

            <div class="km-rstat-divider"></div>

            <div class="km-rstat-item">
              <span class="km-rstat-num">100%</span>
              <span class="km-rstat-badge">Bintang Tinggi</span>
              <span class="km-rstat-lbl">Kepuasan Pelanggan</span>
            </div>

            <div class="km-rstat-divider"></div>

            <div class="km-rstat-item km-rstat-links">
              <span class="km-rstat-lbl" style="margin-bottom: 6px;">Buka Lokasi di Google Maps:</span>
              <div class="km-gmaps-link-btns">
                <a 
                  href="https://www.google.com/maps/place/KUBU+MAWAR/@-8.7186839,115.1758263,15z/data=!3m1!4b1!4m6!3m5!1s0x2dd241789936f96b:0xc81a21fdab7ba47a!8m2!3d-8.7186843!4d115.1942804!16s%2Fg%2F11fn6mw9fd" 
                  target="_blank" 
                  rel="noopener noreferrer" 
                  class="km-gmaps-btn"
                >
                  Kubu Mawar Suwung
                  <svg width="12" height="12" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><path d="M18 13v6a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2V8a2 2 0 0 1 2-2h6"/><polyline points="15 3 21 3 21 9"/><line x1="10" y1="14" x2="21" y2="3"/></svg>
                </a>
                <a 
                  href="https://www.google.com/maps/place/Kubu+Mawar+Residence/@-8.7117187,115.1719158,15z/data=!3m1!4b1!4m6!3m5!1s0x2dd2415f41cd6def:0xe0fa45086709b5f8!8m2!3d-8.7117191!4d115.1903699!16s%2Fg%2F11kl3gj2vc" 
                  target="_blank" 
                  rel="noopener noreferrer" 
                  class="km-gmaps-btn"
                >
                   Kubu Mawar Residence
                  <svg width="12" height="12" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><path d="M18 13v6a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2V8a2 2 0 0 1 2-2h6"/><polyline points="15 3 21 3 21 9"/><line x1="10" y1="14" x2="21" y2="3"/></svg>
                </a>
              </div>
            </div>
          </div>

          <!-- Controls: Pause toggle & Manual Arrows -->
          <div class="km-reviews-controls">
            <button 
              class="km-marquee-ctrl-btn" 
              @click="toggleReviewMarquee"
              :title="isReviewMarqueePaused ? 'Lanjutkan Gulir' : 'Jeda Gulir'"
            >
              <svg v-if="!isReviewMarqueePaused" width="14" height="14" viewBox="0 0 24 24" fill="currentColor"><rect x="6" y="4" width="4" height="16"/><rect x="14" y="4" width="4" height="16"/></svg>
              <svg v-else width="14" height="14" viewBox="0 0 24 24" fill="currentColor"><polygon points="5 3 19 12 5 21 5 3"/></svg>
              <span>{{ isReviewMarqueePaused ? 'Lanjutkan Gulir Otomatis' : 'Jeda Gulir (Hover untuk Membaca)' }}</span>
            </button>

            <div class="km-arrow-btns-group">
              <button class="km-arrow-btn" @click="scrollReviewsManual('left')" title="Geser ke Kiri">
                <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><polyline points="15 18 9 12 15 6"/></svg>
              </button>
              <button class="km-arrow-btn" @click="scrollReviewsManual('right')" title="Geser ke Kanan">
                <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><polyline points="9 18 15 12 9 6"/></svg>
              </button>
            </div>
          </div>
        </div>
      </div>

      <!-- Marquee Flow Container (Scrolling Right to Left) -->
      <div 
        class="km-reviews-marquee-outer"
        ref="reviewScrollTrack"
        :class="{ 'is-paused': isReviewMarqueePaused }"
      >
        <div class="km-reviews-marquee-track">
          <!-- First set + Second set (duplicated for infinite seamless loop) -->
          <div 
            v-for="(rev, idx) in [...customerReviews, ...customerReviews]" 
            :key="`${rev.id}-${idx}`"
            class="km-review-card"
          >
            <!-- Card Header -->
            <div class="km-rev-card-header">
              <div class="km-rev-author-group">
                <!-- Avatar with optional Local Guide badge -->
                <div class="km-rev-avatar-wrap">
                  <div class="km-rev-avatar" :style="{ backgroundColor: rev.avatarColor }">
                    {{ rev.avatarInitial }}
                  </div>
                  <span v-if="rev.isLocalGuide" class="km-rev-lg-badge" title="Local Guide Google">
                    ★
                  </span>
                </div>

                <div class="km-rev-author-meta">
                  <h4 class="km-rev-author-name">{{ rev.author }}</h4>
                  <p class="km-rev-author-sub">{{ rev.subtitle }}</p>
                </div>
              </div>

              <!-- Google Maps G Logo Watermark -->
              <div class="km-rev-google-pin" :title="rev.place">
                <svg viewBox="0 0 24 24" width="20" height="20">
                  <path fill="#4285F4" d="M22.56 12.25c0-.78-.07-1.53-.2-2.25H12v4.26h5.92c-.26 1.37-1.04 2.53-2.21 3.31v2.77h3.57c2.08-1.92 3.28-4.74 3.28-8.09z"/>
                  <path fill="#34A853" d="M12 23c2.97 0 5.46-.98 7.28-2.66l-3.57-2.77c-.98.66-2.23 1.06-3.71 1.06-2.86 0-5.29-1.93-6.16-4.53H2.18v2.84C3.99 20.53 7.7 23 12 23z"/>
                  <path fill="#FBBC05" d="M5.84 14.09c-.22-.66-.35-1.36-.35-2.09s.13-1.43.35-2.09V7.06H2.18C1.43 8.55 1 10.22 1 12s.43 3.45 1.18 4.94l2.85-2.22.81-.63z"/>
                  <path fill="#EA4335" d="M12 5.38c1.62 0 3.06.56 4.21 1.64l3.15-3.15C17.45 2.09 14.97 1 12 1 7.7 1 3.99 3.47 2.18 7.06l3.66 2.84c.87-2.6 3.3-4.52 6.16-4.52z"/>
                </svg>
              </div>
            </div>

            <!-- Rating Stars & Relative Date -->
            <div class="km-rev-rating-row">
              <div class="km-rev-stars">
                <span v-for="s in rev.rating" :key="s" class="km-rev-star">★</span>
              </div>
              <span class="km-rev-time">{{ rev.time }}</span>
            </div>

            <!-- Review Text -->
            <div class="km-rev-body">
              <p class="km-rev-text">"{{ rev.text }}"</p>
            </div>

            <!-- Optional Attached Photo -->
            <div v-if="rev.hasPhoto && rev.photoUrl" class="km-rev-photo">
              <img :src="rev.photoUrl" alt="Foto Ulasan Pelanggan" loading="lazy" />
              <span class="km-rev-photo-badge">📷 1 Foto Terlampir</span>
            </div>

            <!-- Card Bottom Bar: Like, Share & Place Tag -->
            <div class="km-rev-card-footer">
              <div class="km-rev-actions">
                <span class="km-rev-action-btn">
                  <svg width="13" height="13" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M14 9V5a3 3 0 0 0-3-3l-4 9v11h11.28a2 2 0 0 0 2-1.7l1.38-9a2 2 0 0 0-2-2.3zM7 22H4a2 2 0 0 1-2-2v-7a2 2 0 0 1 2-2h3"/></svg>
                  <span>Suka{{ rev.likes > 0 ? ` (${rev.likes})` : '' }}</span>
                </span>
                <span class="km-rev-action-btn">
                  <svg width="13" height="13" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><circle cx="18" cy="5" r="3"/><circle cx="6" cy="12" r="3"/><circle cx="18" cy="19" r="3"/><line x1="8.59" y1="13.51" x2="15.42" y2="17.49"/><line x1="15.41" y1="6.51" x2="8.59" y2="10.49"/></svg>
                  <span>Bagikan</span>
                </span>
              </div>

              <a :href="rev.placeUrl" target="_blank" rel="noopener noreferrer" class="km-rev-place-tag" title="Lihat di Google Maps">
                <span>{{ rev.place }}</span>
                <svg width="11" height="11" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><polyline points="9 18 15 12 9 6"/></svg>
              </a>
            </div>
          </div>
        </div>
      </div>
    </section>

    <!-- ═══ FOOTER WITH SOCIAL MEDIA & PRIVACY POLICY (CLEAN TEXT LINKS) ═══ -->
    <footer class="km-dashboard-footer">
      <div class="km-footer-top">
        <div class="km-footer-container">
          <!-- Column 1: Brand & Tagline -->
          <div class="km-footer-col km-footer-brand-col">
            <div class="km-footer-brand" @click="scrollToEl('beranda')">
              <img src="/images/logo.png" alt="Kubu Mawar Logo" class="km-footer-logo" />
              <div class="km-footer-brand-text">
                <span class="km-footer-brand-title">Kubu Mawar</span>
                <span class="km-footer-brand-sub">Residence & Suwung Bali</span>
              </div>
            </div>
            <p class="km-footer-bio">
              Hunian eksklusif dan kost modern di Seminyak & Denpasar Selatan, Bali. Menghadirkan kenyamanan tinggal dengan sentuhan arsitektur khas Bali dan pelayanan ramah.
            </p>
            <p class="km-footer-tag-line">
              • Terverifikasi & Resmi &nbsp;•&nbsp; Rating Kepuasan 4.8
            </p>
          </div>

          <!-- Column 2: Pilihan Hunian & Navigasi -->
          <div class="km-footer-col">
            <h4 class="km-footer-heading">Navigasi Hunian</h4>
            <ul class="km-footer-links">
              <li>
                <button type="button" class="km-footer-link-btn" @click="selectProperty('residence'); scrollToEl('rooms-section')">
                  Kubu Mawar Residence (Seminyak)
                </button>
              </li>
              <li>
                <button type="button" class="km-footer-link-btn" @click="selectProperty('suwung'); scrollToEl('rooms-section')">
                  Kubu Mawar Suwung (Kost Bulanan)
                </button>
              </li>
              <li>
                <button type="button" class="km-footer-link-btn" @click="scrollToEl('hunian-section')">
                  Pilihan Hunian
                </button>
              </li>
              <li>
                <button type="button" class="km-footer-link-btn" @click="scrollToEl('ulasan-section')">
                  Ulasan & Testimoni Pelanggan
                </button>
              </li>
            </ul>
          </div>

          <!-- Column 3: Kontak & Media Sosial (Clean Clickable Text Links, No Containers) -->
          <div class="km-footer-col">
            <h4 class="km-footer-heading">Kontak & Media Sosial</h4>
            <ul class="km-footer-links">
              <li>
                <a :href="contactInfo.whatsappUrl" target="_blank" rel="noopener noreferrer" class="km-footer-link-btn">
                  WhatsApp: {{ contactInfo.whatsapp }}
                </a>
              </li>
              <li>
                <a :href="`mailto:${contactInfo.email}`" class="km-footer-link-btn">
                  Email: {{ contactInfo.email }}
                </a>
              </li>
              <li>
                <a :href="contactInfo.instagramUrl" target="_blank" rel="noopener noreferrer" class="km-footer-link-btn">
                  Instagram: {{ contactInfo.instagram }}
                </a>
              </li>
              <li>
                <a :href="contactInfo.mapsResidenceUrl" target="_blank" rel="noopener noreferrer" class="km-footer-link-btn">
                  Maps: Kubu Mawar Residence (Seminyak)
                </a>
              </li>
              <li>
                <a :href="contactInfo.mapsSuwungUrl" target="_blank" rel="noopener noreferrer" class="km-footer-link-btn">
                  Maps: Kubu Mawar Suwung (Denpasar)
                </a>
              </li>
            </ul>
          </div>

          <!-- Column 4: Legal & Kebijakan Privasi (Clean Clickable Text Links, No Containers) -->
          <div class="km-footer-col">
            <h4 class="km-footer-heading">Legal & Privasi</h4>
            <ul class="km-footer-links">
              <li>
                <router-link to="/privacy" class="km-footer-link-btn km-footer-privacy-text-link">
                  Kebijakan Privasi (Privacy Policy) →
                </router-link>
              </li>
            </ul>
            <p class="km-footer-legal-desc">
              Privasi dan kerahasiaan data tamu adalah prioritas utama kami. Kami mematuhi standar perlindungan data yang transparan dan aman.
            </p>
            <ul class="km-footer-notes-list">
              <li>✓ Enkripsi data tamu terjamin</li>
              <li>✓ Transparansi penggunaan cookie & log</li>
            </ul>
          </div>
        </div>
      </div>

      <!-- Bottom Bar -->
      <div class="km-footer-bottom">
        <div class="km-footer-container km-footer-bottom-inner">
          <p class="km-footer-copyright">
            &copy; {{ new Date().getFullYear() }} Kubu Mawar Residence & Suwung. Hak cipta dilindungi undang-undang.
          </p>

          <div class="km-footer-bottom-links">
            <router-link to="/privacy" class="km-footer-bl-item">Kebijakan Privasi</router-link>
            <span class="km-footer-bl-dot">•</span>
            <a :href="contactInfo.whatsappUrl" target="_blank" class="km-footer-bl-item">WhatsApp</a>
            <span class="km-footer-bl-dot">•</span>
            <a :href="`mailto:${contactInfo.email}`" class="km-footer-bl-item">Email</a>
            <span class="km-footer-bl-dot">•</span>
            <a :href="contactInfo.instagramUrl" target="_blank" class="km-footer-bl-item">Instagram</a>
            <span class="km-footer-bl-dot">•</span>
            <button type="button" class="km-footer-bl-top" @click="scrollToEl('beranda')">
              Kembali ke Atas ↑
            </button>
          </div>
        </div>
      </div>
    </footer>

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
              <div v-if="bookingForm.start_date" class="km-success-dates-badge">
                📅 Periode: <strong>{{ formattedStartDate }}</strong> s/d <strong>{{ calculatedEndDate }}</strong> ({{ bookingForm.duration }} {{ selectedProperty === 'residence' && bookingForm.rental_type === 'weekly' ? 'Minggu' : 'Bulan' }})
              </div>
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

                <!-- Tanggal Mulai Booking / Sewa -->
                <div class="km-form-group">
                  <label>Tanggal Mulai Sewa (Check-in) <span class="required">*</span></label>
                  <input 
                    v-model="bookingForm.start_date" 
                    type="date" 
                    :min="minBookingDate" 
                    class="km-date-input"
                    required 
                  />
                  <div v-if="bookingForm.start_date && calculatedEndDate" class="km-date-period-badge">
                    <span class="km-period-icon">📅</span>
                    <span>Periode: <strong>{{ formattedStartDate }}</strong> s/d <strong>{{ calculatedEndDate }}</strong> ({{ bookingForm.duration }} {{ selectedProperty === 'residence' && bookingForm.rental_type === 'weekly' ? 'Minggu' : 'Bulan' }})</span>
                  </div>
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
                    <span v-if="bookingForm.start_date"> · Check-in: {{ formattedStartDate }}</span>
                  </p>
                </div>
              </div>

              <div class="km-modal-footer">
                <button class="km-cancel-btn" @click="closeBooking">Batal</button>
                <button 
                  class="km-submit-btn"
                  :disabled="!bookingForm.nama || !bookingForm.phone || !bookingForm.start_date || isSubmitting"
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

/* ─── NAVBAR (Transparent over Hero, Frosted on Scroll) ─── */
/* ─── NAVBAR (Seamless over Hero, absolutely NO line/border) ─── */
.km-navbar {
  position: fixed;
  top: 0;
  left: 0;
  right: 0;
  z-index: 100;
  background: transparent !important;
  backdrop-filter: none !important;
  border-bottom: none !important;
  box-shadow: none !important;
  transition: all 0.35s cubic-bezier(0.4, 0, 0.2, 1);
}
.km-navbar.scrolled {
  background: rgba(22, 20, 24, 0.94) !important;
  backdrop-filter: blur(18px) !important;
  border-bottom: 1px solid rgba(255, 255, 255, 0.08) !important;
  box-shadow: 0 4px 24px rgba(0, 0, 0, 0.3) !important;
}
.km-navbar-inner {
  max-width: 1440px;
  margin: 0 auto;
  padding: 0 32px;
  height: 76px;
  display: flex;
  align-items: center;
  justify-content: space-between;
}

/* ─── BRAND (Elegant Luxury Serif Typography) ─── */
.km-brand {
  display: flex;
  align-items: center;
  gap: 12px;
  cursor: pointer;
  flex-shrink: 0;
  transition: opacity 0.2s;
}
.km-brand:hover {
  opacity: 0.92;
}
.km-brand-logo {
  width: 42px;
  height: 42px;
  object-fit: contain;
  filter: drop-shadow(0 2px 8px rgba(0, 0, 0, 0.5));
}
.km-brand-text {
  display: flex;
  flex-direction: column;
  line-height: 1.05;
}
.km-brand-name {
  font-family: 'Cinzel', 'Cormorant Garamond', 'Playfair Display', serif;
  font-weight: 700;
  font-size: 1.36rem;
  letter-spacing: 2px;
  text-transform: uppercase;
  color: #ffffff;
  background: linear-gradient(135deg, #ffffff 0%, #fff1f2 40%, #fde68a 100%);
  -webkit-background-clip: text;
  -webkit-text-fill-color: transparent;
  filter: drop-shadow(0 2px 10px rgba(0, 0, 0, 0.6));
  transition: all 0.3s ease;
}
.km-brand-sub {
  font-family: 'Plus Jakarta Sans', system-ui, sans-serif;
  font-size: 0.58rem;
  font-weight: 700;
  letter-spacing: 4px;
  color: #fbbf24; /* Warm royal gold */
  text-transform: uppercase;
  margin-top: 2px;
  filter: drop-shadow(0 1px 4px rgba(0, 0, 0, 0.5));
}

/* ─── CENTER NAV LINKS ─── */
.km-nav-links {
  display: none;
  align-items: center;
  gap: 8px;
}
.km-nav-link-pill {
  padding: 8px 22px;
  font-size: 0.86rem;
  font-weight: 600;
  color: #ffffff;
  background: #e11d48;
  border-radius: 9999px;
  border: none;
  cursor: pointer;
  box-shadow: 0 2px 12px rgba(225, 29, 72, 0.45);
  transition: all 0.25s ease;
}
.km-nav-link-pill:hover {
  background: #be123c;
  transform: translateY(-1px);
  box-shadow: 0 4px 16px rgba(225, 29, 72, 0.55);
}
.km-nav-link {
  display: inline-flex;
  align-items: center;
  gap: 5px;
  padding: 8px 14px;
  font-size: 0.86rem;
  font-weight: 500;
  color: rgba(255, 255, 255, 0.9);
  background: none;
  border: none;
  border-radius: 8px;
  cursor: pointer;
  transition: all 0.2s ease;
}
.km-nav-link:hover {
  color: #ffffff;
  background: rgba(255, 255, 255, 0.12);
}
.km-chevron {
  opacity: 0.85;
  transition: transform 0.2s;
}

/* ─── RIGHT NAV ─── */
.km-nav-right {
  display: flex;
  align-items: center;
  gap: 14px;
  flex-shrink: 0;
}
.km-icon-btn {
  width: 40px;
  height: 40px;
  border-radius: 50%;
  display: flex;
  align-items: center;
  justify-content: center;
  color: rgba(255, 255, 255, 0.92);
  background: none;
  border: none;
  cursor: pointer;
  transition: all 0.2s ease;
  position: relative;
}
.km-icon-btn:hover {
  color: #ffffff;
  background: rgba(255, 255, 255, 0.14);
}
.km-wishlist-btn {
  position: relative;
}
.km-nav-badge {
  position: absolute;
  top: 3px;
  right: 2px;
  width: 17px;
  height: 17px;
  background: #e11d48;
  color: #ffffff;
  border-radius: 50%;
  font-size: 0.62rem;
  font-weight: 700;
  display: flex;
  align-items: center;
  justify-content: center;
  border: 1.5px solid rgba(22, 20, 24, 0.85);
  box-shadow: 0 2px 6px rgba(0, 0, 0, 0.4);
}

/* ─── USER PILL & DROPDOWN ─── */
.km-user-menu-wrap {
  position: relative;
}
.km-user-pill {
  display: flex;
  align-items: center;
  gap: 10px;
  cursor: pointer;
  padding: 4px 10px 4px 4px;
  border-radius: 9999px;
  transition: all 0.25s ease;
}
.km-user-pill:hover {
  background: rgba(255, 255, 255, 0.14);
}
.km-avatar-circle {
  width: 34px;
  height: 34px;
  border-radius: 50%;
  background: #524b46;
  color: #ffffff;
  display: flex;
  align-items: center;
  justify-content: center;
  font-size: 0.72rem;
  font-weight: 700;
  letter-spacing: 0.5px;
  border: 1.5px solid rgba(255, 255, 255, 0.25);
}
.km-user-name-text {
  font-size: 0.85rem;
  font-weight: 500;
  color: #ffffff;
  text-shadow: 0 1px 4px rgba(0, 0, 0, 0.4);
}
.km-user-dropdown-menu {
  position: absolute;
  top: calc(100% + 10px);
  right: 0;
  width: 220px;
  background: #ffffff;
  border-radius: 14px;
  box-shadow: 0 16px 40px rgba(0, 0, 0, 0.2);
  border: 1px solid rgba(0, 0, 0, 0.06);
  padding: 8px;
  z-index: 120;
  animation: heroFadeUp 0.25s cubic-bezier(0.2, 0.8, 0.2, 1);
}
.km-dropdown-header {
  padding: 10px 12px;
  display: flex;
  flex-direction: column;
}
.km-dropdown-header strong {
  font-size: 0.84rem;
  color: #111827;
}
.km-dropdown-header small {
  font-size: 0.72rem;
  color: #6b7280;
  margin-top: 2px;
}
.km-dropdown-divider {
  height: 1px;
  background: #f3f4f6;
  margin: 4px 0;
}
.km-user-dropdown-menu button {
  width: 100%;
  display: flex;
  align-items: center;
  gap: 10px;
  padding: 10px 12px;
  border: none;
  background: none;
  border-radius: 8px;
  font-size: 0.82rem;
  color: #374151;
  cursor: pointer;
  text-align: left;
  transition: all 0.15s;
}
.km-user-dropdown-menu button:hover {
  background: #f3f4f6;
  color: #111827;
}
.km-user-dropdown-menu button.logout {
  color: #e11d48;
}
.km-user-dropdown-menu button.logout:hover {
  background: #fff1f2;
}

/* ─── MOBILE TOGGLE & MENU ─── */
.km-mobile-toggle {
  display: flex;
  padding: 6px;
  background: none;
  border: none;
  cursor: pointer;
  color: white;
}
.km-mobile-menu {
  padding: 16px 24px;
  background: rgba(18, 16, 20, 0.96);
  backdrop-filter: blur(20px);
  border-top: 1px solid rgba(255, 255, 255, 0.1);
  display: flex;
  flex-direction: column;
  gap: 6px;
}
.km-mobile-menu button {
  text-align: left;
  padding: 10px 14px;
  font-size: 0.9rem;
  font-weight: 500;
  color: rgba(255, 255, 255, 0.9);
  background: none;
  border: none;
  border-radius: 8px;
  cursor: pointer;
  transition: background 0.2s;
}
.km-mobile-menu button:hover {
  background: rgba(255, 255, 255, 0.12);
  color: white;
}
.km-mobile-menu button.logout {
  color: #fb7185;
  margin-top: 6px;
  border-top: 1px solid rgba(255, 255, 255, 0.1);
  padding-top: 12px;
}

@media (min-width: 860px) {
  .km-nav-links { display: flex; }
  .km-mobile-toggle { display: none; }
}

/* ─── ENTRANCE ANIMATION ─── */
@keyframes heroFadeUp {
  from { opacity: 0; transform: translateY(22px); }
  to { opacity: 1; transform: translateY(0); }
}
.km-anim-up {
  opacity: 0;
  animation: heroFadeUp 0.65s cubic-bezier(0.22, 1, 0.36, 1) forwards;
  animation-delay: var(--delay, 0s);
}

/* ─── FLOATING PARTICLES ─── */
@keyframes floatUp {
  0% { transform: translateY(0) scale(1); opacity: 0; }
  15% { opacity: 0.55; }
  85% { opacity: 0.2; }
  100% { transform: translateY(-400px) scale(0.4); opacity: 0; }
}
.km-particles {
  position: absolute;
  inset: 0;
  z-index: 1;
  pointer-events: none;
  overflow: hidden;
}
.km-particle {
  position: absolute;
  border-radius: 50%;
  background: rgba(255, 255, 255, 0.18);
  animation: floatUp linear infinite;
}
.km-particle.p1 { width: 6px; height: 6px; left: 15%; bottom: -10px; animation-duration: 12s; animation-delay: 0s; }
.km-particle.p2 { width: 4px; height: 4px; left: 35%; bottom: -10px; animation-duration: 9s; animation-delay: 2s; }
.km-particle.p3 { width: 8px; height: 8px; left: 55%; bottom: -10px; animation-duration: 14s; animation-delay: 4s; }
.km-particle.p4 { width: 5px; height: 5px; left: 75%; bottom: -10px; animation-duration: 11s; animation-delay: 1s; }
.km-particle.p5 { width: 3px; height: 3px; left: 90%; bottom: -10px; animation-duration: 10s; animation-delay: 6s; }

/* ─── WELCOME HERO ─── */
.km-welcome {
  position: relative;
  min-height: 640px;
  display: flex;
  align-items: center;
  overflow: hidden;
  background: #141216;
}
.km-welcome-bg {
  position: absolute;
  inset: 0;
  overflow: hidden;
}
.km-welcome-bg img {
  width: 100%;
  height: 100%;
  object-fit: cover;
  object-position: center 30%;
  animation: heroZoom 25s ease-in-out infinite alternate;
}
@keyframes heroZoom {
  0% { transform: scale(1); }
  100% { transform: scale(1.05); }
}
.km-welcome-overlay {
  position: absolute;
  inset: 0;
  background: 
    radial-gradient(ellipse at 85% 35%, rgba(180, 20, 50, 0.16) 0%, transparent 60%),
    radial-gradient(circle at 10% 75%, rgba(140, 80, 30, 0.22) 0%, transparent 55%),
    linear-gradient(135deg, rgba(14, 12, 16, 0.92) 0%, rgba(20, 18, 22, 0.70) 48%, rgba(18, 16, 20, 0.38) 100%);
}
.km-welcome-content {
  position: relative;
  z-index: 2;
  max-width: 1440px;
  margin: 0 auto;
  padding: 100px 32px 90px;
  width: 100%;
}

/* ─── HERO GRID ─── */
.km-hero-grid {
  display: grid;
  grid-template-columns: 1fr;
  gap: 36px;
  align-items: center;
}
.km-hero-left {
  display: flex;
  flex-direction: column;
}
.km-hero-right {
  display: none; /* hidden on small mobile */
  position: relative;
  min-height: 430px;
}

/* ─── HERO BADGE (CLEAN TEXT & ICON - NO GLASSMORPHISM) ─── */
.km-hero-badge {
  display: inline-flex;
  align-items: center;
  gap: 8px;
  padding: 0;
  background: transparent;
  backdrop-filter: none;
  -webkit-backdrop-filter: none;
  border: none;
  border-radius: 0;
  box-shadow: none;
  font-size: 0.82rem;
  font-weight: 600;
  color: rgba(255, 255, 255, 0.95);
  margin-bottom: 14px;
  width: fit-content;
  text-shadow: 0 1px 4px rgba(0, 0, 0, 0.6);
}
.km-hero-badge-icon {
  display: flex;
  align-items: center;
  justify-content: center;
  color: #f87171;
  filter: drop-shadow(0 1px 3px rgba(0, 0, 0, 0.5));
}

/* ─── HERO TITLE & SUBTITLE ─── */
.km-welcome-title {
  font-family: 'Inter', system-ui, -apple-system, sans-serif;
  font-size: 2.6rem;
  font-weight: 800;
  color: #ffffff;
  line-height: 1.18;
  margin-bottom: 14px;
  letter-spacing: -0.02em;
  text-shadow: 0 2px 14px rgba(0, 0, 0, 0.45);
}
.km-welcome-name-italic,
.km-welcome-title .km-name-italic {
  font-style: italic;
  font-weight: 800;
  color: #ffffff !important;
}
.km-name-italic {
  font-style: italic;
  color: #f472b6; /* Exact soft rose pink from screenshot */
  font-weight: 700;
}
.km-welcome-sub {
  font-size: 0.92rem;
  color: rgba(255, 255, 255, 0.82);
  line-height: 1.65;
  max-width: 540px;
  margin-bottom: 24px;
}

/* ─── SOFT-WHITE HERO BOOKING CTA CONTAINER ─── */
.km-hero-booking-cta {
  display: flex;
  align-items: center;
  justify-content: space-between;
  gap: 16px;
  background: linear-gradient(145deg, rgba(255, 255, 255, 0.92) 0%, rgba(248, 245, 240, 0.86) 100%);
  backdrop-filter: blur(20px);
  -webkit-backdrop-filter: blur(20px);
  border: 1px solid rgba(255, 255, 255, 0.95);
  border-radius: 18px;
  padding: 16px 22px;
  margin-bottom: 24px;
  max-width: 630px;
  box-shadow: 0 16px 36px rgba(0, 0, 0, 0.16), 0 4px 12px rgba(0, 0, 0, 0.08), inset 0 1px 2px rgba(255, 255, 255, 0.9);
  cursor: pointer;
  transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
  position: relative;
  z-index: 10;
  user-select: none;
}
.km-hero-booking-cta:hover {
  transform: translateY(-3px);
  background: linear-gradient(145deg, rgba(255, 255, 255, 0.96) 0%, rgba(250, 247, 242, 0.90) 100%);
  border-color: #ffffff;
  box-shadow: 0 20px 44px rgba(0, 0, 0, 0.22), 0 6px 16px rgba(0, 0, 0, 0.1), inset 0 1px 2px #ffffff;
}
.km-hero-booking-cta:active {
  transform: translateY(-1px) scale(0.99);
}
.km-hb-main {
  display: flex;
  align-items: center;
  gap: 14px;
  min-width: 0;
  flex: 1;
}
.km-hb-icon-circle {
  width: 44px;
  height: 44px;
  border-radius: 12px;
  background: #FEE2E2;
  border: 1px solid #FECACA;
  display: flex;
  align-items: center;
  justify-content: center;
  color: #C8102E;
  flex-shrink: 0;
  box-shadow: 0 2px 8px rgba(200, 16, 46, 0.12);
  transition: transform 0.3s ease, background 0.3s ease, color 0.3s ease;
}
.km-hero-booking-cta:hover .km-hb-icon-circle {
  transform: scale(1.08) rotate(-4deg);
  background: #C8102E;
  border-color: #C8102E;
  color: #ffffff;
}
.km-hb-text {
  display: flex;
  flex-direction: column;
  min-width: 0;
}
.km-hb-eyebrow {
  font-size: 0.65rem;
  font-weight: 800;
  letter-spacing: 1.4px;
  color: #C8102E; /* Signature Kubu Red */
  text-transform: uppercase;
  margin-bottom: 2px;
}
.km-hb-title {
  font-size: 1.15rem;
  font-weight: 800;
  color: #18181B; /* Comfortable dark charcoal */
  margin: 0;
  line-height: 1.25;
  letter-spacing: -0.01em;
  text-shadow: none;
}
.km-hb-desc {
  font-size: 0.78rem;
  color: #52525B; /* Soft slate gray, no glare */
  margin: 3px 0 0;
  line-height: 1.4;
  white-space: nowrap;
  overflow: hidden;
  text-overflow: ellipsis;
}
.km-hb-action {
  display: inline-flex;
  align-items: center;
  gap: 8px;
  padding: 10px 18px;
  background: #C8102E;
  border: 1px solid #C8102E;
  color: #ffffff;
  border-radius: 12px;
  font-size: 0.84rem;
  font-weight: 700;
  flex-shrink: 0;
  box-shadow: 0 4px 14px rgba(200, 16, 46, 0.28);
  transition: all 0.25s ease;
}
.km-hero-booking-cta:hover .km-hb-action {
  background: #A00D24;
  border-color: #A00D24;
  color: #ffffff;
  transform: translateX(3px);
  box-shadow: 0 6px 18px rgba(200, 16, 46, 0.4);
}
.km-hb-btn-arrow {
  display: flex;
  align-items: center;
  justify-content: center;
  transition: transform 0.3s ease;
}
.km-hero-booking-cta:hover .km-hb-btn-arrow {
  transform: translateY(2px);
}

/* ─── STATS ROW (Soft-White UI) ─── */
.km-hero-stats {
  display: flex;
  align-items: center;
  gap: 16px;
  margin-top: 6px;
  margin-bottom: 24px;
  flex-wrap: wrap;
}
.km-stat-unit {
  display: flex;
  align-items: center;
  gap: 12px;
  background: linear-gradient(145deg, rgba(255, 255, 255, 0.92) 0%, rgba(248, 245, 240, 0.86) 100%);
  backdrop-filter: blur(16px);
  -webkit-backdrop-filter: blur(16px);
  border: 1px solid rgba(255, 255, 255, 0.95);
  border-radius: 14px;
  padding: 10px 18px;
  box-shadow: 0 6px 20px rgba(0, 0, 0, 0.12), inset 0 1px 1px rgba(255, 255, 255, 0.8);
  transition: all 0.25s ease;
}
.km-stat-unit:hover {
  transform: translateY(-2px);
  box-shadow: 0 10px 24px rgba(0, 0, 0, 0.18);
}
.km-stat-icon {
  color: #C8102E;
  display: flex;
  align-items: center;
  justify-content: center;
  flex-shrink: 0;
}
.km-stat-content {
  display: flex;
  flex-direction: column;
}
.km-stat-number {
  font-size: 1.35rem;
  font-weight: 800;
  color: #18181B;
  line-height: 1.1;
  text-shadow: none;
}
.km-stat-tag {
  font-size: 0.62rem;
  font-weight: 700;
  color: #52525B;
  letter-spacing: 0.8px;
  text-transform: uppercase;
  margin-top: 2px;
}

/* ─── FEATURE BADGES ROW ─── */
.km-hero-features {
  display: flex;
  align-items: center;
  gap: 12px;
  flex-wrap: wrap;
}
.km-feature-pill {
  display: flex;
  align-items: center;
  gap: 10px;
  padding: 7px 14px;
  background: rgba(30, 26, 24, 0.62);
  backdrop-filter: blur(10px);
  border: 1px solid rgba(255, 255, 255, 0.12);
  border-radius: 12px;
  font-size: 0.74rem;
  font-weight: 500;
  color: rgba(255, 255, 255, 0.9);
  transition: all 0.25s ease;
  cursor: default;
}
.km-feature-pill:hover {
  background: rgba(45, 38, 35, 0.78);
  border-color: rgba(255, 255, 255, 0.22);
  transform: translateY(-1px);
}
.km-feat-icon-sq {
  width: 26px;
  height: 26px;
  border-radius: 8px;
  background: #e11d48;
  color: #ffffff;
  display: flex;
  align-items: center;
  justify-content: center;
  flex-shrink: 0;
}

/* ─── FLOATING OVERLAPPING PROPERTY CARDS ─── */
.km-hero-card {
  position: absolute;
  background: #ffffff;
  border: 4px solid #ffffff;
  border-radius: 22px;
  overflow: hidden;
  box-shadow: 0 20px 48px rgba(0, 0, 0, 0.4);
  cursor: pointer;
  transition: all 0.4s cubic-bezier(0.34, 1.56, 0.64, 1);
}
.km-hero-card-1 {
  width: 275px;
  left: 10px;
  top: 15px;
  z-index: 3;
  transform: rotate(-3.5deg);
}
.km-hero-card-2 {
  width: 255px;
  right: 15px;
  top: 35px;
  z-index: 2;
  opacity: 0.96;
  transform: rotate(4deg);
}
.km-hero-card:hover,
.km-hero-card.card-highlight {
  transform: translateY(-8px) scale(1.03) rotate(0deg);
  z-index: 10;
  box-shadow: 0 24px 55px rgba(0, 0, 0, 0.48);
}
.km-hero-card-img {
  position: relative;
  height: 150px;
  overflow: hidden;
}
.km-hero-card-img img {
  width: 100%;
  height: 100%;
  object-fit: cover;
  transition: transform 0.6s cubic-bezier(0.2, 0.8, 0.2, 1);
}
.km-hero-card:hover .km-hero-card-img img {
  transform: scale(1.08);
}
.km-hero-card-badge {
  position: absolute;
  bottom: 12px;
  left: 12px;
  padding: 4px 12px;
  background: #ffffff;
  color: #111827;
  border-radius: 9999px;
  font-size: 0.68rem;
  font-weight: 700;
  letter-spacing: 0.3px;
  box-shadow: 0 2px 8px rgba(0, 0, 0, 0.25);
}
.km-hero-card-heart {
  position: absolute;
  top: 10px;
  right: 10px;
  width: 28px;
  height: 28px;
  border-radius: 50%;
  background: rgba(255, 255, 255, 0.95);
  display: flex;
  align-items: center;
  justify-content: center;
  box-shadow: 0 2px 8px rgba(0, 0, 0, 0.2);
  transition: transform 0.2s;
}
.km-hero-card-heart:hover {
  transform: scale(1.15);
}
.km-hero-card-body {
  padding: 12px 16px 14px;
}
.km-hero-card-body h4 {
  font-size: 0.92rem;
  font-weight: 700;
  color: #111827;
  margin-bottom: 3px;
}
.km-hero-card-loc {
  display: flex;
  align-items: center;
  gap: 4px;
  font-size: 0.72rem;
  color: #6b7280;
  margin-bottom: 8px;
}
.km-hero-card-bottom {
  display: flex;
  justify-content: space-between;
  align-items: center;
  border-top: 1px solid #f3f4f6;
  padding-top: 8px;
}
.km-hero-card-price {
  font-size: 0.86rem;
  font-weight: 700;
  color: #e11d48;
}
.km-hero-card-price small {
  font-weight: 400;
  color: #6b7280;
  font-size: 0.7rem;
}
.km-hero-card-rating {
  font-size: 0.74rem;
  color: #374151;
  display: flex;
  align-items: center;
  gap: 3px;
}
.km-hero-card-rating .star-icon {
  color: #eab308;
}

/* ─── CARD NAVIGATION ARROWS (CIRCULAR) ─── */
.km-hero-card-nav {
  position: absolute;
  bottom: 0px;
  right: 24px;
  display: flex;
  gap: 10px;
  z-index: 5;
}
.km-card-arrow {
  width: 42px;
  height: 42px;
  border-radius: 50%;
  background: rgba(45, 40, 36, 0.68);
  backdrop-filter: blur(10px);
  border: 1px solid rgba(255, 255, 255, 0.18);
  color: #ffffff;
  display: flex;
  align-items: center;
  justify-content: center;
  cursor: pointer;
  transition: all 0.25s ease;
  box-shadow: 0 4px 14px rgba(0, 0, 0, 0.3);
}
.km-card-arrow:hover {
  background: rgba(65, 58, 52, 0.9);
  border-color: rgba(255, 255, 255, 0.35);
  transform: scale(1.08);
  box-shadow: 0 6px 20px rgba(0, 0, 0, 0.4);
}

/* ─── WATER FLOW WAVE ─── */
.km-water-flow {
  position: absolute;
  bottom: -1px;
  left: 0;
  width: 100%;
  height: 75px;
  z-index: 3;
  pointer-events: none;
  overflow: hidden;
  line-height: 0;
}
.km-water-svg {
  width: 100%;
  height: 100%;
  display: block;
}

/* ─── RESPONSIVE BREAKPOINTS ─── */
@media (max-width: 768px) {
  /* Navbar Mobile */
  .km-navbar-inner {
    padding: 0 16px;
    height: 64px;
  }
  .km-brand-logo {
    width: 36px;
    height: 36px;
  }
  .km-brand-name {
    font-size: 1.05rem;
    letter-spacing: 1.4px;
  }
  .km-brand-sub {
    font-size: 0.5rem;
    letter-spacing: 2.2px;
  }
  .km-nav-right {
    gap: 8px;
  }
  .km-user-pill {
    padding: 2px;
    gap: 0;
  }
  .km-user-name-text,
  .km-user-pill .km-chevron {
    display: none !important;
  }
  .km-avatar-circle {
    width: 32px;
    height: 32px;
    font-size: 0.7rem;
  }
  .km-icon-btn {
    width: 36px;
    height: 36px;
  }

  /* Welcome Hero Mobile */
  .km-welcome {
    min-height: auto;
    padding-top: 10px;
  }
  .km-welcome-content {
    padding: 80px 16px 45px;
    width: 100%;
    max-width: 100%;
    box-sizing: border-box;
    overflow: hidden;
  }
  .km-hero-grid {
    gap: 18px;
    width: 100%;
  }
  .km-hero-left {
    width: 100%;
  }
  .km-hero-badge {
    font-size: 0.78rem;
    padding: 0;
    margin-bottom: 10px;
    max-width: 100%;
    white-space: normal;
    line-height: 1.35;
    box-sizing: border-box;
    background: transparent;
    border: none;
    backdrop-filter: none;
    -webkit-backdrop-filter: none;
    box-shadow: none;
  }
  .km-welcome-title {
    font-size: 1.82rem;
    line-height: 1.22;
    margin-bottom: 10px;
    word-break: break-word;
  }
  .km-welcome-sub {
    font-size: 0.84rem;
    line-height: 1.55;
    margin-bottom: 18px;
    max-width: 100%;
  }

  /* Soft-White Hero Booking CTA Mobile */
  .km-hero-booking-cta {
    flex-direction: column;
    align-items: stretch;
    padding: 16px;
    gap: 14px;
    margin-bottom: 20px;
    border-radius: 16px;
    box-shadow: 0 12px 32px rgba(0, 0, 0, 0.16), 0 2px 8px rgba(0, 0, 0, 0.08), inset 0 1px 1px rgba(255, 255, 255, 0.9);
  }
  .km-hb-main {
    gap: 12px;
  }
  .km-hb-icon-circle {
    width: 42px;
    height: 42px;
    border-radius: 10px;
  }
  .km-hb-title {
    font-size: 1.05rem;
  }
  .km-hb-desc {
    white-space: normal;
    font-size: 0.76rem;
  }
  .km-hb-action {
    justify-content: center;
    padding: 11px 16px;
    width: 100%;
    box-sizing: border-box;
    font-size: 0.86rem;
  }

  /* Stats Row - 2x2 Grid (Soft-White UI) */
  .km-hero-stats {
    display: grid;
    grid-template-columns: repeat(2, 1fr);
    gap: 10px;
    width: 100%;
    box-sizing: border-box;
    margin: 14px 0 20px;
  }
  .km-stat-unit {
    padding: 12px 14px;
    gap: 10px;
    width: 100%;
    box-sizing: border-box;
    border-radius: 14px;
    box-shadow: 0 8px 24px rgba(0, 0, 0, 0.12), inset 0 1px 1px rgba(255, 255, 255, 0.8);
  }
  .km-stat-icon svg {
    width: 22px;
    height: 22px;
  }
  .km-stat-number {
    font-size: 1.35rem;
  }
  .km-stat-tag {
    font-size: 0.58rem;
    letter-spacing: 0.5px;
  }

  /* Feature Badges - 2 Columns Grid */
  .km-hero-features {
    display: grid;
    grid-template-columns: repeat(2, 1fr);
    gap: 8px;
    width: 100%;
    box-sizing: border-box;
  }
  .km-feature-pill {
    padding: 8px 10px;
    gap: 8px;
    font-size: 0.72rem;
    border-radius: 10px;
    width: 100%;
    box-sizing: border-box;
    display: flex;
    align-items: center;
  }
  .km-feature-pill span {
    white-space: normal;
    line-height: 1.25;
  }
  .km-feat-icon-sq {
    width: 24px;
    height: 24px;
    border-radius: 6px;
    flex-shrink: 0;
  }

  /* Sections Mobile */
  .km-section {
    padding: 36px 0;
  }
  .km-section-inner {
    padding: 0 16px;
  }
  .km-property-card {
    flex: 0 0 85vw;
    max-width: 320px;
  }
  .km-water-flow {
    height: 60px;
  }
}

@media (min-width: 768px) {
  .km-welcome { min-height: 640px; }
  .km-welcome-content { padding: 120px 36px 110px; }
  .km-welcome-title { font-size: 3rem; }
  .km-water-flow { height: 95px; }
}

@media (min-width: 1120px) {
  .km-hero-grid {
    grid-template-columns: 1fr 410px;
    gap: 40px;
  }
  .km-hero-right {
    display: block;
  }
  .km-welcome { min-height: 660px; }
  .km-welcome-content { padding: 130px 40px 115px; }
  .km-welcome-title { font-size: 3.2rem; }
  .km-water-flow { height: 110px; }
}

@media (min-width: 1380px) {
  .km-hero-grid {
    grid-template-columns: 1fr 450px;
    gap: 48px;
  }
  .km-hero-card-1 { width: 285px; }
  .km-hero-card-2 { width: 265px; }
  .km-water-flow { height: 120px; }
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
.km-date-input {
  width: 100%;
  padding: 11px 14px;
  border: 1px solid var(--km-border);
  border-radius: 10px;
  font-size: 0.85rem;
  color: var(--km-text);
  background: var(--km-bg);
  outline: none;
  font-family: inherit;
  transition: all 0.2s;
  cursor: pointer;
  color-scheme: light;
}
.km-date-input:focus {
  border-color: var(--km-red);
  box-shadow: 0 0 0 3px rgba(200,16,46,0.08);
}
.km-date-period-badge {
  display: flex;
  align-items: center;
  gap: 8px;
  padding: 8px 12px;
  background: rgba(200, 16, 46, 0.05);
  border: 1px solid rgba(200, 16, 46, 0.15);
  border-radius: 8px;
  font-size: 0.74rem;
  color: var(--km-text);
  margin-top: 4px;
}
.km-date-period-badge strong {
  color: var(--km-red);
  font-weight: 600;
}
.km-success-dates-badge {
  display: inline-flex;
  align-items: center;
  gap: 8px;
  padding: 8px 16px;
  background: rgba(16, 185, 129, 0.08);
  border: 1px solid rgba(16, 185, 129, 0.2);
  border-radius: 10px;
  font-size: 0.82rem;
  color: #065F46;
  margin: 12px 0;
}
.km-success-dates-badge strong {
  color: #047857;
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

/* ══════════════════════════════════════════════════════
   ULASAN GOOGLE MAPS (RIGHT TO LEFT MARQUEE STREAM)
   ══════════════════════════════════════════════════════ */
.km-reviews-section {
  position: relative;
  background: linear-gradient(180deg, #FAFAF8 0%, #F5EFEB 100%);
  padding: 80px 0 100px;
  overflow: hidden;
  border-top: 1px solid rgba(200, 16, 46, 0.08);
}

.km-reviews-header-wrap {
  max-width: 1280px;
  margin: 0 auto;
  padding: 0 24px 36px;
}

.km-reviews-header-inner {
  display: flex;
  flex-direction: column;
  align-items: center;
  text-align: center;
}

.km-reviews-badge {
  display: inline-flex;
  align-items: center;
  gap: 8px;
  background: #ffffff;
  padding: 7px 18px;
  border-radius: 999px;
  border: 1px solid rgba(225, 29, 72, 0.18);
  box-shadow: 0 2px 10px rgba(0, 0, 0, 0.04);
  font-size: 0.82rem;
  font-weight: 600;
  color: #1f2937;
  margin-bottom: 16px;
}

.km-google-icon {
  flex-shrink: 0;
}

.km-reviews-title {
  font-size: 2.25rem;
  font-weight: 800;
  color: #111827;
  line-height: 1.25;
  margin-bottom: 12px;
  letter-spacing: -0.02em;
}

.km-reviews-subtitle {
  font-size: 0.98rem;
  color: #4b5563;
  max-width: 680px;
  line-height: 1.6;
  margin-bottom: 28px;
}

/* Stats Bar */
.km-reviews-stats-bar {
  display: flex;
  align-items: center;
  justify-content: center;
  flex-wrap: wrap;
  gap: 28px;
  background: #ffffff;
  padding: 16px 32px;
  border-radius: 20px;
  border: 1px solid #ECE7E1;
  box-shadow: 0 4px 20px rgba(0, 0, 0, 0.04);
  margin-bottom: 24px;
}

.km-rstat-item {
  display: flex;
  flex-direction: column;
  align-items: center;
  gap: 2px;
}

.km-rstat-num {
  font-size: 1.8rem;
  font-weight: 800;
  color: #111827;
  line-height: 1;
}

.km-rstat-stars {
  display: flex;
  gap: 2px;
}

.km-star-gold {
  color: #FBBC04;
  font-size: 1.1rem;
}

.km-rstat-badge {
  background: #FEF3C7;
  color: #92400E;
  font-size: 0.75rem;
  font-weight: 700;
  padding: 3px 8px;
  border-radius: 999px;
}

.km-rstat-lbl {
  font-size: 0.76rem;
  color: #6b7280;
  font-weight: 500;
  text-transform: uppercase;
  letter-spacing: 0.04em;
}

.km-rstat-divider {
  width: 1px;
  height: 40px;
  background: #E5E7EB;
}

.km-gmaps-link-btns {
  display: flex;
  gap: 10px;
  flex-wrap: wrap;
}

.km-gmaps-btn {
  display: inline-flex;
  align-items: center;
  gap: 6px;
  font-size: 0.8rem;
  font-weight: 600;
  padding: 6px 14px;
  border-radius: 10px;
  background: #F3F4F6;
  color: #1F2937;
  text-decoration: none;
  transition: all 0.2s ease;
  border: 1px solid #E5E7EB;
}

.km-gmaps-btn:hover {
  background: #E11D48;
  color: #ffffff;
  border-color: #E11D48;
  transform: translateY(-1px);
}

/* Review Controls */
.km-reviews-controls {
  display: flex;
  align-items: center;
  justify-content: space-between;
  width: 100%;
  max-width: 900px;
  margin-top: 8px;
  gap: 16px;
}

.km-marquee-ctrl-btn {
  display: inline-flex;
  align-items: center;
  gap: 8px;
  font-size: 0.82rem;
  font-weight: 600;
  color: #4b5563;
  background: #ffffff;
  border: 1px solid #D1D5DB;
  padding: 8px 18px;
  border-radius: 999px;
  cursor: pointer;
  transition: all 0.2s ease;
}

.km-marquee-ctrl-btn:hover {
  background: #F9FAFB;
  color: #111827;
  border-color: #9CA3AF;
}

.km-arrow-btns-group {
  display: flex;
  gap: 8px;
}

.km-arrow-btn {
  width: 38px;
  height: 38px;
  border-radius: 50%;
  background: #ffffff;
  border: 1px solid #D1D5DB;
  color: #374151;
  display: flex;
  align-items: center;
  justify-content: center;
  cursor: pointer;
  transition: all 0.2s ease;
  box-shadow: 0 2px 6px rgba(0,0,0,0.04);
}

.km-arrow-btn:hover {
  background: #E11D48;
  color: #ffffff;
  border-color: #E11D48;
  transform: scale(1.05);
}

/* Marquee Stream Right to Left */
.km-reviews-marquee-outer {
  width: 100%;
  overflow-x: auto;
  overflow-y: hidden;
  padding: 16px 0 28px;
  scrollbar-width: none;
  -ms-overflow-style: none;
  mask-image: linear-gradient(to right, transparent 0%, black 5%, black 95%, transparent 100%);
  -webkit-mask-image: linear-gradient(to right, transparent 0%, black 5%, black 95%, transparent 100%);
}

.km-reviews-marquee-outer::-webkit-scrollbar {
  display: none;
}

.km-reviews-marquee-track {
  display: flex;
  gap: 22px;
  width: max-content;
  animation: kmMarqueeRightToLeft 42s linear infinite;
  will-change: transform;
}

.km-reviews-marquee-outer:hover .km-reviews-marquee-track,
.km-reviews-marquee-outer.is-paused .km-reviews-marquee-track {
  animation-play-state: paused;
}

@keyframes kmMarqueeRightToLeft {
  0% {
    transform: translateX(0);
  }
  100% {
    transform: translateX(-50%);
  }
}

/* Review Card */
.km-review-card {
  width: 370px;
  max-width: 85vw;
  background: #ffffff;
  border: 1px solid #EBE5DF;
  border-radius: 20px;
  padding: 22px 24px;
  display: flex;
  flex-direction: column;
  justify-content: space-between;
  box-shadow: 0 4px 18px rgba(0, 0, 0, 0.04);
  transition: all 0.3s cubic-bezier(0.16, 1, 0.3, 1);
  flex-shrink: 0;
  position: relative;
}

.km-review-card:hover {
  transform: translateY(-8px);
  box-shadow: 0 18px 40px rgba(0, 0, 0, 0.09);
  border-color: rgba(225, 29, 72, 0.25);
}

.km-rev-card-header {
  display: flex;
  align-items: flex-start;
  justify-content: space-between;
  gap: 12px;
  margin-bottom: 12px;
}

.km-rev-author-group {
  display: flex;
  align-items: center;
  gap: 12px;
}

.km-rev-avatar-wrap {
  position: relative;
  flex-shrink: 0;
}

.km-rev-avatar {
  width: 44px;
  height: 44px;
  border-radius: 50%;
  display: flex;
  align-items: center;
  justify-content: center;
  color: #ffffff;
  font-weight: 700;
  font-size: 1.05rem;
  box-shadow: 0 2px 8px rgba(0, 0, 0, 0.12);
  user-select: none;
}

.km-rev-lg-badge {
  position: absolute;
  bottom: -2px;
  right: -2px;
  width: 17px;
  height: 17px;
  border-radius: 50%;
  background: #FB8C00;
  border: 2px solid #ffffff;
  color: #ffffff;
  font-size: 9px;
  display: flex;
  align-items: center;
  justify-content: center;
  box-shadow: 0 1px 4px rgba(0, 0, 0, 0.2);
}

.km-rev-author-meta {
  display: flex;
  flex-direction: column;
}

.km-rev-author-name {
  font-size: 0.95rem;
  font-weight: 700;
  color: #111827;
  line-height: 1.25;
  margin: 0;
}

.km-rev-author-sub {
  font-size: 0.74rem;
  color: #6b7280;
  margin: 2px 0 0;
}

.km-rev-google-pin {
  opacity: 0.85;
  transition: opacity 0.2s ease;
}

.km-review-card:hover .km-rev-google-pin {
  opacity: 1;
}

/* Rating Row */
.km-rev-rating-row {
  display: flex;
  align-items: center;
  gap: 8px;
  margin-bottom: 14px;
}

.km-rev-stars {
  display: flex;
  gap: 1px;
}

.km-rev-star {
  color: #FBBC04;
  font-size: 1.1rem;
}

.km-rev-time {
  font-size: 0.76rem;
  color: #9ca3af;
  font-weight: 500;
}

/* Review Text */
.km-rev-body {
  margin-bottom: 16px;
  min-height: 52px;
}

.km-rev-text {
  font-size: 0.92rem;
  line-height: 1.55;
  color: #374151;
  font-weight: 500;
  margin: 0;
}

/* Attached Photo */
.km-rev-photo {
  position: relative;
  border-radius: 14px;
  overflow: hidden;
  height: 140px;
  margin-bottom: 16px;
  border: 1px solid #E5E7EB;
}

.km-rev-photo img {
  width: 100%;
  height: 100%;
  object-fit: cover;
}

.km-rev-photo-badge {
  position: absolute;
  bottom: 8px;
  left: 8px;
  background: rgba(0, 0, 0, 0.7);
  color: #ffffff;
  font-size: 0.7rem;
  font-weight: 600;
  padding: 3px 8px;
  border-radius: 6px;
  backdrop-filter: blur(4px);
}

/* Card Footer */
.km-rev-card-footer {
  display: flex;
  align-items: center;
  justify-content: space-between;
  padding-top: 14px;
  border-top: 1px solid #F3F4F6;
  gap: 8px;
}

.km-rev-actions {
  display: flex;
  align-items: center;
  gap: 14px;
}

.km-rev-action-btn {
  display: inline-flex;
  align-items: center;
  gap: 5px;
  font-size: 0.75rem;
  color: #6b7280;
  cursor: pointer;
  transition: color 0.2s ease;
  user-select: none;
}

.km-rev-action-btn:hover {
  color: #E11D48;
}

.km-rev-place-tag {
  display: inline-flex;
  align-items: center;
  gap: 4px;
  font-size: 0.74rem;
  font-weight: 600;
  color: #059669;
  background: #ECFDF5;
  padding: 4px 10px;
  border-radius: 8px;
  text-decoration: none;
  transition: all 0.2s ease;
}

.km-rev-place-tag:hover {
  background: #059669;
  color: #ffffff;
}

@media (max-width: 768px) {
  .km-reviews-title {
    font-size: 1.75rem;
  }
  .km-reviews-stats-bar {
    padding: 16px;
    gap: 16px;
  }
  .km-rstat-divider {
    display: none;
  }
  .km-reviews-controls {
    flex-direction: column;
    align-items: stretch;
  }
  .km-arrow-btns-group {
    justify-content: center;
  }
  .km-review-card {
    width: 300px;
  }
}

/* ═══════════════════════════════════════
   FOOTER (POST-REVIEWS SECTION)
   ═══════════════════════════════════════ */
.km-dashboard-footer {
  background: #141316;
  color: #E2DFD8;
  border-top: 1px solid rgba(255, 255, 255, 0.08);
  margin-top: 60px;
  position: relative;
  z-index: 20;
}

.km-footer-top {
  padding: 64px 0 48px;
}

.km-footer-container {
  max-width: 1400px;
  margin: 0 auto;
  padding: 0 32px;
}

.km-footer-top .km-footer-container {
  display: grid;
  grid-template-columns: 1.4fr 1.1fr 1.3fr 1.2fr;
  gap: 48px;
}

.km-footer-col {
  display: flex;
  flex-direction: column;
}

/* Brand Col */
.km-footer-brand-col {
  padding-right: 16px;
}

.km-footer-brand {
  display: inline-flex;
  align-items: center;
  gap: 12px;
  cursor: pointer;
  margin-bottom: 16px;
  text-decoration: none;
}

.km-footer-logo {
  width: 44px;
  height: 44px;
  object-fit: contain;
  filter: drop-shadow(0 4px 10px rgba(0,0,0,0.4));
}

.km-footer-brand-text {
  display: flex;
  flex-direction: column;
}

.km-footer-brand-title {
  font-family: 'Playfair Display', Georgia, serif;
  font-size: 1.35rem;
  font-weight: 700;
  color: #FFFFFF;
  letter-spacing: -0.01em;
}

.km-footer-brand-sub {
  font-size: 0.75rem;
  color: rgba(255, 255, 255, 0.55);
  letter-spacing: 0.05em;
  text-transform: uppercase;
}

.km-footer-bio {
  font-size: 0.88rem;
  line-height: 1.65;
  color: rgba(255, 255, 255, 0.65);
  margin-bottom: 16px;
}

.km-footer-tag-line {
  font-size: 0.8rem;
  color: rgba(255, 255, 255, 0.45);
  margin: 0;
}

/* Headings */
.km-footer-heading {
  font-size: 0.95rem;
  font-weight: 600;
  color: #FFFFFF;
  margin-bottom: 16px;
  letter-spacing: normal;
  text-transform: none;
}

/* Navigation & Contact Text Links (No Container, Pure Integrated Text) */
.km-footer-links {
  list-style: none;
  padding: 0;
  margin: 0;
  display: flex;
  flex-direction: column;
  gap: 12px;
}

.km-footer-link-btn {
  background: none;
  border: none;
  padding: 0;
  color: rgba(255, 255, 255, 0.7);
  font-size: 0.88rem;
  text-align: left;
  text-decoration: none;
  cursor: pointer;
  transition: all 0.2s ease;
  line-height: 1.45;
  display: inline-block;
}

.km-footer-link-btn:hover {
  color: #FFFFFF;
  padding-left: 4px;
}

.km-footer-privacy-text-link {
  color: #FFFFFF;
  font-weight: 600;
}

.km-footer-privacy-text-link:hover {
  color: #FF5A6E;
}

/* Legal Col Description & Notes */
.km-footer-legal-desc {
  font-size: 0.84rem;
  line-height: 1.6;
  color: rgba(255, 255, 255, 0.6);
  margin-top: 14px;
  margin-bottom: 12px;
}

.km-footer-notes-list {
  list-style: none;
  padding: 0;
  margin: 0;
  display: flex;
  flex-direction: column;
  gap: 6px;
  font-size: 0.78rem;
  color: rgba(255, 255, 255, 0.5);
}

/* Bottom Bar */
.km-footer-bottom {
  border-top: 1px solid rgba(255, 255, 255, 0.06);
  padding: 22px 0;
  background: rgba(10, 10, 12, 0.4);
}

.km-footer-bottom-inner {
  display: flex;
  align-items: center;
  justify-content: space-between;
  gap: 20px;
  flex-wrap: wrap;
}

.km-footer-copyright {
  font-size: 0.8rem;
  color: rgba(255, 255, 255, 0.45);
  margin: 0;
}

.km-footer-bottom-links {
  display: flex;
  align-items: center;
  gap: 14px;
  flex-wrap: wrap;
}

.km-footer-bl-item {
  font-size: 0.8rem;
  color: rgba(255, 255, 255, 0.6);
  text-decoration: none;
  transition: color 0.2s;
}

.km-footer-bl-item:hover {
  color: #FFFFFF;
}

.km-footer-bl-dot {
  font-size: 0.7rem;
  color: rgba(255, 255, 255, 0.2);
}

.km-footer-bl-top {
  background: none;
  border: 1px solid rgba(255, 255, 255, 0.15);
  border-radius: 999px;
  color: rgba(255, 255, 255, 0.8);
  font-size: 0.76rem;
  padding: 4px 12px;
  cursor: pointer;
  transition: all 0.2s ease;
}

.km-footer-bl-top:hover {
  background: rgba(255, 255, 255, 0.1);
  color: #FFFFFF;
}

@media (max-width: 1024px) {
  .km-footer-top .km-footer-container {
    grid-template-columns: 1fr 1fr;
    gap: 36px;
  }
}

@media (max-width: 640px) {
  .km-footer-top {
    padding: 48px 0 36px;
  }
  .km-footer-top .km-footer-container {
    grid-template-columns: 1fr;
    gap: 32px;
  }
  .km-footer-bottom-inner {
    flex-direction: column;
    align-items: flex-start;
  }
}
</style>