<template>
  <div class="settings-page">
    <div class="page-header">
      <div>
        <h1>Sistem Tənzimləmələri</h1>
        <p class="page-desc">OpenAI API açarı, model seçimi, dublikat filtri və sistem parametrlərini idarə edin (Məlumat bazasında `settings` cədvəlində saxlanılır).</p>
      </div>

      <button @click="saveAll" :disabled="store.loading" class="btn btn-primary">
        <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
          <path d="M19 21H5a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h11l5 5v11a2 2 0 0 1-2 2z"/>
          <polyline points="17 21 17 13 7 13 7 21"/>
          <polyline points="7 3 7 8 15 8"/>
        </svg>
        <span>Yadda Saxla</span>
      </button>
    </div>

    <div class="settings-grid">
      <!-- 1. OpenAI Configuration -->
      <div class="settings-card">
        <div class="card-heading">
          <div class="icon-wrap icon-gold">
            <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
              <path d="M12 2a4 4 0 0 1 4 4v2a4 4 0 0 1-8 0V6a4 4 0 0 1 4-4z"/>
              <rect x="3" y="10" width="18" height="12" rx="4"/>
            </svg>
          </div>
          <div>
            <h3>OpenAI Tənzimləmələri</h3>
            <p>Məqalələrin Azərbaycan dilinə adaptasiyası üçün süni intellekt mühərriki</p>
          </div>
        </div>

        <div class="form-group">
          <label class="form-label">
            <span>OpenAI API Açarı (Secret Key)</span>
            <span class="label-badge">Tələb olunur</span>
          </label>
          <div class="input-with-btn">
            <input 
              type="password" 
              v-model="form.openai_api_key" 
              placeholder="sk-proj-..." 
              class="form-input" 
            />
            <button 
              @click="testOpenAiConnection" 
              :disabled="testingAi || !form.openai_api_key" 
              class="btn btn-secondary btn-sm"
            >
              <span v-if="testingAi">Yoxlanılır...</span>
              <span v-else>Əlaqəni Yoxla</span>
            </button>
          </div>
          <span class="field-hint">Açar birbaşa AI Writer bazasında `settings` cədvəlində şifrəsiz saxlanılır.</span>
          <div v-if="aiTestMsg" class="test-result" :class="aiTestSuccess ? 'res-success' : 'res-error'">
            {{ aiTestMsg }}
          </div>
        </div>

        <div class="form-row-2">
          <div class="form-group">
            <label class="form-label">Model Seçimi</label>
            <select v-model="form.openai_model" class="form-select">
              <option value="gpt-4o-mini">gpt-4o-mini (Tövsiyə olunan, sürətli və qənaətli)</option>
              <option value="gpt-4o">gpt-4o (Maksimum dəqiqlik və zəngin üslub)</option>
              <option value="gpt-4-turbo">gpt-4-turbo</option>
              <option value="gpt-3.5-turbo">gpt-3.5-turbo</option>
            </select>
          </div>

          <div class="form-group">
            <label class="form-label">Temperatur (Yaradıcılıq: {{ form.openai_temperature }})</label>
            <input 
              type="range" 
              min="0.1" 
              max="1.0" 
              step="0.05" 
              v-model="form.openai_temperature" 
              class="form-range" 
            />
            <div class="range-labels">
              <span>Dəqiq (0.1)</span>
              <span>Balanslı (0.7)</span>
              <span>Yaradıcı (1.0)</span>
            </div>
          </div>
        </div>

        <div class="form-group">
          <label class="form-label">Sistem Təlimatı (AI System Prompt)</label>
          <textarea 
            v-model="form.system_prompt" 
            rows="3" 
            class="form-textarea" 
            placeholder="AI üçün sistem təlimatı..."
          ></textarea>
          <span class="field-hint">OpenAI rol təlimatı (məsələn: Sən peşəkar jurnalistsən).</span>
        </div>

        <div class="form-group">
          <div class="label-row-between">
            <label class="form-label mb-0">
              <span>Xəbərlərin Regenerasiya Təlimatı (AI Prompt)</span>
              <span class="label-badge badge-gold">OpenAI Prompt</span>
            </label>
            <button 
              type="button" 
              @click="resetRegeneratePrompt" 
              class="btn-text-action"
              title="İlkin təlimata qaytar"
            >
              <svg width="13" height="13" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                <path d="M3 12a9 9 0 1 0 9-9 9.75 9.75 0 0 0-6.74 2.74L3 8"/>
                <path d="M3 3v5h5"/>
              </svg>
              İlkin təlimata sıfırla
            </button>
          </div>
          <textarea 
            v-model="form.regenerate_prompt" 
            rows="9" 
            class="form-textarea prompt-textarea" 
            placeholder="Xəbərlərin generasiyası üçün prompt..."
          ></textarea>
          <span class="field-hint">
            Xəbər yenidən yazılarkən və ya regenerasiya edilərkən OpenAI-a bu təlimat göndərilir. İstədiyiniz vaxt bu mətni redaktə edib "Yadda Saxla" düyməsinə klikləyərək yeniləyə bilərsiniz.
          </span>
        </div>
      </div>

      <!-- 2. Duplicate Detection Configuration -->
      <div class="settings-card">
        <div class="card-heading">
          <div class="icon-wrap icon-cyan">
            <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
              <rect x="9" y="9" width="13" height="13" rx="2" ry="2"/><path d="M5 15H4a2 2 0 0 1-2-2V4a2 2 0 0 1 2-2h9a2 2 0 0 1 2 2v1"/>
            </svg>
          </div>
          <div>
            <h3>Dublikat Xəbərlərin Filtrlənməsi</h3>
            <p>Müxtəlif saytlarda eyni xəbərin təkrar paylaşılmasının qarşısını alın</p>
          </div>
        </div>

        <div class="form-group">
          <label class="toggle-control">
            <input type="checkbox" v-model="form.duplicate_check_enabled" true-value="1" false-value="0" />
            <span class="switch-box"></span>
            <div>
              <strong>Dublikat Yoxlanışı Aktivdir</strong>
              <p class="field-hint">Mənbələrdən gələn hər bir xəbər həm daxili bazadakı, həm də alt404.az saytındakı mövcud məqalələrlə müqayisə edilir.</p>
            </div>
          </label>
        </div>

        <div class="form-group">
          <label class="form-label">Bənzərlik Həddi (Similarity Threshold: {{ Math.round(form.duplicate_threshold * 100) }}%)</label>
          <input 
            type="range" 
            min="0.4" 
            max="0.95" 
            step="0.05" 
            v-model="form.duplicate_threshold" 
            class="form-range" 
          />
          <div class="range-labels">
            <span>Həssas (40%)</span>
            <span>Standart (70%)</span>
            <span>Ciddi (95%)</span>
          </div>
          <span class="field-hint">Başlıqların və söz leksikasının kəsişmə faizi bu həddi keçdikdə xəbər avtomatik olaraq 'duplicate' statusu alır.</span>
        </div>

        <div class="form-group">
          <label class="toggle-control">
            <input type="checkbox" v-model="form.auto_publish_draft" true-value="1" false-value="0" />
            <span class="switch-box"></span>
            <div>
              <strong>Avtomatik Qaralama Kimi Əlavə Et</strong>
              <p class="field-hint">Rewrite edildikdən dərhal sonra əsas saytın `articles` cədvəlinə `published = 0` (Qaralama) kimi qeyd edilsin.</p>
            </div>
          </label>
        </div>
      </div>

      <!-- 3. Database Connection Diagnostic -->
      <div class="settings-card card-full">
        <div class="card-heading">
          <div class="icon-wrap icon-green">
            <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
              <ellipse cx="12" cy="5" rx="9" ry="3"/>
              <path d="M3 5V19A9 3 0 0 0 21 19V5"/>
              <path d="M3 12A9 3 0 0 0 21 12"/>
            </svg>
          </div>
          <div>
            <h3>Məlumat Bazası Əlaqə Diaqnostikası</h3>
            <p>AI Writer (ayrı DB) və Əsas Veb Sayt (alt404) qoşulma vəziyyəti</p>
          </div>
        </div>

        <div class="db-status-grid">
          <div class="db-box">
            <div class="db-box-header">
              <span class="db-dot" :class="dbStatus.ai_db ? 'dot-active' : 'dot-disabled'"></span>
              <strong>AI Writer Məlumat Bazası (MySQL)</strong>
            </div>
            <p class="db-info">Ayrı baza: <code>alt404_ai_writer</code> (Mənbələr, toplanmış xəbərlər və tənzimləmələr)</p>
            <div v-if="dbStatus.ai_err" class="db-err">{{ dbStatus.ai_err }}</div>
            <div v-else class="db-ok">Qoşulma uğurludur</div>
          </div>

          <div class="db-box">
            <div class="db-box-header">
              <span class="db-dot" :class="dbStatus.web_db ? 'dot-active' : 'dot-disabled'"></span>
              <strong>Əsas Veb Sayt Bazası (MySQL)</strong>
            </div>
            <p class="db-info">Hədəf baza: <code>alt404</code> (Draft xəbərlərin göndərildiyi `articles` cədvəli)</p>
            <div v-if="dbStatus.web_err" class="db-err">{{ dbStatus.web_err }}</div>
            <div v-else class="db-ok">Qoşulma uğurludur</div>
          </div>
        </div>

        <div class="db-actions">
          <button @click="testDatabaseConnections" :disabled="testingDb" class="btn btn-secondary btn-sm">
            <span v-if="testingDb">Yoxlanılır...</span>
            <span v-else>Bazaları Yenidən Yoxla</span>
          </button>
        </div>
      </div>
    </div>
  </div>
