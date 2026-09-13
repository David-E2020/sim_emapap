<template>
  <div
    class="claude-login-page"
    :class="{ 'is-dark': isDarkMode, 'is-light': !isDarkMode }"
  >
    <!-- Main Layout Container -->
    <main class="claude-main-layout">
      <!-- Left Column: Authentication Form -->
      <section class="auth-column">
        <div class="auth-content-wrapper">
          <!-- Editorial Serif Heading -->
          <div class="heading-block">
            <h1 class="editorial-title">Empresa Municipal de Agua Potable y Alcantarillado</h1>
            <p class="editorial-subtitle">
              PATACAMAYA
            </p>
          </div>

          <!-- Main Auth Card -->
          <div class="claude-card">
            <!-- Institutional Info Pill -->
            <div class="card-status-pill">
              <span class="status-indicator"></span>
              <span class="status-text">Servidor Central Conectado</span>
            </div>

            <!-- Login Form -->
            <form @submit.prevent="login" class="claude-form" autocomplete="on">
              <!-- Field: Usuario -->
              <div class="form-group">
                <label for="username" class="input-label">Usuario</label>
                <div class="input-wrapper" :class="{ 'has-focus': focusedField === 'user' }">
                  <v-icon class="input-icon" size="20" :color="isDarkMode ? '#7c7a74' : '#6b6962'">
                    {{ icons.mdiAccount }}
                  </v-icon>
                  <input
                    id="username"
                    v-model="usr_usuario"
                    type="text"
                    placeholder="Ingresa tu usuario institucional"
                    autocomplete="username"
                    required
                    @focus="focusedField = 'user'"
                    @blur="focusedField = null"
                    class="custom-input"
                  />
                </div>
              </div>

              <!-- Field: Contraseña -->
              <div class="form-group">
                <label for="password" class="input-label">Contraseña</label>
                <div class="input-wrapper" :class="{ 'has-focus': focusedField === 'pass' }">
                  <v-icon class="input-icon" size="20" :color="isDarkMode ? '#7c7a74' : '#6b6962'">
                    {{ icons.mdiLockOutline }}
                  </v-icon>
                  <input
                    id="password"
                    v-model="password"
                    :type="isPasswordVisible ? 'text' : 'password'"
                    placeholder="••••••••••••"
                    autocomplete="current-password"
                    required
                    @focus="focusedField = 'pass'"
                    @blur="focusedField = null"
                    class="custom-input"
                  />
                  <button
                    type="button"
                    class="password-toggle-btn"
                    @click="isPasswordVisible = !isPasswordVisible"
                    aria-label="Alternar visibilidad de contraseña"
                  >
                    <v-icon size="19" :color="isDarkMode ? '#8c8a84' : '#6b6962'">
                      {{ isPasswordVisible ? icons.mdiEyeOffOutline : icons.mdiEyeOutline }}
                    </v-icon>
                  </button>
                </div>
              </div>

              <!-- Primary Submit Button -->
              <button
                type="submit"
                class="claude-btn-primary"
                :disabled="loaderLogin"
              >
                <span v-if="!loaderLogin" class="btn-text">Ingresar al sistema</span>
                <v-progress-circular
                  v-else
                  indeterminate
                  size="20"
                  width="2.2"
                  :color="isDarkMode ? '#141413' : '#ffffff'"
                ></v-progress-circular>
              </button>

              <!-- Footer Legal / Policy Note -->
              <p class="card-footer-text">
                Para acceder al sistema debe contar con credenciales autorizadas en
                <span class="highlight-link">EMAPAP · PATACAMAYA</span>.
              </p>
            </form>
          </div>

          <!-- Bottom Pill Button (Secondary Action) -->
          <div class="secondary-action-container">
            <button
              type="button"
              class="claude-btn-secondary"
              @click="showHelpDialog = true"
            >
              <v-icon size="16" class="me-2" :color="isDarkMode ? '#9e9c96' : '#5a5852'">
                {{ icons.mdiHelpCircleOutline }}
              </v-icon>
              Manual de usuario y asistencia técnica
            </button>
            <button
              type="button"
              class="theme-toggle-btn"
              @click="toggleTheme"
              :title="isDarkMode ? 'Cambiar a tema claro' : 'Cambiar a tema oscuro'"
              aria-label="Cambiar tema"
            >
              <v-icon size="18" :color="isDarkMode ? '#e5e3dd' : '#3d3b36'">
                {{ isDarkMode ? icons.mdiWeatherSunny : icons.mdiWeatherNight }}
              </v-icon>
              <span class="theme-toggle-text">{{ isDarkMode ? 'Modo Claro' : 'Modo Oscuro' }}</span>
            </button>
          </div>
        </div>
      </section>

      <!-- Right Column: Rounded Hero Video Card (Claude Authentic Proportions) -->
      <section class="hero-column d-none d-lg-flex">
        <div class="hero-image-card">
          <video
            ref="heroVideo"
            class="hero-video"
            autoplay
            loop
            muted
            playsinline
            poster="/images/logo-ciclo-poster.jpg"
          >
            <source src="/images/logo-ciclo.mp4" type="video/mp4" />
            <source src="/images/logo%20ciclo.mp4" type="video/mp4" />
          </video>
        </div>
      </section>
    </main>

    <!-- Help & Support Dialog -->
    <v-dialog v-model="showHelpDialog" max-width="500" content-class="claude-dialog">
      <div class="dialog-card" :class="{ 'is-dark': isDarkMode, 'is-light': !isDarkMode }">
        <div class="dialog-header">
          <div class="dialog-title-group">
            <v-icon color="#0284c7" class="me-2">{{ icons.mdiInformationOutline }}</v-icon>
            <h3 class="dialog-title">Asistencia & Soporte EMAPAP</h3>
          </div>
          <button class="dialog-close-btn" @click="showHelpDialog = false">
            <v-icon :color="isDarkMode ? '#a09e96' : '#6b6962'" size="20">{{ icons.mdiClose }}</v-icon>
          </button>
        </div>
        <div class="dialog-body">
          <p class="dialog-desc">
            Si tiene problemas para acceder al sistema o requiere la creación o habilitación de su usuario institucional, contacte a la administración:
          </p>
          <div class="dialog-info-box">
            <div class="info-row">
              <span class="info-label">Institución:</span>
              <span class="info-val">EMAPAP · G.A.M. Patacamaya</span>
            </div>
            <div class="info-row">
              <span class="info-label">Soporte Técnico:</span>
              <span class="info-val">Unidad de Informática y Sistemas</span>
            </div>
            <div class="info-row">
              <span class="info-label">Oficinas Centrales:</span>
              <span class="info-val">Av. Panamericana s/n, Patacamaya - La Paz</span>
            </div>
          </div>
        </div>
        <div class="dialog-footer">
          <button class="claude-btn-primary dialog-btn" @click="showHelpDialog = false">
            Entendido
          </button>
        </div>
      </div>
    </v-dialog>

    <!-- Sleek Theme Snackbar Notification -->
    <v-snackbar
      v-model="snackbar.status"
      bottom
      :timeout="3500"
      content-class="claude-snackbar"
    >
      <div class="snackbar-content">
        <span class="snackbar-dot" :class="snackbar.color"></span>
        <span class="snackbar-text">{{ snackbar.text }}</span>
      </div>
      <template v-slot:action="{ attrs }">
        <v-btn
          text
          small
          v-bind="attrs"
          :color="isDarkMode ? '#ffffff' : '#141413'"
          class="snackbar-close-btn"
          @click="snackbar.status = false"
        >
          Cerrar
        </v-btn>
      </template>
    </v-snackbar>
  </div>
