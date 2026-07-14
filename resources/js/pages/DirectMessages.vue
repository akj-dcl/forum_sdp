<script setup lang="ts">
import { Head, Link, usePage } from '@inertiajs/vue3'
import { ref, computed, onMounted, onBeforeUnmount } from 'vue'
import axios from 'axios'
import AppLayout from '@/layouts/AppLayout.vue'
import type { BreadcrumbItem } from '@/types'

const page = usePage()
const currentUser = computed(() => page.props.auth.user)

const breadcrumbs: BreadcrumbItem[] = [
  { title: 'Direct Messages', href: '/direct-messages' }
]

const searchContactQuery = ref('')
const contacts = ref<any[]>([])
const activeContact = ref<any | null>(null)
const messages = ref<any[]>([])
const newMessage = ref('')
const messagesContainer = ref<HTMLDivElement | null>(null)
let pollInterval: any = null

const filteredContacts = computed(() => {
  return contacts.value.filter(c => 
    c.name.toLowerCase().includes(searchContactQuery.value.toLowerCase()) ||
    (c.role && c.role.toLowerCase().includes(searchContactQuery.value.toLowerCase()))
  )
})

const fetchContacts = async () => {
  try {
    const res = await axios.get('/api/direct-messages/contacts')
    contacts.value = res.data
    if (activeContact.value) {
      const updated = res.data.find((c: any) => c.id === activeContact.value.id)
      if (updated) {
        activeContact.value.status = updated.status
      }
    }
  } catch (err) {
    console.error('Error fetching contacts', err)
  }
}

const selectContact = async (contact: any) => {
  activeContact.value = contact
  newMessage.value = ''
  await fetchHistory(contact.id)
  if (pollInterval) clearInterval(pollInterval)
  pollInterval = setInterval(() => {
    if (activeContact.value) {
      fetchHistory(activeContact.value.id, true)
    }
  }, 3000)

  setTimeout(scrollToBottom, 100)
}

const fetchHistory = async (userId: number, quiet = false) => {
  try {
    const res = await axios.get(`/api/direct-messages/history/${userId}`)
    const oldLength = messages.value.length
    messages.value = res.data
    if (messages.value.length > oldLength) {
      setTimeout(scrollToBottom, 50)
    }
  } catch (err) {
    console.error('Error fetching message history', err)
  }
}

const sendMessage = async () => {
  if (!newMessage.value.trim() || !activeContact.value) return
  
  const text = newMessage.value
  newMessage.value = ''
  
  try {
    const res = await axios.post('/api/direct-messages/send', {
      receiver_id: activeContact.value.id,
      message: text
    })
    if (res.data.success) {
      messages.value.push(res.data.message)
      setTimeout(scrollToBottom, 50)
      fetchContacts()
    }
  } catch (err) {
    console.error('Error sending message', err)
    newMessage.value = text
  }
}

const getInitials = (name: string) => {
  if (!name) return 'U'
  return name
    .split(' ')
    .map(n => n[0])
    .join('')
    .slice(0, 2)
    .toUpperCase()
}

const scrollToBottom = () => {
  if (messagesContainer.value) {
    messagesContainer.value.scrollTop = messagesContainer.value.scrollHeight
  }
}

onMounted(() => {
  fetchContacts()
  const contactsInterval = setInterval(fetchContacts, 10000)
  
  onBeforeUnmount(() => {
    clearInterval(contactsInterval)
    if (pollInterval) clearInterval(pollInterval)
  })
})
</script>

