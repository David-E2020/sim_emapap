<style scoped>
.show-btns {

    transition: opacity .4s ease-in-out;
}

.handle {
    float: left;

    cursor: row-resize;
}

.ghost {
    opacity: 0.5;
    background: #c8ebfb;
}
</style>

<template>
    <div>

        <v-card class="mx-auto" outlined>
            <v-card-title>
                <div>
                    ADMINISTRAR ROLES:
                </div>
            </v-card-title>

            <v-card-subtitle v-if="roles">
                {{ roles.length }} role(s)
            </v-card-subtitle>
            <v-card-text>
                <v-row justify="start" v-if="roles">

                    <div v-for="(item, i) in roles" :key="i">
                        <v-hover v-slot="{ hover }">
                            <v-chip class="ma-1" :elevation="hover ? 15 : 0">
                                {{ item.name }}
                                <v-expand-x-transition>
                                    <div v-if="hover" class="transition-fast-in-fast-out">
                                        <v-btn icon color="error" small class="show-btns"
                                            @click="btnChipDeleteRol(item)">
                                            <v-icon small>mdi-delete</v-icon>
                                        </v-btn>
                                        <v-btn icon color="primary" small class="show-btns"
                                            @click="btnChipEditRol(item)">
                                            <v-icon small>mdi-pencil</v-icon>
                                        </v-btn>
                                    </div>
                                </v-expand-x-transition>
                            </v-chip>
                        </v-hover>
                    </div>

                    <v-chip class="ma-1" :input-value="'active'" :color="'primary'" filter-icon="mdi-plus"
                        @click="btnChipNuevoRol()">
                        Nuevo
                    </v-chip>

                </v-row>

                <div v-if="!roles">Esperar..</div>
            </v-card-text>

            <v-card-actions>
                <v-spacer></v-spacer>


            </v-card-actions>


        </v-card>

        <br>
        <v-card class="mx-auto" outlined>
            <v-card-title>
                ADMINISTRAR MENUS:
            </v-card-title>
            <v-card-subtitle v-if="menus">
                {{ menus.length }} Menu(s)
            </v-card-subtitle>


            <v-card-text>






                <br>



                <br>

                <pre v-if="!myArray">
                    {{ myArray | json }}
                </pre>

                <v-row>
                    <v-col cols="5">
                        <v-card class="mx-auto" align-self-auto outlined>

                            <v-card-title>
                                Menus
                                <v-spacer></v-spacer>
                                <v-btn color="primary" x-small elevation="24" @click="btnNuevoMenu(0)">
                                    <v-icon x-small>mdi-plus</v-icon> Nuevo
                                </v-btn>
                            </v-card-title>


                            <v-list shaped>
                                <v-list-item-group color="primary">
                                    <draggable v-model="menus" :move="checkMoveMenu" v-bind="dragOptions"
                                        @start="dragMenu = true" @end="dragMenu = false" handle=".handle">
                                        <transition-group type="transition" :name="!dragMenu ? 'flip-list' : null">
                                            <!--  <li class="list-group-item" v-for="element in myArray" :key="element.order">
                                                <i :class="element.fixed ? 'fa fa-anchor' : 'glyphicon glyphicon-pushpin'" @click="element.fixed = !element.fixed" aria-hidden="true"></i>
                                                {{ element.name }}
                                            </li>
                                            -->
                                            <!--------->
                                            <v-list-item v-for="item in menus" :key="item.order">
                                                <v-list-item-icon class="handle">
                                                    <v-icon color="primary">mdi-drag-horizontal-variant</v-icon>
                                                </v-list-item-icon>

                                                <v-list-item-icon>
                                                    <v-icon v-text="item.icon_mdi"></v-icon>
                                                </v-list-item-icon>
                                                <v-list-item-content>
                                                    <v-list-item-title v-text="item.label"></v-list-item-title>
                                                </v-list-item-content>
                                                <v-btn icon color="error" small @click="btnItemMenuDelete(item)">
                                                    <v-icon small>mdi-delete</v-icon>
                                                </v-btn>
                                                <v-btn icon color="primary" small @click="btnItemMenuEdit(item)">
                                                    <v-icon small>mdi-pencil</v-icon>
                                                </v-btn>

                                               

                                                <v-btn text x-small @click="btnItemMenu(item)">
                                                    <v-chip x-small>{{ item.sub_menu_n1.length }}</v-chip>
                                                    <v-icon small>mdi-arrow-right</v-icon>
                                                </v-btn>

                                            </v-list-item>

                                        </transition-group>
                                    </draggable>
                                </v-list-item-group>
                            </v-list>



                        </v-card>
                    </v-col>
                    <v-col cols="5">




                        <v-card class="mx-auto" outlined v-if="itemSelectMenu">

                            <v-card-title>
                                Sub Menu
                                <v-spacer></v-spacer>
                                <v-btn color="primary" x-small elevation="24" @click="btnNuevoMenu(1)">
                                    <v-icon x-small>mdi-plus</v-icon> Nuevo
                                </v-btn>
                            </v-card-title>

                            <v-card-subtitle>
                                {{ itemSelectMenu.label }}
                            </v-card-subtitle>

                            <v-list>
                                <v-list-item-group>

                                    <draggable v-model="subMenus" v-bind="dragOptions" :move="checkMoveMenu" @start="dragSubMenu = true"
                                        @end="dragSubMenu = false" handle=".handle">
                                        <transition-group type="transition" :name="!dragSubMenu ? 'flip-list' : null">


                                            <v-list-item v-for="item in subMenus" :key="item.order">

                                                <v-list-item-icon class="handle">
                                                    <v-icon color="primary">mdi-drag-horizontal-variant</v-icon>
                                                </v-list-item-icon>

                                                <v-list-item-icon>
                                                    <v-icon v-text="item.icon_mdi"></v-icon>
                                                </v-list-item-icon>
                                                <v-list-item-content>
                                                    <v-list-item-title v-text="item.label"></v-list-item-title>
                                                </v-list-item-content>

                                                <v-btn icon color="error" small @click="btnItemMenuDelete(item)">
                                                    <v-icon small>mdi-delete</v-icon>
                                                </v-btn>
                                                <v-btn icon color="primary" small @click="btnItemMenuEdit(item)">
                                                    <v-icon small>mdi-pencil</v-icon>
                                                </v-btn>
                                            </v-list-item>
                                        </transition-group>
                                    </draggable>




                                </v-list-item-group>
                            </v-list>
                        </v-card>
                    </v-col>

                    <v-col cols="2"></v-col>
                </v-row>




            </v-card-text>

        </v-card>



        <v-snackbar v-model="snackbar.status" bottom :color="snackbar.color" :timeout="1500">
            {{ snackbar.text }}
            <template v-slot:action="{ attrs }">
                <v-btn color="blue" text v-bind="attrs" @click="snackbar.status = false">Cerrar</v-btn>
            </template>
        </v-snackbar>


        <v-dialog v-model="dialogChip" persistent max-width="390">
            <v-form v-model="valid" lazy-validation @submit.prevent="submit" ref="form">
                <v-card>
                    <v-card-title class="text-h5">
                        {{ isFormRolEdit ? 'Editar Rol' : 'Nuevo Rol' }}
                    </v-card-title>

                    <v-card-text>
                        <v-text-field v-model="formRol.name" filled label="Rol" required dense
                            :rules="[v => !!v || 'El Rol es requerido']"></v-text-field>
                    </v-card-text>



                    <v-card-actions>
                        <v-spacer></v-spacer>
                        <v-btn class="ma-2" elevation="10" small color="error" @click="dialogChip = false">
                            <v-icon small>mdi-close</v-icon>Cerrar
                        </v-btn>
                        <v-btn class="ma-2" elevation="10" small :loading="btnLoadingRol"
                            :disabled="btnLoadingRol || !valid" type="submit" color="primary">
                            {{ isFormRolEdit ? 'GUARDAR CAMBIOS' : 'GUARDAR' }}
                        </v-btn>
                    </v-card-actions>
                </v-card>
            </v-form>
        </v-dialog>


        <v-dialog v-model="dialogMenu" persistent max-width="390">
            <v-form v-model="validMenu" lazy-validation @submit.prevent="submitMenu" ref="formMenu">
                <v-card>
                    <v-card-title class="text-h5">
                        {{ isFormMenuEdit ? 'Editar ' : 'Nuevo ' }} {{ (menuNivel == 0) ? 'menu' : 'sub menu' }}
                    </v-card-title>
                    <v-card-text>

                        <v-text-field v-if="itemSelectMenu && menuNivel != 0" disabled v-model="itemSelectMenu.label"
                            filled label="Menu" required dense></v-text-field>


                        <v-text-field v-model="formMenu.label" filled label="Nombre" required dense
                            :rules="[v => !!v || 'El Nombre es requerido']"></v-text-field>

                        <v-text-field v-model="formMenu.route" filled label="Ruta" dense></v-text-field>

                        <v-autocomplete v-model="formMenu.icon" :items="menuList" item-text="icon" item-value="icon"
                            dense filled label="Icono" :rules="[v => !!v || 'El Icono es requerido']">
                            <template v-slot:selection="{ item, selected }">
                                <div :input-value="selected">
                                    <v-icon>{{ item.icon | filterMenuIcon }}</v-icon> {{ item.icon | filterMenuName }}
                                </div>
                            </template>

                            <template v-slot:item="{ item }">
                                <v-list-item-avatar class="text-h5 font-weight-light white--text">
                                    <v-icon>{{ item.icon | filterMenuIcon }}</v-icon>
                                </v-list-item-avatar>
                                <v-list-item-content>
                                    {{ item.icon | filterMenuName }}
                                </v-list-item-content>
                            </template>
                        </v-autocomplete>

                    </v-card-text>





                    <v-card-actions>
                        <v-spacer></v-spacer>
                        <v-btn class="ma-2" elevation="10" small color="error" @click="dialogMenu = false">
                            <v-icon small>mdi-close</v-icon>Cerrar
                        </v-btn>
                        <v-btn class="ma-2" elevation="10" small :loading="btnLoadingMenu"
                            :disabled="btnLoadingMenu || !validMenu" type="submit" color="primary">
                            {{ isFormMenuEdit ? 'GUARDAR CAMBIOS' : 'GUARDAR' }}
                        </v-btn>
                    </v-card-actions>
                </v-card>
            </v-form>
        </v-dialog>



        <v-dialog v-model="dialogConfirm" persistent max-width="360">
            <v-card>
                <v-card-title class="text-h5">¿Esta seguro de eliminar?</v-card-title>
                <v-card-text></v-card-text>
                <v-card-actions>
                    <v-spacer></v-spacer>
                    <v-btn color="green darken-1" text @click="dialogConfirm = false">Cancelar</v-btn>
                    <v-btn color="error darken-1" text @click="btnDialogConfirm()">SI</v-btn>
                </v-card-actions>
            </v-card>
        </v-dialog>



    </div>