</template>

<script>
import {
  mdiAccount,
  mdiLockOutline,
  mdiEyeOutline,
  mdiEyeOffOutline,
  mdiHelpCircleOutline,
  mdiInformationOutline,
  mdiClose,
  mdiWeatherNight,
  mdiWeatherSunny,
} from '@mdi/js'

export default {
  name: 'LoginPage',
  data: () => ({
    usr_usuario: '',
    password: '',
    isPasswordVisible: false,
    focusedField: null,
    loaderLogin: false,
    showHelpDialog: false,
    snackbar: {
      status: false,
      text: '',
      color: '',
    },
    icons: {
      mdiAccount,
      mdiLockOutline,
      mdiEyeOutline,
      mdiEyeOffOutline,
      mdiHelpCircleOutline,
      mdiInformationOutline,
      mdiClose,
      mdiWeatherNight,
      mdiWeatherSunny,
    },
  }),
  computed: {
    isDarkMode() {
      return this.$vuetify.theme.dark
    },
  },
  methods: {
    toggleTheme() {
      const applyTheme = () => {
        this.$vuetify.theme.dark = !this.$vuetify.theme.dark
        localStorage.setItem('theme_dark', this.$vuetify.theme.dark)
        return this.$nextTick()
      }

      if (document.startViewTransition) {
        document.startViewTransition(() => applyTheme())
      } else {
        applyTheme()
      }
    },
    login() {
      if (!this.usr_usuario.trim() || !this.password) {
        this.snackbar = {
          status: true,
          text: 'Por favor, ingrese usuario y contraseña.',
          color: 'error',
        }
        return
      }

      this.loaderLogin = true
      const usr_usuario = this.usr_usuario.trim()
      const password = this.password

      this.$store
        .dispatch('auth/login', { usr_usuario, password })
        .then(res => {
          this.loaderLogin = false
          const route_ = res.data.rute_home || 'dashboard'
          this.$router.push({ name: route_ })
        })
        .catch(err => {
          this.loaderLogin = false
          if (err.response && err.response.data && err.response.data.message) {
            this.snackbar = {
              status: true,
              text: err.response.data.message,
              color: 'error',
            }
          } else {
            this.snackbar = {
              status: true,
              text: 'Credenciales inválidas o error de conexión con el servidor.',
              color: 'error',
            }
          }
        })
    },
  },
}
</script>

