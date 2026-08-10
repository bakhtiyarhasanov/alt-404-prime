<template>
  <div class="categories-view">
    <div class="header-row">
      <h2 class="page-title">Kateqoriyalar</h2>
      <button @click="openAddModal" class="btn-primary">Yeni Kateqoriya</button>
    </div>

    <!-- Categories list -->
    <div class="panel">
      <h2 class="panel-title">Mövcud Kateqoriyalar</h2>
      <div class="table-wrapper">
        <table class="table">
          <thead>
            <tr>
              <th>Kateqoriya</th>
              <th>Sıra</th>
              <th>Status</th>
              <th class="actions-col">Əməliyyatlar</th>
            </tr>
          </thead>
          <tbody>
            <tr v-for="cat in categories" :key="cat.slug" :class="{ inactive: !cat.show_on_site }">
              <td>
                <div class="cat-cell">
                  <span class="cat-label">{{ cat.label }}</span>
                  <span class="cat-slug">/{{ cat.slug }}</span>
                </div>
              </td>
              <td>{{ cat.sort_order }}</td>
              <td>
                <span class="status" :class="{ show: cat.show_on_site }">
                  {{ cat.show_on_site ? 'Aktiv' : 'Gizli' }}
                </span>
              </td>
              <td>
                <div class="actions">
                  <button @click="edit(cat)" class="action-btn edit">Düzəliş</button>
                  <button @click="confirmDelete(cat)" class="action-btn delete">Sil</button>
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
          <h2 class="panel-title">{{ isEdit ? 'Kateqoriyanı Redaktə Et' : 'Yeni Kateqoriya' }}</h2>
          <button @click="closeModal" class="close-btn">&times;</button>
        </div>
        <form @submit.prevent="save" class="form">
          <div class="form-group">
            <label class="label">Kateqoriya Adı</label>
            <input v-model="form.label" type="text" placeholder="Məs. Elm və Texnologiya" class="input" required>
          </div>

          <div class="form-group">
            <label class="label">Keçid Ünvanı (Slug)</label>
            <input v-model="form.slug" type="text" placeholder="elm-ve-texnologiya" class="input" :disabled="isEdit" required>
          </div>

          <div class="form-group">
            <label class="label">Sıralama Nömrəsi</label>
            <input v-model.number="form.sort_order" type="number" class="input" required>
          </div>

          <div class="divider"></div>

          <div class="form-group">
            <label class="label">SEO Başlığı (Meta Title)</label>
            <input v-model="form.meta_title" type="text" placeholder="Axtarış motorları üçün başlıq..." class="input">
          </div>

          <div class="form-group">
            <label class="label">SEO Təsviri (Meta Description)</label>
            <textarea v-model="form.meta_description" rows="3" placeholder="Axtarış motorları üçün qısa məzmun təsviri..." class="input"></textarea>
          </div>

          <div class="divider"></div>

          <div class="form-group check-group">
            <input v-model="form.show_on_site" type="checkbox" id="show_on_site">
            <label for="show_on_site" class="check-label">Sayt menyusunda göstərilsin</label>
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
import { ref, reactive, onMounted } from 'vue'
import client from '../api/client'

export default {
  name: 'CategoriesView',
  setup() {
    const categories = ref([])
    const isEdit = ref(false)
    const saving = ref(false)
    const showModal = ref(false)

    const form = reactive({
      slug: '',
      label: '',
      show_on_site: true,
      sort_order: 10,
      meta_title: '',
      meta_description: '',
      curated_tags: []
    })

    const fetchCategories = async () => {
      try {
        const { data } = await client.get('/categories')
        categories.value = data
      } catch (err) {
        console.error(err)
      }
    }

    onMounted(fetchCategories)

    const openAddModal = () => {
      isEdit.value = false
      resetForm()
      showModal.value = true
    }

    const closeModal = () => {
      showModal.value = false
      resetForm()
    }

    const edit = (cat) => {
      isEdit.value = true
      form.slug = cat.slug
      form.label = cat.label
      form.show_on_site = cat.show_on_site
      form.sort_order = cat.sort_order
      form.meta_title = cat.meta_title || ''
      form.meta_description = cat.meta_description || ''
      form.curated_tags = cat.curated_tags || []
      showModal.value = true
    }

    const resetForm = () => {
      isEdit.value = false
      form.slug = ''
      form.label = ''
      form.show_on_site = true
      form.sort_order = 10
      form.meta_title = ''
      form.meta_description = ''
      form.curated_tags = []
    }

    const save = async () => {
      saving.value = true
      try {
        if (isEdit.value) {
          await client.put(`/categories/${form.slug}`, form)
        } else {
          await client.post('/categories', form)
        }
        closeModal()
        await fetchCategories()
      } catch (err) {
        alert(err.response?.data?.error || 'Xəta baş verdi')
      } finally {
        saving.value = false
      }
    }

    const confirmDelete = async (cat) => {
      if (confirm(`"${cat.label}" kateqoriyasını silmək istədiyinizdən əminsiniz?`)) {
        try {
          await client.delete(`/categories/${cat.slug}`)
          await fetchCategories()
        } catch (err) {
          alert('Xəta baş verdi')
        }
      }
    }

    return {
      categories,
      isEdit,
      saving,
      showModal,
      form,
      openAddModal,
      closeModal,
      edit,
      resetForm,
      save,
      confirmDelete
    }
  }
}
</script>