</template>

<script>
import { ref, reactive, onMounted } from 'vue'
import { useAiWriterStore } from '../stores/aiWriter'

const DEFAULT_REGENERATE_PROMPT = `Linkdəki xəbəri diqqətlə oxu, həqiqiliyini digər mənbələrdə araşdırdıqdan sonra Azərbaycan dilində yaz. Mətnlə bağlı tələblər belədir -
Mətnə uyğun ən uyğun, təsirli başlıq yaz.
Bütün xüsusi isimlər Azərbaycan dilində yazılması, düzgün yazılış forması saytlarda yoxlanılmalıdır.
Səliqəli, abzaslara bölünmüş şəkildə aydın dildə yaz. Xəbərə və yaşanan hadisələrə münasibət bildirmə, sadəcə, xəbəri çatdır.
Mətndə xüsusi vurğulamaq istədiyin hissələri boldla və ya kursivlə vermə, sadə fontla ver hamısını.
Sonda xəbəri yazarkən istifadə etdiyin bütün mənbələri bu şəkildə qeyd et -
MƏNBƏ:
mənbə 1 link şəklində
mənbə 2 link şəklində
mənbə 3 link şəklində və s.
İstinad etdiyin mənbə sayı 5-dən çox olmasın. Link uzun olduqda ixtisar et və … nöqtə ilə tamamla. Məsələn, [ https://www.kaldata.com/it-%d0%bd%d0%be%](https://www.kaldata.com/it-%25d0%25bd%25d0%25be%25)…`