<style lang="scss" scoped>
@import url('https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800&display=swap');

/* Main Page Container - Supports Dark and Light Themes seamlessly via CSS variables */
.claude-login-page {
  /* Dark Theme Tokens (Default) */
  --claude-bg: #141413;
  --claude-text-title: #f7f6f2;
  --claude-text-sub: #9c9a92;
  --claude-card-bg: #1c1b18;
  --claude-card-border: rgba(255, 255, 255, 0.08);
  --claude-card-shadow: 0 16px 36px -12px rgba(0, 0, 0, 0.5);
  --claude-pill-bg: #23221f;
  --claude-pill-border: rgba(255, 255, 255, 0.06);
  --claude-pill-text: #b5b3ab;
  --claude-input-bg: #232220;
  --claude-input-border: rgba(255, 255, 255, 0.11);
  --claude-input-focus-border: rgba(255, 255, 255, 0.38);
  --claude-input-focus-bg: #272623;
  --claude-input-text: #f5f4f0;
  --claude-input-placeholder: #6e6c66;
  --claude-input-label: #b0ada6;
  --claude-btn-primary-bg: #ffffff;
  --claude-btn-primary-text: #141413;
  --claude-btn-primary-hover: #eae9e5;
  --claude-btn-sec-bg: #232220;
  --claude-btn-sec-border: rgba(255, 255, 255, 0.08);
  --claude-btn-sec-text: #b5b3ac;
  --claude-btn-sec-hover-bg: #2a2926;
  --claude-btn-sec-hover-text: #f5f4ef;
  --claude-footer-text: #787670;
  --claude-footer-highlight: #b5b2aa;
  --claude-divider: rgba(255, 255, 255, 0.12);
  --claude-brand-badge-bg: #242321;
  --claude-brand-badge-border: rgba(255, 255, 255, 0.08);
  --claude-brand-badge-text: #9c9a92;
  --claude-brand-title: #e5e3dd;
  --claude-brand-accent: #38bdf8;
  --claude-hero-border: rgba(255, 255, 255, 0.08);
  --claude-hero-shadow: 0 20px 48px -10px rgba(0, 0, 0, 0.65);
  --claude-toggle-bg: #232220;
  --claude-toggle-border: rgba(255, 255, 255, 0.08);
  --claude-toggle-text: #b5b3ab;

  /* Light Theme Tokens */
  &.is-light {
    --claude-bg: #faf9f5;
    --claude-text-title: #141413;
    --claude-text-sub: #686660;
    --claude-card-bg: #ffffff;
    --claude-card-border: rgba(0, 0, 0, 0.08);
    --claude-card-shadow: 0 16px 36px -12px rgba(0, 0, 0, 0.08);
    --claude-pill-bg: #f5f4ef;
    --claude-pill-border: rgba(0, 0, 0, 0.06);
    --claude-pill-text: #5c5a54;
    --claude-input-bg: #fbfbfa;
    --claude-input-border: rgba(0, 0, 0, 0.14);
    --claude-input-focus-border: #141413;
    --claude-input-focus-bg: #ffffff;
    --claude-input-text: #141413;
    --claude-input-placeholder: #8e8b83;
    --claude-input-label: #4a4843;
    --claude-btn-primary-bg: #141413;
    --claude-btn-primary-text: #ffffff;
    --claude-btn-primary-hover: #2b2a27;
    --claude-btn-sec-bg: #ffffff;
    --claude-btn-sec-border: rgba(0, 0, 0, 0.12);
    --claude-btn-sec-text: #4e4c46;
    --claude-btn-sec-hover-bg: #f4f3ed;
    --claude-btn-sec-hover-text: #141413;
    --claude-footer-text: #807e77;
    --claude-footer-highlight: #3b3935;
    --claude-divider: rgba(0, 0, 0, 0.12);
    --claude-brand-badge-bg: #f0ede4;
    --claude-brand-badge-border: rgba(0, 0, 0, 0.08);
    --claude-brand-badge-text: #6a6861;
    --claude-brand-title: #141413;
    --claude-brand-accent: #0284c7;
    --claude-hero-border: rgba(0, 0, 0, 0.08);
    --claude-hero-shadow: 0 20px 48px -10px rgba(0, 0, 0, 0.16);
    --claude-toggle-bg: #ffffff;
    --claude-toggle-border: rgba(0, 0, 0, 0.1);
    --claude-toggle-text: #4a4843;
  }

  background-color: var(--claude-bg) !important;
  min-height: 100vh;
  width: 100%;
  display: flex;
  flex-direction: column;
  position: relative;
  overflow-x: hidden;
  font-family: 'Inter', -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, sans-serif;
  color: var(--claude-text-title);
  box-sizing: border-box;
  transition: background-color 0.3s ease, color 0.3s ease;
}

