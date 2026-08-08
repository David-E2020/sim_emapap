
<style scoped>
.scroll-submenu {
  height: 370px;
  overflow-y: auto;
}
</style>
<template>
  <div>
    <v-card v-if="usuarios">
      <v-toolbar flat>
        <v-toolbar-title>USUARIOS </v-toolbar-title>
        <v-divider class="mx-4" inset vertical></v-divider>
        <v-spacer></v-spacer>
        <div style="width: 300px;">
          <v-text-field v-model="search" solo append-icon="mdi-magnify" label="Buscar" dense single-line hide-details>
          </v-text-field>
        </div>
      </v-toolbar>

      <v-data-table :headers="headers" :items="usuarios" :search="search">
        <template v-slot:item.nombre="{ item }">
          {{ item.name }}
        </template>

        <template v-slot:item.direccion="{ item }">
          {{ item?item.direccion:'' }}
        </template>

        <template v-slot:item.acciones="{ item }">
          <div v-if="item.permissions.length>0">
            <v-tooltip bottom>
              <template v-slot:activator="{ on: tooltip }">
                <v-btn v-on="{ ...tooltip }" @click="btnQuitarAcceso(item)" icon color="error">
                  <v-icon>mdi-delete</v-icon>
                </v-btn>
              </template>
              <span> Quitar Acceso</span>
            </v-tooltip>

            <v-tooltip bottom>
              <template v-slot:activator="{ on: tooltip }">
                <v-btn v-on="{ ...tooltip }" @click="btnAccesosPermisos(item)" icon color="primary">
                  <v-icon>mdi-format-list-checkbox</v-icon>
                </v-btn>
              </template>
              <span> Accesos y permisos</span>
            </v-tooltip>
          </div>

          <div v-if="!item.permissions.length>0">
            <v-tooltip bottom>
              <template v-slot:activator="{ on: tooltip }">
                <v-btn v-on="{ ...tooltip }" @click="btnAgregarSistema(item)" icon color="primary">
                  <v-icon>mdi-account-plus</v-icon>
                </v-btn>
              </template>
              <span>Asignar al sistema</span>
            </v-tooltip>
          </div>
        </template>
      </v-data-table>
    </v-card>

    <v-card v-if="!usuarios">
      <v-card-text>
        <div class="text-center">
          <v-progress-circular :size="50" color="primary" indeterminate></v-progress-circular>
        </div>
      </v-card-text>
    </v-card>

    <v-snackbar v-model="snackbar.status" bottom :color="snackbar.color" :timeout="1500">
      {{ snackbar.text }}
      <template v-slot:action="{ attrs }">
        <v-btn color="blue" text v-bind="attrs" @click="snackbar.status = false">Cerrar</v-btn>
      </template>
    </v-snackbar>

    <!---------->
    <v-dialog persistent scrollable v-model="dialogPersmisos" width="850">
      <v-card>

        <v-card-title class=" grey lighten-2"> Administrar Roles y Accesos
          <v-spacer></v-spacer>
          <v-btn @click="dialogPersmisos = false" class="ma-2" outlined fab small color="grey">
            <v-icon>mdi-close</v-icon>
          </v-btn>
        </v-card-title>

        <v-card-text>
          <v-row>
            <v-col cols="12">
              <span v-if="selectUsuario"><br>
                <v-icon small>mdi-account-circle-outline </v-icon>
                {{ selectUsuario.usr_usuario }}
              </span>
            </v-col>
          </v-row>
          <v-divider></v-divider>
          <!------puntos de venta------>
          <p class="font-weight-bold primary--text">PLANTA:</p>
          <v-row>
            <v-col cols="6">
              <div v-if="puntosVenta">
                <!-- <v-select item-text="nombre" @change="onChangePuntoVenta" item-value="id" v-model="selectPuntoVenta"
                  :items="puntosVenta" append-icon="mdi-storefront-outline" dense filled hide-details="auto"
                  label="Seleccion Planta">
                </v-select> -->
                <v-autocomplete v-if="puntosVenta" v-model="selectPuntoVenta" :items="puntosVenta" item-text="nombre"
                  item-value="id" label="Seleccionar Planta" dense filled hide-details="auto" append-icon="mdi-storefront-outline" @change="onChangePuntoVenta">
                </v-autocomplete>
              </div>
              <div v-if="!puntosVenta">
                <div class="text-center">
                  <v-progress-circular :size="25" color="primary" indeterminate></v-progress-circular>
                </div>
              </div>
            </v-col>
            <v-col cols="6"></v-col>
          </v-row>

          <div v-if="!selectPuntoVenta">
            <br>
            <v-alert outlined type="warning" prominent border="left">
              Seleccione una planta
            </v-alert>
          </div>

          <!------rol------>
          <v-divider></v-divider>
          <p class="font-weight-bold indigo--text">ROL:</p>
          <v-row justify="start" align="center" v-if="roles">
            <div v-for="(item, i) in roles" :key="i">
              <v-chip class="ma-2" @click="btnChipRol(item)" ripple
                :color="(item.id == selectedItemRol) ? 'primary' : ''"
                :input-value="(item.id == selectedItemRol) ? 'active' : ''" filter>
                {{ item.name }}
              </v-chip>
            </div>
          </v-row>

          <v-row v-if="!roles">
            <div>Esperar..</div>
          </v-row>

          <div v-if="!selectedItemRol">
            <br>
            <v-alert outlined type="warning" prominent border="left">
              Seleccione un ROL
            </v-alert>
          </div>




          <v-divider></v-divider>
          <!------menus------>

          <v-row>
            <v-col cols="6">
              <div v-if="menus">
                <p class="font-weight-bold indigo--text">MENUS:</p>

                <v-expand-transition>
                  <v-list dense>
                    <v-list-item-group v-model="selectedItemMenu" color="primary">
                      <v-list-item v-for="(item, i) in menus" :key="i" :disabled="!selectedItemRol"
                        @click="btnItemMenu(item)">
                        <v-list-item-icon>
                          <v-icon v-text="item.icon_mdi"></v-icon>
                        </v-list-item-icon>
                        <v-list-item-content>

                          <v-list-item-title v-text="item.label"></v-list-item-title>
                          <v-progress-linear style="width: 30px;" :value="item.progreso.porcentaje"></v-progress-linear>

                          <small>({{ item.progreso.countActive }}/{{ item.progreso.total }})</small>
                        </v-list-item-content>
                      </v-list-item>
                    </v-list-item-group>
                  </v-list>
                </v-expand-transition>


              </div>
              <div v-if="!menus">
                <div class="text-center">
                  <v-progress-circular indeterminate :size="50" color="primary"></v-progress-circular>
                </div>
              </div>
            </v-col>
            <v-col cols="6">
              <div v-if="subMenus">
                <p>SUB MENUS</p>
                <v-expand-transition>
                  <v-list class="scroll-submenu" subheader two-line flat>
                    <v-list-item-group multiple>
                      <v-list-item v-for="item in subMenus" :key="item.id">
                        <v-list-item-avatar>
                          <v-icon> {{ item.icon_mdi }} </v-icon>
                        </v-list-item-avatar>
                        <v-list-item-content>
                          <v-list-item-title>{{item.label}}</v-list-item-title>
                        </v-list-item-content>
                        <v-list-item-action>
                          <v-checkbox @click="btnItemSubMenu(item)" :input-value=" item.active" color="primary">
                          </v-checkbox>
                        </v-list-item-action>
                      </v-list-item>
                    </v-list-item-group>
                  </v-list>
                </v-expand-transition>

              </div>
              <div v-if="!subMenus"></div>
            </v-col>
          </v-row>



        </v-card-text>



      </v-card>
    </v-dialog>
    <!---------->






  </div>
