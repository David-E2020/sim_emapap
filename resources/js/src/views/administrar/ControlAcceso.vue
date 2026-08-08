<template>
    <div>
        <v-container fluid>
            <v-row justify="center">
                <v-col cols="12">
                    <v-card class="mb-12">
                        <v-card-actions>
                            <v-list-item class="grow">
                                <v-list-item-avatar>
                                    <v-avatar color="primary" size="48">
                                        <v-icon color="white">mdi-folder-account</v-icon>
                                    </v-avatar>
                                </v-list-item-avatar>
                                <v-list-item-content>
                                    <v-list-item-title class="text-h6 uppercase">
                                        Listado de Personal solo visible para Recursos Humanos
                                    </v-list-item-title>
                                    <v-list-item-subtitle>Control de Acceso</v-list-item-subtitle>
                                </v-list-item-content>
                            </v-list-item>
                        </v-card-actions>
                        <v-card-text>
                            <v-container fluid>
                                <v-form v-model="valid" lazy-validation ref="form">
                                    <v-row>
                                        <v-col cols="12">
                                            <v-list-item-content>
                                                <v-list-item-subtitle class="uppercase font-weight-bold">
                                                    <v-icon color="indigo">mdi-home-city-outline</v-icon> DATOS SOLICITUD
                                                </v-list-item-subtitle>
                                            </v-list-item-content>
                                            <v-row>
                                                <v-col cols="6">
                                                    <v-autocomplete 
                                                        label="Seleccionar Usuario" 
                                                        outlined
                                                        :disabled="isDisabled" 
                                                        :items="usuarios" 
                                                        item-text="name" 
                                                        item-value="id" 
                                                        v-model="selectedUserId"
                                                        @change="handleUserChange"
                                                    ></v-autocomplete>
                                                </v-col>
                                                <v-col cols="6">
                                                    <v-switch 
                                                        label="Sie-Facturacion" 
                                                        v-model="sieFacturacion"
                                                    ></v-switch>
                                                    <v-switch 
                                                        label="Sie-Plantas" 
                                                        v-model="siePlantas"
                                                    ></v-switch>
                                                    <v-switch 
                                                        label="Sie-Cartera" 
                                                        v-model="sieCartera"
                                                    ></v-switch>
                                                    <v-switch 
                                                        label="Sie-Financiera" 
                                                        v-model="sieFinanciera"
                                                    ></v-switch>
                                                </v-col>
                                            </v-row>
                                        </v-col>
                                    </v-row>
                                    <v-row v-if="fromUser">
                                        <v-col cols="12">
                                            <v-list-item-content>
                                                <v-list-item-subtitle class="uppercase font-weight-bold">
                                                    <v-icon color="indigo">mdi-account-box</v-icon> DATOS USUARIO
                                                </v-list-item-subtitle>
                                            </v-list-item-content>
                                            <v-row>
                                                <v-col cols="6">
                                                    <v-text-field 
                                                        label="Nombre" 
                                                        outlined 
                                                        disabled 
                                                        v-model="fromUser.name"
                                                        type="text"
                                                    ></v-text-field>
                                                </v-col>
                                                <v-col cols="6">
                                                    <v-text-field 
                                                        label="E-mail" 
                                                        outlined 
                                                        disabled 
                                                        v-model="fromUser.email"
                                                    ></v-text-field>
                                                </v-col>
                                            </v-row>
                                            <v-row>
                                                <v-col cols="6">
                                                    <v-text-field 
                                                        label="Usuario Sistema" 
                                                        outlined 
                                                        disabled 
                                                        v-model="fromUser.usr_usuario"
                                                    ></v-text-field>
                                                </v-col>
                                                <v-col cols="6">
                                                    <v-text-field 
                                                        label="Estado" 
                                                        outlined 
                                                        disabled 
                                                        v-model="fromUser.usr_estado"
                                                    ></v-text-field>
                                                </v-col>
                                            </v-row>
                                            <v-row>
                                                <v-col cols="6">
                                                    <v-card>
                                                        <v-img 
                                                            :src="fromUser.usr_foto || 'https://cdn.vuetifyjs.com/images/cards/desert.jpg'"
                                                            min-height="200" 
                                                            max-height="200" 
                                                            contain
                                                        >
                                                            <v-card-title>Imagen Usuario</v-card-title>
                                                        </v-img>
                                                    </v-card>
                                                </v-col>
                                            </v-row>
                                        </v-col>
                                    </v-row>
                                </v-form>
                            </v-container>
                        </v-card-text>
                    </v-card>
                </v-col>
            </v-row>
        </v-container>
        <v-snackbar v-model="snackbar.status" bottom :color="snackbar.color" :timeout="1500">
            {{ snackbar.text }}
            <template v-slot:action="{ attrs }">
                <v-btn color="blue" text v-bind="attrs" @click="snackbar.status = false">Cerrar</v-btn>
            </template>
        </v-snackbar>
    </div>
</template>

<script>
import axios from 'axios';

export default {
    data: () => ({
        search: '',
        usuarios: [],
        selectedUserId: null,
        fromUser: null,
        sieFacturacion: false,
        siePlantas: false,
        sieCartera: false,
        sieFinanciera: false,
        valid: true,
        menu: false,
        isDisabled: false,
        dialogConfirmSolicitud: false,
        acopio_boletas: [],
        dialogAcopioMovimientos: false,
        snackbar: {
            status: false,
            text: '',
            color: '',
        },
    }),
    mounted() {
        this.getUsuarios();
    },
    methods: {
        getUsuarios() {
            axios.get('api/listar_usuario_acceso')
                .then((response) => {
                    this.usuarios = response.data;
                })
                .catch((error) => {
                    console.error('Error de respuesta usuarios:', error);
                });
        },
        handleUserChange(selectedUserId) {
            this.fromUser = this.usuarios.find(user => user.id === selectedUserId) || null;
        },
        agregarSistema(item) {
            // opcional
        },
        registrarSolicitud() {
            const solicitudData = {
                fromUser: this.fromUser,
                sieFacturacion: this.sieFacturacion,
                siePlantas: this.siePlantas,
                sieCartera: this.sieCartera,
                sieFinanciera: this.sieFinanciera,
            };
            // completar la funcion para enviar al backend aun no hay ruta  
        }
    }
}
</script>
