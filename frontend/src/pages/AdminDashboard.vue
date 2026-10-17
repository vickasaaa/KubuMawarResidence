<script setup lang="ts">
import { ref, onMounted, onUnmounted, computed } from 'vue'
import { useRouter } from 'vue-router'

const router = useRouter()

// ─── Mouse cursor ───
const mouse = ref({ x: 0, y: 0, hover: false })
const handleMouseMove = (e: MouseEvent) => {
  mouse.value.x = e.clientX
  mouse.value.y = e.clientY
}

// ─── Auth / User State ───
const user = computed(() => {
  try {
    return JSON.parse(localStorage.getItem('user') || '{}')
  } catch {
    return {}
  }
})

const handleLogout = () => {
  localStorage.removeItem('auth_token')
  localStorage.removeItem('user')
  router.push('/login')
}

const goHome = () => {
  router.push('/')
}

// ─── Admin Navigation ───
const activeTab = ref<'rooms' | 'bookings' | 'overview'>('rooms')
const propertyFilter = ref<'all' | 'residence' | 'suwung'>('all')
const statusFilter = ref<'all' | 'available' | 'occupied' | 'maintenance'>('all')

// ─── API Base ───
const API_BASE = 'http://localhost:8000/api'

// ─── Live Data ───
const rooms = ref<any[]>([])
const bookings = ref<any[]>([])
const isLoadingRooms = ref(true)
const isLoadingBookings = ref(false)
const notification = ref<{ message: string; type: 'success' | 'error' } | null>(null)

const showNotification = (message: string, type: 'success' | 'error' = 'success') => {
  notification.value = { message, type }
  setTimeout(() => {
    notification.value = null
  }, 4000)
}

// ─── Fetch Data ───
const fetchRooms = async () => {
  isLoadingRooms.value = true
  try {
    const res = await fetch(`${API_BASE}/rooms`)
    if (res.ok) {
      const data = await res.json()
      rooms.value = data.data || []
    }
  } catch (err) {
    console.error('Error fetching rooms', err)
  } finally {
    isLoadingRooms.value = false
  }
}

const fetchBookings = async () => {
  isLoadingBookings.value = true
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
      bookings.value = data.data || []
    }
  } catch (err) {
    console.error('Error fetching bookings', err)
  } finally {
    isLoadingBookings.value = false
  }
}

// ─── Computed Stats ───
const filteredRooms = computed(() => {
  return rooms.value.filter(r => {
    if (propertyFilter.value !== 'all' && r.property_type !== propertyFilter.value) {
      return false
    }
    if (statusFilter.value !== 'all' && r.status !== statusFilter.value) {
      return false
    }
    return true
  })
})

const stats = computed(() => {
  const total = rooms.value.length
  const residence = rooms.value.filter(r => r.property_type === 'residence')
  const suwung = rooms.value.filter(r => r.property_type === 'suwung')

  const resAvailable = residence.filter(r => r.status === 'available').length
  const resOccupied = residence.filter(r => r.status === 'occupied').length

  const swgAvailable = suwung.filter(r => r.status === 'available').length
  const swgOccupied = suwung.filter(r => r.status === 'occupied').length

  const activeBookings = bookings.value.filter(b => b.status === 'confirmed' || b.status === 'active').length

  return {
    total,
    residenceTotal: residence.length,
    resAvailable,
    resOccupied,
    suwungTotal: suwung.length,
    swgAvailable,
    swgOccupied,
    activeBookings,
  }
})

// ─── Quick Toggle Room Status (Kosong <-> Terisi <-> Maintenance) ───
const updatingRoomId = ref<number | null>(null)

const toggleRoomStatus = async (room: any, newStatus: 'available' | 'occupied' | 'maintenance') => {
  if (room.status === newStatus) return
  updatingRoomId.value = room.id

  try {
    const token = localStorage.getItem('auth_token')
    const res = await fetch(`${API_BASE}/rooms/${room.id}/status`, {
      method: 'PATCH',
      headers: {
        'Content-Type': 'application/json',
        'Accept': 'application/json',
        ...(token ? { 'Authorization': `Bearer ${token}` } : {})
      },
      body: JSON.stringify({ status: newStatus })
    })

    const data = await res.json()
    if (res.ok) {
      room.status = newStatus
      showNotification(`Status kamar ${room.room_number} berhasil diubah menjadi "${newStatus === 'available' ? 'KOSONG' : newStatus === 'occupied' ? 'TERISI' : 'MAINTENANCE'}".`)
      // Refresh to ensure sync
      fetchRooms()
      fetchBookings()
    } else {
      showNotification(data.message || 'Gagal mengubah status kamar.', 'error')
    }
  } catch (err: any) {
    showNotification('Gagal menghubungi server.', 'error')
  } finally {
    updatingRoomId.value = null
  }
}