export default {
  name: 'Settings',
  setup() {
    const store = useAiWriterStore()
    const testingAi = ref(false)
    const testingDb = ref(false)
    const aiTestMsg = ref('')
    const aiTestSuccess = ref(false)
    const dbStatus = ref({ ai_db: false, web_db: false, ai_err: '', web_err: '' })

    const form = reactive({
      openai_api_key: '',
      openai_model: 'gpt-4o-mini',
      openai_temperature: '0.7',
      system_prompt: '',
      regenerate_prompt: DEFAULT_REGENERATE_PROMPT,
      duplicate_check_enabled: '1',
      duplicate_threshold: '0.70',
      auto_publish_draft: '1'
    })

    onMounted(async () => {
      await store.fetchSettings()
      if (store.settings) {
        Object.keys(form).forEach(k => {
          if (store.settings[k] && store.settings[k].setting_value !== undefined && store.settings[k].setting_value !== '') {
            form[k] = store.settings[k].setting_value
          }
        })
      }
      testDatabaseConnections()
    })

    const resetRegeneratePrompt = () => {
      form.regenerate_prompt = DEFAULT_REGENERATE_PROMPT
      store.showToast('Regenerasiya təlimatı ilkin formata qaytarıldı. Saxlamaq üçün "Yadda Saxla" düyməsinə klikləyin.', 'info')
    }

    const saveAll = async () => {
      await store.saveSettings(form)
    }

    const testOpenAiConnection = async () => {
      testingAi.value = true
      aiTestMsg.value = ''
      try {
        const res = await store.testOpenAI(form.openai_api_key, form.openai_model)
        aiTestSuccess.value = true
        aiTestMsg.value = res.message || 'OpenAI API ilə əlaqə uğurludur!'
      } catch (err) {
        aiTestSuccess.value = false
        aiTestMsg.value = err.message
      } finally {
        testingAi.value = false
      }
    }

    const testDatabaseConnections = async () => {
      testingDb.value = true
      try {
        dbStatus.value = await store.testDatabase()
      } finally {
        testingDb.value = false
      }
    }

    return {
      store,
      form,
      saveAll,
      resetRegeneratePrompt,
      testingAi,
      testingDb,
      aiTestMsg,
      aiTestSuccess,
      testOpenAiConnection,
      dbStatus,
      testDatabaseConnections
    }
  }
}
</script>

