<template>
  <div class="users-view">
    <!-- View Header -->
    <div class="view-header">
      <div class="title-group">
        <h1 class="view-title">İstifadəçi İdarəetməsi</h1>
        <p class="view-sub">AI Writer sisteminə çıxışı olan istifadəçiləri idarə edin. Tək səviyyəli tam idarəetmə hüququ.</p>
      </div>

      <div class="header-actions">
        <button class="btn btn-primary" @click="openCreateModal">
          <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5">
            <line x1="12" y1="5" x2="12" y2="19"/>
            <line x1="5" y1="12" x2="19" y2="12"/>
          </svg>
          <span>Yeni İstifadəçi</span>
        </button>
      </div>
    </div>

    <!-- Info Banner -->
    <div class="info-card">
      <div class="info-icon">
        <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
          <circle cx="12" cy="12" r="10"/>
          <line x1="12" y1="16" x2="12" y2="12"/>
          <line x1="12" y1="8" x2="12.01" y2="8"/>
        </svg>
      </div>
      <div class="info-body">
        <strong>Tək Səviyyəli İcazə Qaydası:</strong>
        Bütün istifadəçilər AI Writer üzərində eyni hüquqlara malikdir. Sistemdə açıq qeydiyyat yoxdur, yeni istifadəçilər yalnız daxil olmuş inzibatçılar tərəfindən bu bölmədən əlavə edilir.
      </div>
    </div>

    <!-- Users Table Card -->
    <div class="card users-card">
      <div class="card-header">
        <div class="card-title">
          <span>Mövcud İstifadəçilər</span>
          <span class="count-badge">{{ users.length }}</span>
        </div>
        <button class="btn btn-ghost btn-sm" @click="fetchUsers" :disabled="loading">
          <svg class="spin-icon" :class="{ spinning: loading }" width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
            <path d="M21.5 2v6h-6M21.34 15.57a10 10 0 1 1-.57-8.38l5.67-5.67"/>
          </svg>
          <span>Yenilə</span>
        </button>
      </div>

      <!-- Loading State -->
      <div v-if="loading && users.length === 0" class="loading-state">
        <div class="spinner"></div>
        <p>İstifadəçilər yüklənir...</p>
      </div>

      <!-- Users Table -->
      <div v-else class="table-responsive">
        <table class="data-table">
          <thead>
            <tr>
              <th>İstifadəçi</th>
              <th>Ad / Soyad</th>
              <th>Səlahiyyət</th>
              <th>Yaradılma Tarixi</th>
              <th class="text-right">Əməliyyatlar</th>
            </tr>
          </thead>
          <tbody>
            <tr v-for="user in users" :key="user.id" :class="{ 'current-row': isCurrentUser(user) }">
              <td>
                <div class="user-cell">
                  <div class="avatar-badge">
                    {{ (user.name || user.username).charAt(0).toUpperCase() }}
                  </div>
                  <div class="user-meta">
                    <span class="user-name">{{ user.username }}</span>
                    <span v-if="isCurrentUser(user)" class="current-user-tag">Siz</span>
                  </div>
                </div>
              </td>
              <td>
                <span class="text-main">{{ user.name || '—' }}</span>
              </td>
              <td>
                <span class="role-badge">İnzibatçı</span>
              </td>
              <td>
                <span class="text-muted text-sm">{{ formatDate(user.created_at) }}</span>
              </td>
              <td class="text-right">
                <div class="actions-group">
                  <button 
                    class="action-btn edit-btn" 
                    @click="openEditModal(user)"
                    title="Redaktə et"
                  >
                    <svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                      <path d="M11 4H4a2 2 0 0 0-2 2v14a2 2 0 0 0 2 2h14a2 2 0 0 0 2-2v-7"/>
                      <path d="M18.5 2.5a2.121 2.121 0 0 1 3 3L12 15l-4 1 1-4 9.5-9.5z"/>
                    </svg>
                    <span>Düzəliş</span>
                  </button>

                  <button 
                    class="action-btn delete-btn" 
                    @click="confirmDelete(user)"
                    :disabled="isCurrentUser(user) || users.length <= 1"
                    :title="isCurrentUser(user) ? 'Öz hesabınızı silə bilməzsiniz' : (users.length <= 1 ? 'Sonuncu istifadəçi silinə bilməz' : 'İstifadəçini sil')"
                  >
                    <svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                      <polyline points="3 6 5 6 21 6"/>
                      <path d="M19 6v14a2 2 0 0 1-2 2H7a2 2 0 0 1-2-2V6m3 0V4a2 2 0 0 1 2-2h4a2 2 0 0 1 2 2v2"/>
                    </svg>
                    <span>Sil</span>
                  </button>
                </div>
              </td>
            </tr>
          </tbody>
        </table>
      </div>
    </div>

    <!-- Create User Modal -->
    <transition name="modal-fade">
      <div v-if="showCreateModal" class="modal-backdrop" @click.self="showCreateModal = false">
        <div class="modal-dialog">
          <div class="modal-header">
            <h3>Yeni İstifadəçi Əlavə Et</h3>
            <button class="close-btn" @click="showCreateModal = false">&times;</button>
          </div>

          <form @submit.prevent="handleCreateUser" class="modal-body">
            <div v-if="modalError" class="modal-alert">
              {{ modalError }}
            </div>

            <div class="form-group">
              <label for="new-username">İstifadəçi adı (Giriş üçün) <span class="required">*</span></label>
              <input 
                id="new-username"
                v-model="createForm.username" 
                type="text" 
                placeholder="məsələn: redaktor1"
                required
                autocomplete="off"
                class="form-control"
              />
              <span class="help-text">Yalnız latın hərfləri, rəqəmlər və altxətt (min 3 simvol).</span>
            </div>

            <div class="form-group">
              <label for="new-name">Ad və Soyad</label>
              <input 
                id="new-name"
                v-model="createForm.name" 
                type="text" 
                placeholder="məsələn: Əli Məmmədov"
                autocomplete="off"
                class="form-control"
              />
            </div>

            <div class="form-group">
              <label for="new-password">Şifrə <span class="required">*</span></label>
              <input 
                id="new-password"
                v-model="createForm.password" 
                type="password" 
                placeholder="Ən azı 4 simvol"
                required
                autocomplete="new-password"
                class="form-control"
              />
            </div>

            <div class="modal-footer">
              <button type="button" class="btn btn-secondary" @click="showCreateModal = false">İmtina</button>
              <button type="submit" class="btn btn-primary" :disabled="modalSaving">
                <span v-if="modalSaving">Yaradılır...</span>
                <span v-else>İstifadəçini Yarat</span>
              </button>
            </div>
          </form>
        </div>
      </div>
    </transition>

    <!-- Edit User Modal -->
    <transition name="modal-fade">
      <div v-if="showEditModal" class="modal-backdrop" @click.self="showEditModal = false">
        <div class="modal-dialog">
          <div class="modal-header">
            <h3>İstifadəçi Məlumatlarını Redaktə Et</h3>
            <button class="close-btn" @click="showEditModal = false">&times;</button>
          </div>

          <form @submit.prevent="handleUpdateUser" class="modal-body">
            <div v-if="modalError" class="modal-alert">
              {{ modalError }}
            </div>

            <div class="form-group">
              <label for="edit-username">İstifadəçi adı <span class="required">*</span></label>
              <input 
                id="edit-username"
                v-model="editForm.username" 
                type="text" 
                required
                class="form-control"
              />
            </div>

            <div class="form-group">
              <label for="edit-name">Ad və Soyad</label>
              <input 
                id="edit-name"
                v-model="editForm.name" 
                type="text" 
                class="form-control"
              />
            </div>

            <div class="form-group">
              <label for="edit-password">Yeni Şifrə</label>
              <input 
                id="edit-password"
                v-model="editForm.password" 
                type="password" 
                placeholder="Dəyişmək istəmirsinizsə boş buraxın"
                autocomplete="new-password"
                class="form-control"
              />
              <span class="help-text">Yalnız şifrəni yeniləmək istədikdə doldurun.</span>
            </div>

            <div class="modal-footer">
              <button type="button" class="btn btn-secondary" @click="showEditModal = false">İmtina</button>
              <button type="submit" class="btn btn-primary" :disabled="modalSaving">
                <span v-if="modalSaving">Yadda saxlanılır...</span>
                <span v-else>Yenilə</span>
              </button>
            </div>
          </form>
        </div>
      </div>
    </transition>

    <!-- Delete Confirmation Modal -->
    <transition name="modal-fade">
      <div v-if="userToDelete" class="modal-backdrop" @click.self="userToDelete = null">
        <div class="modal-dialog modal-dialog-sm">
          <div class="modal-header modal-header-danger">
            <h3>İstifadəçini Sil</h3>
            <button class="close-btn" @click="userToDelete = null">&times;</button>
          </div>

          <div class="modal-body">
            <p class="delete-warning">
              <strong>{{ userToDelete.username }}</strong> adlı istifadəçini silmək istədiyinizdən əminsiniz? Bu əməliyyat geri qaytarıla bilməz.
            </p>

            <div class="modal-footer">
              <button type="button" class="btn btn-secondary" @click="userToDelete = null">Ləğv Et</button>
              <button 
                type="button" 
                class="btn btn-danger" 
                :disabled="deleteLoading"
                @click="executeDelete"
              >
                <span v-if="deleteLoading">Silinir...</span>
                <span v-else>Bəli, Sil</span>
              </button>
            </div>
          </div>
        </div>
      </div>
    </transition>
  </div>
