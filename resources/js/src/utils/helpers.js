import moment from 'moment'
import 'moment/locale/es'

moment.locale('es')

/**
 * ============================================================================
 * UTILIDADES NATIVAS DEL ERP EMAPAP (2026)
 * ============================================================================
 */

// 1. FORMATEADORES DE MONEDA BOLIVIANA (BOB)
export const formatMoney = (val, conSimbolo = true) => {
  if (val === null || val === undefined || isNaN(val) || val === '') {
    return conSimbolo ? 'Bs. 0,00' : '0,00'
  }
  const numero = parseFloat(val)
  const partes = numero.toFixed(2).split('.')
  const enteros = partes[0].replace(/\B(?=(\d{3})+(?!\d))/g, '.')
  const decimales = partes[1]
  const formateado = `${enteros},${decimales}`
  return conSimbolo ? `Bs. ${formateado}` : formateado
}

// 2. FORMATEADORES DE FECHA Y HORA (MOMENT ES)
export const formatDate = (date, format = 'DD/MM/YYYY') => {
  if (!date) return '-'
  const m = moment(date)
  return m.isValid() ? m.format(format) : '-'
}

export const formatDateTime = (date, format = 'DD/MM/YYYY HH:mm') => {
  if (!date) return '-'
  const m = moment(date)
  return m.isValid() ? m.format(format) : '-'
}

export const formatRelativeTime = date => {
  if (!date) return '-'
  const m = moment(date)
  return m.isValid() ? m.fromNow() : '-'
}

// 3. SISTEMA DE PERMISOS SPATIE ROBUSTO
export const getUserPermissions = () => {
  try {
    const permissionsStr = localStorage.getItem('permissions')
    if (!permissionsStr) return []
    const parsed = JSON.parse(permissionsStr)
    return Array.isArray(parsed) ? parsed : []
  } catch (e) {
    return []
  }
}

export const isSuperAdmin = () => {
  try {
    const userStr = localStorage.getItem('user')
    if (userStr) {
      const user = JSON.parse(userStr)
      if (user && (user.usr_usuario === 'admin' || user.id === 1)) {
        return true
      }
    }
    const roleStr = localStorage.getItem('rol')
    if (roleStr && (roleStr.includes('Administrador') || roleStr.includes('Admin'))) {
      return true
    }
  } catch (e) {}
  return false
}

export const can = permission => {
  if (!permission) return true
  if (isSuperAdmin()) return true

  const permissions = getUserPermissions()
  if (permissions.includes('*') || permissions.includes('SIGP')) return true

  // Coincidencia exacta
  if (permissions.includes(permission)) return true

  // Coincidencia por módulo wildcard (ej. 'rrhh.*' autoriza 'rrhh.personal.ver')
  const parts = permission.split('.')
  if (parts.length > 1) {
    const wildcard = `${parts[0]}.*`
    if (permissions.includes(wildcard)) return true
  }

  return false
}

export const canAny = (permissionArray = []) => {
  if (!Array.isArray(permissionArray) || permissionArray.length === 0) return true
  if (isSuperAdmin()) return true
  return permissionArray.some(p => can(p))
}

export const canAll = (permissionArray = []) => {
  if (!Array.isArray(permissionArray) || permissionArray.length === 0) return true
  if (isSuperAdmin()) return true
  return permissionArray.every(p => can(p))
}

export const hasRole = roleName => {
  try {
    const rolActual = localStorage.getItem('rol') || ''
    return rolActual.toLowerCase().includes(roleName.toLowerCase())
  } catch (e) {
    return false
  }
}

// 4. WRAPPER ENRIQUECIDO PARA IZITOAST
export const toast = {
  success(message, title = 'Operación Exitosa') {
    if (window.iziToast) {
      window.iziToast.success({
        title,
        message,
        position: 'topRight',
        timeout: 4000,
        transitionIn: 'fadeInDown',
        transitionOut: 'fadeOutUp',
      })
    }
  },
  error(message, title = 'Error') {
    if (window.iziToast) {
      window.iziToast.error({
        title,
        message,
        position: 'topRight',
        timeout: 6000,
        transitionIn: 'fadeInDown',
        transitionOut: 'fadeOutUp',
      })
    }
  },
  warning(message, title = 'Atención') {
    if (window.iziToast) {
      window.iziToast.warning({
        title,
        message,
        position: 'topRight',
        timeout: 5000,
        transitionIn: 'fadeInDown',
        transitionOut: 'fadeOutUp',
      })
    }
  },
  info(message, title = 'Información') {
    if (window.iziToast) {
      window.iziToast.info({
        title,
        message,
        position: 'topRight',
        timeout: 4000,
        transitionIn: 'fadeInDown',
        transitionOut: 'fadeOutUp',
      })
    }
  },
}

// 5. PLUGIN DE INSTALACIÓN PARA VUE
export default {
  install(Vue) {
    // Helpers en el prototype
    Vue.prototype.$formatMoney = formatMoney
    Vue.prototype.$formatDate = formatDate
    Vue.prototype.$formatDateTime = formatDateTime
    Vue.prototype.$timeAgo = formatRelativeTime
    Vue.prototype.$can = can
    Vue.prototype.$canAny = canAny
    Vue.prototype.$canAll = canAll
    Vue.prototype.$hasRole = hasRole
    Vue.prototype.$toast = toast

    // Filtros de plantilla Vue
    Vue.filter('formatMoney', formatMoney)
    Vue.filter('formatDate', formatDate)
    Vue.filter('formatDateTime', formatDateTime)
    Vue.filter('timeAgo', formatRelativeTime)

    // Directiva personalizada v-can
    Vue.directive('can', {
      inserted(el, binding) {
        const requiredPermission = binding.value
        if (!can(requiredPermission)) {
          // Ocultar de manera limpia o remover el elemento
          if (el.parentNode) {
            el.parentNode.removeChild(el)
          } else {
            el.style.display = 'none'
          }
        }
      },
    })
  },
}
