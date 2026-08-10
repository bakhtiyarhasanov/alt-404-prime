<template>
  <div class="users-view">
    <div class="header-row">
      <h2 class="page-title">İstifadəçilər</h2>
      <button @click="openAddModal" class="btn-primary">Yeni İstifadəçi</button>
    </div>

    <!-- Users list -->
    <div class="panel">
      <h2 class="panel-title">Mövcud İstifadəçilər</h2>
      <div class="table-wrapper">
        <table class="table">
          <thead>
            <tr>
              <th>İstifadəçi</th>
              <th>E-poçt</th>
              <th>Rol</th>
              <th>Qeydiyyat Tarixi</th>
              <th class="actions-col">Əməliyyatlar</th>
            </tr>
          </thead>
          <tbody>
            <tr v-for="usr in users" :key="usr.id">
              <td>
                <div class="user-cell">
                  <img :src="usr.avatar_url || 'https://images.pexels.com/photos/531880/pexels-photo-531880.jpeg'" class="usr-avatar" alt="">
                  <span class="usr-name">{{ usr.name }}</span>
                </div>
              </td>
              <td>{{ usr.email }}</td>
              <td>
                <span class="role-badge" :class="usr.role">
                  {{ usr.role === 'editor' ? 'Redaktor (Editor)' : 'Müxbir (Reporter)' }}
                </span>
              </td>
              <td>{{ formatDate(usr.created_at) }}</td>
              <td>
                <div class="actions">
                  <button @click="edit(usr)" class="action-btn edit">Düzəliş</button>
                  <button @click="confirmDelete(usr)" class="action-btn delete" :disabled="usr.id === currentUser.id">Sil</button>
                </div>
              </td>
            </tr>
          </tbody>
        </table>
      </div>
    </div>

    <!-- Add/Edit Modal -->
    <div v-if="showModal" class="modal-overlay" @click.self="closeModal">
      <div class="modal-content panel">
        <div class="modal-header">
          <h2 class="panel-title">{{ isEdit ? 'İstifadəçini Redaktə Et' : 'Yeni İstifadəçi' }}</h2>
          <button @click="closeModal" class="close-btn">&times;</button>
        </div>
        <form @submit.prevent="save" class="form">
          <div class="form-group">
            <label class="label">Ad Soyad</label>
            <input v-model="form.name" type="text" placeholder="Məs. Əli Məmmədov" class="input" required>
          </div>

          <div class="form-group">
            <label class="label">E-poçt Ünvanı</label>
            <input v-model="form.email" type="email" placeholder="email@alt404.com" class="input" required>
          </div>

          <div class="form-group">
            <label class="label">Şifrə {{ isEdit ? '(dəyişmək istəmirsinizsə, boş buraxın)' : '' }}</label>
            <input v-model="form.password" type="password" placeholder="Şifrə" class="input" :required="!isEdit">
          </div>

          <div class="form-group">
            <label class="label">Rol</label>
            <select v-model="form.role" class="select-input">
              <option value="reporter">Müxbir (Reporter) — Xəbər daxil edə bilər, amma dərc edə bilməz</option>
              <option value="editor">Redaktor (Editor) — Tam idarəetmə və təsdiq imkanı</option>
            </select>
          </div>

          <div class="form-group">
            <label class="label">Avatar Linki</label>
            <input v-model="form.avatar_url" type="text" placeholder="https://..." class="input">
          </div>

          <div class="actions-row">
            <button type="submit" class="btn-primary" :disabled="saving">
              {{ saving ? 'Yadda saxlanılır...' : 'Yadda Saxla' }}
            </button>
            <button type="button" @click="closeModal" class="btn-ghost">Ləğv et</button>
          </div>
        </form>
      </div>
    </div>
  </div>
</template>

<script>
import { ref, reactive, onMounted, computed } from 'vue'
import { useAuthStore } from '../stores/auth'
import client from '../api/client'

export default {
  name: 'UsersView',
  setup() {
    const authStore = useAuthStore()
    const users = ref([])
    const isEdit = ref(false)
    const saving = ref(false)
    const showModal = ref(false)
    const editingId = ref(null)

    const form = reactive({
      email: '',
      name: '',
      password: '',
      role: 'reporter',
      avatar_url: ''
    })

    const currentUser = computed(() => authStore.user || {})

    const fetchUsers = async () => {
      try {
        const { data } = await client.get('/users')
        users.value = data
      } catch (err) {
        console.error(err)
      }
    }

    onMounted(fetchUsers)

    const openAddModal = () => {
      isEdit.value = false
      resetForm()
      showModal.value = true
    }

    const closeModal = () => {
      showModal.value = false
      resetForm()
    }

    const resetForm = () => {
      form.email = ''
      form.name = ''
      form.password = ''
      form.role = 'reporter'
      form.avatar_url = ''
      editingId.value = null
    }

    const edit = (usr) => {
      isEdit.value = true
      editingId.value = usr.id
      form.email = usr.email
      form.name = usr.name
      form.password = ''
      form.role = usr.role
      form.avatar_url = usr.avatar_url
      showModal.value = true
    }

    const save = async () => {
      saving.value = true
      try {
        if (isEdit.value) {
          await client.put(`/users/${editingId.value}`, form)
        } else {
          await client.post('/users', form)
        }
        await fetchUsers()
        closeModal()
      } catch (err) {
        alert(err.response?.data?.error || 'Xəta baş verdi.')
      } finally {
        saving.value = false
      }
    }

    const confirmDelete = async (usr) => {
      if (confirm(`"${usr.name}" istifadəçisini silmək istədiyinizdən əminsiniz?`)) {
        try {
          await client.delete(`/users/${usr.id}`)
          await fetchUsers()
        } catch (err) {
          alert(err.response?.data?.error || 'Silinmə zamanı xəta baş verdi.')
        }
      }
    }

    const formatDate = (dateStr) => {
      if (!dateStr) return ''
      const date = new Date(dateStr)
      return date.toLocaleDateString('az-AZ', {
        year: 'numeric',
        month: 'long',
        day: 'numeric',
        hour: '2-digit',
        minute: '2-digit'
      })
    }

    return {
      users,
      showModal,
      isEdit,
      saving,
      form,
      currentUser,
      openAddModal,
      closeModal,
      edit,
      save,
      confirmDelete,
      formatDate
    }
  }
}
</script>