</template>

<script>
import Multiselect from 'vue-multiselect'
import { mdiPencilOutline } from '@mdi/js'
import draggable from 'vuedraggable'

export default {
    setup() {
        return {
            icons: {
                mdiPencilOutline,
            },
        }
    },
    data: () => ({

        dragMenu: false,
        dragSubMenu: false,

        enabled: true,

        dragging: false,

        myArray: [
            { id: 1, name: 'item1', order: 2 },
            { id: 2, name: 'item2', order: 3 },
            { id: 3, name: 'item3', order: 4 },
            { id: 4, name: 'item4', order: 5 },
            { id: 5, name: 'item5', order: 6 },
            { id: 6, name: 'item6', order: 7 },
            { id: 7, name: 'item7', order: 8 }
        ],

        menuList: [],

        snackbar: {
            status: false,
            text: '',
        },
        roles: null,
        dialogChip: false,
        isFormRolEdit: false,
        btnLoadingRol: false,
        valicon: false,
        formRol: {
            name: null
        },
        valid: false,
        dialogConfirm: false,
        dataRolDelete: null,

        menus: null,
        subMenus: [],

        dialogMenu: false,
        validMenu: false,
        isFormMenuEdit: false,
        formMenu: {
            label: null,
            icon: null,
            route: null
        },
        btnLoadingMenu: false,
        dataMenuDelete: null,

        itemSelectMenu: null,
        menuNivel: null,

    }),
    computed: {

        dragOptions() {
            return {
                animation: 200,
                group: "description",
                disabled: false,
                ghostClass: "ghost"
            };
        }

    },
    mounted() {
        this.getRoles();
        this.getMenus();
        this.loadMenu();

    },
    watch: {},

    filters: {
        filterMenuName: function (v) {

            // if (!v) { return ""; }

            var resp = "";
            if (v) {
                resp = v
                    .replace(/([a-z])([A-Z])/g, "$1-$2")
                    .replace(/[\s_]+/g, '-')
                    .toLowerCase().replace('mdi-', '').replaceAll('-', ' ');
            }
            return resp;
        },
        filterMenuIcon: function (v) {
            //if (!v) { return ""; }
            var resp = "";
            if (v) {
                resp = v
                    .replace(/([a-z])([A-Z])/g, "$1-$2")
                    .replace(/[\s_]+/g, '-')
                    .toLowerCase();
            }


            return resp;
        }

    },

    created() { },
    methods: {
        checkMoveMenu: function (e) {


            var data = {
                dragged_id: e.draggedContext.element.id,
                dragged_order: e.draggedContext.element.order,

                related_id: e.relatedContext.element.id,
                related_order: e.relatedContext.element.order
            }

            var url = 'api/menu/change/cambiar-orden';
            axios
                .post(url, data)
                .then(response => {

                })
                .catch(error => { })

        },


        loadMenu() {
            this.menuList.push({ icon: 'mdiAccount' });
            this.menuList.push({ icon: 'mdiAccountBox' });
            this.menuList.push({ icon: 'mdiAccountBoxMultiple' });
            this.menuList.push({ icon: 'mdiAccountBoxOutline' });
            this.menuList.push({ icon: 'mdiAccountCheck' });
            this.menuList.push({ icon: 'mdiAccountCheckOutline' });
            this.menuList.push({ icon: 'mdiAccountCircle' });
            this.menuList.push({ icon: 'mdiAccountCircleOutline' });
            this.menuList.push({ icon: 'mdiAccountDetails' });
            this.menuList.push({ icon: 'mdiAccountMultiple' });
            this.menuList.push({ icon: 'mdiAccountMultipleMinus' });
            this.menuList.push({ icon: 'mdiAccountMultipleMinusOutline' });
            this.menuList.push({ icon: 'mdiAccountMultiplePlus' });
            this.menuList.push({ icon: 'mdiAccountMultiplePlusOutline' });
            this.menuList.push({ icon: 'mdiAccountMusic' });
            this.menuList.push({ icon: 'mdiAccountOutline' });
            this.menuList.push({ icon: 'mdiAccountPlus' });
            this.menuList.push({ icon: 'mdiAccountSupervisor' });
            this.menuList.push({ icon: 'mdiAccountSupervisorCircle' });
            this.menuList.push({ icon: 'mdiAccountSupervisorCircleOutline' });
            this.menuList.push({ icon: 'mdiAccountVoice' });
            this.menuList.push({ icon: 'mdiAccountVoiceOff' });
            this.menuList.push({ icon: 'mdiAirPurifier' });
            this.menuList.push({ icon: 'mdiAlarm' });
            this.menuList.push({ icon: 'mdiAlarmCheck' });
            this.menuList.push({ icon: 'mdiAlarmOff' });
            this.menuList.push({ icon: 'mdiAlarmPlus' });
            this.menuList.push({ icon: 'mdiAlarmSnooze' });
            this.menuList.push({ icon: 'mdiAlert' });
            this.menuList.push({ icon: 'mdiAlertCircle' });
            this.menuList.push({ icon: 'mdiAlertCircleOutline' });
            this.menuList.push({ icon: 'mdiAlertDecagram' });
            this.menuList.push({ icon: 'mdiAlertOctagon' });
            this.menuList.push({ icon: 'mdiAlignHorizontalCenter' });
            this.menuList.push({ icon: 'mdiAlignHorizontalDistribute' });
            this.menuList.push({ icon: 'mdiAlignHorizontalLeft' });
            this.menuList.push({ icon: 'mdiAlignVerticalBottom' });
            this.menuList.push({ icon: 'mdiAlignVerticalCenter' });
            this.menuList.push({ icon: 'mdiAllInclusive' });
            this.menuList.push({ icon: 'mdiAnchor' });
            this.menuList.push({ icon: 'mdiAndroid' });
            this.menuList.push({ icon: 'mdiAndroidMessages' });
            this.menuList.push({ icon: 'mdiAndroidStudio' });
            this.menuList.push({ icon: 'mdiAnimation' });
            this.menuList.push({ icon: 'mdiAnimationPlay' });
            this.menuList.push({ icon: 'mdiAntenna' });
            this.menuList.push({ icon: 'mdiApi' });
            this.menuList.push({ icon: 'mdiApps' });
            this.menuList.push({ icon: 'mdiAppsBox' });
            this.menuList.push({ icon: 'mdiArrowBottomLeftThinCircleOutline' });
            this.menuList.push({ icon: 'mdiArrowBottomRightThinCircleOutline' });
            this.menuList.push({ icon: 'mdiArrowCollapseHorizontal' });
            this.menuList.push({ icon: 'mdiArrowCollapseVertical' });
            this.menuList.push({ icon: 'mdiArrowDownDropCircle' });
            this.menuList.push({ icon: 'mdiArrowDownThinCircleOutline' });
            this.menuList.push({ icon: 'mdiArrowRight' });
            this.menuList.push({ icon: 'mdiArrowRightThinCircleOutline' });
            this.menuList.push({ icon: 'mdiArrowTopLeftThinCircleOutline' });
            this.menuList.push({ icon: 'mdiArrowTopRightThinCircleOutline' });
            this.menuList.push({ icon: 'mdiArrowUpThinCircleOutline' });
            this.menuList.push({ icon: 'mdiAspectRatio' });
            this.menuList.push({ icon: 'mdiAssistant' });
            this.menuList.push({ icon: 'mdiAt' });
            this.menuList.push({ icon: 'mdiAtm' });
            this.menuList.push({ icon: 'mdiAttachment' });
            this.menuList.push({ icon: 'mdiAutoFix' });
            this.menuList.push({ icon: 'mdiAutoUpload' });
            this.menuList.push({ icon: 'mdiAutorenew' });
            this.menuList.push({ icon: 'mdiAvTimer' });
            this.menuList.push({ icon: 'mdiBabyCarriage' });
            this.menuList.push({ icon: 'mdiBabyFace' });
            this.menuList.push({ icon: 'mdiBackspace' });
            this.menuList.push({ icon: 'mdiBackspaceOutline' });
            this.menuList.push({ icon: 'mdiBackupRestore' });
            this.menuList.push({ icon: 'mdiBadgeAccountHorizontal' });
            this.menuList.push({ icon: 'mdiBadgeAccountHorizontalOutline' });
            this.menuList.push({ icon: 'mdiBagChecked' });
            this.menuList.push({ icon: 'mdiBagSuitcase' });
            this.menuList.push({ icon: 'mdiBagSuitcaseOff' });
            this.menuList.push({ icon: 'mdiBalcony' });
            this.menuList.push({ icon: 'mdiBallot' });
            this.menuList.push({ icon: 'mdiBallotOutline' });
            this.menuList.push({ icon: 'mdiBandage' });
            this.menuList.push({ icon: 'mdiBank' });
            this.menuList.push({ icon: 'mdiBarrel' });
            this.menuList.push({ icon: 'mdiBarrelOutline' });
            this.menuList.push({ icon: 'mdiBasket' });
            this.menuList.push({ icon: 'mdiBasketOutline' });
            this.menuList.push({ icon: 'mdiBasketball' });
            this.menuList.push({ icon: 'mdiBedOutline' });
            this.menuList.push({ icon: 'mdiBee' });
            this.menuList.push({ icon: 'mdiBeeFlower' });
            this.menuList.push({ icon: 'mdiBell' });
            this.menuList.push({ icon: 'mdiBellCircle' });
            this.menuList.push({ icon: 'mdiBellCircleOutline' });
            this.menuList.push({ icon: 'mdiBellOff' });
            this.menuList.push({ icon: 'mdiBellOffOutline' });
            this.menuList.push({ icon: 'mdiBellOutline' });
            this.menuList.push({ icon: 'mdiBellRing' });
            this.menuList.push({ icon: 'mdiBellRingOutline' });
            this.menuList.push({ icon: 'mdiBellSleepOutline' });
            this.menuList.push({ icon: 'mdiBike' });
            this.menuList.push({ icon: 'mdiBlender' });
            this.menuList.push({ icon: 'mdiBlindsHorizontal' });
            this.menuList.push({ icon: 'mdiBlur' });
            this.menuList.push({ icon: 'mdiBookVariant' });
            this.menuList.push({ icon: 'mdiBookmark' });
            this.menuList.push({ icon: 'mdiBookmarkCheck' });
            this.menuList.push({ icon: 'mdiBookmarkMultiple' });
            this.menuList.push({ icon: 'mdiBookmarkOutline' });
            this.menuList.push({ icon: 'mdiBookmarkCheck' });
            this.menuList.push({ icon: 'mdiBriefcaseOutline' });
            this.menuList.push({ icon: 'mdiBriefcaseVariant' });
            this.menuList.push({ icon: 'mdiBriefcaseVariantOutline' });
            this.menuList.push({ icon: 'mdiBrush' });
            this.menuList.push({ icon: 'mdiCached' });
            this.menuList.push({ icon: 'mdiCakeVariant' });
            this.menuList.push({ icon: 'mdiCalculatorVariantOutline' });
            this.menuList.push({ icon: 'mdiCalendar' });
            this.menuList.push({ icon: 'mdiCalendarBlankOutline' });
            this.menuList.push({ icon: 'mdiCalendarCheck' });
            this.menuList.push({ icon: 'mdiChartBox' });
            this.menuList.push({ icon: 'mdiChartBoxPlusOutline' });
            this.menuList.push({ icon: 'mdiCheck' });
            this.menuList.push({ icon: 'mdiCheckCircle' });
            this.menuList.push({ icon: 'mdiCheckCircleOutline' });
            this.menuList.push({ icon: 'mdiCheckOutline' });
            this.menuList.push({ icon: 'mdiCheckbook' });
            this.menuList.push({ icon: 'mdiCheckboxMarked' });
            this.menuList.push({ icon: 'mdiChevronRight' });
            this.menuList.push({ icon: 'mdiChevronLeft' });
            this.menuList.push({ icon: 'mdiClipboardAccount' });
            this.menuList.push({ icon: 'mdiClipboardAlert' });
            this.menuList.push({ icon: 'mdiClipboardArrowDownOutline' });
            this.menuList.push({ icon: 'mdiClipboardText' });
            this.menuList.push({ icon: 'mdiCloseCircleOutline' });
            this.menuList.push({ icon: 'mdiCloudDownloadOutline' });
            this.menuList.push({ icon: 'mdiCloudDownload' });
            this.menuList.push({ icon: 'mdiCloudOffOutline' });
            this.menuList.push({ icon: 'mdiCloudOutline' });
            this.menuList.push({ icon: 'mdiCog' });
            this.menuList.push({ icon: 'mdiCogs' });
            this.menuList.push({ icon: 'mdiContacts' });
            this.menuList.push({ icon: 'mdiContentPaste' });
            this.menuList.push({ icon: 'mdiDelete' });
            this.menuList.push({ icon: 'mdiDevices' });
            this.menuList.push({ icon: 'mdiDownload' });
            this.menuList.push({ icon: 'mdiEarth' });
            this.menuList.push({ icon: 'mdiElevatorPassengerOutline' });
            this.menuList.push({ icon: 'mdiEmailOutline' });
            this.menuList.push({ icon: 'mdiEqualizer' });
            this.menuList.push({ icon: 'mdiEyeOff' });
            this.menuList.push({ icon: 'mdiFaceMan' });
            this.menuList.push({ icon: 'mdiFileOutline' });
            this.menuList.push({ icon: 'mdiFinance' });
            this.menuList.push({ icon: 'mdiFolder' });
            this.menuList.push({ icon: 'mdiFolderAccount' });
            this.menuList.push({ icon: 'mdiFolderAccountOutline' });
            this.menuList.push({ icon: 'mdiFolderMove' });
            this.menuList.push({ icon: 'mdiFolderOutline' });
            this.menuList.push({ icon: 'mdiFolderPlusOutline' });
            this.menuList.push({ icon: 'mdiGoogleMyBusiness' });
            this.menuList.push({ icon: 'mdiHelpCircle' });
            this.menuList.push({ icon: 'mdiHome' });
            this.menuList.push({ icon: 'mdiInboxMultiple' });
            this.menuList.push({ icon: 'mdiInformationOutline' });
            this.menuList.push({ icon: 'mdiLabelOutline' });
            this.menuList.push({ icon: 'mdiLabelOffOutline' });
            this.menuList.push({ icon: 'mdiLabelVariant' });
            this.menuList.push({ icon: 'mdiLeadPencil' });
            this.menuList.push({ icon: 'mdiLibrary' });
            this.menuList.push({ icon: 'mdiListStatus' });
            this.menuList.push({ icon: 'mdiLockOutline' });
            this.menuList.push({ icon: 'mdiMessageBadge' });
            this.menuList.push({ icon: 'mdiMessageBadgeOutline' });
            this.menuList.push({ icon: 'mdiMessageBulleted' });
            this.menuList.push({ icon: 'mdiNewspaperVariant' });
            this.menuList.push({ icon: 'mdiPictureInPictureBottomRight' });
            this.menuList.push({ icon: 'mdiRefresh' });
            this.menuList.push({ icon: 'mdiShieldAccountVariant' });
            this.menuList.push({ icon: 'mdiShieldCheck' });
            this.menuList.push({ icon: 'mdiShieldPlus' });
            this.menuList.push({ icon: 'mdiShieldSearch' });
            this.menuList.push({ icon: 'mdiStarCircle' });
            this.menuList.push({ icon: 'mdiStarFace' });
            this.menuList.push({ icon: 'mdiStickerEmoji' });
            this.menuList.push({ icon: 'mdiWidgetsOutline' });
            this.menuList.push({ icon: 'mdiWatermark' });
            this.menuList.push({ icon: 'mdiViewHeadline' });
            this.menuList.push({ icon: 'mdiTrendingNeutral' });
            this.menuList.push({ icon: 'mdiThumbsUpDownOutline' });

            this.menuList.push({ icon: 'mdiCheckboxBlankCircleOutline' });
            this.menuList.push({ icon: 'mdiFileDocumentOutline' });
            this.menuList.push({ icon: 'mdiTextBoxPlusOutline' });
            this.menuList.push({ icon: 'mdiBookmarkBoxMultiple' });
            this.menuList.push({ icon: 'mdiBookmarkPlusOutline' });
            this.menuList.push({ icon: 'mdiCloudSyncOutline' });
            this.menuList.push({ icon: 'mdiFolderCogOutline' });





        },

        submitMenu: function () {

            var validateForm = this.$refs.formMenu.validate()
            if (!validateForm) {
                return false
            }
            this.btnLoadingMenu = true;
            var dataForm = this.formMenu;

            if (this.isFormMenuEdit) {
                var urlEdit = 'api/menu/' + dataForm.id;
                axios
                    .put(urlEdit, dataForm)
                    .then(response => {
                        this.getMenus()
                        this.dialogMenu = false;
                        this.btnLoadingMenu = false;
                        this.snackbar = {
                            status: true,
                            text: 'Registro Editado',
                            color: 'primary',
                        }
                    })
                    .catch(error => { })

            } else {
                var dataSend = dataForm;
                dataSend.order=this.menus.length + 1;
                if (this.itemSelectMenu != null) {
                    dataSend.menu_id = this.itemSelectMenu.id;
                    dataSend.level = 1;
                    dataSend.order= this.subMenus.length + 1;
                }

            

                axios
                    .post('api/menu', dataSend)
                    .then(response => {
                        this.subMenus.push(response.data);
                        this.getMenus();
                        this.dialogMenu = false;
                        this.btnLoadingMenu = false;
                        this.snackbar = {
                            status: true,
                            text: 'Nuevo Registro',
                            color: 'primary',
                        }
                    })
                    .catch(error => { })
            }

        },

        submit: function () {
            var validateForm = this.$refs.form.validate()
            if (!validateForm) {
                return false
            }
            this.btnLoadingRol = true;
            var dataForm = this.formRol;

            if (this.isFormRolEdit) {
                var urlEdit = 'api/rol/' + dataForm.id;
                axios
                    .put(urlEdit, dataForm)
                    .then(response => {
                        this.getRoles();
                        this.dialogChip = false;
                        this.btnLoadingRol = false;
                        this.snackbar = {
                            status: true,
                            text: 'Registro Editado',
                            color: 'primary',
                        }
                    })
                    .catch(error => { })

            } else {
                axios
                    .post('api/rol', dataForm)
                    .then(response => {
                        this.getRoles();
                        this.dialogChip = false;
                        this.btnLoadingRol = false;
                        this.snackbar = {
                            status: true,
                            text: 'Nuevo Registro',
                            color: 'primary',
                        }
                    })
                    .catch(error => { })

            }

        },

        getMenus() {
            axios
                .get('api/menu')
                .then(response => {
                    this.menus = response.data;
                    if (this.itemSelectMenu) {
                        var menu_ = this.menus.find(x => x.id == this.itemSelectMenu.id);
                        this.subMenus = menu_.sub_menu_n1;
                    }
                })
                .catch(error => { })
        },
        getRoles() {
            axios
                .get('api/rol')
                .then(response => {
                    this.roles = response.data;
                })
                .catch(error => { })
        },

        btnItemMenu(item) {
            this.itemSelectMenu = item;
            this.subMenus = item.sub_menu_n1;
        },

        btnChipDeleteRol(item) {
            this.dataMenuDelete = null;
            this.dataRolDelete = item;
            this.dialogConfirm = true;
        },

        btnItemMenuDelete(item) {
            this.dataRolDelete = null;
            this.dataMenuDelete = item;
            this.dialogConfirm = true;
        },

        btnItemMenuEdit(item) {
            this.formMenu = item;
            this.dialogMenu = true;
            this.isFormMenuEdit = true;
            
        },
        btnNuevoMenu(nivel) {
            this.menuNivel = nivel;
            this.formMenu = {
                label: null,
                icon: null,
                route: null
            };
            this.isFormMenuEdit = false;
            this.dialogMenu = true;
        },

        btnDialogConfirm() {
            if (this.dataRolDelete) {
                var urlEdit = 'api/rol/' + this.dataRolDelete.id;
                axios
                    .delete(urlEdit)
                    .then(response => {
                        this.snackbar = {
                            status: true,
                            text: 'Eliminado',
                            color: 'primary',
                        }
                        this.getRoles();
                        this.dialogConfirm = false;
                        this.dataRolDelete = null;
                    })
                    .catch(error => {
                        this.snackbar = {
                            status: true,
                            text: error.response.data.message,
                            color: 'error',
                        }
                    })
            }

            if (this.dataMenuDelete) {

                var urlEdit = 'api/menu/' + this.dataMenuDelete.id;
                axios
                    .delete(urlEdit)
                    .then(response => {
                        this.snackbar = {
                            status: true,
                            text: 'Eliminado',
                            color: 'primary',
                        }
                        this.getMenus();
                        this.dialogConfirm = false;
                        this.dataMenuDelete = null;
                    })
                    .catch(error => {
                        this.snackbar = {
                            status: true,
                            text: error.response.data.message,
                            color: 'error',
                        }
                    });

            }
        },

        btnChipEditRol(item) {
            this.dialogChip = true;
            this.isFormRolEdit = true;
            this.formRol = item;
        },

        btnChipNuevoRol() {
            this.formRol = {
                name: null
            }

            this.isFormRolEdit = false,
                this.dialogChip = true;
        },




    },
    components: {
        draggable
    },
}
</script>