</template>

<script>
import { ref, onMounted } from 'vue'
import { useAuthStore } from '../stores/auth'
import { useAiWriterStore } from '../stores/aiWriter'
import api from '../api'

export default {
  name: 'UsersView',
  setup() {
    const authStore = useAuthStore()
    const aiWriterStore = useAiWriterStore()

    const users = ref([])
    const loading = ref(false)
    const modalSaving = ref(false)
    const modalError = ref('')

    const showCreateModal = ref(false)
    const createForm = ref({
      username: '',
      name: '',
      password: ''
    })

    const showEditModal = ref(false)
    const editForm = ref({
      id: null,
      username: '',
      name: '',
      password: ''
    })

    const userToDelete = ref(null)
    const deleteLoading = ref(false)

    const fetchUsers = async () => {
      loading.value = true
      try {
        const res = await api.get('?endpoint=users')
        if (res.data?.success) {
          users.value = res.data.users || []
        }
      } catch (err) {
        aiWriterStore.showToast(err.response?.data?.error || 'İstifadəçilər yüklənə bilmədi', 'error')
      } finally {
        loading.value = false
      }
    }

    const isCurrentUser = (user) => {
      return authStore.user && (authStore.user.id === user.id || authStore.user.username === user.username)
    }

    const formatDate = (dateStr) => {
      if (!dateStr) return '—'
      try {
        const d = new Date(dateStr)
        return d.toLocaleDateString('az-AZ', {
          year: 'numeric',
          month: 'short',
          day: 'numeric',
          hour: '2-digit',
          minute: '2-digit'
        })
      } catch (e) {
        return dateStr
      }
    }

    const openCreateModal = () => {
      createForm.value = { username: '', name: '', password: '' }
      modalError.value = ''
      showCreateModal.value = true
    }

    const handleCreateUser = async () => {
      modalSaving.value = true
      modalError.value = ''
      try {
        const res = await api.post('?endpoint=users', createForm.value)
        if (res.data?.success) {
          aiWriterStore.showToast('Yeni istifadəçi uğurla əlavə edildi', 'success')
          showCreateModal.value = false
          await fetchUsers()
        }
      } catch (err) {
        modalError.value = err.response?.data?.error || 'İstifadəçi yaradılarkən xəta baş verdi'
      } finally {
        modalSaving.value = false
      }
    }

    const openEditModal = (user) => {
      editForm.value = {
        id: user.id,
        username: user.username,
        name: user.name,
        password: ''
      }
      modalError.value = ''
      showEditModal.value = true
    }

    const handleUpdateUser = async () => {
      modalSaving.value = true
      modalError.value = ''
      try {
        const res = await api.put(`?endpoint=users&id=${editForm.value.id}`, editForm.value)
        if (res.data?.success) {
          aiWriterStore.showToast('İstifadəçi məlumatları yeniləndi', 'success')
          showEditModal.value = false
          await fetchUsers()

          // If current user updated their own info, update authStore
          if (authStore.user && authStore.user.id === editForm.value.id) {
            authStore.user.username = editForm.value.username
            authStore.user.name = editForm.value.name || editForm.value.username
          }
        }
      } catch (err) {
        modalError.value = err.response?.data?.error || 'Məlumatlar yenilənərkən xəta baş verdi'
      } finally {
        modalSaving.value = false
      }
    }

    const confirmDelete = (user) => {
      if (isCurrentUser(user) || users.value.length <= 1) return
      userToDelete.value = user
    }

    const executeDelete = async () => {
      if (!userToDelete.value) return
      deleteLoading.value = true
      try {
        const res = await api.delete(`?endpoint=users&id=${userToDelete.value.id}`)
        if (res.data?.success) {
          aiWriterStore.showToast('İstifadəçi silindi', 'success')
          userToDelete.value = null
          await fetchUsers()
        }
      } catch (err) {
        aiWriterStore.showToast(err.response?.data?.error || 'Silinmə zamanı xəta baş verdi', 'error')
      } finally {
        deleteLoading.value = false
      }
    }

    onMounted(() => {
      fetchUsers()
    })

    return {
      authStore,
      users,
      loading,
      modalSaving,
      modalError,
      showCreateModal,
      createForm,
      showEditModal,
      editForm,
      userToDelete,
      deleteLoading,
      fetchUsers,
      isCurrentUser,
      formatDate,
      openCreateModal,
      handleCreateUser,
      openEditModal,
      handleUpdateUser,
      confirmDelete,
      executeDelete
    }
  }
}
</script>