<style scoped>
.users-view {
  display: flex;
  flex-direction: column;
  gap: 24px;
}
.header-row {
  display: flex;
  justify-content: space-between;
  align-items: center;
}
.page-title {
  font-size: 20px;
  font-weight: 600;
  margin: 0;
}
.btn-primary {
  background-color: var(--color-primary);
  color: #111;
  border: none;
  padding: 10px 20px;
  border-radius: 8px;
  font-weight: 600;
  cursor: pointer;
  font-size: 14px;
  transition: all 0.2s;
}
.btn-primary:hover:not(:disabled) {
  background-color: var(--color-primary-hover);
  transform: translateY(-1px);
}
.btn-primary:disabled {
  opacity: 0.6;
  cursor: not-allowed;
}
.panel {
  background-color: var(--color-surface);
  border: 1px solid var(--color-border);
  border-radius: 12px;
  padding: 24px;
}
.panel-title {
  font-size: 15px;
  font-weight: 600;
  margin: 0 0 20px 0;
  color: var(--color-text-primary);
}
.table-wrapper {
  overflow-x: auto;
}
.table {
  width: 100%;
  border-collapse: collapse;
  text-align: left;
}
.table th, .table td {
  padding: 14px 16px;
  border-bottom: 1px solid var(--color-border);
  font-size: 13px;
}
.table th {
  color: var(--color-text-muted);
  font-weight: 600;
  text-transform: uppercase;
  font-size: 11px;
  letter-spacing: 0.05em;
}
.user-cell {
  display: flex;
  align-items: center;
  gap: 12px;
}
.usr-avatar {
  width: 32px;
  height: 32px;
  border-radius: 50%;
  object-fit: cover;
  border: 1px solid var(--color-border);
}
.usr-name {
  font-weight: 600;
  color: var(--color-text-primary);
}
.role-badge {
  font-size: 11px;
  font-weight: 600;
  padding: 4px 10px;
  border-radius: 6px;
  display: inline-block;
}
.role-badge.editor {
  background-color: rgba(252, 219, 86, 0.15);
  color: var(--color-primary);
}
.role-badge.reporter {
  background-color: rgba(16, 185, 129, 0.15);
  color: var(--color-green);
}
.actions {
  display: flex;
  gap: 8px;
}
.action-btn {
  font-size: 12px;
  font-weight: 500;
  padding: 6px 12px;
  border-radius: 6px;
  cursor: pointer;
  transition: all 0.2s;
  background: none;
}
.action-btn.edit {
  color: var(--color-primary);
  border: 1px solid var(--color-primary);
}
.action-btn.edit:hover {
  background-color: rgba(252, 219, 86, 0.1);
}
.action-btn.delete {
  color: var(--color-red);
  border: 1px solid var(--color-red);
}
.action-btn.delete:hover:not(:disabled) {
  background-color: rgba(239, 110, 110, 0.1);
}
.action-btn.delete:disabled {
  opacity: 0.5;
  cursor: not-allowed;
}

/* Modal Styling */
.modal-overlay {
  position: fixed;
  top: 0;
  left: 0;
  right: 0;
  bottom: 0;
  background-color: rgba(0, 0, 0, 0.7);
  backdrop-filter: blur(4px);
  display: flex;
  align-items: center;
  justify-content: center;
  z-index: 1000;
  padding: 20px;
}
.modal-content {
  width: 100%;
  max-width: 500px;
  max-height: 90vh;
  overflow-y: auto;
}
.modal-header {
  display: flex;
  justify-content: space-between;
  align-items: center;
  margin-bottom: 24px;
}
.close-btn {
  background: none;
  border: none;
  color: var(--color-text-secondary);
  font-size: 24px;
  cursor: pointer;
}
.form {
  display: flex;
  flex-direction: column;
  gap: 16px;
}
.form-group {
  display: flex;
  flex-direction: column;
  gap: 8px;
}
.label {
  font-size: 12px;
  font-weight: 600;
  color: var(--color-text-secondary);
}
.input, .select-input {
  background-color: var(--color-bg);
  border: 1px solid var(--color-border);
  color: var(--color-text-primary);
  padding: 10px 14px;
  border-radius: 8px;
  font-size: 13px;
  outline: none;
  font-family: inherit;
}
.input:focus, .select-input:focus {
  border-color: var(--color-primary);
}
.select-input option {
  background-color: var(--color-surface);
  color: var(--color-text-primary);
}
.actions-row {
  display: flex;
  gap: 12px;
  margin-top: 12px;
}
.btn-ghost {
  background: none;
  border: 1px solid var(--color-border);
  color: var(--color-text-secondary);
  padding: 10px 20px;
  border-radius: 8px;
  font-weight: 600;
  cursor: pointer;
  font-size: 14px;
}
.btn-ghost:hover {
  background-color: var(--color-surface-hover);
}
</style>