<style scoped>
.categories-view {
  width: 100%;
}
.header-row {
  display: flex;
  align-items: center;
  justify-content: space-between;
  margin-bottom: 24px;
  gap: 16px;
}
.page-title {
  font-size: 20px;
  font-weight: 700;
  margin: 0;
  color: #fff;
}
.panel {
  background-color: var(--color-surface);
  border: 1px solid var(--color-border);
  border-radius: 12px;
  padding: 24px;
  display: flex;
  flex-direction: column;
  gap: 20px;
}
.panel-title {
  font-size: 15px;
  font-weight: 600;
  margin: 0;
  color: #fff;
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
  padding: 14px 18px;
  border-bottom: 1px solid var(--color-border);
  font-size: 13px;
}
.table th {
  background-color: rgba(255,255,255,0.01);
  color: var(--color-text-secondary);
  font-weight: 600;
  text-transform: uppercase;
  font-size: 10px;
  letter-spacing: 0.05em;
}
.cat-cell {
  display: flex;
  flex-direction: column;
}
.cat-label {
  font-weight: 600;
  color: #fff;
}
.cat-slug {
  font-size: 11px;
  color: var(--color-text-muted);
}
.status {
  font-size: 11px;
  font-weight: 600;
  color: var(--color-text-muted);
}
.status.show {
  color: var(--color-green);
}
.actions {
  display: flex;
  gap: 8px;
}
.action-btn {
  font-size: 11px;
  padding: 4px 8px;
  border-radius: 4px;
  border: 1px solid var(--color-border);
  cursor: pointer;
  background: none;
  font-family: inherit;
  transition: all 0.2s;
}
.action-btn.edit {
  color: var(--color-primary);
}
.action-btn.edit:hover {
  background-color: rgba(252, 219, 86, 0.05);
}
.action-btn.delete {
  color: var(--color-red);
}
.action-btn.delete:hover {
  background-color: rgba(239, 68, 68, 0.05);
}
.form {
  display: flex;
  flex-direction: column;
  gap: 16px;
}
.form-group {
  display: flex;
  flex-direction: column;
  gap: 6px;
}
.label {
  font-size: 11px;
  color: var(--color-text-secondary);
  text-transform: uppercase;
  letter-spacing: 0.05em;
  font-weight: 600;
}
.input {
  background-color: var(--color-bg);
  border: 1px solid var(--color-border);
  border-radius: 8px;
  padding: 8px 12px;
  color: #fff;
  font-family: inherit;
  outline: none;
  font-size: 13px;
}
.input:focus {
  border-color: var(--color-primary);
}
.input:disabled {
  opacity: 0.5;
  cursor: not-allowed;
}
.divider {
  height: 1px;
  background-color: var(--color-border);
}
.check-group {
  flex-direction: row;
  align-items: center;
  gap: 8px;
  cursor: pointer;
}
.check-label {
  font-size: 13px;
  color: var(--color-text-primary);
  user-select: none;
}
.actions-row {
  display: flex;
  gap: 8px;
  margin-top: 8px;
}
.btn-primary {
  background-color: var(--color-primary);
  color: #111;
  font-weight: 600;
  padding: 10px 16px;
  border-radius: 8px;
  font-size: 13px;
  cursor: pointer;
  border: none;
}
.btn-primary:hover {
  background-color: var(--color-primary-hover);
}
.btn-ghost {
  border: 1px solid var(--color-border);
  color: var(--color-text-secondary);
  padding: 10px 16px;
  border-radius: 8px;
  font-size: 13px;
  background: none;
  cursor: pointer;
}
.btn-ghost:hover {
  background-color: var(--color-surface-hover);
  color: #fff;
}

/* Modal Overlay */
.modal-overlay {
  position: fixed;
  top: 0;
  left: 0;
  width: 100vw;
  height: 100vh;
  background-color: rgba(0, 0, 0, 0.6);
  backdrop-filter: blur(8px);
  display: flex;
  justify-content: center;
  align-items: center;
  z-index: 1000;
  animation: fadeIn 0.2s ease-out;
}

.modal-content {
  width: 100%;
  max-width: 500px;
  max-height: 90vh;
  overflow-y: auto;
  box-shadow: 0 20px 25px -5px rgba(0, 0, 0, 0.3), 0 10px 10px -5px rgba(0, 0, 0, 0.3);
  animation: slideUp 0.2s ease-out;
}

.modal-header {
  display: flex;
  justify-content: space-between;
  align-items: center;
  margin-bottom: 12px;
}

.close-btn {
  background: none;
  border: none;
  color: var(--color-text-secondary);
  font-size: 24px;
  cursor: pointer;
  transition: color 0.2s;
  line-height: 1;
  padding: 0;
}

.close-btn:hover {
  color: #fff;
}

@keyframes fadeIn {
  from { opacity: 0; }
  to { opacity: 1; }
}

@keyframes slideUp {
  from { transform: translateY(20px); opacity: 0; }
  to { transform: translateY(0); opacity: 1; }
}
</style>