<style scoped>
.settings-page {
  display: flex;
  flex-direction: column;
  gap: 24px;
}

.page-header {
  display: flex;
  align-items: center;
  justify-content: space-between;
}

.page-header h1 {
  font-size: 1.6rem;
  font-weight: 700;
  color: #fff;
}

.page-desc {
  font-size: 0.9rem;
  color: var(--text-muted);
  margin-top: 4px;
}

/* Grid */
.settings-grid {
  display: grid;
  grid-template-columns: 1fr 1fr;
  gap: 24px;
}

.card-full {
  grid-column: 1 / -1;
}

.settings-card {
  background: var(--bg-card);
  border: 1px solid var(--border-subtle);
  border-radius: var(--radius-lg);
  padding: 26px;
  display: flex;
  flex-direction: column;
  gap: 20px;
}

.card-heading {
  display: flex;
  align-items: center;
  gap: 14px;
  border-bottom: 1px solid rgba(255, 255, 255, 0.05);
  padding-bottom: 16px;
}

.icon-wrap {
  width: 42px;
  height: 42px;
  border-radius: var(--radius-md);
  display: flex;
  align-items: center;
  justify-content: center;
}
.icon-gold { background: rgba(252, 219, 86, 0.12); color: var(--color-primary); }
.icon-cyan { background: rgba(0, 240, 255, 0.12); color: var(--color-accent); }
.icon-green { background: rgba(16, 185, 129, 0.12); color: #10b981; }

.card-heading h3 {
  font-size: 1.15rem;
  font-weight: 700;
  color: #fff;
}

.card-heading p {
  font-size: 0.82rem;
  color: var(--text-muted);
}

/* Forms */
.form-group {
  display: flex;
  flex-direction: column;
  gap: 8px;
}

.form-label {
  display: flex;
  align-items: center;
  justify-content: space-between;
  font-size: 0.85rem;
  font-weight: 600;
  color: #fff;
}

.label-badge {
  font-size: 0.7rem;
  background: rgba(252, 219, 86, 0.15);
  color: var(--color-primary);
  padding: 2px 6px;
  border-radius: 4px;
}

.badge-gold {
  background: rgba(252, 219, 86, 0.18);
  color: var(--color-primary);
  font-weight: 600;
}

.label-row-between {
  display: flex;
  align-items: center;
  justify-content: space-between;
  margin-bottom: 2px;
}

.mb-0 {
  margin-bottom: 0 !important;
}

.btn-text-action {
  background: transparent;
  border: none;
  color: var(--color-primary);
  font-size: 0.76rem;
  cursor: pointer;
  display: flex;
  align-items: center;
  gap: 5px;
  padding: 3px 8px;
  border-radius: var(--radius-sm);
  transition: all 0.2s;
}

.btn-text-action:hover {
  background: rgba(252, 219, 86, 0.12);
  text-decoration: underline;
}

.prompt-textarea {
  font-family: inherit;
  font-size: 0.84rem;
  line-height: 1.55;
  min-height: 200px;
  resize: vertical;
}

.form-input, .form-select, .form-textarea {
  background: var(--bg-surface);
  border: 1px solid var(--border-subtle);
  color: #fff;
  padding: 10px 14px;
  border-radius: var(--radius-md);
  font-size: 0.88rem;
  font-family: inherit;
  outline: none;
  transition: border-color 0.2s;
}

.form-input:focus, .form-select:focus, .form-textarea:focus {
  border-color: var(--color-accent);
}

.input-with-btn {
  display: flex;
  gap: 10px;
}

.input-with-btn input {
  flex: 1;
}

.field-hint {
  font-size: 0.78rem;
  color: var(--text-dim);
  line-height: 1.4;
}

.form-row-2 {
  display: grid;
  grid-template-columns: 1fr 1fr;
  gap: 16px;
}

.form-range {
  accent-color: var(--color-primary);
  height: 6px;
  cursor: pointer;
}

.range-labels {
  display: flex;
  justify-content: space-between;
  font-size: 0.72rem;
  color: var(--text-dim);
}

.test-result {
  margin-top: 6px;
  padding: 8px 12px;
  border-radius: var(--radius-sm);
  font-size: 0.82rem;
}
.res-success { background: rgba(16, 185, 129, 0.15); color: #10b981; border: 1px solid rgba(16, 185, 129, 0.3); }
.res-error { background: rgba(239, 68, 68, 0.15); color: #ef4444; border: 1px solid rgba(239, 68, 68, 0.3); }

/* Toggle */
.toggle-control {
  display: flex;
  align-items: flex-start;
  gap: 14px;
  cursor: pointer;
  user-select: none;
}

.toggle-control input {
  display: none;
}

.switch-box {
  width: 40px;
  height: 22px;
  background: #334155;
  border-radius: 99px;
  position: relative;
  transition: all 0.2s ease;
  flex-shrink: 0;
  margin-top: 2px;
}

.switch-box::after {
  content: '';
  position: absolute;
  top: 2px;
  left: 2px;
  width: 18px;
  height: 18px;
  background: #fff;
  border-radius: 50%;
  transition: all 0.2s ease;
}

.toggle-control input:checked + .switch-box {
  background: #10b981;
}

.toggle-control input:checked + .switch-box::after {
  transform: translateX(18px);
}

.toggle-control strong {
  display: block;
  font-size: 0.92rem;
  color: #fff;
  margin-bottom: 2px;
}

/* DB Diagnostic Grid */
.db-status-grid {
  display: grid;
  grid-template-columns: 1fr 1fr;
  gap: 16px;
}

.db-box {
  background: var(--bg-surface);
  border: 1px solid rgba(255, 255, 255, 0.05);
  border-radius: var(--radius-md);
  padding: 16px;
  display: flex;
  flex-direction: column;
  gap: 8px;
}

.db-box-header {
  display: flex;
  align-items: center;
  gap: 8px;
  font-size: 0.92rem;
  color: #fff;
}

.db-dot {
  width: 8px;
  height: 8px;
  border-radius: 50%;
}
.dot-active { background: #10b981; box-shadow: 0 0 8px #10b981; }
.dot-disabled { background: #ef4444; box-shadow: 0 0 8px #ef4444; }

.db-info {
  font-size: 0.8rem;
  color: var(--text-muted);
}

.db-info code {
  color: var(--color-primary);
  background: rgba(252, 219, 86, 0.1);
  padding: 2px 5px;
  border-radius: 4px;
}

.db-ok {
  font-size: 0.78rem;
  color: #10b981;
  font-weight: 600;
}

.db-err {
  font-size: 0.78rem;
  color: #ef4444;
  word-break: break-word;
}

.db-actions {
  display: flex;
  justify-content: flex-end;
}

@media (max-width: 900px) {
  .settings-grid {
    grid-template-columns: 1fr;
  }
}
</style>