/* Split Main Layout */
.claude-main-layout {
  display: flex;
  flex: 1;
  width: 100%;
  max-width: 1180px;
  margin: 0 auto;
  min-height: 100vh;
  padding: 32px 24px;
  box-sizing: border-box;
  align-items: center;
  justify-content: center;
  gap: 56px;

  @media (max-width: 960px) {
    padding: 24px 16px;
    justify-content: center;
    gap: 0;
  }
}

/* Left Column: Form Section */
.auth-column {
  flex: 1;
  max-width: 440px;
  display: flex;
  align-items: center;
  justify-content: center;
  padding: 0;

  @media (max-width: 960px) {
    padding: 0;
    width: 100%;
  }

  .auth-content-wrapper {
    width: 100%;
    max-width: 420px;
    display: flex;
    flex-direction: column;
    align-items: center;
  }
}

/* Editorial Title & Subtitle */
.heading-block {
  text-align: center;
  margin-bottom: 28px;
  width: 100%;

  .editorial-title {
    font-family: 'Inter', -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, sans-serif;
    font-size: 1.75rem;
    font-weight: 700;
    line-height: 1.25;
    color: var(--claude-text-title);
    margin-bottom: 8px;
    letter-spacing: -0.025em;
    transition: color 0.3s ease;

    @media (max-width: 600px) {
      font-size: 1.45rem;
    }
  }

  .editorial-subtitle {
    font-family: 'Inter', -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, sans-serif;
    font-size: 0.8125rem;
    font-weight: 700;
    letter-spacing: 0.16em;
    text-transform: uppercase;
    line-height: 1.4;
    color: var(--claude-brand-accent);
    margin: 0 auto;
    max-width: 380px;
    transition: color 0.3s ease;
  }
}

/* Main Card Container */
.claude-card {
  width: 100%;
  background-color: var(--claude-card-bg);
  border: 1px solid var(--claude-card-border);
  border-radius: 24px;
  padding: 26px 26px 24px 26px;
  box-shadow: var(--claude-card-shadow);
  display: flex;
  flex-direction: column;
  box-sizing: border-box;
  transition: all 0.3s ease;
}

/* Status Pill in Card Header */
.card-status-pill {
  display: inline-flex;
  align-items: center;
  gap: 8px;
  background-color: var(--claude-pill-bg);
  border: 1px solid var(--claude-pill-border);
  border-radius: 20px;
  padding: 6px 14px;
  margin: 0 auto 22px auto;
  transition: all 0.3s ease;

  .status-indicator {
    width: 7px;
    height: 7px;
    border-radius: 50%;
    background-color: #16a34a;
    box-shadow: 0 0 8px rgba(22, 163, 74, 0.6);
  }

  .status-text {
    font-size: 12px;
    font-weight: 500;
    color: var(--claude-pill-text);
    transition: color 0.3s ease;
  }
}

