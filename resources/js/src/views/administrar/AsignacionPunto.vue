<template>
    <div>
        <v-card>
            <v-card-title>
                <h3>Asignar Puntos</h3>
            </v-card-title>
            <v-card-text>
                <v-row>
                    <v-col cols="12">
                        <v-data-table :headers="headers" :items="usuarios" :items-per-page="20" class="elevation-1">
                            <template v-slot:top>
                                <v-toolbar flat>
                                    <v-toolbar-title>Usuarios</v-toolbar-title>
                                </v-toolbar>
                            </template>
                            <template v-slot:item.actions="{ item }">
                                <v-btn color="primary" @click="asignarPuntos(item)">
                                    <v-icon>mdi mdi-map-marker-check-outline</v-icon>
                                    Asignar
                                </v-btn>
                            </template>
                        </v-data-table>
                    </v-col>
                </v-row>
            </v-card-text>
        </v-card>
        <v-dialog v-model="dialogPuntos">
            <v-card>
                <v-card-title class="p-5 fixed-title">
                    <v-row align="center" justify="space-between" style="width: 100%;">
                        <v-col cols="4">
                            <v-toolbar-title>Asignar Puntos</v-toolbar-title>
                        </v-col>
                        <v-col cols="2" class="d-flex align-center justify-center">
                            <v-card-actions class="mt-2">
                                <v-btn color="primary" :disabled="!isSaveEnabled" @click="enviarPuntosActivados">
                                    <v-icon>mdi-content-save</v-icon>
                                    Guardar</v-btn>
                            </v-card-actions>
                        </v-col>
                        <v-spacer></v-spacer>
                        <v-col cols="5" class="d-flex align-center justify-center">
                            <v-text-field v-model="searchPuntos" append-icon="mdi-magnify" label="Search" single-line
                                hide-details></v-text-field>
                        </v-col>
                        <v-col cols="1">
                            <v-btn icon @click="dialogPuntos = false" class="mt-1" color="error">
                                <v-icon>mdi-close</v-icon>
                            </v-btn>
                        </v-col>
                    </v-row>
                </v-card-title>
                <v-card-text>
                    <v-row>
                        <v-col cols="12">
                            <v-data-table :headers="headersPuntos" :items="puntos" :items-per-page="20"
                                class="elevation-1" :search="searchPuntos">
                                <template v-slot:top>
                                    <v-toolbar flat>
                                        <v-toolbar-title>Puntos</v-toolbar-title>
                                    </v-toolbar>
                                </template>
                                <template v-slot:item.asignado="{ item }">
                                    <v-switch v-model="item.asignado" @change="checkSaveEnabled"></v-switch>
                                </template>
                            </v-data-table>
                        </v-col>
                    </v-row>
                </v-card-text>
            </v-card>
        </v-dialog>
        <template>
            <v-snackbar v-model="snackbar.status" bottom :color="snackbar.color" :timeout="1500">
                {{ snackbar.text }}
                <template v-slot:action="{ attrs }">
                    <v-btn color="blue" text v-bind="attrs" @click="snackbar.status = false">Cerrar</v-btn>
                </template>
            </v-snackbar>
        </template>
    </div>
</template>