// ─── Edit Room Modal State ───
const isEditModalOpen = ref(false)
const editingRoom = ref<any | null>(null)
const editForm = ref({
  name: '',
  room_number: '',
  type_label: '',
  price_weekly: null as number | null,
  price_monthly: 0,
  description: '',
  status: 'available',
})
const isSavingRoom = ref(false)

const openEditModal = (room: any) => {
  editingRoom.value = room
  editForm.value = {
    name: room.name || '',
    room_number: room.room_number || '',
    type_label: room.type_label || '',
    price_weekly: room.price_weekly || null,
    price_monthly: room.price_monthly || 0,
    description: room.description || '',
    status: room.status || 'available',
  }
  isEditModalOpen.value = true
}

const closeEditModal = () => {
  isEditModalOpen.value = false
  editingRoom.value = null
}

const saveRoomEdit = async () => {
  if (!editingRoom.value) return
  isSavingRoom.value = true

  try {
    const token = localStorage.getItem('auth_token')
    const res = await fetch(`${API_BASE}/rooms/${editingRoom.value.id}`, {
      method: 'PUT',
      headers: {
        'Content-Type': 'application/json',
        'Accept': 'application/json',
        ...(token ? { 'Authorization': `Bearer ${token}` } : {})
      },
      body: JSON.stringify(editForm.value)
    })

    const data = await res.json()
    if (res.ok) {
      showNotification(`Kamar ${editForm.value.room_number} berhasil diperbarui.`)
      closeEditModal()
      fetchRooms()
    } else {
      showNotification(data.message || 'Gagal memperbarui kamar.', 'error')
    }
  } catch {
    showNotification('Gagal menghubungi server.', 'error')
  } finally {
    isSavingRoom.value = false
  }
}

// ─── Update Booking Status (Check-out / Selesai / Batal) ───
const updateBookingStatus = async (booking: any, newStatus: string) => {
  try {
    const token = localStorage.getItem('auth_token')
    const res = await fetch(`${API_BASE}/bookings/${booking.id}/status`, {
      method: 'PATCH',
      headers: {
        'Content-Type': 'application/json',
        'Accept': 'application/json',
        ...(token ? { 'Authorization': `Bearer ${token}` } : {})
      },
      body: JSON.stringify({ status: newStatus })
    })

    if (res.ok) {
      showNotification(`Reservasi ${booking.booking_code} diubah menjadi "${newStatus}". Kamar otomatis disinkronkan.`)
      fetchBookings()
      fetchRooms() // room status may free up!
    } else {
      const err = await res.json()
      showNotification(err.message || 'Gagal mengubah status reservasi.', 'error')
    }
  } catch {
    showNotification('Gagal menghubungi server.', 'error')
  }
}

// ─── Helper Formats ───
const formatRupiah = (val: number | null | undefined) => {
  if (!val && val !== 0) return '-'
  return 'Rp ' + Number(val).toLocaleString('id-ID')
}

onMounted(() => {
  window.addEventListener('mousemove', handleMouseMove)
  fetchRooms()
  fetchBookings()
})

onUnmounted(() => {
  window.removeEventListener('mousemove', handleMouseMove)
})
</script>

