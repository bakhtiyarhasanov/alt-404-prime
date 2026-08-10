<template>
  <div class="contacts-view">
    <div class="panel">
      <table class="table">
        <thead>
          <tr>
            <th>Ad Soyad</th>
            <th>E-poçt</th>
            <th>Mesaj</th>
            <th>Status</th>
            <th>Tarix</th>
            <th class="actions-col">Əməliyyatlar</th>
          </tr>
        </thead>
        <tbody>
          <tr v-for="c in contacts" :key="c.id" :class="{ new: c.status === 'new' }">
            <td><span class="user-name">{{ c.ad_soyad }}</span></td>
            <td><a :href="'mailto:' + c.email" class="email-link">{{ c.email }}</a></td>
            <td>
              <p class="message-text">{{ c.mesaj }}</p>
            </td>
            <td>
              <span class="status-badge" :class="c.status">
                {{ c.status === 'new' ? 'Yeni' : 'Baxılıb' }}
              </span>
            </td>
            <td>{{ formatDate(c.created_at) }}</td>
            <td>
              <div class="actions" v-if="c.status === 'new'">
                <button @click="markReviewed(c)" class="action-btn">Baxıldı</button>
              </div>
            </td>
          </tr>
          <tr v-if="contacts.length === 0">
            <td colspan="6" class="empty-cell">Heç bir müraciət yoxdur.</td>
          </tr>
        </tbody>
      </table>
    </div>
  </div>
</template>

<script>
import { ref, onMounted } from 'vue'
import client from '../api/client'

export default {
  name: 'ContactsView',
  setup() {
    const contacts = ref([])

    const fetchContacts = async () => {
      try {
        const { data } = await client.get('/contacts')
        contacts.value = data
      } catch (err) {
        console.error(err)
      }
    }

    onMounted(fetchContacts)

    const markReviewed = async (c) => {
      try {
        await client.put(`/contacts/${c.id}`, { status: 'reviewed' })
        await fetchContacts()
      } catch (err) {
        alert('Xəta baş verid')
      }
    }

    const formatDate = (dateStr) => {
      if (!dateStr) return ''
      const d = new Date(dateStr)
      return d.toLocaleDateString('az-AZ', { day: 'numeric', month: 'short' }) + ' ' + d.toLocaleTimeString('az-AZ', { hour: '2-digit', minute: '2-digit' })
    }

    return {
      contacts,
      markReviewed,
      formatDate
    }
  }
}
</script>

<style scoped>
.panel {
  background-color: var(--color-surface);
  border: 1px solid var(--color-border);
  border-radius: 12px;
  overflow: hidden;
}
.table {
  width: 100%;
  border-collapse: collapse;
  text-align: left;
}
.table th, .table td {
  padding: 16px 24px;
  border-bottom: 1px solid var(--color-border);
  font-size: 13px;
}
.table th {
  background-color: rgba(255,255,255,0.02);
  color: var(--color-text-secondary);
  font-weight: 600;
  text-transform: uppercase;
  font-size: 10px;
  letter-spacing: 0.05em;
}
.table tr.new {
  background-color: rgba(252, 219, 86, 0.01);
}
.user-name {
  font-weight: 600;
  color: #fff;
}
.email-link {
  color: var(--color-primary);
  text-decoration: none;
}
.message-text {
  color: var(--color-text-secondary);
  line-height: 1.5;
  margin: 0;
  max-width: 400px;
}
.status-badge {
  font-size: 11px;
  padding: 2px 6px;
  border-radius: 4px;
  font-weight: 600;
}
.status-badge.new {
  background-color: rgba(239, 68, 68, 0.15);
  color: var(--color-red);
}
.status-badge.reviewed {
  background-color: rgba(16, 185, 129, 0.15);
  color: var(--color-green);
}
.actions {
  display: flex;
  gap: 8px;
}
.action-btn {
  font-size: 12px;
  padding: 6px 12px;
  border-radius: 6px;
  border: 1px solid var(--color-border);
  cursor: pointer;
  background: none;
  font-family: inherit;
  color: var(--color-primary);
  transition: all 0.2s;
}
.action-btn:hover {
  background-color: rgba(252, 219, 86, 0.05);
}
.empty-cell {
  text-align: center;
  color: var(--color-text-muted);
  padding: 48px;
}
</style>