/* Form Styles */
.claude-form {
  display: flex;
  flex-direction: column;
  gap: 16px;
  width: 100%;
}

.form-group {
  display: flex;
  flex-direction: column;
  gap: 6px;
  width: 100%;

  .input-label {
    font-size: 12.5px;
    font-weight: 500;
    color: var(--claude-input-label);
    letter-spacing: 0.01em;
    transition: color 0.3s ease;
  }
}

/* Custom Input Field */
.input-wrapper {
  display: flex;
  align-items: center;
  background-color: var(--claude-input-bg);
  border: 1px solid var(--claude-input-border);
  border-radius: 12px;
  padding: 0 14px;
  height: 48px;
  transition: all 0.2s ease;
  width: 100%;
  box-sizing: border-box;

  &.has-focus {
    border-color: var(--claude-input-focus-border);
    background-color: var(--claude-input-focus-bg);
    box-shadow: 0 0 0 3px rgba(0, 0, 0, 0.04);
  }

  .input-icon {
    margin-right: 10px;
    flex-shrink: 0;
  }

  .custom-input {
    flex: 1;
    background: transparent;
    border: none;
    outline: none;
    color: var(--claude-input-text);
    font-size: 14px;
    font-family: inherit;
    width: 100%;

    &::placeholder {
      color: var(--claude-input-placeholder);
    }
  }

  .password-toggle-btn {
    background: transparent;
    border: none;
    cursor: pointer;
    padding: 4px;
    display: flex;
    align-items: center;
    justify-content: center;
    border-radius: 6px;
    transition: opacity 0.2s;

    &:hover {
      opacity: 0.8;
    }
  }
}

/* Primary Button (White in Dark Mode, Black in Light Mode) */
.claude-btn-primary {
  display: flex;
  align-items: center;
  justify-content: center;
  width: 100%;
  height: 48px;
  background-color: var(--claude-btn-primary-bg);
  color: var(--claude-btn-primary-text);
  border: none;
  border-radius: 12px;
  font-size: 14.5px;
  font-weight: 600;
  cursor: pointer;
  margin-top: 8px;
  transition: background-color 0.2s ease, transform 0.15s ease, color 0.2s ease;

  &:hover:not(:disabled) {
    background-color: var(--claude-btn-primary-hover);
    transform: translateY(-1px);
  }

  &:active:not(:disabled) {
    transform: translateY(0);
  }

  &:disabled {
    opacity: 0.75;
    cursor: not-allowed;
  }

  .btn-text {
    letter-spacing: 0.01em;
  }
}

/* Footer text inside card */
.card-footer-text {
  font-size: 12px;
  line-height: 1.45;
  color: var(--claude-footer-text);
  text-align: center;
  margin: 6px 0 0 0;
  transition: color 0.3s ease;

  .highlight-link {
    color: var(--claude-footer-highlight);
    font-weight: 500;
    transition: color 0.3s ease;
  }
}

/* Secondary Actions Below Card */
.secondary-action-container {
  margin-top: 18px;
  display: flex;
  flex-direction: column;
  align-items: center;
  gap: 10px;
  width: 100%;
}

.claude-btn-secondary {
  display: inline-flex;
  align-items: center;
  justify-content: center;
  background-color: var(--claude-btn-sec-bg);
  border: 1px solid var(--claude-btn-sec-border);
  border-radius: 12px;
  padding: 9px 20px;
  font-size: 13px;
  font-weight: 500;
  color: var(--claude-btn-sec-text);
  cursor: pointer;
  box-shadow: 0 2px 6px rgba(0, 0, 0, 0.04);
  transition: all 0.2s ease;

  &:hover {
    background-color: var(--claude-btn-sec-hover-bg);
    color: var(--claude-btn-sec-hover-text);
    transform: translateY(-1px);
  }
}