<style scoped>
.users-view {
  max-width: 1400px;
  margin: 0 auto;
  padding: 32px 24px;
  display: flex;
  flex-direction: column;
  gap: 24px;
}

.view-header {
  display: flex;
  align-items: center;
  justify-content: space-between;
  flex-wrap: wrap;
  gap: 16px;
}

.view-title {
  font-size: 26px;
  font-weight: 800;
  color: #fff;
  letter-spacing: -0.02em;
}

.view-sub {
  font-size: 14px;
  color: var(--text-muted);
  margin-top: 4px;
}

.info-card {
  display: flex;
  align-items: flex-start;
  gap: 14px;
  background: rgba(0, 240, 255, 0.05);
  border: 1px solid rgba(0, 240, 255, 0.18);
  border-radius: var(--radius-md);
  padding: 16px 20px;
  color: var(--text-main);
  font-size: 13.5px;
  line-height: 1.5;
}

.info-icon {
  color: var(--color-accent);
  flex-shrink: 0;
  margin-top: 1px;
}

.users-card {
  background: var(--bg-surface);
  border: 1px solid var(--border-subtle);
  border-radius: var(--radius-lg);
  overflow: hidden;
}

.card-header {
  padding: 18px 24px;
  border-bottom: 1px solid var(--border-subtle);
  display: flex;
  align-items: center;
  justify-content: space-between;
}