<template>
  <Head title="Direct Messages" />

  <AppLayout :breadcrumbs="breadcrumbs">
    <div class="flex-1 w-full bg-surface-container-lowest border border-outline-variant rounded-xl shadow-sm overflow-hidden flex h-[calc(100vh-200px)] min-h-[500px]">
      
      <div class="w-80 sm:w-96 border-r border-outline-variant flex flex-col bg-surface-container-lowest shrink-0">
        <div class="p-4 border-b border-outline-variant flex flex-col gap-3">
          <h2 class="text-xl font-bold text-on-surface select-none">Messages</h2>
          <div class="relative">
            <span class="material-symbols-outlined absolute left-3 top-1/2 -translate-y-1/2 text-outline text-[18px]">search</span>
            <input 
              v-model="searchContactQuery" 
              type="text" 
              class="w-full h-10 pl-10 pr-4 bg-surface-container-low border border-outline focus:border-primary rounded-lg text-sm text-on-surface focus:outline-none"
              placeholder="Search colleagues..."
            />
          </div>
        </div>

        <div class="flex-1 overflow-y-auto p-2 divide-y divide-outline-variant/30">
          <div 
            v-for="contact in filteredContacts" 
            :key="contact.id" 
            @click="selectContact(contact)"
            :class="[
              'flex items-center gap-3 p-3 rounded-lg cursor-pointer transition-colors group',
              activeContact?.id === contact.id ? 'bg-primary/5' : 'hover:bg-surface-container-low'
            ]"
          >
            <div class="relative shrink-0 select-none">
              <div class="w-11 h-11 rounded-full bg-primary-container text-on-primary-container flex items-center justify-center font-bold text-sm shrink-0 border border-outline-variant overflow-hidden">
                <img v-if="contact.avatar" :src="contact.avatar" class="w-full h-full object-cover" />
                <span v-else>{{ getInitials(contact.name) }}</span>
              </div>
              <div :class="['absolute bottom-0 right-0 w-3 h-3 rounded-full border-2 border-surface-container-lowest', contact.status === 'online' ? 'bg-green-500' : 'bg-outline-variant']"></div>
            </div>

            <div class="flex-1 min-w-0">
              <div class="flex justify-between items-baseline mb-0.5">
                <h4 :class="['font-label-md truncate transition-colors group-hover:text-primary', activeContact?.id === contact.id ? 'text-primary font-bold' : 'text-on-surface font-semibold']">
                  {{ contact.name }}
                </h4>
                <span v-if="contact.last_message_time" class="text-[10px] text-on-surface-variant font-medium select-none shrink-0">{{ contact.last_message_time }}</span>
              </div>
              <p class="text-xs text-on-surface-variant truncate">{{ contact.last_message || contact.role }}</p>
            </div>

            <div v-if="contact.unread_count > 0" class="bg-error text-on-error font-bold text-[10px] px-2 py-0.5 rounded-full shrink-0">
              {{ contact.unread_count }}
            </div>
          </div>

          <div v-if="filteredContacts.length === 0" class="text-center py-16 text-on-surface-variant text-sm select-none">
            <span class="material-symbols-outlined text-[48px] text-outline mb-2">contacts</span>
            <p>No colleagues found.</p>
          </div>
        </div>
      </div>

      <div class="flex-1 flex flex-col bg-surface-container-low overflow-hidden">
        
        <div v-if="!activeContact" class="flex-1 flex flex-col items-center justify-center text-on-surface-variant p-6 select-none">
          <div class="w-16 h-16 rounded-full bg-surface-container-high flex items-center justify-center border border-outline-variant shadow-sm mb-4">
            <span class="material-symbols-outlined text-primary text-[32px]">forum</span>
          </div>
          <h3 class="text-lg font-bold text-on-surface mb-1">Direct Messages</h3>
          <p class="text-sm max-w-sm text-center">Select a colleague from the list on the left to start a private conversation.</p>
        </div>

        <div v-else class="flex-1 flex flex-col overflow-hidden">
          <div class="bg-surface-container-lowest border-b border-outline-variant p-4 flex items-center gap-3 shrink-0">
            <Link :href="`/profile/${activeContact.id}`" class="relative shrink-0 select-none hover:opacity-90 transition-opacity">
              <div class="w-10 h-10 rounded-full bg-primary-container text-on-primary-container flex items-center justify-center font-bold text-sm shrink-0 border border-outline-variant overflow-hidden">
                <img v-if="activeContact.avatar" :src="activeContact.avatar" class="w-full h-full object-cover" />
                <span v-else>{{ getInitials(activeContact.name) }}</span>
              </div>
              <div :class="['absolute bottom-0 right-0 w-2.5 h-2.5 rounded-full border-2 border-surface-container-lowest', activeContact.status === 'online' ? 'bg-green-500' : 'bg-outline-variant']"></div>
            </Link>
            <div class="min-w-0">
              <Link :href="`/profile/${activeContact.id}`" class="font-bold text-on-surface hover:text-primary transition-colors truncate block no-underline font-semibold text-lg">{{ activeContact.name }}</Link>
              <p class="text-xs text-on-surface-variant truncate">
                {{ activeContact.role }} • <span :class="activeContact.status === 'online' ? 'text-green-600 font-semibold' : 'text-on-surface-variant'">{{ activeContact.status === 'online' ? 'Online' : 'Offline' }}</span>
              </p>
            </div>
          </div>

          <div ref="messagesContainer" class="flex-1 overflow-y-auto p-6 space-y-4 flex flex-col">
            <div 
              v-for="msg in messages" 
              :key="msg.id"
              :class="[
                'max-w-[70%] rounded-xl px-4 py-2.5 shadow-sm text-sm flex flex-col leading-relaxed',
                msg.is_mine ? 'bg-primary text-on-primary self-end rounded-br-none' : 'bg-surface-container-lowest text-on-surface self-start rounded-bl-none'
              ]"
            >
              <p class="whitespace-pre-wrap">{{ msg.message }}</p>
              <span :class="['text-[9px] mt-1.5 self-end font-medium select-none', msg.is_mine ? 'text-on-primary/60' : 'text-on-surface-variant']">{{ msg.time }}</span>
            </div>

            <div v-if="messages.length === 0" class="text-center py-16 text-on-surface-variant text-xs select-none">
              No messages here yet. Say hello to {{ activeContact.name }}!
            </div>
          </div>

          <div class="p-4 bg-surface-container-lowest border-t border-outline-variant flex gap-3 items-center shrink-0">
            <input 
              v-model="newMessage" 
              @keyup.enter="sendMessage"
              type="text" 
              class="flex-1 h-12 px-4 bg-surface-container-low border border-outline focus:border-primary rounded-xl text-sm text-on-surface focus:outline-none focus:ring-1 focus:ring-primary"
              :placeholder="`Message ${activeContact.name}...`"
            />
            <button 
              @click="sendMessage"
              class="h-12 w-12 bg-primary hover:bg-on-primary-fixed-variant text-on-primary shadow-sm rounded-xl border-none cursor-pointer flex items-center justify-center transition-colors"
            >
              <span class="material-symbols-outlined text-[20px]">send</span>
            </button>
          </div>
        </div>

      </div>

    </div>
  </AppLayout>
</template>
