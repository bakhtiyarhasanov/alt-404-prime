<template>
  <div class="subscribers-view">
    <div class="panel">
      <table class="table">
        <thead>
          <tr>
            <th>ID</th>
            <th>E-poçt</th>
            <th>Tarix</th>
            <th class="actions-col">Əməliyyatlar</th>
          </tr>
        </thead>
        <tbody>
          <tr v-for="s in subscribers" :key="s.id">
            <td>#{{ s.id }}</td>
            <td><a :href="'mailto:' + s.email" class="email-link">{{ s.email }}</a></td>
            <td>{{ formatDate(s.created_at) }}</td>
            <td>
              <div class="actions">
                <button @click="deleteSubscriber(s)" class="action-btn delete-btn">Sil</button>
              </div>
            </td>
          </tr>
          <tr v-if="subscribers.length === 0">
            <td colspan="4" class="empty-cell">Heç bir abunəçi yoxdur.</td>
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
  name: 'SubscribersView',
  setup() {
    const subscribers = ref([])

    const fetchSubscribers = async () => {
      try {
        const { data } = await client.get('/subscribers')
        subscribers.value = Array.isArray(data) ? data : []
      } catch (err) {
        console.error(err)
        subscribers.value = []
      }
    }

    onMounted(fetchSubscribers)

    const formatDate = (dateStr) => {
      if (!dateStr) return ''
      const d = new Date(dateStr)
      return d.toLocaleDateString('az-AZ', { day: 'numeric', month: 'short', year: 'numeric' }) + ' ' + d.toLocaleTimeString('az-AZ', { hour: '2-digit', minute: '2-digit' })
    }

    const deleteSubscriber = async (s) => {
      if (!confirm(`${s.email} ünvanını abunəlikdən silmək istədiyinizə əminsiniz?`)) return
      try {
        await client.delete(`/subscribers/${s.id}`)
        await fetchSubscribers()
      } catch (err) {
        alert('Silinmə zamanı xəta baş verdi')
      }
    }

    return {
      subscribers,
      formatDate,
      deleteSubscriber
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
.email-link {
  color: var(--color-primary);
  text-decoration: none;
  font-weight: 500;
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
.delete-btn {
  color: var(--color-red, #ef4444);
  border-color: rgba(239, 68, 68, 0.3);
}
.delete-btn:hover {
  background-color: rgba(239, 68, 68, 0.1);
  border-color: var(--color-red, #ef4444);
}
.empty-cell {
  text-align: center;
  color: var(--color-text-muted);
  padding: 48px;
}
</style>