.card-title {
  display: flex;
  align-items: center;
  gap: 10px;
  font-size: 16px;
  font-weight: 700;
  color: #fff;
}

.count-badge {
  background: rgba(255, 255, 255, 0.08);
  color: var(--color-primary);
  font-size: 12px;
  font-weight: 700;
  padding: 2px 8px;
  border-radius: var(--radius-full);
}

.table-responsive {
  overflow-x: auto;
}

.data-table {
  width: 100%;
  border-collapse: collapse;
  text-align: left;
}

.data-table th {
  padding: 14px 24px;
  font-size: 12px;
  font-weight: 600;
  text-transform: uppercase;
  letter-spacing: 0.05em;
  color: var(--text-dim);
  border-bottom: 1px solid var(--border-subtle);
  background: rgba(0, 0, 0, 0.15);
}

.data-table td {
  padding: 16px 24px;
  font-size: 14px;
  border-bottom: 1px solid rgba(255, 255, 255, 0.04);
  vertical-align: middle;
}

.data-table tr:hover td {
  background: rgba(255, 255, 255, 0.02);
}

.current-row td {
  background: rgba(252, 219, 86, 0.02);
}

.user-cell {
  display: flex;
  align-items: center;
  gap: 12px;
}

.avatar-badge {
  width: 36px;
  height: 36px;
  border-radius: var(--radius-sm);
  background: linear-gradient(135deg, rgba(252, 219, 86, 0.2) 0%, rgba(0, 240, 255, 0.2) 100%);
  border: 1px solid rgba(0, 240, 255, 0.3);
  color: var(--color-accent);
  display: flex;
  align-items: center;
  justify-content: center;
  font-weight: 800;
  font-size: 14px;
  flex-shrink: 0;
}

.user-meta {
  display: flex;
  align-items: center;
  gap: 8px;
}

.user-name {
  font-weight: 600;
  color: #fff;
}

.current-user-tag {
  font-size: 10px;
  font-weight: 700;
  text-transform: uppercase;
  background: rgba(252, 219, 86, 0.15);
  color: var(--color-primary);
  border: 1px solid rgba(252, 219, 86, 0.3);
  padding: 1px 6px;
  border-radius: 4px;
}

.role-badge {
  display: inline-block;
  font-size: 11px;
  font-weight: 600;
  padding: 3px 10px;
  border-radius: 4px;
  background: rgba(56, 189, 248, 0.1);
  color: #38bdf8;
  border: 1px solid rgba(56, 189, 248, 0.25);
}

.actions-group {
  display: flex;
  align-items: center;
  justify-content: flex-end;
  gap: 8px;
}

.action-btn {
  display: inline-flex;
  align-items: center;
  gap: 6px;
  padding: 6px 12px;
  border-radius: var(--radius-sm);
  font-size: 12px;
  font-weight: 600;
  border: 1px solid transparent;
  cursor: pointer;
  transition: all 0.2s;
  background: transparent;
}