</template>

<script>
import Multiselect from 'vue-multiselect'
import { mdiPencilOutline } from '@mdi/js'

export default {
  setup() {
    return {
      icons: {
        mdiPencilOutline,
      },
    }
  },
  data: () => ({
    usuarios: null,
    roles: null,
    menus: [],
    subMenus: null,

    selectedItemRol: null,
    selectedItemMenu: null,
    selectedItemSubMenu: null,

    selectUsuario: null,
    selectPuntoVenta: null,

    selected: [],
    puntosVenta: null,
    puntoVentaUser: null,
    snackbar: {
      status: false,
      text: '',
    },
    search: '',
    headers: [
      { text: 'Usuario', value: 'usr_usuario', sortable: true },
      { text: 'Nombre', value: 'nombre', sortable: true },
      { text: 'Dirección', value: 'direccion', sortable: true },
      { text: 'Acciones', value: 'acciones', sortable: false },
    ],

    dialogPersmisos: false,

  }),
  computed: {},
  mounted() {
    this.getUsuarios()
  },
  watch: {},

  created() { },
  methods: {
    getUsuarios() {
      axios
        .get('api/usuario')
        .then(response => {
          this.usuarios = response.data
        })
        .catch(error => { })
    },
    btnAgregarSistema(item) {
      axios
        .get('api/usuario/agregar-sistema/' + item.id)
        .then(response => {
          if (response.data) {
            item.acceso_comex = true
            this.snackbar = {
              status: true,
              text: response.data.message,
              color: 'primary',
            }
            this.getUsuarios();
          }
        })
        .catch(error => { })
      
    },
    btnQuitarAcceso(item) {
      axios
        .get('api/usuario/quitar-sistema/' + item.id)
        .then(response => {
          if (response.data) {
            item.acceso_comex = false
            this.snackbar = {
              status: true,
              text: response.data.message,
              color: 'warning',
            }
            this.getUsuarios();
          }
        })
        .catch(error => { })
    },

    onChangePuntoVenta(item) {

      if (this.selectUsuario) {
        var puntosVentaId = this.selectPuntoVenta;
        var userId = this.selectUsuario.id

        axios
          .get('api/usuario/asignar-puntoventa/' + userId + '/' + puntosVentaId)
          .then(response => {

          })
          .catch(error => { })
      }
    },

    btnItemMenu(item) {
      this.subMenus = item.sub_menu_n1;
    },

    btnItemSubMenu(item) {
      var estado_ = item.active;
      if (this.selectedItemRol != null) {
        var rolId = this.selectedItemRol
        var subMenuId = item.id

        var data = {
          menu_id: subMenuId,
          rol_id: rolId,
        }

        axios
          .post('api/menu-rol', data)
          .then(response => {
            var check = response.data.check
            item.active = estado_;
            this.getMenuUser(this.selectedItemRol)
          })
          .catch(error => {
            item.active = !estado_
            this.snackbar = {
              status: true,
              text: 'Error al asignar el sub menu',
              color: 'error',
            }
          })
      } else {
        console.error('Error Rol')
      }
    },


    btnChipRol(item) {
      if (this.selectUsuario) {
        var userId = this.selectUsuario.id
        var rolId = item.id
        var data = {
          rol_id: rolId,
          usuario_id: userId,
        }
        this.selectedItemMenu = null;
        this.subMenus = null;
        axios
          .post('api/rol-user', data)
          .then(response => {
            this.selectedItemRol = item.id;
            this.getMenuUser(item.id);
            this.snackbar = {
              status: true,
              text: 'Se cambio el ROL ',
              color: 'primary',
            }

          })
          .catch(error => { })
      }
    },

    btnItemRol(item) {
      if (this.selectUsuario) {
        var userId = this.selectUsuario.usr_id
        var rolId = item.id
        var data = {
          rol_id: rolId,
          usuario_id: userId,
        }
        this.menus = null;
        this.selectedItemMenu = null;
        this.subMenus = null;
        axios
          .post('api/rol-user', data)
          .then(response => {
            this.getMenuUser(item.id)
          })
          .catch(error => { })
      }
    },

    getRoles() {
      axios
        .get('api/rol')
        .then(response => {
          this.roles = response.data;
        })
        .catch(error => { })
    },

    getMenus() {
      axios
        .get('api/menu')
        .then(response => {
          this.menus = response.data;
        })
        .catch(error => { })
    },

    getPuntoVentaUser() {
      if (this.selectUsuario) {
        var userId = this.selectUsuario.id;
        axios
          .get('api/punto-venta/user/' + userId)
          .then(response => {
            var data = response.data;
            if (data) {
              this.selectPuntoVenta = data.puntoventa_id;
            } else {
              this.selectPuntoVenta = null;
            }
          })
          .catch(error => {
            
          })
      }
    },


    getPuntosVentas() {
      axios
        .get('api/punto-venta')
        .then(response => {
          var data = response.data;
          this.puntosVenta = data;

        })
        .catch(error => {
          
        })
    },

    btnAccesosPermisos(item) {
      this.dialogPersmisos = true;
      this.selectUsuario = item;
      this.subMenus = null;
      this.selectedItemMenu = null;
      this.menus = [];
      this.getPuntosVentas();
      this.getPuntoVentaUser();
      //  this.getRoles();
      // this.getMenus();
      axios
        .get('api/usuario/rol-user/' + item.id)
        .then(response => {
          var data = response.data
          this.roles = data.roles
          this.selectedItemRol = data.rolUser
          if (this.selectedItemRol != null) {
            this.getMenuUser(this.selectedItemRol)
          }
         
        })
        .catch(error => {
          
        })
    },

    getMenuUser(rolId) {
      // this.menus = null
      axios
        .get('api/usuario/menu-rol/' + rolId)
        .then(response => {
          var data = response.data
          this.menus = data.menus
          
        })
        .catch(error => {
          
        })
    },
  },
  components: {
    Multiselect,
  },
}
</script>
<style src="vue-multiselect/dist/vue-multiselect.min.css">
</style>