.theme-toggle-btn {
  display: inline-flex;
  align-items: center;
  justify-content: center;
  gap: 8px;
  background-color: var(--claude-toggle-bg);
  border: 1px solid var(--claude-toggle-border);
  color: var(--claude-toggle-text);
  padding: 8px 18px;
  border-radius: 20px;
  font-size: 12.5px;
  font-weight: 500;
  cursor: pointer;
  box-shadow: 0 2px 6px rgba(0, 0, 0, 0.04);
  transition: all 0.2s ease;

  &:hover {
    background-color: var(--claude-btn-sec-hover-bg);
    color: var(--claude-btn-sec-hover-text);
    transform: translateY(-1px);
  }

  .theme-toggle-text {
    letter-spacing: 0.01em;
  }
}

/* Right Column: Hero Video Card (Claude Authentic Proportions) */
.hero-column {
  flex: 1;
  max-width: 540px;
  display: flex;
  align-items: center;
  justify-content: center;
  height: calc(100vh - 80px);
  max-height: 720px;
  padding: 0;
}

.hero-image-card {
  position: relative;
  width: 100%;
  height: 100%;
  border-radius: 28px;
  overflow: hidden;
  border: none;
  box-shadow: var(--claude-hero-shadow);
  background-color: transparent;
  transition: all 0.3s ease;

  .hero-video {
    width: 100%;
    height: 100%;
    object-fit: cover;
    object-position: center;
    display: block;
    border-radius: inherit;
    transition: transform 0.6s ease;
  }

  &:hover .hero-video {
    transform: scale(1.015);
  }
}

/* Dialog Styles */
::v-deep .claude-dialog {
  border-radius: 20px !important;
  overflow: hidden;
}

.dialog-card {
  padding: 24px;
  border-radius: 20px;

  &.is-dark {
    background-color: #1b1a18;
    color: #f4f3ef;
    border: 1px solid rgba(255, 255, 255, 0.1);

    .dialog-title {
      color: #f5f4ef;
    }
    .dialog-desc {
      color: #a8a69e;
    }
    .dialog-info-box {
      background: #232220;
      border: 1px solid rgba(255, 255, 255, 0.08);
    }
    .info-label {
      color: #8c8a82;
    }
    .info-val {
      color: #e3e2dd;
    }
    .dialog-btn {
      background-color: #ffffff;
      color: #141413;
    }
  }

  &.is-light {
    background-color: #ffffff;
    color: #141413;
    border: 1px solid rgba(0, 0, 0, 0.1);

    .dialog-title {
      color: #141413;
    }
    .dialog-desc {
      color: #686660;
    }
    .dialog-info-box {
      background: #fbfbfa;
      border: 1px solid rgba(0, 0, 0, 0.08);
    }
    .info-label {
      color: #787670;
    }
    .info-val {
      color: #141413;
    }
    .dialog-btn {
      background-color: #141413;
      color: #ffffff;
    }
  }

  .dialog-header {
    display: flex;
    justify-content: space-between;
    align-items: center;
    margin-bottom: 18px;

    .dialog-title-group {
      display: flex;
      align-items: center;
    }

    .dialog-title {
      font-size: 17px;
      font-weight: 600;
      margin: 0;
    }

    .dialog-close-btn {
      background: transparent;
      border: none;
      cursor: pointer;
      padding: 4px;
    }
  }

  .dialog-desc {
    font-size: 13.5px;
    line-height: 1.5;
    margin-bottom: 16px;
  }

  .dialog-info-box {
    border-radius: 12px;
    padding: 14px 16px;
    display: flex;
    flex-direction: column;
    gap: 10px;
    margin-bottom: 20px;

    .info-row {
      display: flex;
      flex-direction: column;
      gap: 2px;

      .info-label {
        font-size: 11.5px;
        font-weight: 500;
      }

      .info-val {
        font-size: 13px;
        font-weight: 500;
      }
    }
  }

  .dialog-footer {
    display: flex;
    justify-content: flex-end;

    .dialog-btn {
      height: 42px;
      padding: 0 24px;
      font-size: 13.5px;
      width: auto;
    }
  }
}

/* Snackbar custom styling */
::v-deep .claude-snackbar {
  border-radius: 12px !important;
  box-shadow: 0 10px 30px rgba(0, 0, 0, 0.4) !important;

  .snackbar-content {
    display: flex;
    align-items: center;
    gap: 10px;

    .snackbar-dot {
      width: 8px;
      height: 8px;
      border-radius: 50%;
      background-color: #ef4444;

      &.warning {
        background-color: #f59e0b;
      }

      &.success {
        background-color: #10b981;
      }
    }

    .snackbar-text {
      font-size: 13px;
    }
  }
}
</style>
