export const autentication = {
    namespaced: true,
    state:{
        status: '',
        token: localStorage.getItem('token') || '',
        user : JSON.parse(localStorage.getItem('user'))|| {},
        permissions: JSON.parse(localStorage.getItem('permissions')) || [],
        roles: JSON.parse(localStorage.getItem('roles')) || [],
    },
    mutations: {
        auth_request(state){
          state.status = 'loading';
        },
        auth_success(state, {token, user,permissions,roles}){
          state.status = 'success'
          state.token = token
          state.user = user
          state.permissions = permissions
          state.roles = roles
        },
        auth_error(state){
          state.status = 'error'
        },
        logout(state){
          state.status = ''
          state.token = ''
        },
    },
    actions:{
        login({commit}, user){
            return new Promise((resolve, reject) => {
              commit('auth_request')
              axios({url: '../api/login', data: user, method: 'POST' })
              .then(resp => {
                const token = resp.data.token
                const user = resp.data.user
                const permissions = resp.data.permissions
                const roles = resp.data.roles
                const employee = resp.data.employee
                localStorage.setItem('token', token)
                localStorage.setItem('user',JSON.stringify(user))
                localStorage.setItem('permissions',JSON.stringify(permissions))
                localStorage.setItem('roles',JSON.stringify(roles))
                localStorage.setItem('rol',resp.data.rol);
                localStorage.setItem('rute_home',resp.data.rute_home);
                localStorage.setItem('employee',JSON.stringify(employee))
                axios.defaults.headers.common['Authorization'] = 'Bearer '+token
                commit('auth_success', {token, user,permissions,roles});
                resolve(resp);
              })
              .catch(err => {
                commit('auth_error')
                localStorage.removeItem('token')
                localStorage.clear()
                reject(err)
              })
            })
        },
        logout({commit}){
            return new Promise((resolve, reject) => {
                commit('logout')
                localStorage.removeItem('token')
                localStorage.clear()
                delete axios.defaults.headers.common['Authorization']
                resolve()
            })
        }
    },
    getters : {
        isLoggedIn: state => !!state.token,
        authStatus: state => state.status,
        userLoged: state=> state.user,
        isAdmin: state=> _.find(state.roles, (role)=> { return role.name == "Administrador"}),
        isRefreshment: state=> _.find(state.roles, (role)=> { return role.name == "Refrigerios"}),
        isRRHH: state=> _.find(state.roles, (role)=> { return role.name == "Recursos Humanos"}),
        isTecnicoRRHH: state=> _.find(state.roles, (role)=> { return role.name == "Tecnico Recursos Humanos"}),
    }

};