.edit-btn {
  color: var(--text-muted);
  border-color: var(--border-subtle);
  background: rgba(255, 255, 255, 0.03);
}

.edit-btn:hover {
  color: #fff;
  border-color: var(--color-accent);
  background: rgba(0, 240, 255, 0.08);
}

.delete-btn {
  color: #f87171;
  border-color: rgba(239, 68, 68, 0.2);
  background: rgba(239, 68, 68, 0.05);
}

.delete-btn:hover:not(:disabled) {
  background: rgba(239, 68, 68, 0.15);
  border-color: #ef4444;
}

.delete-btn:disabled {
  opacity: 0.35;
  cursor: not-allowed;
}

.text-right {
  text-align: right;
}

.text-main {
  color: var(--text-main);
}

.text-muted {
  color: var(--text-muted);
}

.text-sm {
  font-size: 12.5px;
}

.loading-state {
  padding: 60px 24px;
  text-align: center;
  color: var(--text-muted);
  display: flex;
  flex-direction: column;
  align-items: center;
  gap: 12px;
}

.spinner {
  width: 32px;
  height: 32px;
  border: 3px solid rgba(255, 255, 255, 0.1);
  border-top-color: var(--color-primary);
  border-radius: 50%;
  animation: spin 0.8s linear infinite;
}

/* Modals */
.modal-backdrop {
  position: fixed;
  inset: 0;
  background: rgba(0, 0, 0, 0.75);
  backdrop-filter: blur(4px);
  z-index: 100;
  display: flex;
  align-items: center;
  justify-content: center;
  padding: 20px;
}

.modal-dialog {
  width: 100%;
  max-width: 480px;
  background: var(--bg-surface);
  border: 1px solid var(--border-subtle);
  border-radius: var(--radius-lg);
  box-shadow: 0 25px 50px -12px rgba(0, 0, 0, 0.75);
  overflow: hidden;
}

.modal-dialog-sm {
  max-width: 420px;
}

.modal-header {
  padding: 20px 24px;
  border-bottom: 1px solid var(--border-subtle);
  display: flex;
  align-items: center;
  justify-content: space-between;
}

.modal-header h3 {
  font-size: 18px;
  font-weight: 700;
  color: #fff;
}

.modal-header-danger h3 {
  color: #f87171;
}

.close-btn {
  background: transparent;
  border: none;
  color: var(--text-dim);
  font-size: 24px;
  cursor: pointer;
  line-height: 1;
}

.close-btn:hover {
  color: #fff;
}

.modal-body {
  padding: 24px;
  display: flex;
  flex-direction: column;
  gap: 18px;
}

.modal-alert {
  padding: 10px 14px;
  background: rgba(239, 68, 68, 0.12);
  border: 1px solid rgba(239, 68, 68, 0.35);
  border-radius: var(--radius-sm);
  color: #fca5a5;
  font-size: 13px;
}

.form-group {
  display: flex;
  flex-direction: column;
  gap: 6px;
}

.form-group label {
  font-size: 13px;
  font-weight: 500;
  color: var(--text-muted);
}

.required {
  color: #f87171;
}

.form-control {
  width: 100%;
  background: rgba(255, 255, 255, 0.03);
  border: 1px solid var(--border-subtle);
  border-radius: var(--radius-sm);
  padding: 10px 14px;
  font-size: 14px;
  color: #fff;
  font-family: inherit;
  outline: none;
  transition: all 0.2s;
}

.form-control:focus {
  border-color: var(--color-accent);
  background: rgba(0, 240, 255, 0.04);
  box-shadow: 0 0 0 2px rgba(0, 240, 255, 0.15);
}

.help-text {
  font-size: 11.5px;
  color: var(--text-dim);
}

.delete-warning {
  color: var(--text-main);
  font-size: 14px;
  line-height: 1.6;
}

.delete-warning strong {
  color: #fff;
}

.modal-footer {
  display: flex;
  align-items: center;
  justify-content: flex-end;
  gap: 12px;
  margin-top: 10px;
}

.btn-danger {
  background: #ef4444;
  color: #fff;
  border: none;
  padding: 10px 18px;
  border-radius: var(--radius-sm);
  font-weight: 600;
  font-size: 13.5px;
  cursor: pointer;
  transition: background 0.2s;
}

.btn-danger:hover:not(:disabled) {
  background: #dc2626;
}

.btn-danger:disabled {
  opacity: 0.5;
  cursor: not-allowed;
}

.modal-fade-enter-active, .modal-fade-leave-active {
  transition: opacity 0.2s ease;
}
.modal-fade-enter-from, .modal-fade-leave-to {
  opacity: 0;
}
</style>