<template>
  <div class="bg-[#0E0E0E] text-white selection:bg-kubu-red selection:text-white font-body min-h-screen overflow-x-hidden">
    
    <!-- Custom Cursor -->
    <div 
      class="fixed w-4 h-4 bg-kubu-red rounded-full pointer-events-none z-[9999] mix-blend-difference transition-transform duration-100 ease-out hidden lg:block"
      :style="{ transform: `translate3d(${mouse.x - 8}px, ${mouse.y - 8}px, 0) scale(${mouse.hover ? 3 : 1})` }"
    ></div>

    <!-- Notification Toast -->
    <div 
      v-if="notification"
      class="fixed top-6 right-6 z-[99999] px-5 py-3 rounded-xl shadow-2xl text-xs font-mono tracking-wide flex items-center gap-3 transition-all"
      :class="notification.type === 'error' ? 'bg-rose-900 border border-rose-700 text-white' : 'bg-emerald-900 border border-emerald-700 text-white'"
    >
      <span>{{ notification.type === 'error' ? '⚠️' : '✓' }}</span>
      <span>{{ notification.message }}</span>
    </div>

    <!-- Admin Sidebar (Original Style) -->
    <aside class="fixed left-0 top-0 w-64 h-full bg-[#141414] border-r border-white/10 flex flex-col z-40">
      <!-- Logo -->
      <div class="p-6 border-b border-white/10">
        <div class="flex items-center gap-3 cursor-pointer" @click="goHome" @mouseenter="mouse.hover=true" @mouseleave="mouse.hover=false">
          <img src="/images/logo.png" alt="Logo" class="w-8 h-8 object-contain" />
          <div>
            <span class="font-display text-sm font-medium block">Kubu Mawar</span>
            <span class="font-mono text-[10px] tracking-widest uppercase text-kubu-red-light">Admin Panel</span>
          </div>
        </div>
      </div>

      <!-- Nav Items -->
      <nav class="flex-1 p-4 space-y-1">
        <button 
          @click="activeTab = 'rooms'"
          :class="[
            'w-full flex items-center justify-between px-4 py-3 rounded-lg font-mono text-xs tracking-wider uppercase transition-colors cursor-pointer text-left',
            activeTab === 'rooms' ? 'bg-white/10 text-white font-bold' : 'text-white/40 hover:text-white hover:bg-white/5'
          ]"
        >
          <span class="flex items-center gap-3"><span>🛏️</span> Monitoring Kamar</span>
          <span class="text-[10px] bg-white/10 px-1.5 py-0.5 rounded">{{ rooms.length }}</span>
        </button>

        <button 
          @click="activeTab = 'bookings'"
          :class="[
            'w-full flex items-center justify-between px-4 py-3 rounded-lg font-mono text-xs tracking-wider uppercase transition-colors cursor-pointer text-left',
            activeTab === 'bookings' ? 'bg-white/10 text-white font-bold' : 'text-white/40 hover:text-white hover:bg-white/5'
          ]"
        >
          <span class="flex items-center gap-3"><span>📋</span> Daftar Reservasi</span>
          <span class="text-[10px] bg-kubu-red text-white px-1.5 py-0.5 rounded">{{ bookings.length }}</span>
        </button>

        <button 
          @click="activeTab = 'overview'"
          :class="[
            'w-full flex items-center justify-between px-4 py-3 rounded-lg font-mono text-xs tracking-wider uppercase transition-colors cursor-pointer text-left',
            activeTab === 'overview' ? 'bg-white/10 text-white font-bold' : 'text-white/40 hover:text-white hover:bg-white/5'
          ]"
        >
          <span class="flex items-center gap-3"><span>📊</span> Ringkasan Unit</span>
        </button>
      </nav>

      <!-- User Info & Logout -->
      <div class="p-4 border-t border-white/10">
        <div class="flex items-center gap-3 mb-4">
          <div class="w-8 h-8 rounded-full bg-kubu-red flex items-center justify-center text-white font-mono text-xs">
            {{ user.name?.charAt(0)?.toUpperCase() || 'A' }}
          </div>
          <div class="truncate">
            <p class="text-sm font-medium truncate">{{ user.name || 'Admin Kubu Mawar' }}</p>
            <p class="text-[10px] text-white/40 font-mono tracking-wider uppercase">Administrator</p>
          </div>
        </div>
        <button 
          @click="handleLogout"
          class="w-full py-2 border border-white/10 hover:border-kubu-red hover:text-kubu-red text-white/40 font-mono text-xs tracking-widest uppercase transition-all rounded-lg cursor-pointer"
          @mouseenter="mouse.hover=true" @mouseleave="mouse.hover=false"
        >
          Logout
        </button>
      </div>
    </aside>

    <!-- Main Content Area -->
    <main class="ml-64 min-h-screen flex flex-col">
      <!-- Top Bar -->
      <header class="px-8 py-5 border-b border-white/10 flex items-center justify-between bg-[#111]">
        <div>
          <h1 class="font-display text-2xl">
            {{ activeTab === 'rooms' ? 'Monitoring & Edit Status Kamar' : activeTab === 'bookings' ? 'Manajemen Reservasi Tamu' : 'Ringkasan Properti' }}
          </h1>
          <p class="text-white/40 text-xs font-light mt-0.5">
            Semua perubahan status kamar terintegrasi langsung dengan Dashboard User & Landing Page
          </p>
        </div>
        <div class="flex items-center gap-4">
          <button 
            @click="fetchRooms(); fetchBookings()" 
            class="px-3 py-1.5 border border-white/20 hover:border-white text-xs font-mono uppercase tracking-wider rounded-lg transition-colors cursor-pointer"
          >
            🔄 Sinkronkan Data
          </button>
          <div class="text-xs font-mono tracking-wider text-white/40 uppercase hidden sm:block">
            {{ new Date().toLocaleDateString('id-ID', { weekday: 'long', year: 'numeric', month: 'long', day: 'numeric' }) }}
          </div>
        </div>
      </header>

      <!-- Content Area -->
      <div class="p-8 space-y-8 flex-1">
        
        <!-- Stats Cards -->
        <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-5">
          <!-- Card 1: Total Rooms -->
          <div class="bg-[#141414] border border-white/10 rounded-xl p-5">
            <p class="font-mono text-[11px] tracking-wider uppercase text-white/40 mb-1">Total Seluruh Unit</p>
            <p class="font-display text-3xl text-white">{{ stats.total }} <span class="text-sm font-sans text-white/40">Kamar</span></p>
            <p class="text-[11px] text-white/40 mt-1">Residence + Suwung</p>
          </div>

          <!-- Card 2: Residence Rooms -->
          <div class="bg-[#141414] border border-white/10 rounded-xl p-5">
            <p class="font-mono text-[11px] tracking-wider uppercase text-kubu-red-light mb-1">Kubu Mawar Residence</p>
            <div class="flex items-baseline gap-2">
              <span class="font-display text-2xl text-emerald-400">{{ stats.resAvailable }} Kosong</span>
              <span class="text-white/40 text-xs">/ {{ stats.resOccupied }} Terisi</span>
            </div>
            <p class="text-[11px] text-white/40 mt-1">Home Stay · Mingguan & Bulanan</p>
          </div>

          <!-- Card 3: Suwung Rooms -->
          <div class="bg-[#141414] border border-white/10 rounded-xl p-5">
            <p class="font-mono text-[11px] tracking-wider uppercase text-amber-400 mb-1">Kubu Mawar Suwung</p>
            <div class="flex items-baseline gap-2">
              <span class="font-display text-2xl text-emerald-400">{{ stats.swgAvailable }} Kosong</span>
              <span class="text-white/40 text-xs">/ {{ stats.swgOccupied }} Terisi</span>
            </div>
            <p class="text-[11px] text-white/40 mt-1">Kost-Kostan · Khusus Bulanan</p>
          </div>

          <!-- Card 4: Active Bookings -->
          <div class="bg-[#141414] border border-white/10 rounded-xl p-5">
            <p class="font-mono text-[11px] tracking-wider uppercase text-white/40 mb-1">Reservasi Aktif</p>
            <p class="font-display text-3xl text-white">{{ stats.activeBookings }} <span class="text-sm font-sans text-white/40">Tamu</span></p>
            <p class="text-[11px] text-white/40 mt-1">{{ bookings.length }} total riwayat sewa</p>
          </div>
        </div>

        <!-- ═══════════ TAB 1: ROOMS MONITORING & EDIT ═══════════ -->
        <div v-if="activeTab === 'rooms'" class="space-y-6">
          
          <!-- Filter Controls -->
          <div class="flex flex-wrap items-center justify-between gap-4 bg-[#141414] p-4 rounded-xl border border-white/10">
            <!-- Property Selector -->
            <div class="flex items-center gap-2">
              <span class="text-xs font-mono uppercase text-white/50 mr-2">Pilih Properti:</span>
              <button 
                @click="propertyFilter = 'all'"
                :class="[
                  'px-3.5 py-1.5 rounded-lg text-xs font-mono uppercase tracking-wider transition-colors cursor-pointer',
                  propertyFilter === 'all' ? 'bg-white text-black font-bold' : 'text-white/50 hover:text-white bg-white/5'
                ]"
              >
                Semua ({{ rooms.length }})
              </button>
              <button 
                @click="propertyFilter = 'residence'"
                :class="[
                  'px-3.5 py-1.5 rounded-lg text-xs font-mono uppercase tracking-wider transition-colors cursor-pointer',
                  propertyFilter === 'residence' ? 'bg-kubu-red text-white font-bold' : 'text-white/50 hover:text-white bg-white/5'
                ]"
              >
                Kubu Mawar Residence (Home Stay)
              </button>
              <button 
                @click="propertyFilter = 'suwung'"
                :class="[
                  'px-3.5 py-1.5 rounded-lg text-xs font-mono uppercase tracking-wider transition-colors cursor-pointer',
                  propertyFilter === 'suwung' ? 'bg-[#8E5B23] text-white font-bold' : 'text-white/50 hover:text-white bg-white/5'
                ]"
              >
                Kubu Mawar Suwung (Kost)
              </button>
            </div>

            <!-- Status Filter -->
            <div class="flex items-center gap-2">
              <span class="text-xs font-mono uppercase text-white/50">Status:</span>
              <select 
                v-model="statusFilter"
                class="bg-black border border-white/20 text-white text-xs px-3 py-1.5 rounded-lg outline-none"
              >
                <option value="all">Semua Status</option>
                <option value="available">Kosong (Tersedia)</option>
                <option value="occupied">Terisi (Penuh)</option>
                <option value="maintenance">Maintenance</option>
              </select>
            </div>
          </div>

          <!-- Rooms Table -->
          <div class="bg-[#141414] border border-white/10 rounded-xl overflow-hidden">
            <div class="overflow-x-auto">
              <table class="w-full text-left text-xs">
                <thead class="bg-black/50 border-b border-white/10 font-mono uppercase tracking-wider text-white/50">
                  <tr>
                    <th class="py-3.5 px-4">No. Kamar</th>
                    <th class="py-3.5 px-4">Nama Kamar</th>
                    <th class="py-3.5 px-4">Properti</th>
                    <th class="py-3.5 px-4">Skema & Tarif Sewa</th>
                    <th class="py-3.5 px-4 text-center">Status Kamar (Klik Ubah)</th>
                    <th class="py-3.5 px-4 text-right">Aksi</th>
                  </tr>
                </thead>
                <tbody class="divide-y divide-white/5 font-sans">
                  <tr v-for="room in filteredRooms" :key="room.id" class="hover:bg-white/[0.02] transition-colors">
                    
                    <!-- Room Number & Image -->
                    <td class="py-4 px-4 font-mono font-bold text-white whitespace-nowrap">
                      <div class="flex items-center gap-3">
                        <img :src="room.image" class="w-10 h-10 rounded-lg object-cover" />
                        <span>{{ room.room_number }}</span>
                      </div>
                    </td>

                    <!-- Name & Subtitle -->
                    <td class="py-4 px-4">
                      <p class="font-bold text-sm text-white">{{ room.name }}</p>
                      <p class="text-[11px] text-white/40">{{ room.type_label }} · Max {{ room.max_guests }} tamu</p>
                    </td>

                    <!-- Property -->
                    <td class="py-4 px-4 whitespace-nowrap">
                      <span 
                        class="px-2.5 py-1 rounded-md text-[10px] font-mono uppercase font-bold"
                        :class="room.property_type === 'suwung' ? 'bg-[#8E5B23]/30 text-amber-300 border border-[#8E5B23]/50' : 'bg-red-950/40 text-red-300 border border-red-800/40'"
                      >
                        {{ room.property_type === 'suwung' ? 'Kubu Mawar Suwung (Kost)' : 'Kubu Mawar Residence' }}
                      </span>
                    </td>

                    <!-- Rent Pricing -->
                    <td class="py-4 px-4 whitespace-nowrap font-mono">
                      <template v-if="room.property_type === 'residence'">
                        <p class="text-kubu-red-light font-semibold">Mgg: {{ formatRupiah(room.price_weekly) }}</p>
                        <p class="text-emerald-400 font-semibold">Bln: {{ formatRupiah(room.price_monthly) }}</p>
                      </template>
                      <template v-else>
                        <p class="text-amber-400 font-semibold">Bln: {{ formatRupiah(room.price_monthly) }}</p>
                        <span class="text-[10px] text-white/30 italic">Khusus sewa bulanan</span>
                      </template>
                    </td>

                    <!-- Quick 1-Click Status Switcher -->
                    <td class="py-4 px-4 text-center whitespace-nowrap">
                      <div class="inline-flex rounded-lg border border-white/20 p-1 bg-black/40 gap-1">
                        <!-- Kosong Button -->
                        <button 
                          @click="toggleRoomStatus(room, 'available')"
                          :disabled="updatingRoomId === room.id"
                          :class="[
                            'px-2.5 py-1 text-[11px] font-mono uppercase rounded transition-colors cursor-pointer',
                            room.status === 'available' ? 'bg-emerald-600 text-white font-bold' : 'text-white/40 hover:text-white'
                          ]"
                          title="Tandai Kamar KOSONG (Tersedia)"
                        >
                          Kosong
                        </button>

                        <!-- Terisi Button -->
                        <button 
                          @click="toggleRoomStatus(room, 'occupied')"
                          :disabled="updatingRoomId === room.id"
                          :class="[
                            'px-2.5 py-1 text-[11px] font-mono uppercase rounded transition-colors cursor-pointer',
                            room.status === 'occupied' ? 'bg-rose-600 text-white font-bold' : 'text-white/40 hover:text-white'
                          ]"
                          title="Tandai Kamar TERISI (Dihuni)"
                        >
                          Terisi
                        </button>

                        <!-- Maintenance Button -->
                        <button 
                          @click="toggleRoomStatus(room, 'maintenance')"
                          :disabled="updatingRoomId === room.id"
                          :class="[
                            'px-2.5 py-1 text-[11px] font-mono uppercase rounded transition-colors cursor-pointer',
                            room.status === 'maintenance' ? 'bg-amber-600 text-white font-bold' : 'text-white/40 hover:text-white'
                          ]"
                          title="Tandai Maintenance"
                        >
                          Perbaikan
                        </button>
                      </div>
                    </td>

                    <!-- Actions -->
                    <td class="py-4 px-4 text-right whitespace-nowrap">
                      <button 
                        @click="openEditModal(room)"
                        class="px-3 py-1.5 bg-white/10 hover:bg-white text-white hover:text-black font-mono text-[11px] uppercase tracking-wider rounded-lg transition-colors cursor-pointer"
                      >
                        Edit Kamar
                      </button>
                    </td>

                  </tr>
                </tbody>
              </table>
            </div>
          </div>

        </div>

        <!-- ═══════════ TAB 2: BOOKINGS LIST ═══════════ -->
        <div v-if="activeTab === 'bookings'" class="space-y-6">
          <div class="bg-[#141414] border border-white/10 rounded-xl overflow-hidden">
            <div class="overflow-x-auto">
              <table class="w-full text-left text-xs">
                <thead class="bg-black/50 border-b border-white/10 font-mono uppercase tracking-wider text-white/50">
                  <tr>
                    <th class="py-3.5 px-4">Kode Booking</th>
                    <th class="py-3.5 px-4">Tamu</th>
                    <th class="py-3.5 px-4">Kamar & Properti</th>
                    <th class="py-3.5 px-4">Durasi & Periode</th>
                    <th class="py-3.5 px-4">Total Biaya</th>
                    <th class="py-3.5 px-4">Status</th>
                    <th class="py-3.5 px-4 text-right">Aksi</th>
                  </tr>
                </thead>
                <tbody class="divide-y divide-white/5 font-sans">
                  <tr v-for="b in bookings" :key="b.id" class="hover:bg-white/[0.02] transition-colors">
                    <td class="py-4 px-4 font-mono font-bold text-white whitespace-nowrap">
                      {{ b.booking_code }}
                    </td>
                    <td class="py-4 px-4">
                      <p class="font-bold text-white">{{ b.guest_name }}</p>
                      <p class="text-[11px] text-white/50 font-mono">{{ b.guest_email }} · {{ b.guest_phone }}</p>
                    </td>
                    <td class="py-4 px-4">
                      <p class="font-bold text-white">{{ b.room?.name || 'Unit' }} ({{ b.room?.room_number }})</p>
                      <p class="text-[11px] text-white/50 font-mono">
                        {{ b.property_type === 'suwung' ? 'Kubu Mawar Suwung' : 'Kubu Mawar Residence' }}
                      </p>
                    </td>
                    <td class="py-4 px-4 whitespace-nowrap text-xs">
                      <span class="font-bold text-white">{{ b.duration }} {{ b.rental_type === 'weekly' ? 'Minggu' : 'Bulan' }}</span>
                      <p class="text-[11px] text-white/40 font-mono">{{ b.start_date }} s/d {{ b.end_date }}</p>
                    </td>
                    <td class="py-4 px-4 whitespace-nowrap font-mono font-bold text-kubu-red-light">
                      {{ formatRupiah(b.total_price) }}
                    </td>
                    <td class="py-4 px-4 whitespace-nowrap">
                      <span 
                        class="px-2.5 py-1 rounded text-[10px] font-mono uppercase font-bold"
                        :class="[
                          b.status === 'active' || b.status === 'confirmed' ? 'bg-emerald-900/60 text-emerald-300 border border-emerald-700' :
                          b.status === 'completed' ? 'bg-blue-900/60 text-blue-300 border border-blue-700' : 'bg-gray-800 text-gray-400'
                        ]"
                      >
                        {{ b.status }}
                      </span>
                    </td>
                    <td class="py-4 px-4 text-right whitespace-nowrap">
                      <div class="flex items-center justify-end gap-2">
                        <button 
                          v-if="b.status === 'confirmed' || b.status === 'active'"
                          @click="updateBookingStatus(b, 'completed')"
                          class="px-2.5 py-1 bg-emerald-700 hover:bg-emerald-600 text-white rounded text-[10px] font-mono uppercase cursor-pointer"
                          title="Tandai Selesai / Tamu Check-out (Kamar otomatis kembali kosong)"
                        >
                          Check-out (Kosongkan)
                        </button>
                        <button 
                          v-if="b.status === 'confirmed'"
                          @click="updateBookingStatus(b, 'cancelled')"
                          class="px-2.5 py-1 bg-rose-900 hover:bg-rose-800 text-white rounded text-[10px] font-mono uppercase cursor-pointer"
                        >
                          Batal
                        </button>
                      </div>
                    </td>
                  </tr>
                </tbody>
              </table>
            </div>
          </div>
        </div>

        <!-- ═══════════ TAB 3: OVERVIEW ═══════════ -->
        <div v-if="activeTab === 'overview'" class="grid md:grid-cols-2 gap-6">
          <div class="bg-[#141414] border border-white/10 rounded-xl p-6 space-y-4">
            <h3 class="font-display text-xl text-white">Kubu Mawar Residence (Home Stay)</h3>
            <p class="text-xs text-white/60 leading-relaxed">
              Fasilitas home stay premium di Seminyak, Bali. Mendukung sistem sewa mingguan dan bulanan.
            </p>
            <div class="p-4 bg-black/40 rounded-lg border border-white/10 space-y-2 text-xs font-mono">
              <div class="flex justify-between">
                <span class="text-white/50">Total Unit:</span>
                <span class="text-white font-bold">{{ stats.residenceTotal }} Kamar</span>
              </div>
              <div class="flex justify-between">
                <span class="text-white/50">Kamar Kosong (Tersedia):</span>
                <span class="text-emerald-400 font-bold">{{ stats.resAvailable }} Kamar</span>
              </div>
              <div class="flex justify-between">
                <span class="text-white/50">Kamar Terisi:</span>
                <span class="text-rose-400 font-bold">{{ stats.resOccupied }} Kamar</span>
              </div>
              <div class="flex justify-between pt-2 border-t border-white/10">
                <span class="text-white/50">Aturan Sewa:</span>
                <span class="text-white">Per Minggu & Per Bulan</span>
              </div>
            </div>
          </div>

          <div class="bg-[#141414] border border-white/10 rounded-xl p-6 space-y-4">
            <h3 class="font-display text-xl text-white">Kubu Mawar Suwung (Kost-Kostan)</h3>
            <p class="text-xs text-white/60 leading-relaxed">
              Kost modern eksklusif di Suwung Denpasar Selatan. Menawarkan hunian jangka panjang nyaman dengan tarif bulanan terjangkau.
            </p>
            <div class="p-4 bg-black/40 rounded-lg border border-white/10 space-y-2 text-xs font-mono">
              <div class="flex justify-between">
                <span class="text-white/50">Total Unit:</span>
                <span class="text-white font-bold">{{ stats.suwungTotal }} Kamar</span>
              </div>
              <div class="flex justify-between">
                <span class="text-white/50">Kamar Kosong (Tersedia):</span>
                <span class="text-emerald-400 font-bold">{{ stats.swgAvailable }} Kamar</span>
              </div>
              <div class="flex justify-between">
                <span class="text-white/50">Kamar Terisi:</span>
                <span class="text-rose-400 font-bold">{{ stats.swgOccupied }} Kamar</span>
              </div>
              <div class="flex justify-between pt-2 border-t border-white/10">
                <span class="text-white/50">Aturan Sewa:</span>
                <span class="text-amber-400 font-bold">Khusus Per Bulan Saja</span>
              </div>
            </div>
          </div>
        </div>

      </div>
    </main>

    <!-- ═══ EDIT ROOM MODAL ═══ -->
    <div v-if="isEditModalOpen && editingRoom" class="fixed inset-0 z-50 flex items-center justify-center bg-black/80 backdrop-blur-sm p-4">
      <div class="bg-[#181818] border border-white/20 rounded-2xl max-w-lg w-full p-6 space-y-5 text-xs">
        
        <div class="flex items-center justify-between pb-3 border-b border-white/10">
          <div>
            <span class="text-[10px] font-mono uppercase text-kubu-red-light font-bold">
              {{ editingRoom.property_name }}
            </span>
            <h3 class="font-display text-xl text-white">Edit Data Kamar</h3>
          </div>
          <button @click="closeEditModal" class="text-white/40 hover:text-white text-lg p-1 cursor-pointer">✕</button>
        </div>

        <div class="space-y-3.5">
          <div class="grid grid-cols-2 gap-3">
            <div>
              <label class="block font-mono uppercase text-white/50 mb-1">Nomor Kamar</label>
              <input 
                v-model="editForm.room_number" 
                type="text" 
                class="w-full bg-black border border-white/20 px-3 py-2 rounded-lg text-white font-mono"
              />
            </div>
            <div>
              <label class="block font-mono uppercase text-white/50 mb-1">Status Kamar</label>
              <select 
                v-model="editForm.status"
                class="w-full bg-black border border-white/20 px-3 py-2 rounded-lg text-white font-mono"
              >
                <option value="available">KOSONG (Tersedia)</option>
                <option value="occupied">TERISI (Penuh)</option>
                <option value="maintenance">Maintenance</option>
              </select>
            </div>
          </div>

          <div>
            <label class="block font-mono uppercase text-white/50 mb-1">Nama Kamar</label>
            <input 
              v-model="editForm.name" 
              type="text" 
              class="w-full bg-black border border-white/20 px-3 py-2 rounded-lg text-white"
            />
          </div>

          <!-- Pricing: If Residence show Weekly + Monthly; If Suwung only Monthly -->
          <div class="grid grid-cols-2 gap-3">
            <div v-if="editingRoom.property_type === 'residence'">
              <label class="block font-mono uppercase text-white/50 mb-1">Tarif Per Minggu (Rp)</label>
              <input 
                v-model="editForm.price_weekly" 
                type="number" 
                class="w-full bg-black border border-white/20 px-3 py-2 rounded-lg text-white font-mono"
              />
            </div>
            <div :class="editingRoom.property_type === 'suwung' ? 'col-span-2' : ''">
              <label class="block font-mono uppercase text-white/50 mb-1">Tarif Per Bulan (Rp)</label>
              <input 
                v-model="editForm.price_monthly" 
                type="number" 
                class="w-full bg-black border border-white/20 px-3 py-2 rounded-lg text-white font-mono"
              />
              <p v-if="editingRoom.property_type === 'suwung'" class="text-[10px] text-white/40 mt-1 italic">
                *Kubu Mawar Suwung hanya melayani sistem sewa per bulan.
              </p>
            </div>
          </div>

          <div>
            <label class="block font-mono uppercase text-white/50 mb-1">Deskripsi Singkat</label>
            <textarea 
              v-model="editForm.description"
              rows="3"
              class="w-full bg-black border border-white/20 px-3 py-2 rounded-lg text-white"
            ></textarea>
          </div>
        </div>

        <div class="flex items-center justify-end gap-3 pt-3 border-t border-white/10">
          <button 
            @click="closeEditModal"
            class="px-4 py-2 border border-white/20 text-white/60 hover:text-white rounded-lg font-mono uppercase cursor-pointer"
          >
            Batal
          </button>
          <button 
            @click="saveRoomEdit"
            :disabled="isSavingRoom"
            class="px-5 py-2 bg-kubu-red hover:bg-kubu-red-dark text-white rounded-lg font-mono uppercase font-bold cursor-pointer disabled:opacity-50"
          >
            {{ isSavingRoom ? 'Menyimpan...' : 'Simpan Perubahan' }}
          </button>
        </div>

      </div>
    </div>

  </div>
</template>