<script>
import axios from "axios";
export default {
    data() {
        return {
            headers: [
                { text: "Nº", value: "DT_RowIndex", align: "center" },
                { text: "Nombre", value: "name", align: "center" },
                { text: "Cargo", value: "user_cargos.nombre", align: "center" },
                { text: "Acciones", value: "actions", sortable: false, align: "center" },
            ],
            headersPuntos: [
                { text: "Nombre", value: "nombre" },
                { text: "Asignado", value: "asignado" },
            ],
            usuarios: [],
            puntos: [],
            dialogPuntos: false,
            searchPuntos: '',
            isSaveEnabled: false,
            selectedUsuario: null,
            usuariosPunto: [],
            snackbar: {
                status: false,
                color: '',
                text: '',
            },
        };
    },
    created() {
        this.getUsuarios();
        // this.getPuntos();
        // this.listarUsuarioPunto();
    },
    methods: {
        getUser() {
            this.user = JSON.parse(localStorage.getItem('user'))
            if (this.user && this.user.usr_cargo_id) {
                this.userCargoId = this.user.usr_cargo_id;
            } else {
                this.userCargoId = null;
            }
        },
        getUsuarios() {
            axios.get("/api/usuario_punto").then((response) => {
                this.usuarios = response.data.data;
                this.usuarios.forEach((item, index) => {
                    item.DT_RowIndex = index + 1;
                });
            });
        },
        getPuntos() {
            return axios.get("/api/listar_puntos");
        },
        getPuntosAsignados(userId) {
            return axios.get(`/api/listar_usuario_punto/${userId}`)
                .then(response => {
                    return response;
                });
        },
        asignarPuntos(usuario) {
            this.dialogPuntos = true;
            this.selectedUsuario = usuario;
            this.cargarPuntos(usuario.id);
        },
        cargarPuntos(userId) {
            axios.all([this.getPuntos(), this.getPuntosAsignados(userId)])
                .then(axios.spread((puntosResponse, puntosAsignadosResponse) => {
                    this.puntos = puntosResponse.data.data || [];
                    const puntosAsignados = puntosAsignadosResponse.data.data || [];
                    this.puntos.forEach(punto => {
                        punto.asignado = puntosAsignados.some(asignado => asignado.punto_id === punto.id);
                    });
                    this.checkSaveEnabled();
                }))
                .catch(error => {
                    console.error('Error al cargar puntos:', error);
                });
        },
        checkSaveEnabled() {
            this.isSaveEnabled = this.puntos.some(punto => punto.asignado);
        },
        enviarPuntosActivados() {
            const puntosActivados = this.puntos.filter(punto => punto.asignado).map(punto => ({
                punto_id: punto.id,
                usuario_id: this.selectedUsuario.id
            }));
            axios.post('/api/asignar_puntos', { puntos: puntosActivados })
                .then(response => {
                    this.dialogPuntos = false;
                    this.snackbar = {
                        status: true,
                        color: 'success',
                        text: 'Puntos asignados correctamente',
                    }
                })
                .catch(error => {
                    this.snackbar = {
                        status: true,
                        color: 'error',
                        text: 'Error al asignar puntos',
                    }
                });
        },
        // getPuntos() {
        //     axios.get("/api/listar_puntos").then((response) => {
        //         this.puntos = response.data.data;
        //     });
        // },
        // asignarPuntos(usuario) {
        //     this.dialogPuntos = true;
        //     this.selectedUsuario = usuario;
        //     this.cargarPuntos(usuario.id);
        // },
        // cargarPuntos(userId) {
        //     axios.all([
        //         axios.get("/api/listar_puntos"),
        //         axios.get(`/api/listar_usuario_punto/${userId}`)
        //     ])
        //     .then(axios.spread((puntosResponse, puntosAsignadosResponse) => {
        //         this.puntos = puntosResponse.data.data;
        //         const puntosAsignados = puntosAsignadosResponse.data.data;
        //         this.puntos.forEach(punto => {
        //             punto.asignado = puntosAsignados.some(asignado => asignado.punto_id === punto.id);
        //         });
        //         this.checkSaveEnabled();
        //     }))
        //     .catch(error => {
        //         console.error('Error al cargar puntos:', error);
        //     });
        // },
        // checkSaveEnabled() {
        //     this.isSaveEnabled = this.puntos.some(punto => punto.asignado);
        // },
        // enviarPuntosActivados() {
        //     const puntosActivados = this.puntos.filter(punto => punto.asignado).map(punto => ({
        //         punto_id: punto.id,
        //         usuario_id: this.selectedUsuario.id
        //     }));
        //     axios.post('/api/asignar_puntos', { puntos: puntosActivados })
        //         .then(response => {
        //             this.dialogPuntos = false;
        //         })
        //         .catch(error => {
        //             console.error('Error al asignar puntos:', error);
        //         });
        // }

    },
};
</script>

<style scoped>
.fixed-title {
    position: sticky;
    top: 0;
    z-index: 1;
    background-color: white;
}
</style>